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
 * Version details.
 *
 * @package    report
 * @subpackage tonjoo
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_performance_student_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;

    require_once($CFG->libdir.'/completionlib.php');

    $showonnavigation = has_capability('report/performance_student:view', $context);
    if ($showonnavigation) {
        $url = new moodle_url('/report/performance_student/index.php', array('course'=>$course->id));
        $url_cache = new moodle_url('/report/performance_student/cache.php', array());
        $navigation->add('Performance Report', $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
        $navigation->add('Performance Report - Clear Cache', $url_cache, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}