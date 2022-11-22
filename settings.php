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
 * Settings and links
 *
 * @package    report_performance_student
 * @copyright  2022 Tofan Wahyu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// // // No report settings.
$settings = null;

if ( $hassiteconfig ) {
	$settings = new admin_settingpage( 'report_performance_student', 'Performance Student API' );

	$settings->add(
        new admin_setting_configcheckbox(
            'report_performance_student/enabled',
			get_string( 'setting_enable', 'report_performance_student' ),
			get_string( 'setting_enable_desc', 'report_performance_student' ),
			'1'
        )
    );
        
	$settings->add(
        new admin_setting_configtext(
            'report_performance_student/path',
			'Base Path API',
			'Mandatory before using plugin. Path = http://elok.dev.ugm.ac.id/moodleLA/api',
            'http://elok.dev.ugm.ac.id/moodleLA/api'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'report_performance_student/username',
			'HTTP Auth : Username',
			'Mandatory before using plugin. Username to access',
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'report_performance_student/password',
			'HTTP Auth : Password',
			'Mandatory before using plugin. Password to access',
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'report_performance_student/max_user',
			'Maximum User per Course',
			'Set Maximum user. Ex: 200',
            '200'
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'report_performance_student/event_group',
			'Event Group',
			'Event request body',
            '
            {
                "Course Engagement":[
                   "\\core\\event\\course_user_report_viewed",
                   "\\core\\event\\course_viewed",
                   "\\gradereport_user\\event\\grade_report_viewed",
                   "\\mod_lesson\\event\\question_viewed",
                   "\\mod_page\\event\\course_module_viewed",
                   "\\mod_resource\\event\\course_module_viewed",
                   "\\mod_url\\event\\course_module_viewed"
                ],
                "Social Activity":[
                   "\\assignsubmission_comments\\event\\comment_created",
                   "\\mod_forum\\event\\course_module_viewed",
                   "\\mod_forum\\event\\discussion_viewed",
                   "\\mod_forum\\event\\discussion_created",
                   "\\mod_forum\\event\\post_updated"
                ],
                "Class Activity":[
                   "\\mod_lesson\\event\\course_module_viewed",
                   "\\mod_lesson\\event\\lesson_started",
                   "\\mod_lesson\\event\\lesson_ended",
                   "\\mod_lesson\\event\\question_answered"
                ],
                "Assignment Activity":[
                   "\\assignsubmission_file\\event\\assessable_uploaded",
                   "\\assignsubmission_file\\event\\submission_created",
                   "\\assignsubmission_file\\event\\submission_updated",
                   "\\mod_forum\\event\\assessable_uploaded",
                   "\\mod_assign\\event\\assessable_submitted",
                   "\\mod_assign\\event\\submission_form_viewed"
                ],
                "Quiz Activity":[
                  "\\mod_hvp\\event\\course_module_viewed"
                ],
                "Module Completion":[
                  "\\core\\event\\course_module_completion_updated"
                ]
             }
             
            '
            
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'report_performance_student/event_group_faculty',
			'Event Group Faculty / Teacher',
			'Event request body faculty',
            '
            { 
              [
                {
                  "Course Engagement": [
                    "\\core\\event\\course_user_report_viewed",
                    "\\core\\event\\course_viewed",
                    "\\gradereport_user\\event\\grade_report_viewed",
                    "\\mod_lesson\\event\\question_viewed",
                    "\\mod_page\\event\\course_module_viewed",
                    "\\mod_resource\\event\\course_module_viewed",
                    "\\mod_url\\event\\course_module_viewed"
                  ],
                  "Social Activity": [
                    "\\mod_forum\\event\\course_module_viewed",
                    "\\mod_forum\\event\\discussion_viewed",
                    "\\mod_forum\\event\\discussion_created",
                    "\\mod_forum\\event\\post_updated"
                  ],
                  "Class Activity": [
                    "\\mod_lesson\\event\\course_module_viewed",
                    "\\mod_lesson\\event\\lesson_started",
                    "\\mod_lesson\\event\\lesson_ended",
                    "\\mod_lesson\\event\\question_answered"
                  ],
                  "Assignment Activity": [
                    "\\assignsubmission_comments\\event\\comment_created",
                    "\\assignsubmission_file\\event\\assessable_uploaded",
                    "\\assignsubmission_file\\event\\submission_created",
                    "\\assignsubmission_file\\event\\submission_updated",
                    "\\mod_forum\\event\\assessable_uploaded",
                    "\\mod_assign\\event\\assessable_submitted",
                    "\\mod_assign\\event\\submission_form_viewed"
                  ],
                  "Quiz Activity": [
                    "\\mod_hvp\\event\\course_module_viewed",
                    "\\mod_quiz\\event\\attempt_abandoned",
                    "\\mod_quiz\\event\\attempt_becameoverdue",
                    "\\mod_quiz\\event\\attempt_started",
                    "\\mod_quiz\\event\\attempt_submitted"
                  ],
                  "Module Completion": [
                    "\\core\\event\\course_module_completion_updated"
                  ]
                },
                {
                  "Course Engagement": [
                    "\\core\\event\\course_user_report_viewed",
                    "\\core\\event\\course_viewed",
                    "\\gradereport_user\\event\\grade_report_viewed",
                    "\\mod_lesson\\event\\question_viewed",
                    "\\mod_page\\event\\course_module_viewed",
                    "\\mod_resource\\event\\course_module_viewed",
                    "\\mod_url\\event\\course_module_viewed"
                  ],
                  "Social Activity": [
                    "\\mod_forum\\event\\course_module_viewed",
                    "\\mod_forum\\event\\discussion_viewed",
                    "\\mod_forum\\event\\discussion_created",
                    "\\mod_forum\\event\\post_updated"
                  ],
                  "Class Activity": [
                    "\\mod_lesson\\event\\course_module_viewed",
                    "\\mod_lesson\\event\\lesson_started",
                    "\\mod_lesson\\event\\lesson_ended",
                    "\\mod_lesson\\event\\question_answered"
                  ],
                  "Assignment Activity": [
                    "\\assignsubmission_comments\\event\\comment_created",
                    "\\assignsubmission_file\\event\\assessable_uploaded",
                    "\\assignsubmission_file\\event\\submission_created",
                    "\\assignsubmission_file\\event\\submission_updated",
                    "\\mod_forum\\event\\assessable_uploaded",
                    "\\mod_assign\\event\\assessable_submitted",
                    "\\mod_assign\\event\\submission_form_viewed"
                  ],
                  "Quiz Activity": [
                    "\\mod_hvp\\event\\course_module_viewed",
                    "\\mod_quiz\\event\\attempt_abandoned",
                    "\\mod_quiz\\event\\attempt_becameoverdue",
                    "\\mod_quiz\\event\\attempt_started",
                    "\\mod_quiz\\event\\attempt_submitted"
                  ],
                  "Module Completion": [
                    "\\core\\event\\course_module_completion_updated"
                  ]
                }
              ]
            }
            '
            
        )
    );

    $ADMIN->add('root', new admin_externalpage('report_performance_student_api', 'Performance Student API',
        $CFG->wwwroot."/admin/settings.php?section=report_performance_student", 'report/performance_student:view'));
    $ADMIN->add('root', new admin_externalpage('report_performance_student_cache', 'Performance Student API Cache',
        $CFG->wwwroot."/report/performance_student/cache.php", 'report/performance_student:view'));    

}


