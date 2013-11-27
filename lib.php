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
 * Public API of the link validator report.
 *
 * Defines the APIs used by reports
 *
 * @package    report_linkvalidator
 * @copyright  Catalyst IT 2013
 * @author     Chris Wharton <chrisw@catalyst.net.nz>
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
function report_linkvalidator_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/linkvalidator:view', $context)) {
        $url = new moodle_url('/report/linkvalidator/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_linkvalidator'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Is current user allowed to access this report
 *
 * @access private defined in lib.php for performance reasons
 * @global stdClass $USER
 * @param stdClass $user
 * @param stdClass $course
 * @return array with two elements $all, $today
 */
function report_linkvalidator_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    $all = false;

    if (has_capability('report/linkvalidator:view', $coursecontext)) {
        $today = true;
    }

    if ($today) {
        return array(true);
    }

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return array(true);
        }

    } else if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return array(true);
        }
    }

    return array($today);
}

/**
 * Return a list of page types
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array a list of page types
 */
function report_linkvalidator_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                => get_string('page-x', 'pagetype'),
        'report-*'         => get_string('page-report-x', 'pagetype'),
        'report-linkvalidator-*'     => get_string('page-report-linkvalidator-x',  'report_linkvalidator'),
        'report-linkvalidator-index' => get_string('page-report-linkvalidator-index',  'report_linkvalidator'),
        'report-linkvalidator-user'  => get_string('page-report-linkvalidator-user',  'report_linkvalidator')
    );
    return $array;
}
