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
 * This file contains functions used by the log reports
 *
 * This files lists the functions that are used during the log report generation.
 *
 * @package    report_log
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');

class report_linkvalidator {
    function __construct($course) {
        $this->course = $course;
        $this->modinfo = get_fast_modinfo($course);
        $this->sections = get_all_sections($course->id);
    }

    // build table
    public function print_report() {
        echo html_writer::table($this->build_table());
    }

    // insert data into table
    private function build_table() {
        global $CFG, $OUTPUT;

        $table = new html_table();
        $table->attributes['class'] = 'generaltable boxaligncenter';
        $table->cellpadding = 5;
        $table->id = 'linkvalidator';
        // set up table headings
        $table->head = array(
                get_string('title', 'report_linkvalidator'),
                get_string('url'),
                get_string('error'),
                );

        $prevsecctionnum = 0;
        foreach ($this->modinfo->sections as $sectionnum=>$section) {
            foreach ($section as $cmid) {
                $cm = $this->modinfo->cms[$cmid];

                // get the course section
                if ($prevsecctionnum != $sectionnum) {
                    $sectionrow = new html_table_row();
                    $sectionrow->attributes['class'] = 'section';
                    $sectioncell = new html_table_cell();
                    $sectioncell->colspan = count($table->head);

                    $sectiontitle = get_section_name($this->course, $this->sections[$sectionnum]);

                    $sectioncell->text = $OUTPUT->heading($sectiontitle, 3);
                    $sectionrow->cells[] = $sectioncell;
                    $table->data[] = $sectionrow;

                    $prevsecctionnum = $sectionnum;
                }

                $dimmed = $cm->visible ? '' : 'class="dimmed"';
                $modulename = get_string('modulename', $cm->modname);

                // add a row for each activity in the section
                $reportrow = new html_table_row();

                // activity cell
                $activitycell = new html_table_cell();
                $activitycell->attributes['class'] = 'activity';

                $activityicon = $OUTPUT->pix_icon('icon', $modulename, $cm->modname, array('class'=>'icon'));

                $attributes = array();
                if (!$cm->visible) {
                    $attributes['class'] = 'dimmed';
                }

                $activitycell->text = $activityicon . html_writer::link("$CFG->wwwroot/mod/$cm->modname/view.php?id=$cm->id", format_string($cm->name), $attributes);;

                $reportrow->cells[] = $activitycell;

                // URL cell
                $urlcell = new html_table_cell();
                $urlcell->attributes['class'] = 'url';
                $urlcell->text = ''; 

                // error cell
                $errorcell = new html_table_cell();
                $errorcell->attributes['class'] = 'error';
                $errorcell->text = ''; 

                // fetch url content from activity
                $content = $this->parse_content($cm);
                // validate the urls
                foreach ($content as $url) {
                    $urlcell->text .= html_writer::link($url, format_string($url)) . '</br>';
                    $errorcell->text .= $this->test_url($url) . '</br>';
                }

                $reportrow->cells[] = $urlcell;
                $reportrow->cells[] = $errorcell;


                $table->data[] = $reportrow;
            }
        }


        return $table;
    }

    // get data
    private function get_activity_links($activity) {

    }

    // validate and test the url
    private function test_url($url){
        // first do some quick sanity checks:
        if(!$url || !is_string($url)){
            return 'url is not a string';
        }
        // quick check url is roughly a valid http request: ( http://blah/... ) 
        if( ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url) ){
            return 'url is invalid';
        }
        // the next bit could be slow:
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        $ch = @curl_init($url);
        if($ch === false){
            return false;
        }

        @curl_setopt($ch, CURLOPT_HEADER         ,true);    // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY         ,true);    // dont need body
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);    // catch output (do NOT print!)
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
        @curl_setopt($ch, CURLOPT_MAXREDIRS      ,5);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        //      @curl_setopt($ch, CURLOPT_USERAGENT      ,"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        @curl_exec($ch);

        if(@curl_errno($ch)){   // should be 0
            @curl_close($ch);
            return false;
        }

        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        @curl_close($ch);

        return $code;
    }

    private function filter_results() {

    }

    private function parse_content($coursemodule) {
        /*
        global $DB;

        $content = $DB->get_record($module->modname, array('id'=>$module->instance));

        $urls = array();
        foreach ($content as $field) {
            // a more readably-formatted version of the pattern is on http://daringfireball.net/2010/07/improved_regex_for_matching_urls
            $pattern  = '(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';

            preg_match_all($pattern, 'this is a link <a href="http://testme.com">linky!</a>', $matches);

            var_dump($content, $matches);
            die;
            $urls[] = $matches;
            // search for urls in text fields
        }
        */
        $urls = array('http://catalyst.net.nz', 'http://planetexpress.wgtn.cat-it.co.nz/mongrels', 'notgoodbro');

        return $urls;
    }
}
