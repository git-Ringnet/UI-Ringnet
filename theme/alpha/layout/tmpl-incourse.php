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
 * A two column layout for the alpha theme.
 *
 * @package   theme_alpha
 * @copyright 2022 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 */

use tool_brickfield\local\areas\mod_lesson\name;

defined('MOODLE_INTERNAL') || die();
user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('sidepre-open', PARAM_ALPHA);
user_preference_allow_ajax_update('darkmode-on', PARAM_ALPHA);
user_preference_allow_ajax_update('drawer-open-index', PARAM_BOOL);
user_preference_allow_ajax_update('drawer-open-block', PARAM_BOOL);
require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$draweropenright = false;
$extraclasses = [];

// Moodle 4.0 - Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();
if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}
// End.

// Hidden sidebar
if (theme_alpha_get_setting('turnoffsidebarincourse') == '1') {
    $hiddensidebar = true;
    $navdraweropen = false;
    $extraclasses[] = 'hidden-sidebar';
} else {
    $hiddensidebar = false;
}
// End.

// Dark mode
if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    $draweropenright = (get_user_preferences('sidepre-open', 'true') == 'true');

    if (theme_alpha_get_setting('darkmodetheme') == '1') {
        $darkmodeon = (get_user_preferences('darkmode-on', 'false') == 'true'); //return 1
        if ($darkmodeon) {
            $extraclasses[] = 'theme-dark';
        }
    } else {
        $darkmodeon = false;
    }
} else {
    $navdraweropen = false;
}

if ($navdraweropen && !$hiddensidebar) {
    $extraclasses[] = 'drawer-open-left';
}

$siteurl = $CFG->wwwroot;

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;

$sidecourseblocks = $OUTPUT->blocks('sidecourseblocks');
$hassidecourseblocks = strpos($sidecourseblocks, 'data-block=') !== false;

$blockstopsidebar = $OUTPUT->blocks('sidebartopblocks');
$blocksbottomsidebar = $OUTPUT->blocks('sidebarbottomblocks');

if ($draweropenright && $hasblocks) {
    $extraclasses[] = 'drawer-open-right';
}

if ($PAGE->course->enablecompletion == '1') {
    $extraclasses[] = 'rui-course--enablecompletion';
}

if ($PAGE->course->showactivitydates == '1') {
    $extraclasses[] = 'rui-course--showactivitydates';
}

if ($PAGE->course->visible == '1') {
    $extraclasses[] = 'rui-course--visible';
}

$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

// Moodle 4.0
$courseindex = core_course_drawer();

if (!$courseindex) {
    $courseindexopen = false;
}
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
$PAGE->set_secondary_navigation(false);
$renderer = $PAGE->get_renderer('core');

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

// Don't display new moodle 4.0 secondary menu if old settings region is available
$secondarynavigation = false;
$overflow = '';

if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}
// End.


if ($hassidecourseblocks) {
    $extraclasses[] = 'page-has-blocks';
}

if (!isloggedin()) {
    $isnotloggedin = true;
} else {
    $isnotloggedin = false;
}

// Default moodle setting menu
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;
$iscoursepage = true;
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$re = $PAGE->title;
$arr = explode(':', $re);
$last = $arr[count($arr) - 1];
$get_title = $last;

//Bug to đùng Việt Comments
global $DB;
if($COURSE->id){
$sql = $DB->get_records_sql("SELECT mdl_course_modules.id as module_id
FROM mdl_course_sections
INNER JOIN mdl_course_modules ON mdl_course_modules.section = mdl_course_sections.id
INNER JOIN mdl_modules ON mdl_modules.id = mdl_course_modules.module
WHERE mdl_course_sections.course = $COURSE->id AND mdl_course_modules.deletioninprogress = 0
ORDER BY mdl_course_sections.section, mdl_course_modules.id");
}
$new_array = array();
foreach ($sql as $key => $value) {
    $new_array[] = $value->module_id;
}
$total = count($new_array);
$current = array_search($id, $new_array);
if ($current == 0) {
    $pre = 0;
    $next = 1;
} else if ($current > 0 && $current < $total - 1) {
    $pre = $current - 1;
    $next = $current + 1;
} else {
    $pre = $current - 1;
    $next = $total - 1;
}

$pre = $new_array[$pre];
$next = $new_array[$next];
$urlPre = $DB->get_records_sql("SELECT m.name
FROM mdl_course_modules cm
JOIN mdl_modules m ON cm.module = m.id
WHERE cm.id = $pre");
if($total>2){
$urlNext = $DB->get_records_sql("SELECT m.name
FROM mdl_course_modules cm
JOIN mdl_modules m ON cm.module = m.id
WHERE cm.id = $next");
}
$namePre = reset($urlPre)->name;
$nameNext = reset($urlNext)->name;

$btnUrlPre =  $CFG->wwwroot . "/mod/".$namePre."/view.php?id=".$pre;
$btnUrlNext =  $CFG->wwwroot . "/mod/".$nameNext."/view.php?id=".$next;

$returnurl= new moodle_url('/course/view.php', ['id' => $COURSE->id]);

$title = $PAGE->title;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'darkmodeon' => !empty($darkmodeon),
    'siteurl' => $siteurl,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'sidebartopblocks' => $blockstopsidebar,
    'sidebarbottomblocks' => $blocksbottomsidebar,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'hiddensidebar' => $hiddensidebar,
    'navdraweropen' => $navdraweropen,
    'get_title' => $get_title,
    'draweropenright' => $draweropenright,
    'isnotloggedin' => $isnotloggedin,
    'iscoursepage' => $iscoursepage,
    'title' => $title,
    'btnUrlPre' => $btnUrlPre,
    'btnUrlNext' => $btnUrlNext,
    // Moodle 4.0
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'secondarymoremenu' => $secondarynavigation ?: false,
    'headercontent' => $headercontent,
    'overflow' => $overflow,
    'addblockbutton' => $addblockbutton,
    'returnurl' => $returnurl,
];

// Get and use the course page information banners HTML code, if any course page hints are configured.
$coursepageinformationbannershtml = theme_alpha_get_course_information_banners();
if ($coursepageinformationbannershtml) {
    $templatecontext['coursepageinformationbanners'] = $coursepageinformationbannershtml;
}
// End.

// Load theme settings
$themesettings = new \theme_alpha\util\theme_settings();

$templatecontext = array_merge($templatecontext, $themesettings->global_settings());
$templatecontext = array_merge($templatecontext, $themesettings->footer_settings());

$PAGE->requires->js_call_amd('theme_alpha/rui', 'init');

echo $OUTPUT->render_from_template('theme_alpha/tmpl-incourse', $templatecontext);
