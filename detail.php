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
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_login();
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;

//cek kalo course id & user id gak ketemu return()
$id = optional_param('course', 0, PARAM_INT); // Show detailed info about one check only.
if(empty($id)){
    return;
}
$student_id = optional_param('student', 0, PARAM_INT); // Show detailed info about one check only.
if(empty($student_id)){
    return;
}
$rps_config = get_config('report_performance_student');
$path = $rps_config->path;

global $USER, $PAGE, $DB;
$PAGE->set_context(context_system::instance());
$url = '/report/performance_student/detail.php';
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);

//kondisional buat teacher & student
$html = '';
$role = 'Non Enrolled User';

if (user_has_role_assignment($USER->id,1) || user_has_role_assignment($USER->id,2) || user_has_role_assignment($USER->id,3) || $USER->id == $student_id) {
	$url_api = $rps_config->path.$id.'/user/'.$student_id.'/norm'; 
	$client = new GuzzleHttp\Client();
	try {
		$response = $client->request(
			'POST',
			$url_api,
			array(
				'auth' => array(
					$rps_config->username,
					$rps_config->password
				),
				'content-type' => 'application/json',
				'body' => $rps_config->event_group,
			),
	
		);
		$res_code = $response->getStatusCode();
	} catch (\Throwable $th) {
		$res_code = 500;
	}
	if ($res_code == 200) {

		$data = json_decode($response->getBody(),true);
		
		$html .= '<p>Cache : '.$data['status'].'</p>';
		if ($data['status'] != 'cached') {
			$html.='Moodle is still analyze log, please wait..';
		}
		else{	
			$role = 'Teacher';
			$data_student = []; $nn = 0;
			if (!empty($data['student_stat'][$student_id]['activities'])) {
				foreach ($data['student_stat'][$student_id]['activities'] as $v_data) :
					$data_student[] = ceil($v_data); $nn++;
				endforeach;
				$course = $DB->get_record('course',['id'=> $id]);
				$html.='
					<h1>Moodle Alaytics</h1>
					<h2>Course : '.$course->fullname.'</h2>
					<h2>Student : '.$data['student_stat'][$student_id]['name'].'</h2>
					<canvas id="studentCart"></canvas>';
			}
			else{
				$html.='Moodle is still analyze log, please wait..';
			}
		}
	}
	else{
		$html.='Sorry, Performance not available.';
	}
}
else{
	$role = 'Student';
	$html = 'Sorry, Performance not available.';
}

// admin_externalpage_setup('reportperformance_student', '', null, '', ['pagelayout' => 'course']);
// externalpage_setup('reportperformance_student', '', null, '', ['pagelayout' => 'course']);

echo $OUTPUT->header();
echo $OUTPUT->heading('Student and Teacher performance report');
echo '<hr>';

echo $html;


// $event = \report_performance_student\event\report_viewed::create(['context' => context_system::instance()]);
// $event->trigger();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
	<script>
			var ctx = document.getElementById('studentCart');

			var myChart = new Chart(ctx, {
					type: 'radar',
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
