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
 * performance_student Reset Data report
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

$rps_config = get_config('report_performance_student');
$path 				= $rps_config->path;
$reset_config = $rps_config->reset;
$cache_config = $rps_config->cache;
$url 					= '/report/performance_student/cache.php';
$html					= '';

$reset_table = optional_param('reset_table', 0, PARAM_INT);
$reset_cache = optional_param('reset_cache', 0, PARAM_INT);
if (user_has_role_assignment($USER->id,1) || user_has_role_assignment($USER->id,2) || user_has_role_assignment($USER->id,3)) {
	if (  empty($reset_table) || $reset_table == 0  ) {
		$count_local = $DB->count_records('performance_student', []);
		if ($count_local != 0) {
			$html .= '
				<p>Please click button below to clear Local data performance. </p>
				<a href="'.$CFG->wwwroot.$url.'?reset_table=1" onclick="return confirm(\'Are you sure want to delete local performance data? (data cannot be restored)\')" class="btn btn-primary mb-3">Clear data Local Performance</a>
			';
		}
		else{
			$html.='<p>Local Data info : <span class="label label-warning">Nothing to CLear, Local Data Perfomance already Cleared</span></p>';
		}
	}
	else{
		$DB->delete_records('performance_student', []);
		$html.='<p>Local Data info : <span class="label label-warning">Local Data Perfomance Cleared Successfully</span></p>';
	}
	if (  empty($reset_cache) || $reset_cache == 0  ) {
			$html .= '
				<p>Please click button below to clear Cache API performance. </p>
				<a href="'.$CFG->wwwroot.$url.'?reset_cache=1" onclick="return confirm(\'Are you sure want to delete Cache API Perfomance data? (data cannot be restored)\')" class="btn btn-primary">Clear cache API Performance</a>
			';
	}
	else{
		$rps_config = get_config('report_performance_student');
		$path				= $rps_config->path;
		$username		= $rps_config->username;
		$password		= $rps_config->password;
		$url_api		= $path.'/cache'; 
		$client			= new GuzzleHttp\Client();

		try {
			$response = $client->request(
				'DELETE',
				$url_api,
				array(
					'auth' => array(
						$username,
						$password
					),
					'content-type' => 'application/json'
				),		
			);

			$res_code = $response->getStatusCode();

		} catch (\Throwable $th) {
			$res_code = 500;
		}
		
		if ($res_code == 200) {
			$html.='<p>Cache API info : <span class="label label-warning">Cache API Perfomance Cleared Successfully</span></p>';
		}
		else{
			$html.= 'Moodle is working on delete the cache API, please wait..';
		}
	}
}
else{
	$html .= '<p>Oops, You not allowed to access this page. <a href="'.$CFG->wwwroot.'">Redirect to Dashboard</a> </p>';
}

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);

echo $OUTPUT->header();
echo $OUTPUT->heading('Student and Teacher performance Clear Cache');
echo '<hr>'.$html;
echo $OUTPUT->footer();