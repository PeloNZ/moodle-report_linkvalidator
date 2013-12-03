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
 * Displays different views of the logs.
 *
 * @package    report_linkvalidator
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('/home/chrisw/dev/topnz-moodle/htdocs/config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/report/linkvalidator/lib.php');
require_once($CFG->dirroot.'/report/linkvalidator/locallib.php');
require_once($CFG->libdir.'/adminlib.php');

$id = required_param('id', PARAM_INT); // Course ID

$page        = optional_param('page', '0', PARAM_INT);     // which page to show
$perpage     = optional_param('perpage', '100', PARAM_INT); // how many per page
$filter      = optional_param('filter', 'errorsonly', PARAM_ALPHA);
$logformat   = optional_param('logformat', 'showashtml', PARAM_ALPHA);

$params = array();
if ($id !== 0) {
    $params['id'] = $id;
}
if ($page !== '0') {
    $params['page'] = $page;
}
if ($perpage !== '100') {
    $params['perpage'] = $perpage;
}
if ($logformat !== 'showashtml') {
    $params['logformat'] = $logformat;
}
if ($filter !== 'errorsonly') {
    $params['filter'] = $filter;
}
$url = '/report/linkvalidator/index.php';
$PAGE->set_url($url, $params);
$PAGE->set_pagelayout('report');

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_login($course);

$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_capability('report/linkvalidator:view', $context);

add_to_log($course->id, "course", "report link validator", "report/linkvalidator/index.php?id=$course->id", $course->id);

$strlinks = get_string('links', 'report_linkvalidator');
$stradministration = get_string('administration');
$strreports = get_string('reports');

// Before we close session, make sure we have editing information in session.
$adminediting = optional_param('adminedit', -1, PARAM_BOOL);
if ($PAGE->user_allowed_editing() && $adminediting != -1) {
    $USER->editing = $adminediting;
}
session_get_instance()->write_close();

$userinfo = get_string('allparticipants');
$dateinfo = get_string('alldays');

$report = new report_linkvalidator($course);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_linkvalidator') . ': ' . format_string($course->fullname));
// selector for report type
$report->print_selector_form($params);

switch ($logformat) {
    case 'showashtml':
        $report->print_table($params);
        break;
    case 'downloadascsv':
        $report->download_csv($params);
        exit;
    case 'downloadasods':
        $report->download_ods($params);
        exit;
    case 'downloadasexcel':
        $report->download_xls($params);
        exit;
}

echo $OUTPUT->footer();
