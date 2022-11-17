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

$id 			= optional_param('course', 0, PARAM_INT);
$analyze	= optional_param('analyze', 0, PARAM_INT);

if(empty($id)){
    return;
}

$rps_config 	= get_config('report_performance_student');
$path					= $rps_config->path;
$username			= $rps_config->username;
$password			= $rps_config->password;
$event_group	= $rps_config->event_group;
$url 					= '/report/performance_student/index.php';
$html 				= '';

if (user_has_role_assignment($USER->id,1) || user_has_role_assignment($USER->id,2) || user_has_role_assignment($USER->id,3)) {

	$cek_cached = $DB->get_record('performance_student', ['course_id' => $id]);

	if ( empty($cek_cached) && ( empty($analyze) || $analyze == 0 ) ) {
		$html .= '
			<p>Before performance report can be used this plugin will need to analyze existing log. Please click start to begin the proccess. </p>
			<a href="'.$CFG->wwwroot.$url.'?course='.$id.'&analyze=1" class="btn btn-primary">Start Analyze</a>
		';
	}
	else{

		if(!$cek_cached || empty($cek_cached)){
			$p_record = new stdClass();
			$p_record->course_id 		= $id;
			$p_record->task_id 			= 1;
			$p_record->last_updated = time();

			$DB->insert_record('performance_student', $p_record);
		}

		$url_api	= $path.'/activities/class/'.$id.'/?force=false'; 
		$client		= new GuzzleHttp\Client();

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
					'body' => $event_group
				),		
			);

			$res_code = $response->getStatusCode();

		} catch (\Throwable $th) {
			$res_code = 500;
		}

		if ($res_code == 200) {
			$data	= json_decode($response->getBody(),true);
			$html.= '<p>Cache : '.$data['status'].'</p>';

			if ($data['status'] == 'cached') {
				$html.= 'Moodle is still analyze log, please wait..';
			}
			else{
				$html.= '
				<h4>Class Stats</h4>
				<div class="overflow-auto">
					<table class="table table-striped">
						<thead class="thead-dark">
								<tr>
										<th>Indicator</th>										
										<th class="text-center">Count</th>
										<th class="text-center">Mean</th>
										<th class="text-center">Std</th>
										<th class="text-center">Min</th>										
										<th class="text-center">25%</th>										
										<th class="text-center">50%</th>										
										<th class="text-center">75%</th>										
										<th class="text-center">Max</th>										
								</tr>
						</thead>
						<tbody>';
							foreach ($data['details'] as $key => $v_data) :
								$html.='
								<tr>
										<td>'.$key.'</td>
										<td class="text-center">'.number_format(round($v_data['count'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['mean'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['std'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['min'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['25%'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['50%'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['75%'], 1),1).'</td>
										<td class="text-center">'.number_format(round($v_data['max'], 1),1).'</td>										
								</tr>';
							endforeach;
							$html.='	
						</tbody>
					</table>
				</div>
				<h4>Student Performance</h4>
				<div class="overflow-auto">
					<table class="table table-bordered>
						<thead class="thead-dark">
							<tr>
									<th>ID</th>
									<th>Name</th>									
									<th class="text-center">Course Engagement</th>									
									<th class="text-center">Social Activity</th>									
									<th class="text-center">Class Activity</th>									
									<th class="text-center">Assignment Activity</th>									
									<th class="text-center">Quiz Activity</th>									
									<th class="text-center">Module Completion</th>
									<th>Detail</th>
							</tr>
						</thead>
						<tbody>';
							foreach ($data['stats'] as $key => $v_data) :
								$html.='
								<tr>
										<td>'.$key.'</td>										
										<td class="text-center">'.$v_data['name'].'</td>
										<td class="text-center">'.number_format(round($v_data['activities']['Course Engagement'],1),1).'</td>										
										<td class="text-center">'.number_format(round($v_data['activities']['Social Activity'],1),1).'</td>										
										<td class="text-center">'.number_format(round($v_data['activities']['Class Activity'],1),1).'</td>										
										<td class="text-center">'.number_format(round($v_data['activities']['Assignment Activity'],1),1).'</td>										
										<td class="text-center">'.number_format(round($v_data['activities']['Quiz Activity'],1),1).'</td>										
										<td class="text-center">'.number_format(round($v_data['activities']['Module Completion'],1),1).'</td>
										<td class="text-center"><a href="'.$CFG->wwwroot.'/report/performance_student/detail.php?course='.$id.'&student='.$key.'">Detail</a></td>
								</tr>';
							endforeach;
							$html.='	
						</tbody>
					</table>
				</div>
				';
			}
		}
		else{
			$html.= 'Moodle is still analyze log, please wait..';
		}
	}
}
else{
	$html = 'Sorry, Performance not available.';
}

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);

echo $OUTPUT->header();
echo $OUTPUT->heading('Student and Teacher performance report');
echo '<hr>'.$html;
echo $OUTPUT->footer();