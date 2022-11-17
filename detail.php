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

$rps_config 	= get_config('report_performance_student');
$path 				= $rps_config->path;
$username 		= $rps_config->username;
$password 		= $rps_config->password;
$event_group	= $rps_config->event_group;
$url					= '/report/performance_student/detail.php';
$html					= '';
$count_local	= $DB->count_records('performance_student', []);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);

if ( (user_has_role_assignment($USER->id,1) || user_has_role_assignment($USER->id,2) || user_has_role_assignment($USER->id,3) || $USER->id == $student_id) && $count_local != 0 ) {
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
			$data_student = []; $nn = 0; $li = '';

			if (!empty($data['student_stat'][$student_id]['activities'])) {
				foreach ($data['student_stat'][$student_id]['activities'] as $key => $v_data) :
					$data_student[] = number_format(round($v_data,1),1); $nn++;
					$li .= '
					<li class="row">
						
						'.$key.'<br>
						<div class="bar_container">
							<span class="bar" data-bar=\'{ "color": rgb(251,63,63) }\'>
								<span class="pct">'.(number_format(round($v_data,1),1)/4*100).'%</span><span class="marker"><i class="fa fa-map-marker"></i></span>
							</span>
						</div>
							<span class="low">Low</span>
							<span class="hight">Hight</span>
						<div class="right">
							<span class="label-percent">
						'.(number_format(round($v_data,1),1)/4*100).'%
							</span>
						</div>
					</li>
					';
				endforeach;
				
				$course = $DB->get_record('course',['id'=> $id]);
				
				$html.= '
					<h2>Moodle Alaytics</h2>
					<h4>Course : '.$course->fullname.'</h4>
					<h4>Student : '.$data['student_stat'][$student_id]['name'].'</h4>
					<div class="chart-container container row" style="position: relative;">
						<canvas id="studentCart"></canvas>
						<ul id="skills">
							'.$li.'
							
						</ul>
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
echo '
<style>

ul {
  list-style-type: none;
}
#skills {
  margin: 0 auto;
  width: 90%;
}
#skills li {
  position: relative;
  margin-bottom: 32px;
  padding-left: 6px;
}
.bar_container,
.bar {
  position: absolute;
  left: 0;
  height: 5px;
  border-radius: 5px;
  content: "";
}
.bar_container {
  bottom: -8px;
  width: 90%;
  background: rgb(251,63,63);
	background: linear-gradient(90deg, rgba(251,63,63,1) 0%, rgba(250,110,35,1) 25%, rgba(249,187,0,1) 50%, rgba(133,238,66,1) 80%, rgba(0,110,29,1) 100%);
  text-align: right;
}
.bar {
  top: 0;
}
.pct {
  position: absolute;
  top: -19px;
  right: 0;
  opacity: 0;
  transition: opacity 0.3s linear;
	font-color:#000;
}
.low {
	top: 26px;
	position: relative;
	left: 0%;
	font-size: 10px;
}
.hight {
	top: 26px;
	position: relative;
	right: -83%;
	font-size: 10px;
}
.marker {
	position: relative;
	top: -20px;
}
.label-percent {
	font-weight: 700;
}
</style>';
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

		$(".bar").each(function() {
    
			var $bar = $(this),
					$pct = $bar.find(".pct"),
					data = $bar.data("bar");
			
			setTimeout(function() {
				
				$bar
					.css("background-color", data.color)
					.animate({
						"width": $pct.html()
						
				}, data.speed || 2000, function() {
					
					$pct.css({
						"color": data.color,
						"opacity": 1,
						"display": "none"
					});
					
				});
				
			}, data.delay || 0);
			
		});


</script>

<?php 
echo $OUTPUT->footer();
