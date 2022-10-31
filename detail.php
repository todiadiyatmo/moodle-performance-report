<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * performance_student overview report
 *
 * @package    report_performance_student
 * @copyright  2022 Tofan Wahyu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require('vendor/autoload.php');
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;

require_login();

global $USER, $PAGE, $DB;

$id = optional_param('course', 0, PARAM_INT);
if(empty($id)){
    return;
}

$student_id = optional_param('student', 0, PARAM_INT);
if(empty($student_id)){
    return;
}

$rps_config = get_config('report_performance_student');
$path 			= $rps_config->path;
$username 	= $rps_config->username;
$password 	= $rps_config->password;
$event_group= $rps_config->event_group;
$url				= '/report/performance_student/detail.php';
$html				= '';

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);

if (user_has_role_assignment($USER->id,1) || user_has_role_assignment($USER->id,2) || user_has_role_assignment($USER->id,3) || $USER->id == $student_id) {
	$url_api = $rps_config->path.'/activities/class/'.$id.'/user/'.$student_id; 
	$client = new GuzzleHttp\Client();

	try {
		$response = $client->request(
			'POST',
			$url_api,
			array(
				'auth' => array(
					$username,
					$password
				),
				'content-type' => 'application/json',
				'body' => $event_group,
			),
		);

		$res_code = $response->getStatusCode();

	} catch (\Throwable $th) {
		$res_code = 500;
	}
		
	if ($res_code == 200) {
		$data = json_decode($response->getBody(),true);		
		$html.= '<p>Cache : '.$data['status'].'</p>';

		if ($data['status'] != 'cached') {
			$html.='Moodle is still analyze log, please wait..';
		}
		else{
			$data_student = []; $nn = 0;

			if (!empty($data['student_stat'][$student_id]['activities'])) {
				foreach ($data['student_stat'][$student_id]['activities'] as $v_data) :
					$data_student[] = number_format(round($v_data,1),1); $nn++;
				endforeach;
				
				$course = $DB->get_record('course',['id'=> $id]);
				
				$html.= '
					<h2>Moodle Alaytics</h2>
					<h4>Course : '.$course->fullname.'</h4>
					<h4>Student : '.$data['student_stat'][$student_id]['name'].'</h4>
					<div class="chart-container container" style="position: relative;">
						<canvas id="studentCart"></canvas>
					</div>
				';
			}
			else{
				$html.= 'Moodle is still analyze log, please wait..';
			}
		}
	}
	else{
		$html.= 'Sorry, Performance not available.';
	}
}
else{
	$html = 'Sorry, Performance not available.';
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Student and Teacher performance report');
echo '<hr>'.$html;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
		var ctx = document.getElementById('studentCart');

		var myChart = new Chart(ctx, {
				type: 'radar',
				responsive: true,
				data: {
					"labels": [
						"Course Engagement", "Social Activity", "Class Activity", "Assignment Activity", "Quiz Activity", "Module Completion"
					],
					"datasets": [
						{
							"label": "Student",
								"data": <?php echo json_encode( $data_student ); ?>,									
								"fill": "true", "backgroundColor": "rgba(255, 99, 132, 0.2)", "borderColor": "rgb(255, 99, 132)", "pointBackgroundColor": "rgb(255, 99, 132)", "pointBorderColor": "#fff", "pointHoverBackgroundColor": "#fff", "pointHoverBorderColor": "rgb(255, 99, 132)"
						},
						{
							"label": "Class",
							"data": [2, 2, 2, 2, 2, 2],
							"fill": "true",
							"backgroundColor": "rgba(54, 162, 235, 0.2)", "borderColor": "rgb(54, 162, 235)", "pointBackgroundColor": "rgb(54, 162, 235)", "pointBorderColor": "#fff", "pointHoverBackgroundColor": "#fff", "pointHoverBorderColor": "rgb(54, 162, 235)"
						}
					]
				},
				options: {
					scales: {
						r: {
								suggestedMin: 0,
								suggestedMax: 4
						}
					}
				}
		});

</script>

<?php 
echo $OUTPUT->footer();
