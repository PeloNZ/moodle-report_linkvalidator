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
 * Links and settings
 *
 * Contains settings used by logs report.
 *
 * @package    report_linkvalidator
 * @copyright  Catalyst IT 2013
 * @author     Chris Wharton <chrisw@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$temp = new admin_settingpage('report_linkvalidator', get_string('settingstitle', 'report_linkvalidator'));

$temp->add(new admin_setting_configtext('report_linkvalidator/timeout', get_string('timeout', 'report_linkvalidator'), get_string('timeout_desc', 'report_linkvalidator'), 3, PARAM_INT, 2));

$ADMIN->add('reports', $temp);
