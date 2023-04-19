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

namespace theme_alpha\output;

use html_writer;
use stdClass;
use moodle_url;
use context_course;
use core_course_list_element;
use custom_menu;
use action_menu_filler;
use action_menu_link_secondary;
use action_menu;
use action_link;
use core_text;
use coding_exception;
use navigation_node;
use context_header;
use pix_icon;
use renderer_base;
use theme_config;
use get_string;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_alpha
 * @copyright  2022 Marcin Czaja (https://rosea.io)
 * @license    Commercial https://themeforest.net/licenses
 */
class core_renderer extends \core_renderer
{

    public function edit_button(moodle_url $url)
    {
        if ($this->page->theme->haseditswitch) {
            return;
        }
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }
        $button = new \single_button($url, $editstring, 'post', ['class' => 'btn btn-primary']);
        return $this->render_single_button($button);
    }

    /**
     * The standard tags (meta tags, links to stylesheets and JavaScript, etc.)
     * that should be included in the <head> tag. Designed to be called in theme
     * layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_end_of_body_html()
    {
        $output = parent::standard_end_of_body_html();

        $googleanalyticscode = "<script
                                    async
                                    src='https://www.googletagmanager.com/gtag/js?id=GOOGLE-ANALYTICS-CODE'>
                                </script>
                                <script>
                                    window.dataLayer = window.dataLayer || [];
                                    function gtag() {
                                        dataLayer.push(arguments);
                                    }
                                    gtag('js', new Date());
                                    gtag('config', 'GOOGLE-ANALYTICS-CODE');
                                </script>";

        $theme = theme_config::load('alpha');

        if (!empty($theme->settings->googleanalytics)) {
            $output .= str_replace("GOOGLE-ANALYTICS-CODE", trim($theme->settings->googleanalytics), $googleanalyticscode);
        }

        return $output;
    }

    /**
     *
     * Method to load theme element form 'layout/elements' folder
     *
     */
    public function theme_part($name, $vars = array())
    {

        global $CFG, $SITE, $USER;

        $OUTPUT = $this;
        $PAGE = $this->page;
        $COURSE = $this->page->course;
        $element = $name . '.php';
        $candidate1 = $this->page->theme->dir . '/layout/parts/' . $element;

        // Require for child theme
        if (file_exists($candidate1)) {
            $candidate = $candidate1;
        } else {
            $candidate = $CFG->dirroot . theme_alpha_theme_dir() . '/alpha/layout/parts/' . $element;
        }

        if (!is_readable($candidate)) {
            debugging("Could not include element $name.");
            return;
        }

        extract($vars);
        ob_start();
        include($candidate);
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Renders the custom menu
     *
     * @param custom_menu $menu
     * @return mixed
     */
    protected function render_custom_menu(custom_menu $menu)
    {
        if (!$menu->has_children()) {
            return '';
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/moremenu_children', $context);
        }

        return $content;
    }

    /**
     * Outputs the favicon urlbase.
     *
     * @return string an url
     */
    public function favicon()
    {
        $theme = theme_config::load('alpha');

        $favicon = $theme->setting_file_url('favicon', 'favicon');

        if (!empty(($favicon))) {
            return $favicon;
        }

        return parent::favicon();
    }

    public function render_lang_menu()
    {
        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';
        $menu = new custom_menu;

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
            foreach ($menu->get_children() as $item) {
                $context = $item->export_for_template($this);
            }

            $context->currentlangname = array_search($currentlang, $langs);

            if (isset($context)) {
                return $this->render_from_template('theme_alpha/lang_menu', $context);
            }
        }
    }

    public static function get_course_progress_count($course, $userid = 0)
    {
        global $USER;

        // Make sure we continue with a valid userid.
        if (empty($userid)) {
            $userid = $USER->id;
        }

        $completion = new \completion_info($course);

        // First, let's make sure completion is enabled.
        if (!$completion->is_enabled()) {
            return null;
        }

        if (!$completion->is_tracked_user($userid)) {
            return null;
        }

        // Before we check how many modules have been completed see if the course has.
        if ($completion->is_course_complete($userid)) {
            return 100;
        }

        // Get the number of modules that support completion.
        $modules = $completion->get_activities();
        $count = count($modules);
        if (!$count) {
            return null;
        }

        // Get the number of modules that have been completed.
        $completed = 0;
        foreach ($modules as $module) {
            $data = $completion->get_data($module, true, $userid);
            $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
        }

        return ($completed / $count) * 100;
    }

    /**
     * TODO
     * Outputs the course progress donut if course completion is on.
     *
     * @return string Markup.
     */
    protected function courseprogress($course)
    {
        global $USER;
        $theme = \theme_config::load('alpha');

        $output = '';
        $courseformat = course_get_format($course);

        if (get_class($courseformat) != 'format_tiles') {
            $completion = new \completion_info($course);

            // Start Course progress count
            // Make sure we continue with a valid userid.
            if (empty($userid)) {
                $userid = $USER->id;
            }
            $completion = new \completion_info($course);

            // Get the number of modules that support completion.
            $modules = $completion->get_activities();
            $count = count($modules);
            if (!$count) {
                return null;
            }

            // Get the number of modules that have been completed.
            $completed = 0;
            foreach ($modules as $module) {
                $data = $completion->get_data($module, true, $userid);
                $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
            }
            $progresscountc = $completed;
            $progresscounttotal = $count;
            // End. progress count

            if ($completion->is_enabled()) {
                $templatedata = new \stdClass;
                $templatedata->progress = \core_completion\progress::get_course_progress_percentage($course);
                $templatedata->progresscountc = $progresscountc;
                $templatedata->progresscounttotal = $progresscounttotal;

                if (!is_null($templatedata->progress)) {
                    $templatedata->progress = floor($templatedata->progress);
                } else {
                    $templatedata->progress = 0;
                }
                if (get_config('theme_alpha', 'courseprogressbar') == 1) {
                    $progressbar = '<div class="rui-course-progresschart">' . $this->render_from_template('theme_alpha/progress-chart', $templatedata) . '</div>';
                    if (has_capability('report/progress:view', \context_course::instance($course->id))) {
                        $courseprogress = new \moodle_url('/report/progress/index.php');
                        $courseprogress->param('course', $course->id);
                        $courseprogress->param('sesskey', sesskey());
                        $output .= html_writer::link($courseprogress, $progressbar, array('class' => 'rui-course-progressbar'));
                    } else {
                        $output .= $progressbar;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Get the user profile pic
     *
     * @param null $userobject
     * @param int $imgsize
     * @return moodle_url
     * @throws \coding_exception
     */
    protected function get_user_picture($userobject = null, $imgsize = 60)
    {
        global $USER, $PAGE;

        if (!$userobject) {
            $userobject = $USER;
        }

        $userimg = new \user_picture($userobject);

        $userimg->size = $imgsize;

        return $userimg->get_url($PAGE);
    }


    /**
     * TODO: Teachers string
     * Returns HTML to display course contacts.
     *
     */
    protected function course_contacts()
    {
        global $CFG, $COURSE, $DB;
        $course = $DB->get_record('course', ['id' => $COURSE->id]);
        $course = new core_course_list_element($course);
        $instructors = $course->get_course_contacts();

        if (!empty($instructors)) {
            $content = html_writer::start_div('course-teachers-box mt-4');

            // $content .= html_writer::start_tag('h5', array('class'=>'course-contact-title'));
            // $content .= html_writer::end_tag('h5');

            foreach ($instructors as $key => $instructor) {
                $name = $instructor['username'];
                $role = $instructor['rolename'];
                $roleshortname = $instructor['role']->shortname;

                $url = $CFG->wwwroot . '/user/profile.php?id=' . $key;
                $picture = $this->get_user_picture($DB->get_record('user', array('id' => $key)));

                $content .= "<div class='course-contact-title-item'><a href='{$url}' 'title='{$name}' class='course-contact rui-user-{$roleshortname}'>";
                $content .= "<img src='{$picture}' class='course-teacher-avatar' alt='{$name}' title='{$name} - {$role}' data-toggle='tooltip'/>";
                $content .= "</a></div>";
            }

            $content .= html_writer::end_div(); // teachers-box
            return $content;
        }
    }
    public function course_navigate()
    {
        global $CFG, $COURSE, $DB, $USER;
        $course = $DB->get_record('course', ['id' => $COURSE->id]);
        // Kiểm tra role có phải là admin hay không
        // Kiểm tra role có phải là teacher hay không
        // sau đó kiểm tra có phải là người tạo khóa học hay không
        if (is_siteadmin()) {
            $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id;
            // '&returnto=catmanage'
        } else if (is_teacher()) {
            if (is_course_creator($COURSE->id)) {
                $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id ;
                // '&returnto=catmanage'
            } else {
                $urledit = $CFG->wwwroot . '/course/show.php?id=' . $course->id;
            }
        } else {
            $urledit = $CFG->wwwroot . '/course/show.php?id=' . $course->id;
        }

        if($course->id!=1)
        {
            $content = html_writer::start_div('course-navigation');
            //$urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id . '&returnto=catmanage';
            $urlcontent = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
            $urlparticipant = $CFG->wwwroot . '/user/index.php?id=' . $course->id;
            $urlbades = $CFG->wwwroot . '/badges/view.php?type=2&id=' . $course->id;
            $urlgrades = $CFG->wwwroot . '/grade/report/grader/index.php?id=' . $course->id;
            //Thảo luận
            $id_khoa_hoc = $course->id; // thay bằng id khóa học cần truy vấn
            $sql = sprintf(
                "
        SELECT f.id
        FROM mdl_forum f
        INNER JOIN mdl_course_modules cm ON f.id = cm.instance
        WHERE cm.module = (SELECT id FROM mdl_modules WHERE name = 'forum')
        AND cm.course = %d",
                $id_khoa_hoc
            );
            $idforum = $DB->get_field_sql($sql); // lấy giá trị của cột đầu tiên trong kết quả truy vấn
            $urlforum = $CFG->wwwroot . '/mod/forum/view.php?f=' . $idforum;
    
            $pages = new stdClass();
            $pages->urledit = ['title' => 'Thông tin', 'url' => $urledit];
            $pages->urlcontent = ['title' => 'Bài học', 'url' => $urlcontent];
            $pages->urlforum = ['title' => 'Thảo luận', 'url' => $urlforum];
            if (is_siteadmin() || is_teacher()) {
                $pages->urlparticipant = ['title' => 'Thành viên', 'url' => $urlparticipant];
                $pages->urlbades = ['title' => 'Chứng chỉ', 'url' => $urlbades];
                $pages->urlgrades = ['title' => 'Điểm số', 'url' => $urlgrades];
            }
    
            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
            $urltest = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
            $content .= "<nav class='navbar navbar-expand-lg navbar-light'>
    <div id='navbarNav'>
      <ul class='navbar-nav'>";
            foreach ($pages as $key => $value) {
                $active = $urltest === $value['url'] ? 'active' : 'before';
                $content .=
                    "<li class='nav-item {$active}  mr-2'>
            <a class='nav-link title' href='{$value['url']}'>{$value['title']} <span class='sr-only'>(current)</span></a>
            </li>";
            }
            $content .= "</ul>
        </div>
      </nav> <hr/>";
            //     echo '<br/>';
            $content .= html_writer::end_div(); // navigation-box
        }
        else
        {
            $content="";
        }
        return $content;
    }
    /**
     * TODO:
     * Returns HTML to display course summary.
     *
     */
    protected function course_summary()
    {
        global $COURSE;
        $output = '';
        $output .= html_writer::start_div('rui-course-desc mt-3');
        $output .= $COURSE->summary;
        $output .= html_writer::end_div();
        return $output;
    }

    /**
     * Outputs the pix url base
     *
     * @return string an URL.
     */
    public function get_pix_image_url_base()
    {
        global $CFG;

        return $CFG->wwwroot . "/theme/alpha/pix";
    }


    /**
     * TODO: alt dla img
     * Returns HTML to display course hero.
     *
     */
    public function course_hero()
    {
        global $CFG, $COURSE, $DB;

        $course = $DB->get_record('course', ['id' => $COURSE->id]);

        $course = new core_course_list_element($course);

        $courseimage = '';
        $imageindex = 1;
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();

            $url = new moodle_url("$CFG->wwwroot/pluginfile.php" . '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), ['forcedownload' => !$isimage]);

            if ($isimage) {
                $courseimage = $url;
            }

            if ($imageindex == 2) {
                break;
            }

            $imageindex++;
        }

        $html = '';
        // Create html for header.
        if (!empty($courseimage)) {
            $html .= '<img src=' . $courseimage . ' class="course-hero-img img-fluid w-100" alt="">';
        }
        return $html;
    }


    /**
     * Breadcrumbs
     *
     */
    public function breadcrumbs()
    {
        global $USER, $COURSE, $CFG;

        $header = new stdClass();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->courseheader = $this->course_header();
        $html = $this->render_from_template('theme_alpha/breadcrumbs', $header);

        return $html;
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function simple_header()
    {

        global $USER, $COURSE, $CFG, $PAGE;
        $html = null;

        if (
            $this->page->include_region_main_settings_in_header_actions() &&
            !$this->page->blocks->is_block_present('settings')
        ) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(
                html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
                )
            );
        }

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();

        if ($PAGE->pagelayout != 'admin') {
            $html .= $this->render_from_template('theme_alpha/header', $header);
        }
        if ($PAGE->pagelayout == 'admin') {
            $html .= $this->render_from_template('theme_alpha/header_admin', $header);
        }

        // MODIFICATION START:
        // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (
            get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 1
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $this->page->has_set_url()
            && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'course-guestaccess-infobox alert alert-warning'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-pull-left icon d-inline-flex mr-3'));
            $html .= get_string(
                'showhintcourseguestaccesssettinggeneral',
                'theme_alpha',
                array('role' => role_get_name(get_guest_role()))
            );
            $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
        }
        // End.
        // MODIFICATION START:
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (
            get_config('theme_alpha', 'page-header-headings') == 1 && $COURSE->visible == false &&
            $this->page->has_set_url() && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'course-hidden-infobox alert alert-warning'));
            $html .= html_writer::tag('i', null, array('class' => 'far fa-eye-slash fa-pull-left icon d-inline-flex mr-3'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag(
                    'div',
                    get_string(
                        'showhintcoursehiddensettingslink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id)
                    )
                );
            }
            $html .= html_writer::end_tag('div');
        }
        // End.
        // MODIFICATION START.
        if (get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 1) {
            // Check if the user did a role switch.
            // If not, adding this section would make no sense and, even worse,
            // user_get_user_navigation_info() will throw an exception due to the missing user object.
            if (is_role_switched($COURSE->id)) {
                // Get the role name switched to.
                $opts = \user_get_user_navigation_info($USER, $this->page);
                $role = $opts->metadata['rolename'];
                // Get the URL to switch back (normal role).
                $url = new moodle_url(
                    '/course/switchrole.php',
                    array(
                        'id' => $COURSE->id,
                        'sesskey' => sesskey(),
                        'switchrole' => 0,
                        'returnurl' => $this->page->url->out_as_local_url(false)
                    )
                );
                $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning mt-4'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
                $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
                        <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
                        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
                        </svg>';
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
                $html .= get_string('switchedroleto', 'theme_alpha');
                // Give this a span to be able to address via CSS.
                $html .= html_writer::tag('strong', $role, array('class' => 'switched-role px-2'));

                // Return to normal role link.
                $html .= html_writer::tag(
                    'a',
                    get_string('switchrolereturn', 'core'),
                    array('class' => 'switched-role-backlink d-block', 'href' => $url)
                );
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // End.    
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (
            get_config('theme_alpha', 'showhintcoursehidden') == 'yes'
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == false
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning mt-4'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
            <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
            <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag(
                    'div',
                    get_string(
                        'showhintcoursehiddensettingslink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id)
                    )
                );
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }

        // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (
            get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 'yes'
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-guestaccess-infobox alert alert-warning mt-4'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path>
            <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string(
                'showhintcourseguestaccesssettinggeneral',
                'theme_alpha',
                array('role' => role_get_name(get_guest_role()))
            );
            $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }


        return $html;
    }



    public function display_course_progress()
    {
        global $USER, $COURSE, $CFG, $PAGE;
        $html = null;
        $html .= $this->courseprogress($this->page->course);
        return $html;
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header()
    {
        global $USER, $COURSE, $CFG, $PAGE;
        $theme = \theme_config::load('alpha');
        $html = null;
        $pagetype = $this->page->pagetype;
        $homepage = get_home_page();
        $homepagetype = null;
        // Add a special case since /my/courses is a part of the /my subsystem.
        if ($homepage == HOMEPAGE_MY || $homepage == HOMEPAGE_MYCOURSES) {
            $homepagetype = 'my-index';
        } else if ($homepage == HOMEPAGE_SITE) {
            $homepagetype = 'site-index';
        }
        if (
            $this->page->include_region_main_settings_in_header_actions() &&
            !$this->page->blocks->is_block_present('settings')
        ) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(
                html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
                )
            );
        }

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();
        // if (!empty($pagetype) && !empty($homepagetype) && $pagetype == $homepagetype) {
        //     $header->welcomemessage = \core_user::welcome_message();
        // }



        // MODIFICATION START:
        // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (
            get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 1
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $this->page->has_set_url()
            && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'course-guestaccess-infobox alert alert-warning'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-pull-left icon d-inline-flex mr-3'));
            $html .= get_string(
                'showhintcourseguestaccesssettinggeneral',
                'theme_alpha',
                array('role' => role_get_name(get_guest_role()))
            );
            $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
        }
        // End.
        // MODIFICATION START:
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (
            get_config('theme_alpha', 'showhintcoursehidden') == 1 && $COURSE->visible == false &&
            $this->page->has_set_url() && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'course-hidden-infobox alert alert-warning'));
            $html .= html_writer::tag('i', null, array('class' => 'far fa-eye-slash fa-pull-left icon d-inline-flex mr-3'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag(
                    'div',
                    get_string(
                        'showhintcoursehiddensettingslink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id)
                    )
                );
            }
            $html .= html_writer::end_tag('div');
        }
        // End.
        // MODIFICATION START.
        if (get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 1) {
            // Check if the user did a role switch.
            // If not, adding this section would make no sense and, even worse,
            // user_get_user_navigation_info() will throw an exception due to the missing user object.
            if (is_role_switched($COURSE->id)) {
                // Get the role name switched to.
                $opts = \user_get_user_navigation_info($USER, $this->page);
                $role = $opts->metadata['rolename'];
                // Get the URL to switch back (normal role).
                $url = new moodle_url(
                    '/course/switchrole.php',
                    array(
                        'id' => $COURSE->id,
                        'sesskey' => sesskey(),
                        'switchrole' => 0,
                        'returnurl' => $this->page->url->out_as_local_url(false)
                    )
                );
                $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning wrapper-md'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
                $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
                        <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
                        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
                        </svg>';
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
                $html .= get_string('switchedroleto', 'theme_alpha');
                // Give this a span to be able to address via CSS.
                $html .= html_writer::tag('strong', $role, array('class' => 'switched-role px-2'));

                // Return to normal role link.
                $html .= html_writer::tag(
                    'a',
                    get_string('switchrolereturn', 'core'),
                    array('class' => 'switched-role-backlink d-block', 'href' => $url)
                );
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // End.    
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (
            get_config('theme_alpha', 'showhintcoursehidden') == 'yes'
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == false
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning wrapper-md'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
            <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
            <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag(
                    'div',
                    get_string(
                        'showhintcoursehiddensettingslink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id)
                    )
                );
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
        // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (
            get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 'yes'
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-guestaccess-infobox alert alert-warning mt-4'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path>
            <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string(
                'showhintcourseguestaccesssettinggeneral',
                'theme_alpha',
                array('role' => role_get_name(get_guest_role()))
            );
            $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }

        //$html .= $this->courseprogress($this->page->course);

        
  
        $html .= html_writer::start_tag('div', array('class' => 'rui-course-navigation'));
        $html .= $this->course_navigate();
        $html .= html_writer::end_tag('div'); //rui-course-navigation

        $html .= html_writer::start_tag('div', array('class' => 'rui-course-header'));
        if ($PAGE->theme->settings->cccteachers == 1) {
            $html .= $this->course_contacts();
        }
        $html .= $this->render_from_template('theme_alpha/header', $header);
        if ($PAGE->theme->settings->ipcoursesummary == 1) {
            $html .= $this->course_summary();
        }
        $html .= html_writer::end_tag('div'); //rui-course-header

        return $html;
    }


    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function clean_header()
    {
        global $USER, $COURSE, $CFG, $PAGE;
        $theme = \theme_config::load('alpha');
        $html = null;
        $pagetype = $this->page->pagetype;
        $homepage = get_home_page();
        $homepagetype = null;
        // Add a special case since /my/courses is a part of the /my subsystem.
        if ($homepage == HOMEPAGE_MY || $homepage == HOMEPAGE_MYCOURSES) {
            $homepagetype = 'my-index';
        } else if ($homepage == HOMEPAGE_SITE) {
            $homepagetype = 'site-index';
        }
        if (
            $this->page->include_region_main_settings_in_header_actions() &&
            !$this->page->blocks->is_block_present('settings')
        ) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(
                html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
                )
            );
        }

        $header = new stdClass();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();

        $html .= $this->render_from_template('theme_alpha/header', $header);

        // MODIFICATION START:
        // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (
            get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 1
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $this->page->has_set_url()
            && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'course-guestaccess-infobox alert alert-warning wrapper-md mb-0'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-pull-left icon d-inline-flex mr-3'));
            $html .= get_string(
                'showhintcourseguestaccesssettinggeneral',
                'theme_alpha',
                array('role' => role_get_name(get_guest_role()))
            );
            $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
        }
        // End.
        // MODIFICATION START:
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (
            get_config('theme_alpha', 'showhintcoursehidden') == 1 && $COURSE->visible == false &&
            $this->page->has_set_url() && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'course-hidden-infobox alert alert-warning wrapper-md mb-0'));
            $html .= html_writer::tag('i', null, array('class' => 'far fa-eye-slash fa-pull-left icon d-inline-flex mr-3'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag(
                    'div',
                    get_string(
                        'showhintcoursehiddensettingslink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id)
                    )
                );
            }
            $html .= html_writer::end_tag('div');
        }
        // End.
        // MODIFICATION START.
        if (get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 1) {
            // Check if the user did a role switch.
            // If not, adding this section would make no sense and, even worse,
            // user_get_user_navigation_info() will throw an exception due to the missing user object.
            if (is_role_switched($COURSE->id)) {
                // Get the role name switched to.
                $opts = \user_get_user_navigation_info($USER, $this->page);
                $role = $opts->metadata['rolename'];
                // Get the URL to switch back (normal role).
                $url = new moodle_url(
                    '/course/switchrole.php',
                    array(
                        'id' => $COURSE->id,
                        'sesskey' => sesskey(),
                        'switchrole' => 0,
                        'returnurl' => $this->page->url->out_as_local_url(false)
                    )
                );
                $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning wrapper-md mb-0'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
                $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
                        <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
                        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
                        </svg>';
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
                $html .= get_string('switchedroleto', 'theme_alpha');
                // Give this a span to be able to address via CSS.
                $html .= html_writer::tag('strong', $role, array('class' => 'switched-role px-2'));

                // Return to normal role link.
                $html .= html_writer::tag(
                    'a',
                    get_string('switchrolereturn', 'core'),
                    array('class' => 'switched-role-backlink d-block', 'href' => $url)
                );
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // End.    
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (
            get_config('theme_alpha', 'showhintcoursehidden') == 'yes'
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == false
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning wrapper-md mb-0'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
            <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
            <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag(
                    'div',
                    get_string(
                        'showhintcoursehiddensettingslink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id)
                    )
                );
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }

        // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (
            get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 'yes'
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)
        ) {
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-guestaccess-infobox alert alert-warning wrapper-md mb-0'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path>
            <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string(
                'showhintcourseguestaccesssettinggeneral',
                'theme_alpha',
                array('role' => role_get_name(get_guest_role()))
            );
            $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }

        return $html;
    }


    public function courseheadermenu()
    {
        global $PAGE, $COURSE, $USER, $DB;

        $headerlinks = '';

        $editcog = html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        // Header Menus for Users.
        if ($PAGE->pagelayout !== 'coursecategory' && $PAGE->pagetype !== 'course-management' && $PAGE->pagetype !== 'course-delete') {
            $course = $this->page->course;
            $showcoursenav = true;
            $context = context_course::instance($course->id);
            $hasgradebookshow = $PAGE->course->showgrades == 1;

            $hascompetencyshow = get_config('core_competency', 'enabled');
            $isteacher = has_capability('moodle/course:viewhiddenactivities', $context);

            $gradeurl = '';
            $gradestatus = '';
            // Show for student in course.
            if ($COURSE->id > 1 && isloggedin() && !isguestuser() && has_capability('gradereport/user:view', $context) && $hasgradebookshow) {
                $gradeurl = new moodle_url('/grade/report/user/index.php', array('id' => $PAGE->course->id));
                $gradestatus = true;
            }
            // Show for teacher in course.
            if ($COURSE->id > 1 && has_capability('gradereport/grader:view', $context) && isloggedin() && !isguestuser()) {
                $gradeurl = new moodle_url('/grade/report/grader/index.php', array('id' => $PAGE->course->id));
                $gradestatus = true;
            }

            // TODO: Sprawdzic i ewentualnie usunac. Easy Enrollment Integration.
            $globalhaseasyenrollment = enrol_get_plugin('easy');
            $coursehaseasyenrollment = '';
            $easycodelink = '';
            $easycodetitle = '';
            if ($globalhaseasyenrollment) {
                $coursehaseasyenrollment = $DB->record_exists('enrol', array('courseid' => $COURSE->id, 'enrol' => 'easy'));
                $easyenrollinstance = $DB->get_record('enrol', array('courseid' => $COURSE->id, 'enrol' => 'easy'));
            }
            if ($coursehaseasyenrollment && isset($COURSE->id) && $COURSE->id > 1) {
                $easycodetitle = get_string('header_coursecodes', 'enrol_easy');
                $easycodelink = new moodle_url('/enrol/editinstance.php', array('courseid' => $PAGE->course->id, 'id' => $easyenrollinstance->id, 'type' => 'easy'));
            }

            // Header links on course pages.

            $course = $this->page->course;
            $context = context_course::instance($course->id);
            $hasadminlink = has_capability('moodle/site:configview', $context);
           
            if ($COURSE->id > 1 && isloggedin() && !isguestuser() && is_enrolled($context, $USER->id, '', true) || is_siteadmin() || $hasadminlink) {
                global $CFG;
                $headerlinks = [
                    'showcoursenav' => $showcoursenav,
                    'editcog' => $editcog,
                    'headerlinksdata' => array(
                        array(
                            'status' => !isguestuser() && has_capability('moodle/course:viewparticipants', $context),
                            'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.78168 19.25H13.2183C13.7828 19.25 14.227 18.7817 14.1145 18.2285C13.804 16.7012 12.7897 14 9.5 14C6.21031 14 5.19605 16.7012 4.88549 18.2285C4.773 18.7817 5.21718 19.25 5.78168 19.25Z"></path>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 14C17.8288 14 18.6802 16.1479 19.0239 17.696C19.2095 18.532 18.5333 19.25 17.6769 19.25H16.75"></path>
                            <circle cx="9.5" cy="7.5" r="2.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.75 10.25C16.2688 10.25 17.25 9.01878 17.25 7.5C17.25 5.98122 16.2688 4.75 14.75 4.75"></path>
                            </svg>
                            ',
                            'title' => get_string('participants', 'moodle'),
                            'url' => new moodle_url('/user/index.php', array('id' => $PAGE->course->id)),
                            'isactiveitem' => $this->isMenuActive('/user/index.php'),
                            'itemid' => 'itemParticipants',
                        ),
                        array(
                            'status' => has_capability('moodle/badges:earnbadge', $context) && $CFG->enablebadges == 1,
                            'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.25 8.75L18.25 4.75H5.75L9.75 8.75"></path>
                            <circle cx="12" cy="14" r="5.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
                            </svg>
                            ',
                            'title' => get_string('badges', 'badges'),
                            'url' => new moodle_url('/badges/view.php?type=2', array('id' => $PAGE->course->id)),
                            'isactiveitem' => $this->isMenuActive('/badges/view.php'),
                            'itemid' => 'itemBadges',
                        ),
                        // array(
                        //   'status' => !isguestuser() && $hascompetencyshow,
                        //    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        //  <path d="M9.25 7C9.25 8.24264 8.24264 9.25 7 9.25C5.75736 9.25 4.75 8.24264 4.75 7C4.75 5.75736 5.75736 4.75 7 4.75C8.24264 4.75 9.25 5.75736 9.25 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        //  <path d="M6.75 9.5V14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        //  <path d="M10.75 12.25H15.25C16.3546 12.25 17.25 11.3546 17.25 10.25V9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        //<path d="M19.25 7C19.25 8.24264 18.2426 9.25 17 9.25C15.7574 9.25 14.75 8.24264 14.75 7C14.75 5.75736 15.7574 4.75 17 4.75C18.2426 4.75 19.25 5.75736 19.25 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        //   <path d="M9.25 17C9.25 18.2426 8.24264 19.25 7 19.25C5.75736 19.25 4.75 18.2426 4.75 17C4.75 15.7574 5.75736 14.75 7 14.75C8.24264 14.75 9.25 15.7574 9.25 17Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        // </svg>',
                        //'title' => get_string('competencies', 'competency'),
                        //'url' => new moodle_url('/admin/tool/lp/coursecompetencies.php', array('courseid' => $PAGE->course->id)),
                        //'isactiveitem' => $this->isMenuActive('/admin/tool/lp/coursecompetencies'),
                        //'itemid' => 'itemCompetency',
                        //),
                        array(
                            'status' => $coursehaseasyenrollment && $isteacher,
                            'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.75 19.2502H18.25C18.8023 19.2502 19.25 18.8025 19.25 18.2502V5.75C19.25 5.19772 18.8023 4.75 18.25 4.75H5.75C5.19772 4.75 4.75 5.19772 4.75 5.75V18.2502C4.75 18.8025 5.19772 19.2502 5.75 19.2502Z"></path>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 9.25L5.25 9.25"></path>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 14.75L5.25 14.75"></path>
                            </svg>
                            ',
                            'title' => $easycodetitle,
                            'url' => $easycodelink,
                            'isactiveitem' => $this->isMenuActive('/enrol/editinstance.php'),
                            'itemid' => 'itemEditInstance',
                        ),
                        array(
                            'status' => $gradestatus,
                            'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.75 19.2502H18.25C18.8023 19.2502 19.25 18.8025 19.25 18.2502V5.75C19.25 5.19772 18.8023 4.75 18.25 4.75H5.75C5.19772 4.75 4.75 5.19772 4.75 5.75V18.2502C4.75 18.8025 5.19772 19.2502 5.75 19.2502Z"></path>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 9.25L5.25 9.25"></path>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 14.75L5.25 14.75"></path>
                            </svg>
                            ',
                            'title' => get_string('grades', 'moodle'),
                            'url' => $gradeurl,
                            'isactiveitem' => $this->isMenuActive('/grade/report/grader/index.php'),
                            'itemid' => 'itemGrade',
                        ),
                    ),
                ];
            }
        }
        return $this->render_from_template('theme_alpha/nav-course', $headerlinks);
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * @return string
     */
    public function context_header_settings_menu()
    {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (
            ($context->contextlevel == CONTEXT_COURSE) &&
            !empty($currentnode) &&
            ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)
        ) {
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if (
            $context->contextlevel == CONTEXT_MODULE &&
            !$courseformat->has_view_page()
        ) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (
                !empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                    $activenode->type == navigation_node::TYPE_RESOURCE)
            ) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if (
            $context->contextlevel == CONTEXT_COURSE &&
            !empty($currentnode) &&
            $currentnode->key === 'home'
        ) {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if (
            $context->contextlevel == CONTEXT_USER &&
            !empty($currentnode) &&
            ($currentnode->key === 'myprofile')
        ) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    public function isMenuActive($x)
    {
        if (strpos($_SERVER['REQUEST_URI'], strval($x)) != false) {
            return true;
        } else {
            return false;
        }
    }

    public function mainsidebarmenu()
    {
        global $CFG, $PAGE, $COURSE, $USER, $DB;

        if (
            $this->page->include_region_main_settings_in_header_actions() &&
            !$this->page->blocks->is_block_present('settings')
        ) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(
                html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
                )
            );
        }

        $header = new stdClass();

        $course = $this->page->course;
        $context = context_course::instance($course->id);

        if (is_role_switched($course->id)) { // Has switched roles
            $rolename = '';
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
                $rolename = ': ' . role_get_name($role, $context);
            }

            $loggedinas = get_string('loggedinas', 'moodle', $fullname) . $rolename;
        }
        if (\core\session\manager::is_loggedinas()) {
            $header->loginas = $this->login_info();
        }
        if (is_role_switched($course->id) && !\core\session\manager::is_loggedinas()) {
            $header->roleswitch = $loggedinas;
        }
        $hascontentbankpermission = has_capability('contenttype/h5p:access', $context);

        $calendarurl = new moodle_url('/calendar/view.php?view=month');
        $calendarurl = '';
        if (isset($COURSE->id) && $COURSE->id > 1 && isloggedin() && !isguestuser()) {
            $calendarurl = new moodle_url('/calendar/view.php?view=upcoming', array('course' => $PAGE->course->id));
        } else {
            $calendarurl = new moodle_url('/calendar/view.php?view=month');
        }

        if($_SESSION["sessionzoomid"]==null)
        {        
            session_start();
            $coursezoom = get_course(1);
            $basicltis = get_all_instances_in_course("lti", $coursezoom);
            foreach ($basicltis as $basiclti)
           {
              if ($basiclti->visible)
              {
               $idzoom = $basiclti->coursemodule;
                }
               break;
            }
            $_SESSION["sessionzoomid"] = $idzoom;
        }
        else
        {
            $idzoom = $_SESSION["sessionzoomid"];
        }
        // Header links on non course areas.
        if (isloggedin() && !isguestuser()) {
            if ($COURSE->id > 1) {
                $headerlinks = [
                    'headerlinksdata' => array(
                        // Home
                        array(
                            'status' => !isguestuser(),
                            'icon' => '<svg  style="fill: none;"  width="24" height="24" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75024 19.2502H17.2502C18.3548 19.2502 19.2502 18.3548 19.2502 17.2502V9.75025L12.0002 4.75024L4.75024 9.75025V17.2502C4.75024 18.3548 5.64568 19.2502 6.75024 19.2502Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.74963 15.7493C9.74963 14.6447 10.6451 13.7493 11.7496 13.7493H12.2496C13.3542 13.7493 14.2496 14.6447 14.2496 15.7493V19.2493H9.74963V15.7493Z"></path></svg>',
                            'title' => get_string('sitehome', 'moodle'),
                            'url' => new moodle_url('/'),
                            'isactiveitem' => $this->isMenuActive('/'),
                            'itemid' => 'itemHome',
                            'visability' => true,
                        ),
                        // Meeting
                        array(
                            'status' => !isguestuser(),
                            'icon' => '
                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                width="24px" height="24px" viewBox="0 0 512.000000 512.000000"
                                preserveAspectRatio="xMidYMid meet">
                                <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                fill="#000000" stroke="none">
                                <path d="M1155 4131 c-56 -14 -93 -40 -165 -118 -597 -646 -737 -1601 -351
                                -2390 102 -207 226 -386 375 -541 57 -60 93 -82 151 -93 159 -30 290 120 245
                                282 -6 23 -42 72 -93 129 -124 139 -200 250 -276 405 -75 150 -117 271 -153
                                436 -19 90 -22 134 -22 319 0 186 3 229 23 320 71 328 204 589 428 840 51 57
                                87 106 93 129 17 60 12 114 -15 168 -30 59 -79 97 -145 112 -51 12 -53 12 -95
                                2z"/>
                                <path d="M3865 4129 c-121 -29 -190 -154 -155 -280 6 -23 42 -72 93 -129 301
                                -337 457 -730 457 -1155 0 -419 -151 -817 -424 -1125 -114 -127 -127 -149
                                -133 -219 -16 -175 165 -291 323 -208 65 34 260 267 358 428 74 122 162 314
                                206 447 243 741 66 1556 -460 2125 -100 108 -170 139 -265 116z"/>
                                <path d="M1715 3511 c-80 -36 -218 -206 -300 -367 -93 -181 -135 -365 -135
                                -584 0 -219 42 -403 135 -584 82 -161 220 -331 300 -367 91 -42 215 -9 271 72
                                22 32 29 55 32 104 6 84 -9 122 -73 194 -101 114 -159 217 -199 355 -82 283
                                -11 571 199 807 64 72 79 110 73 194 -5 79 -38 131 -107 169 -59 32 -136 35
                                -196 7z"/>
                                <path d="M3205 3501 c-69 -41 -98 -87 -103 -166 -6 -84 9 -122 73 -194 101
                                -114 158 -214 201 -358 23 -76 27 -106 27 -223 0 -117 -4 -147 -27 -223 -43
                                -144 -100 -244 -201 -358 -64 -72 -79 -110 -73 -194 3 -49 10 -72 32 -104 56
                                -81 180 -114 271 -72 83 38 234 228 315 396 83 174 120 345 120 555 0 210 -37
                                381 -120 555 -81 168 -232 358 -315 396 -63 29 -140 25 -200 -10z"/>
                                <path d="M2426 2970 c-274 -87 -385 -427 -216 -663 169 -235 531 -235 700 0
                                137 192 93 467 -97 603 -108 77 -260 101 -387 60z"/>
                                </g>
                               </svg>',
                            'title' => get_string('meeting', 'moodle'),
                            //Sửa meeting sidebar
                            'url' => new moodle_url('/mod/lti/view.php', array('id' => $idzoom)),
                            'isactiveitem' => $this->isMenuActive('/mod/lti/view.php', array('id' => $idzoom)),
                            'itemid' => 'itemMeeting',
                            'visability' => true,
                        ),
                   
                        //Quản lí khóa học
                        array(
                            'status' => !isguestuser(),
                            'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                width="20px" height="20px" viewBox="0 0 938.000000 980.000000"
                                preserveAspectRatio="xMidYMid meet">
                               <g transform="translate(0.000000,980.000000) scale(0.100000,-0.100000)"
                               fill="#000000" stroke="none">
                               <path d="M1090 9789 c-193 -22 -403 -90 -550 -181 -114 -69 -269 -224 -338
                               -337 -91 -149 -149 -315 -177 -508 -14 -97 -15 -465 -13 -3488 l3 -3380 23
                               -97 c63 -274 164 -464 338 -640 201 -203 473 -324 803 -357 67 -7 587 -11
                               1492 -11 l1389 0 0 170 0 169 -1462 4 -1463 3 -84 22 c-191 51 -319 120 -437
                               237 -128 127 -209 286 -251 492 -17 84 -18 254 -18 3433 0 3156 1 3350 18
                               3438 68 363 263 578 607 670 86 23 118 25 338 30 l242 4 0 169 0 169 -192 -1
                               c-106 -1 -227 -5 -268 -10z"/>
                               <path d="M7140 9631 l0 -171 226 0 c188 0 243 -3 327 -21 185 -37 328 -111
                               451 -234 115 -115 187 -248 234 -435 15 -59 17 -246 22 -1965 l5 -1900 168 -3
                               167 -2 -3 1902 c-3 2107 2 1947 -68 2165 -61 190 -158 350 -293 486 -159 159
                               -360 263 -614 318 -79 17 -145 22 -359 26 l-263 6 0 -172z"/>
                               <path d="M2360 9625 l0 -165 1985 0 1985 0 0 165 0 165 -1985 0 -1985 0 0
                               -165z"/>
                               <path d="M2360 7535 l0 -165 2065 0 2065 0 0 165 0 165 -2065 0 -2065 0 0
                               -165z"/>
                               <path d="M2360 5920 l0 -170 1238 2 1237 3 3 168 2 167 -1240 0 -1240 0 0
                               -170z"/>
                               <path d="M5895 4848 c-220 -88 -403 -163 -407 -166 -4 -4 29 -168 73 -365 l79
                               -358 -121 -117 c-67 -64 -125 -118 -129 -120 -4 -2 -166 34 -360 79 -193 44
                               -353 79 -355 77 -7 -7 -346 -801 -343 -803 2 -1 143 -90 313 -199 l310 -198
                               -2 -171 -1 -171 -308 -192 c-170 -106 -310 -196 -312 -202 -3 -10 314 -781
                               328 -795 4 -5 168 27 365 70 l358 78 122 -122 122 -122 -83 -343 c-46 -189
                               -83 -350 -84 -359 0 -9 6 -19 13 -22 6 -3 183 -75 391 -161 209 -86 386 -157
                               393 -158 8 -2 93 118 212 302 l198 305 176 3 175 2 188 -292 c103 -161 193
                               -299 199 -306 10 -11 84 15 410 142 219 85 402 159 405 163 4 5 -30 169 -75
                               366 l-82 358 120 120 119 120 360 -82 c215 -49 362 -79 367 -73 12 12 344 794
                               340 799 -2 2 -145 91 -317 197 l-313 194 0 169 -1 170 311 195 c170 107 313
                               198 316 202 9 8 -325 808 -337 808 -5 0 -168 -38 -362 -85 l-353 -84 -122 122
                               -123 122 82 358 c46 200 79 362 74 366 -10 9 -790 331 -802 331 -4 0 -94 -139
                               -201 -309 l-194 -309 -171 0 -171 0 -165 265 c-91 146 -178 287 -195 313 l-30
                               48 -400 -160z m445 -547 l175 -282 45 5 c257 31 376 31 596 0 l41 -6 168 268
                               c92 148 174 275 181 283 10 12 34 5 153 -44 78 -32 141 -62 141 -67 0 -9 -137
                               -612 -145 -638 -3 -11 23 -37 82 -80 117 -86 256 -226 327 -327 32 -46 61 -85
                               65 -87 4 -3 34 2 67 10 259 63 599 143 600 141 15 -18 114 -281 108 -286 -5
                               -5 -124 -79 -264 -166 -140 -87 -267 -166 -281 -176 l-25 -18 12 -88 c16 -115
                               16 -384 0 -498 -7 -49 -13 -91 -12 -91 0 -1 127 -79 281 -174 154 -95 283
                               -175 288 -179 7 -7 -96 -257 -113 -275 -7 -7 -582 115 -657 139 -6 2 -37 -31
                               -69 -73 -81 -108 -224 -252 -326 -329 -49 -36 -88 -71 -88 -78 0 -8 32 -150
                               70 -316 39 -167 70 -311 70 -320 0 -13 -36 -31 -142 -73 -78 -31 -142 -55
                               -143 -53 -67 107 -351 545 -359 554 -8 8 -33 8 -101 -3 -115 -18 -364 -18
                               -480 -1 -49 8 -93 13 -96 11 -3 -2 -88 -130 -189 -284 -101 -155 -188 -279
                               -194 -276 -6 2 -67 27 -136 56 -69 28 -128 55 -133 59 -4 4 27 147 68 319 41
                               171 75 318 75 326 0 7 -35 40 -77 72 -112 84 -243 214 -325 321 -39 51 -75 93
                               -80 93 -6 0 -154 -32 -330 -71 -177 -38 -324 -66 -328 -62 -4 4 -31 68 -61
                               141 -34 86 -50 136 -43 140 6 4 130 81 275 171 145 90 269 169 276 175 8 8 8
                               33 -3 101 -17 112 -17 371 0 490 8 50 11 93 7 96 -3 3 -130 83 -281 179 -151
                               95 -276 174 -278 175 -3 3 109 268 119 278 5 7 555 -112 640 -138 11 -4 36 21
                               79 77 88 115 218 240 332 321 69 49 95 72 92 85 -46 187 -142 648 -137 650 5
                               2 67 27 138 57 72 29 134 52 140 51 5 -1 88 -130 185 -285z"/>
                               <path d="M6650 3475 c-122 -27 -271 -91 -370 -158 -66 -45 -186 -163 -236
                               -233 -62 -87 -131 -236 -161 -351 -32 -124 -36 -313 -9 -437 42 -193 144 -373
                               288 -512 189 -182 419 -274 683 -274 462 0 855 303 972 750 29 111 36 313 15
                               415 -84 388 -342 669 -717 781 -77 23 -112 27 -245 30 -110 3 -174 -1 -220
                               -11z m401 -352 c214 -70 394 -263 443 -473 20 -83 20 -217 0 -300 -103 -440
                               -634 -647 -1013 -396 -385 257 -386 835 -1 1091 74 50 173 90 253 105 87 15
                               226 4 318 -27z"/>
                               <path d="M2360 4200 l0 -170 760 0 760 0 0 170 0 170 -760 0 -760 0 0 -170z"/>
                               </g>
                               </svg>',
                            'title' => get_string('coursemanager', 'moodle'),
                            'url' => new moodle_url('/my/courses.php'),
                            'isactiveitem' => $this->isMenuActive('/courses.php') || $this->isMenuActive('/course/') || $this->isMenuActive('/user/') || $this->isMenuActive('/badges/') || $this->isMenuActive('/grade/'),
                            'itemid' => 'itemCourseManager',
                            'visability' => true,
                        ),
                        // Quản lý người dùng
                        array(
                            'status' => is_siteadmin() || is_teacher(),
                            'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                width="20px" height="20px" viewBox="0 0 512.000000 512.000000"
                                preserveAspectRatio="xMidYMid meet">
                               <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                               fill="#000000" stroke="none">
                               <path d="M2040 4639 c-151 -17 -321 -80 -458 -168 -91 -59 -235 -203 -298
                               -297 -116 -175 -171 -344 -181 -554 -14 -328 105 -619 345 -840 l79 -74 -91
                               -39 c-360 -155 -677 -439 -878 -789 -185 -321 -270 -740 -227 -1118 13 -114
                               31 -150 89 -184 35 -21 43 -21 1180 -21 1127 0 1146 0 1179 20 95 56 113 174
                               38 253 -17 18 -47 37 -67 42 -22 6 -421 10 -1067 10 l-1033 0 0 101 c0 559
                               336 1092 848 1349 118 59 251 105 381 131 106 21 572 45 673 34 143 -15 225
                               -63 397 -229 94 -91 125 -116 155 -121 83 -16 155 18 187 89 42 93 18 146
                               -130 289 -62 59 -152 133 -200 165 l-88 58 78 82 c131 136 218 288 267 471 23
                               86 26 114 26 271 0 158 -3 185 -27 275 -116 432 -479 745 -920 794 -108 12
                               -149 12 -257 0z m325 -345 c141 -37 291 -133 385 -247 63 -77 133 -219 154
                               -314 37 -165 13 -344 -66 -503 -58 -117 -196 -257 -314 -317 -119 -62 -217
                               -87 -344 -86 -133 0 -218 20 -340 80 -170 84 -311 248 -377 438 -24 72 -27 94
                               -28 220 0 159 18 237 82 355 104 191 296 339 493 379 30 6 64 13 75 15 38 9
                               220 -4 280 -20z"/>
                               <path d="M3605 2247 c-35 -35 -35 -36 -35 -124 l0 -90 -42 -13 c-24 -6 -59
                               -20 -78 -30 l-35 -18 -67 64 c-60 58 -70 64 -108 64 -40 0 -47 -5 -146 -102
                               -140 -140 -145 -167 -45 -272 l58 -61 -28 -60 c-15 -33 -28 -68 -29 -77 0 -16
                               -11 -18 -85 -18 -152 0 -168 -22 -163 -230 3 -137 3 -140 31 -167 25 -25 36
                               -28 118 -33 l91 -5 30 -79 30 -78 -55 -57 c-95 -96 -91 -129 31 -254 140 -143
                               170 -150 268 -56 l64 62 57 -26 c32 -14 68 -28 80 -31 19 -6 21 -15 25 -97 3
                               -88 4 -91 36 -121 l34 -29 155 3 155 3 24 28 c21 25 24 38 24 115 l1 87 81 32
                               82 33 62 -60 c56 -54 66 -60 105 -60 41 0 47 4 151 108 105 105 108 109 108
                               152 0 41 -5 49 -60 105 l-59 60 31 82 31 81 94 4 c89 3 96 5 119 31 23 27 24
                               34 24 183 l0 156 -29 29 c-28 28 -33 29 -118 29 l-89 0 -33 81 -33 80 61 64
                               c55 57 61 67 61 106 0 41 -4 47 -108 151 -104 104 -110 108 -151 108 -39 0
                               -49 -6 -109 -63 l-67 -64 -75 33 -75 33 -5 95 c-7 140 -8 140 -206 144 l-155
                               3 -34 -34z m245 -225 l0 -108 61 -13 c71 -14 154 -49 222 -93 l48 -31 78 77
                               77 76 44 -45 44 -45 -76 -78 -76 -77 39 -60 c34 -53 76 -159 93 -237 l6 -28
                               105 0 105 0 0 -65 0 -65 -105 0 -105 0 -6 -27 c-23 -108 -57 -191 -111 -275
                               l-18 -28 73 -73 72 -73 -45 -44 -45 -44 -68 67 c-37 37 -72 67 -79 67 -6 0
                               -37 -16 -69 -35 -65 -39 -144 -71 -216 -86 l-48 -11 0 -104 0 -104 -65 0 -65
                               0 0 103 0 104 -82 22 c-75 20 -116 38 -215 97 l-32 19 -74 -73 -73 -72 -44 45
                               -44 45 73 74 73 73 -35 54 c-42 67 -83 169 -92 232 l-7 47 -109 0 -109 0 0 65
                               0 65 110 0 c103 0 110 1 110 20 0 40 50 173 90 241 l42 71 -73 74 -73 74 44
                               45 44 45 76 -75 76 -75 55 35 c64 40 164 79 227 90 l42 7 0 109 0 109 65 0 65
                               0 0 -108z"/>
                               <path d="M3670 1583 c-63 -23 -151 -113 -173 -177 -9 -27 -17 -77 -17 -111 0
                               -92 24 -151 89 -216 67 -67 123 -89 226 -89 60 0 82 5 133 31 69 34 105 71
                               139 145 34 74 39 133 19 207 -24 89 -76 153 -155 194 -56 29 -72 33 -139 32
                               -43 0 -97 -8 -122 -16z m204 -183 c70 -61 70 -148 1 -209 -86 -75 -225 -12
                               -225 102 0 81 58 137 142 137 38 0 54 -6 82 -30z"/>
                               </g>
                               </svg>',
                            'title' =>  is_siteadmin() ? get_string('usermanager', 'moodle') : get_string('groupsmanager','moodle'),
                            'url' => is_siteadmin() ? new moodle_url('/admin/user.php') : new moodle_url('/cohort/index.php?contextid=1'),
                            'isactiveitem' => is_siteadmin() ? $this->isMenuActive('/admin/user.php') : $this->isMenuActive('/cohort/index.php?contextid=1'),
                            'itemid' => 'itemCourseManager',
                            'visability' => true,
                        ),
                        //Báo cáo
                        array(
                            'status' => !isguestuser(),
                            'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                width="26px" height="26px" viewBox="0 0 820.000000 512.000000"
                                preserveAspectRatio="xMidYMid meet">
                               <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                               fill="#000000" stroke="none">
                               <path d="M1894 4600 c-81 -12 -158 -53 -223 -118 -65 -65 -103 -131 -120 -210
                               -16 -75 -16 -3349 0 -3424 17 -79 55 -145 120 -210 68 -69 142 -106 237 -119
                               40 -6 939 -8 2242 -7 l2175 3 58 23 c109 44 205 140 249 250 l23 57 0 1715 0
                               1715 -23 58 c-44 109 -140 205 -250 249 l-57 23 -2190 1 c-1204 1 -2213 -2
                               -2241 -6z m4365 -341 c65 -23 61 96 61 -1699 0 -1795 4 -1676 -61 -1699 -45
                               -16 -4273 -16 -4318 0 -65 23 -61 -96 -61 1698 0 1266 3 1636 12 1657 26 56
                               -100 53 2204 54 1535 0 2140 -3 2163 -11z"/>
                               <path d="M5295 3918 c-3 -7 -4 -623 -3 -1368 l3 -1355 340 0 340 0 0 1365 0
                               1365 -338 3 c-265 2 -339 0 -342 -10z"/>
                               <path d="M3252 2388 l3 -1193 338 -3 337 -2 0 1195 0 1195 -340 0 -340 0 2
                               -1192z"/>
                               <path d="M4270 2045 l0 -855 338 2 337 3 3 853 2 852 -340 0 -340 0 0 -855z"/>
                               <path d="M2222 1708 l3 -513 340 0 340 0 0 510 0 510 -343 3 -342 2 2 -512z"/>
                               </g>
                               </svg>
                               ',
                            'title' => get_string('reports', 'moodle'),
                            'url' => new moodle_url('/reportbuilder/index.php', array('contextid' => 1)),
                            'isactiveitem' => $this->isMenuActive('/reportbuilder'),
                            'itemid' => 'itemReportBuilder',
                            'visability' => true,
                        ),
                        //Thêm mục cho side bar
                        // 'status' => !isguestuser(),
                        // 'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V6.75Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 8.75V19"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8.25H19"></path></svg>',
                        // 'title' => get_string('myhome', 'moodle'),
                        // 'url' => new moodle_url('/my/'),
                        // 'isactiveitem' => $this->isMenuActive('/my'),
                        // 'visability' => true,




                        // array(
                        //     'status' => !isguestuser() ,
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V6.75Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 8.75V19"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8.25H19"></path></svg>',
                        //     'title' => get_string('myhome', 'moodle'),
                        //     'url' => new moodle_url('/my/'),
                        //     'isactiveitem' => $this->isMenuActive('/my'),
                        //     'visability' => true,
                        //     ),
                        // array(
                        //     'status' => !isguestuser(),
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 11.75L17.6644 6.20056C17.4191 5.34195 16.6344 4.75 15.7414 4.75H8.2586C7.36564 4.75 6.58087 5.34196 6.33555 6.20056L4.75 11.75"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.2142 12.3689C9.95611 12.0327 9.59467 11.75 9.17085 11.75H4.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H17.25C18.3546 19.25 19.25 18.3546 19.25 17.25V11.75H14.8291C14.4053 11.75 14.0439 12.0327 13.7858 12.3689C13.3745 12.9046 12.7276 13.25 12 13.25C11.2724 13.25 10.6255 12.9046 10.2142 12.3689Z"></path></svg>',
                        //     'title' => get_string('privatefiles', 'moodle'),
                        //     'url' => new moodle_url('/user/files.php'),
                        //     'isactiveitem' => $this->isMenuActive('/user/files'),
                        //     'visability' => true,
                        //     ),
                        // array(
                        //     'status' => !isguestuser(),
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 8.75C4.75 7.64543 5.64543 6.75 6.75 6.75H17.25C18.3546 6.75 19.25 7.64543 19.25 8.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V8.75Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4.75V8.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 4.75V8.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.75 10.75H16.25"></path></svg>',
                        //     'title' => get_string('calendar', 'calendar'),
                        //     'url' => $calendarurl,
                        //     'isactiveitem' => $this->isMenuActive('/calendar'),
                        //     'visability' => true,
                        //     ),
                        // array(
                        //     'status' => $hascontentbankpermission,
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 17.25V9.75C19.25 8.64543 18.3546 7.75 17.25 7.75H4.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H17.25C18.3546 19.25 19.25 18.3546 19.25 17.25Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 7.5L12.5685 5.7923C12.2181 5.14977 11.5446 4.75 10.8127 4.75H6.75C5.64543 4.75 4.75 5.64543 4.75 6.75V11"></path></svg>',
                        //     'title' => get_string('contentbank', 'moodle'),
                        //     'url' => new moodle_url('/contentbank/index.php', array('contextid' => $context->id)),
                        //     'isactiveitem' => $this->isMenuActive('/contentbank'),
                        //     'visability' => true,
                        //     ),

                    ),
                ];
            } else {
                $headerlinks = [
                    'headerlinksdata' => array(
                        array(
                            'status' => !isguestuser(),
                            'icon' => '<svg style="fill: none;" width="24" height="24" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75024 19.2502H17.2502C18.3548 19.2502 19.2502 18.3548 19.2502 17.2502V9.75025L12.0002 4.75024L4.75024 9.75025V17.2502C4.75024 18.3548 5.64568 19.2502 6.75024 19.2502Z"></path><path stroke="currentColor" stroke-linecap="round" style="fill:#fff;" stroke-linejoin="round" stroke-width="1.5" d="M9.74963 15.7493C9.74963 14.6447 10.6451 13.7493 11.7496 13.7493H12.2496C13.3542 13.7493 14.2496 14.6447 14.2496 15.7493V19.2493H9.74963V15.7493Z"></path></svg>',
                            'title' => get_string('sitehome', 'moodle'),
                            'url' => new moodle_url('/'),
                            'isactiveitem' => $this->isMenuActive('/'),
                            'itemid' => 'itemHome',
                            'visability' => true,
                        ),
                        array(
                            'status' => !isguestuser(),
                            'icon' => '
                            <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                            width="24px" height="24px" viewBox="0 0 512.000000 512.000000"
                            preserveAspectRatio="xMidYMid meet">
                           <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                           fill="#000000" stroke="none">
                           <path d="M1155 4131 c-56 -14 -93 -40 -165 -118 -597 -646 -737 -1601 -351
                           -2390 102 -207 226 -386 375 -541 57 -60 93 -82 151 -93 159 -30 290 120 245
                           282 -6 23 -42 72 -93 129 -124 139 -200 250 -276 405 -75 150 -117 271 -153
                           436 -19 90 -22 134 -22 319 0 186 3 229 23 320 71 328 204 589 428 840 51 57
                           87 106 93 129 17 60 12 114 -15 168 -30 59 -79 97 -145 112 -51 12 -53 12 -95
                           2z"/>
                           <path d="M3865 4129 c-121 -29 -190 -154 -155 -280 6 -23 42 -72 93 -129 301
                           -337 457 -730 457 -1155 0 -419 -151 -817 -424 -1125 -114 -127 -127 -149
                           -133 -219 -16 -175 165 -291 323 -208 65 34 260 267 358 428 74 122 162 314
                           206 447 243 741 66 1556 -460 2125 -100 108 -170 139 -265 116z"/>
                           <path d="M1715 3511 c-80 -36 -218 -206 -300 -367 -93 -181 -135 -365 -135
                           -584 0 -219 42 -403 135 -584 82 -161 220 -331 300 -367 91 -42 215 -9 271 72
                           22 32 29 55 32 104 6 84 -9 122 -73 194 -101 114 -159 217 -199 355 -82 283
                           -11 571 199 807 64 72 79 110 73 194 -5 79 -38 131 -107 169 -59 32 -136 35
                           -196 7z"/>
                           <path d="M3205 3501 c-69 -41 -98 -87 -103 -166 -6 -84 9 -122 73 -194 101
                           -114 158 -214 201 -358 23 -76 27 -106 27 -223 0 -117 -4 -147 -27 -223 -43
                           -144 -100 -244 -201 -358 -64 -72 -79 -110 -73 -194 3 -49 10 -72 32 -104 56
                           -81 180 -114 271 -72 83 38 234 228 315 396 83 174 120 345 120 555 0 210 -37
                           381 -120 555 -81 168 -232 358 -315 396 -63 29 -140 25 -200 -10z"/>
                           <path d="M2426 2970 c-274 -87 -385 -427 -216 -663 169 -235 531 -235 700 0
                           137 192 93 467 -97 603 -108 77 -260 101 -387 60z"/>
                           </g>
                           </svg>',
                            'title' => get_string('meeting', 'moodle'),
                            //Sửa meeting sidebar
                            'url' => new moodle_url('/mod/lti/view.php', array('id' => $idzoom)),
                            'isactiveitem' => $this->isMenuActive('/mod/lti/view.php', array('id' => $idzoom)),
                            'itemid' => 'itemMeeting',
                            'visability' => true,
                        ),

                        //Quản lí khóa học
                        array(
                            'status' => !isguestuser(),
                            'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                            width="20px" height="20px" viewBox="0 0 938.000000 980.000000"
                            preserveAspectRatio="xMidYMid meet">
                           <g transform="translate(0.000000,980.000000) scale(0.100000,-0.100000)"
                           fill="#000000" stroke="none">
                           <path d="M1090 9789 c-193 -22 -403 -90 -550 -181 -114 -69 -269 -224 -338
                           -337 -91 -149 -149 -315 -177 -508 -14 -97 -15 -465 -13 -3488 l3 -3380 23
                           -97 c63 -274 164 -464 338 -640 201 -203 473 -324 803 -357 67 -7 587 -11
                           1492 -11 l1389 0 0 170 0 169 -1462 4 -1463 3 -84 22 c-191 51 -319 120 -437
                           237 -128 127 -209 286 -251 492 -17 84 -18 254 -18 3433 0 3156 1 3350 18
                           3438 68 363 263 578 607 670 86 23 118 25 338 30 l242 4 0 169 0 169 -192 -1
                           c-106 -1 -227 -5 -268 -10z"/>
                           <path d="M7140 9631 l0 -171 226 0 c188 0 243 -3 327 -21 185 -37 328 -111
                           451 -234 115 -115 187 -248 234 -435 15 -59 17 -246 22 -1965 l5 -1900 168 -3
                           167 -2 -3 1902 c-3 2107 2 1947 -68 2165 -61 190 -158 350 -293 486 -159 159
                           -360 263 -614 318 -79 17 -145 22 -359 26 l-263 6 0 -172z"/>
                           <path d="M2360 9625 l0 -165 1985 0 1985 0 0 165 0 165 -1985 0 -1985 0 0
                           -165z"/>
                           <path d="M2360 7535 l0 -165 2065 0 2065 0 0 165 0 165 -2065 0 -2065 0 0
                           -165z"/>
                           <path d="M2360 5920 l0 -170 1238 2 1237 3 3 168 2 167 -1240 0 -1240 0 0
                           -170z"/>
                           <path d="M5895 4848 c-220 -88 -403 -163 -407 -166 -4 -4 29 -168 73 -365 l79
                           -358 -121 -117 c-67 -64 -125 -118 -129 -120 -4 -2 -166 34 -360 79 -193 44
                           -353 79 -355 77 -7 -7 -346 -801 -343 -803 2 -1 143 -90 313 -199 l310 -198
                           -2 -171 -1 -171 -308 -192 c-170 -106 -310 -196 -312 -202 -3 -10 314 -781
                           328 -795 4 -5 168 27 365 70 l358 78 122 -122 122 -122 -83 -343 c-46 -189
                           -83 -350 -84 -359 0 -9 6 -19 13 -22 6 -3 183 -75 391 -161 209 -86 386 -157
                           393 -158 8 -2 93 118 212 302 l198 305 176 3 175 2 188 -292 c103 -161 193
                           -299 199 -306 10 -11 84 15 410 142 219 85 402 159 405 163 4 5 -30 169 -75
                           366 l-82 358 120 120 119 120 360 -82 c215 -49 362 -79 367 -73 12 12 344 794
                           340 799 -2 2 -145 91 -317 197 l-313 194 0 169 -1 170 311 195 c170 107 313
                           198 316 202 9 8 -325 808 -337 808 -5 0 -168 -38 -362 -85 l-353 -84 -122 122
                           -123 122 82 358 c46 200 79 362 74 366 -10 9 -790 331 -802 331 -4 0 -94 -139
                           -201 -309 l-194 -309 -171 0 -171 0 -165 265 c-91 146 -178 287 -195 313 l-30
                           48 -400 -160z m445 -547 l175 -282 45 5 c257 31 376 31 596 0 l41 -6 168 268
                           c92 148 174 275 181 283 10 12 34 5 153 -44 78 -32 141 -62 141 -67 0 -9 -137
                           -612 -145 -638 -3 -11 23 -37 82 -80 117 -86 256 -226 327 -327 32 -46 61 -85
                           65 -87 4 -3 34 2 67 10 259 63 599 143 600 141 15 -18 114 -281 108 -286 -5
                           -5 -124 -79 -264 -166 -140 -87 -267 -166 -281 -176 l-25 -18 12 -88 c16 -115
                           16 -384 0 -498 -7 -49 -13 -91 -12 -91 0 -1 127 -79 281 -174 154 -95 283
                           -175 288 -179 7 -7 -96 -257 -113 -275 -7 -7 -582 115 -657 139 -6 2 -37 -31
                           -69 -73 -81 -108 -224 -252 -326 -329 -49 -36 -88 -71 -88 -78 0 -8 32 -150
                           70 -316 39 -167 70 -311 70 -320 0 -13 -36 -31 -142 -73 -78 -31 -142 -55
                           -143 -53 -67 107 -351 545 -359 554 -8 8 -33 8 -101 -3 -115 -18 -364 -18
                           -480 -1 -49 8 -93 13 -96 11 -3 -2 -88 -130 -189 -284 -101 -155 -188 -279
                           -194 -276 -6 2 -67 27 -136 56 -69 28 -128 55 -133 59 -4 4 27 147 68 319 41
                           171 75 318 75 326 0 7 -35 40 -77 72 -112 84 -243 214 -325 321 -39 51 -75 93
                           -80 93 -6 0 -154 -32 -330 -71 -177 -38 -324 -66 -328 -62 -4 4 -31 68 -61
                           141 -34 86 -50 136 -43 140 6 4 130 81 275 171 145 90 269 169 276 175 8 8 8
                           33 -3 101 -17 112 -17 371 0 490 8 50 11 93 7 96 -3 3 -130 83 -281 179 -151
                           95 -276 174 -278 175 -3 3 109 268 119 278 5 7 555 -112 640 -138 11 -4 36 21
                           79 77 88 115 218 240 332 321 69 49 95 72 92 85 -46 187 -142 648 -137 650 5
                           2 67 27 138 57 72 29 134 52 140 51 5 -1 88 -130 185 -285z"/>
                           <path d="M6650 3475 c-122 -27 -271 -91 -370 -158 -66 -45 -186 -163 -236
                           -233 -62 -87 -131 -236 -161 -351 -32 -124 -36 -313 -9 -437 42 -193 144 -373
                           288 -512 189 -182 419 -274 683 -274 462 0 855 303 972 750 29 111 36 313 15
                           415 -84 388 -342 669 -717 781 -77 23 -112 27 -245 30 -110 3 -174 -1 -220
                           -11z m401 -352 c214 -70 394 -263 443 -473 20 -83 20 -217 0 -300 -103 -440
                           -634 -647 -1013 -396 -385 257 -386 835 -1 1091 74 50 173 90 253 105 87 15
                           226 4 318 -27z"/>
                           <path d="M2360 4200 l0 -170 760 0 760 0 0 170 0 170 -760 0 -760 0 0 -170z"/>
                           </g>
                           </svg>',
                            'title' => get_string('coursemanager', 'moodle'),
                            'url' => new moodle_url('/my/courses.php'),
                            'isactiveitem' => $this->isMenuActive('/courses.php') || $this->isMenuActive('/course/') || $this->isMenuActive('/user/') || $this->isMenuActive('/badges/') || $this->isMenuActive('/grade/'),
                            'itemid' => 'itemCourseManager',
                            'visability' => true,
                        ),
                        // Quản lý người dùng
                        array(
                            'status' => is_siteadmin() || is_teacher(),
                            'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                            width="20px" height="20px" viewBox="0 0 512.000000 512.000000"
                            preserveAspectRatio="xMidYMid meet">
                           <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                           fill="#000000" stroke="none">
                           <path d="M2040 4639 c-151 -17 -321 -80 -458 -168 -91 -59 -235 -203 -298
                           -297 -116 -175 -171 -344 -181 -554 -14 -328 105 -619 345 -840 l79 -74 -91
                           -39 c-360 -155 -677 -439 -878 -789 -185 -321 -270 -740 -227 -1118 13 -114
                           31 -150 89 -184 35 -21 43 -21 1180 -21 1127 0 1146 0 1179 20 95 56 113 174
                           38 253 -17 18 -47 37 -67 42 -22 6 -421 10 -1067 10 l-1033 0 0 101 c0 559
                           336 1092 848 1349 118 59 251 105 381 131 106 21 572 45 673 34 143 -15 225
                           -63 397 -229 94 -91 125 -116 155 -121 83 -16 155 18 187 89 42 93 18 146
                           -130 289 -62 59 -152 133 -200 165 l-88 58 78 82 c131 136 218 288 267 471 23
                           86 26 114 26 271 0 158 -3 185 -27 275 -116 432 -479 745 -920 794 -108 12
                           -149 12 -257 0z m325 -345 c141 -37 291 -133 385 -247 63 -77 133 -219 154
                           -314 37 -165 13 -344 -66 -503 -58 -117 -196 -257 -314 -317 -119 -62 -217
                           -87 -344 -86 -133 0 -218 20 -340 80 -170 84 -311 248 -377 438 -24 72 -27 94
                           -28 220 0 159 18 237 82 355 104 191 296 339 493 379 30 6 64 13 75 15 38 9
                           220 -4 280 -20z"/>
                           <path d="M3605 2247 c-35 -35 -35 -36 -35 -124 l0 -90 -42 -13 c-24 -6 -59
                           -20 -78 -30 l-35 -18 -67 64 c-60 58 -70 64 -108 64 -40 0 -47 -5 -146 -102
                           -140 -140 -145 -167 -45 -272 l58 -61 -28 -60 c-15 -33 -28 -68 -29 -77 0 -16
                           -11 -18 -85 -18 -152 0 -168 -22 -163 -230 3 -137 3 -140 31 -167 25 -25 36
                           -28 118 -33 l91 -5 30 -79 30 -78 -55 -57 c-95 -96 -91 -129 31 -254 140 -143
                           170 -150 268 -56 l64 62 57 -26 c32 -14 68 -28 80 -31 19 -6 21 -15 25 -97 3
                           -88 4 -91 36 -121 l34 -29 155 3 155 3 24 28 c21 25 24 38 24 115 l1 87 81 32
                           82 33 62 -60 c56 -54 66 -60 105 -60 41 0 47 4 151 108 105 105 108 109 108
                           152 0 41 -5 49 -60 105 l-59 60 31 82 31 81 94 4 c89 3 96 5 119 31 23 27 24
                           34 24 183 l0 156 -29 29 c-28 28 -33 29 -118 29 l-89 0 -33 81 -33 80 61 64
                           c55 57 61 67 61 106 0 41 -4 47 -108 151 -104 104 -110 108 -151 108 -39 0
                           -49 -6 -109 -63 l-67 -64 -75 33 -75 33 -5 95 c-7 140 -8 140 -206 144 l-155
                           3 -34 -34z m245 -225 l0 -108 61 -13 c71 -14 154 -49 222 -93 l48 -31 78 77
                           77 76 44 -45 44 -45 -76 -78 -76 -77 39 -60 c34 -53 76 -159 93 -237 l6 -28
                           105 0 105 0 0 -65 0 -65 -105 0 -105 0 -6 -27 c-23 -108 -57 -191 -111 -275
                           l-18 -28 73 -73 72 -73 -45 -44 -45 -44 -68 67 c-37 37 -72 67 -79 67 -6 0
                           -37 -16 -69 -35 -65 -39 -144 -71 -216 -86 l-48 -11 0 -104 0 -104 -65 0 -65
                           0 0 103 0 104 -82 22 c-75 20 -116 38 -215 97 l-32 19 -74 -73 -73 -72 -44 45
                           -44 45 73 74 73 73 -35 54 c-42 67 -83 169 -92 232 l-7 47 -109 0 -109 0 0 65
                           0 65 110 0 c103 0 110 1 110 20 0 40 50 173 90 241 l42 71 -73 74 -73 74 44
                           45 44 45 76 -75 76 -75 55 35 c64 40 164 79 227 90 l42 7 0 109 0 109 65 0 65
                           0 0 -108z"/>
                           <path d="M3670 1583 c-63 -23 -151 -113 -173 -177 -9 -27 -17 -77 -17 -111 0
                           -92 24 -151 89 -216 67 -67 123 -89 226 -89 60 0 82 5 133 31 69 34 105 71
                           139 145 34 74 39 133 19 207 -24 89 -76 153 -155 194 -56 29 -72 33 -139 32
                           -43 0 -97 -8 -122 -16z m204 -183 c70 -61 70 -148 1 -209 -86 -75 -225 -12
                           -225 102 0 81 58 137 142 137 38 0 54 -6 82 -30z"/>
                           </g>
                           </svg>',
                            'title' => is_siteadmin() ? get_string('usermanager', 'moodle') : get_string('groupsmanager','moodle'),
                            'url' => is_siteadmin() ? new moodle_url('/admin/user.php') : new moodle_url('/cohort/index.php?contextid=1'),
                            'isactiveitem' => is_siteadmin() ? $this->isMenuActive('/admin/user.php') : $this->isMenuActive('/cohort/index.php?contextid=1'),
                            'itemid' => 'itemCourseManager',
                            'visability' => true,
                        ),
                        //Báo cáo
                        array(
                            'status' => !isguestuser(),
                            'icon' => '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                            width="26px" height="26px" viewBox="0 0 820.000000 512.000000"
                            preserveAspectRatio="xMidYMid meet">
                           <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                           fill="#000000" stroke="none">
                           <path d="M1894 4600 c-81 -12 -158 -53 -223 -118 -65 -65 -103 -131 -120 -210
                           -16 -75 -16 -3349 0 -3424 17 -79 55 -145 120 -210 68 -69 142 -106 237 -119
                           40 -6 939 -8 2242 -7 l2175 3 58 23 c109 44 205 140 249 250 l23 57 0 1715 0
                           1715 -23 58 c-44 109 -140 205 -250 249 l-57 23 -2190 1 c-1204 1 -2213 -2
                           -2241 -6z m4365 -341 c65 -23 61 96 61 -1699 0 -1795 4 -1676 -61 -1699 -45
                           -16 -4273 -16 -4318 0 -65 23 -61 -96 -61 1698 0 1266 3 1636 12 1657 26 56
                           -100 53 2204 54 1535 0 2140 -3 2163 -11z"/>
                           <path d="M5295 3918 c-3 -7 -4 -623 -3 -1368 l3 -1355 340 0 340 0 0 1365 0
                           1365 -338 3 c-265 2 -339 0 -342 -10z"/>
                           <path d="M3252 2388 l3 -1193 338 -3 337 -2 0 1195 0 1195 -340 0 -340 0 2
                           -1192z"/>
                           <path d="M4270 2045 l0 -855 338 2 337 3 3 853 2 852 -340 0 -340 0 0 -855z"/>
                           <path d="M2222 1708 l3 -513 340 0 340 0 0 510 0 510 -343 3 -342 2 2 -512z"/>
                           </g>
                           </svg>
                           ',
                            'title' => get_string('reports', 'moodle'),
                            'url' => new moodle_url('/reportbuilder/index.php', array('contextid' => 1)),
                            'isactiveitem' => $this->isMenuActive('/reportbuilder'),
                            'itemid' => 'itemReportBuilder',
                            'visability' => true,
                        ),
                        // array(
                        //     'status' => !isguestuser() ,
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V6.75Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 8.75V19"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8.25H19"></path></svg>',
                        //     'title' => get_string('myhome', 'moodle'),
                        //     'url' => new moodle_url('/my/'),
                        //     'isactiveitem' => $this->isMenuActive('/my'),
                        //     'itemid' => 'itemDashboard',
                        //     'visability' => true,
                        //     ),
                        // array(
                        //     'status' => !isguestuser(),
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 11.75L17.6644 6.20056C17.4191 5.34195 16.6344 4.75 15.7414 4.75H8.2586C7.36564 4.75 6.58087 5.34196 6.33555 6.20056L4.75 11.75"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.2142 12.3689C9.95611 12.0327 9.59467 11.75 9.17085 11.75H4.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H17.25C18.3546 19.25 19.25 18.3546 19.25 17.25V11.75H14.8291C14.4053 11.75 14.0439 12.0327 13.7858 12.3689C13.3745 12.9046 12.7276 13.25 12 13.25C11.2724 13.25 10.6255 12.9046 10.2142 12.3689Z"></path></svg>',
                        //     'title' => get_string('privatefiles', 'moodle'),
                        //     'url' => new moodle_url('/user/files.php'),
                        //     'isactiveitem' => $this->isMenuActive('/user/files'),
                        //     'itemid' => 'itemFiles',
                        //     'visability' => true,
                        //     ),
                        // array(
                        //     'status' => !isguestuser(),
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 8.75C4.75 7.64543 5.64543 6.75 6.75 6.75H17.25C18.3546 6.75 19.25 7.64543 19.25 8.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V8.75Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4.75V8.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 4.75V8.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.75 10.75H16.25"></path></svg>',
                        //     'title' => get_string('calendar', 'calendar'),
                        //     'url' => $calendarurl,
                        //     'isactiveitem' => $this->isMenuActive('/calendar'),
                        //     'itemid' => 'itemCalendar',
                        //     'visability' => true,
                        //     ),
                        // array(
                        //     'status' => $hascontentbankpermission,
                        //     'icon' => '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 17.25V9.75C19.25 8.64543 18.3546 7.75 17.25 7.75H4.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H17.25C18.3546 19.25 19.25 18.3546 19.25 17.25Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 7.5L12.5685 5.7923C12.2181 5.14977 11.5446 4.75 10.8127 4.75H6.75C5.64543 4.75 4.75 5.64543 4.75 6.75V11"></path></svg>',
                        //     'title' => get_string('contentbank', 'moodle'),
                        //     'url' => new moodle_url('/contentbank/index.php', array('contextid' => 1)),
                        //     'isactiveitem' => $this->isMenuActive('/contentbank'),
                        //     'itemid' => 'itemContentBank',
                        //     'visability' => true,
                        //     ),


                    ),
                ];
            }

            return $this->render_from_template('theme_alpha/nav-main', $headerlinks);
        }
    }



    public function adminheaderlink()
    {
        global $PAGE, $COURSE, $CFG, $USER, $OUTPUT;

        $course = $this->page->course;
        $context = context_course::instance($course->id);
        $hasadminlink = has_capability('moodle/site:configview', $context);
        if(is_siteadmin()){
            if (is_siteadmin() || $hasadminlink) {
                $adminlinktitle = get_string('administrationsite', 'moodle');
                $adminlinkurl = new moodle_url('/admin/search.php');
    
                // Send to template
                $adminlinkheaderlinktmpl = [
                    'admintitle' => $adminlinktitle,
                    'adminurl' => $adminlinkurl
                ];
    
                return $this->render_from_template('theme_alpha/btn-admin', $adminlinkheaderlinktmpl);
            }
        }
        if(is_teacher() || is_teacher()){
            $content = "
                <ul class='rui-flatnavigation rui-flatnavigation-box pt-0 pb-1 mt-0'>
                    <a class='rui-sidebar-nav-item-link' href='$CFG->wwwroot/user/edit.php?id=$USER->id'>
                        <span class='rui-sidebar-nav-icon'>
                            <svg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
                            <path d='M5.62117 14.9627L6.72197 15.1351C7.53458 15.2623 8.11491 16.0066 8.05506 16.8451L7.97396 17.9816C7.95034 18.3127 8.12672 18.6244 8.41885 18.7686L9.23303 19.1697C9.52516 19.3139 9.87399 19.2599 10.1126 19.0352L10.9307 18.262C11.5339 17.6917 12.4646 17.6917 13.0685 18.262L13.8866 19.0352C14.1252 19.2608 14.4733 19.3139 14.7662 19.1697L15.5819 18.7678C15.8733 18.6244 16.0489 18.3135 16.0253 17.9833L15.9441 16.8451C15.8843 16.0066 16.4646 15.2623 17.2772 15.1351L18.378 14.9627C18.6985 14.9128 18.9568 14.6671 19.0292 14.3433L19.23 13.4428C19.3025 13.119 19.1741 12.7831 18.9064 12.5962L17.9875 11.9526C17.3095 11.4774 17.1024 10.5495 17.5119 9.82051L18.067 8.83299C18.2284 8.54543 18.2017 8.18538 17.9993 7.92602L17.4363 7.2035C17.2339 6.94413 16.8969 6.83701 16.5867 6.93447L15.5221 7.26794C14.7355 7.51441 13.8969 7.1012 13.5945 6.31908L13.1866 5.26148C13.0669 4.95218 12.7748 4.7492 12.4496 4.75L11.5472 4.75242C11.222 4.75322 10.9307 4.95782 10.8126 5.26793L10.4149 6.31344C10.1157 7.1004 9.27319 7.51683 8.4842 7.26874L7.37553 6.92078C7.0645 6.82251 6.72591 6.93044 6.52355 7.19142L5.96448 7.91474C5.76212 8.17652 5.73771 8.53738 5.90228 8.82493L6.47 9.81487C6.88812 10.5446 6.68339 11.4814 6.00149 11.9591L5.0936 12.5954C4.82588 12.7831 4.69754 13.119 4.76998 13.442L4.97077 14.3425C5.04242 14.6671 5.30069 14.9128 5.62117 14.9627Z' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'></path>
                            <path d='M13.5911 10.4089C14.4696 11.2875 14.4696 12.7125 13.5911 13.5911C12.7125 14.4696 11.2875 14.4696 10.4089 13.5911C9.53036 12.7125 9.53036 11.2875 10.4089 10.4089C11.2875 9.53036 12.7125 9.53036 13.5911 10.4089Z' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'></path>
                            </svg>
                        </span>
                        <span class='rui-sidebar-nav-text'>Cài đặt chung</span>
                    </a>
                </ul>
                ";
            return $content;
        }
    }

    public function customeditblockbtn()
    {
        global $USER, $COURSE, $CFG;
        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->pageheadingbutton = $this->page_heading_button();

        $html = $this->render_from_template('theme_alpha/header_settings_menu', $header);

        return $html;
    }


    // My Course Menu - Inspred by Fordson Theme
    public function alpha_allcourseslink()
    {
        global $CFG;
        $allcourseicon = '';
        $allcoursetxt = theme_alpha_get_setting('stringallcourses');

        if (!empty($allcoursetxt)) {
            $allcourses = "<hr class=\"rui-sidebar-hr\"/><a class=\"rui-course-menu-list--more\" href=\"$CFG->wwwroot/course/index.php\">" . $allcoursetxt . '</a>';
        } else {
            $allcourses = '';
        }

        return $allcourses;
    }

    public function alpha_mycourses_heading()
    {
        global $CFG;
        $url = new moodle_url('/my/courses.php');
        $allcourseicon = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
        <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
        </svg>';

        $detailstxt = theme_alpha_get_setting('stringdetails');
        if (!empty($detailstxt)) {
            $html = '<a class="rui-course-menu-list--more mt-1" href="' . $url . '">' . $detailstxt . '</a><hr class="rui-sidebar-hr"/>';
        } else {
            $html = '<a class="rui-course-menu-list--more mt-1" href="' . $url . '">' . get_string('details', 'moodle') . $allcourseicon . '</a><hr class="rui-sidebar-hr"/>';
        }

        return $html;
    }


    public function alpha_mycourses_heading_text()
    {
        global $CFG;
        $html = null;
        $count = null;
        $lang = current_language();
        if ($lang == "en") {
            $txt = theme_alpha_get_setting('stringmycourses');
        } else {
            $txt = "Khoá học đã đăng ký";
        }


        $courses = enrol_get_my_courses(null); // more info about sorting etc. -> lib/enrollib.php
        foreach ($courses as $course) {
            if ($course->visible) {
                $count++;
            }
        }

        if ($count > 0) {
            $html .= '<span>' . $txt . '</span><span class="rui-drawer-badge ml-auto">' . $count . '</span>';
        } else {
            $html .= '<span>' . $txt . '</span>';
        }

        return $html;
    }


    public function alpha_mycourses()
    {
        global $DB, $USER;

        $courses = enrol_get_my_courses(null, 'fullname ASC'); // more info about sorting etc. -> lib/enrollib.php
        $nomycoursestxt = theme_alpha_get_setting('stringnocourses');
        $nomycourses = '<div class="alert alert-info alert-block m-0">' . $nomycoursestxt . '</div>';
        if ($courses) {

            // Determine if we need to query the enrolment and user enrolment tables.
            $enrolquery = false;
            foreach ($courses as $course) {
                if (empty($course->timeaccess)) {
                    $enrolquery = true;
                    break;
                }
            }
            if ($enrolquery) {
                $params = array(
                    'userid' => $USER->id
                );
                $sql = "SELECT ue.id, e.courseid, ue.timestart
                    FROM {enrol} e
                    JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)";

                $enrolments = $DB->get_records_sql($sql, $params, 0, 0);

                if ($enrolments) {
                    // Sort out any multiple enrolments on the same course.
                    $userenrolments = array();
                    foreach ($enrolments as $enrolment) {
                        if (!empty($userenrolments[$enrolment->courseid])) {
                            if ($userenrolments[$enrolment->courseid] < $enrolment->timestart) {
                                // Replace.
                                $userenrolments[$enrolment->courseid] = $enrolment->timestart;
                            }
                        } else {
                            $userenrolments[$enrolment->courseid] = $enrolment->timestart;
                        }
                    }

                    // We don't need to worry about timeend etc. as our course list will be valid for the user from above.
                    foreach ($courses as $course) {
                        if (empty($course->timeaccess)) {
                            $course->timestart = $userenrolments[$course->id];
                        }
                    }
                }
            }
        } else {
            return $nomycourses;
        }

        $content = '';
        foreach ($courses as $course) {
            if ($course->visible) {
                $url = new moodle_url('/course/view.php?id=' . $course->id);
                $name = '<span class="rui-course-menu-list-text">' . format_string($course->fullname) . '</span>';
                $shortname = format_string($course->shortname);
                $checkactive = $this->isMenuActive('/course/view.php?id=' . $course->id);
                $isactive = '';
                $icon = '<div class="rui-sidebar-nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.25 15.25V5.75C19.25 5.19772 18.8023 4.75 18.25 4.75H6.75C5.64543 4.75 4.75 5.64543 4.75 6.75V16.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19.25 15.25H6.75C5.64543 15.25 4.75 16.1454 4.75 17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H19.25V15.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>';
                if ($checkactive == true) {
                    $isactive .= format_string("active");
                }
                $content .= '<li class="rui-sidebar-nav-item"><a href="' . $url . '" class="rui-sidebar-nav-item-link rui-sidebar-nav-item-link--sm ' . $isactive . '" title="' . $shortname . '">' . $icon . $name . '</a></li>';
                //$branch->add(format_string($course->fullname), new moodle_url('/course/view.php?id=' . $course->id) , format_string($course->shortname));
            }
        }

        return '<ul class="rui-flatnavigation p-0">' . $content . '</ul>';
    }


    /**
     * Renders the context header for the page.
     *
     * @param array $headerinfo Heading information.
     * @param int $headinglevel What 'h' level to make the heading.
     * @return string A rendered context header.
     */
    public function context_header($headerinfo = null, $headinglevel = 1): string
    {
        global $DB, $USER, $CFG, $SITE;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $subheader = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if ((isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) && $this->page->pagetype !== 'my-index') {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 'message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $contacttitle = $iscontact ? 'removefromyourcontacts' : 'addtoyourcontacts';
                        $contacturlaction = $iscontact ? 'removecontact' : 'addcontact';
                        $contactimage = $iscontact ? 'removecontact' : 'addcontact';
                        $userbuttons['togglecontact'] = array(
                            'buttontype' => 'togglecontact',
                            'title' => get_string($contacttitle, 'message'),
                            'url' => new moodle_url(
                                '/message/index.php',
                                array(
                                    'user1' => $USER->id,
                                    'user2' => $user->id,
                                    $contacturlaction => $user->id,
                                    'sesskey' => sesskey()
                                )
                            ),
                            'image' => $contactimage,
                            'linkattributes' => \core_message\helper::togglecontact_link_params($user, $iscontact),
                            'page' => $this->page
                        );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        $prefix = null;
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($this->page->course->format === 'singleactivity') {
                $heading = $this->page->course->fullname;
            } else {
                $heading = $this->page->cm->get_formatted_name();
                $imagedata = $this->pix_icon('monologo', '', $this->page->activityname, ['class' => 'activityicon']);
                $purposeclass = plugin_supports('mod', $this->page->activityname, FEATURE_MOD_PURPOSE);
                $purposeclass .= ' activityiconcontainer';
                $purposeclass .= ' modicon_' . $this->page->activityname;
                $imagedata = html_writer::tag('div', $imagedata, ['class' => $purposeclass]);
                $prefix = get_string('modulename', $this->page->activityname);
            }
        }


        $contextheader = new \context_header($heading, $headinglevel, $imagedata, $userbuttons, $prefix);
        return $this->render_context_header($contextheader);
    }

    /**
     * Renders the header bar.
     *
     * @param context_header $contextheader Header bar object.
     * @return string HTML for the header bar.
     */
    protected function render_context_header(context_header $contextheader)
    {
        $heading = null;
        $imagedata = null;
        $html = null;

        // Generate the heading first and before everything else as we might have to do an early return.
        if (!isset($contextheader->heading)) {
            $heading = $this->heading($this->page->heading, $contextheader->headinglevel);
        } else {
            $heading = $this->heading($contextheader->heading, $contextheader->headinglevel);
        }

        $showheader = empty($this->page->layout_options['nocontextheader']);
        if (!$showheader) {
            return '';
        }

        if (isset($contextheader->imagedata)) {
            $html .= html_writer::start_div('page-context-header page-content-header--img flex-wrap');
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image');
        }

        // Headings.
        if (!isset($contextheader->heading)) {
            $html .= html_writer::tag('h3', $heading, array('class' => 'rui-page-title rui-page-title--page'));
        } elseif (isset($contextheader->imagedata)) {
            $html .= html_writer::tag('div', $this->heading($contextheader->heading, 4), array('class' => 'rui-page-title rui-page-title--icon'));
        } else {
            $html .= html_writer::tag('h2', $contextheader->heading, array('class' => 'rui-page-title rui-page-title--context'));
        }




        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('header-button-group mt-2 mt-md-0 ml-md-2');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon(
                        $button['formattedimage'],
                        $button['title'],
                        'moodle',
                        array(
                            'class' => 'iconsmall',
                            'role' => 'presentation'
                        )
                    );
                    $image .= html_writer::span($button['title'], 'header-button-title ml-2');
                } else {
                    $image = html_writer::empty_tag(
                        'img',
                        array(
                            'src' => $button['formattedimage'],
                            'role' => 'presentation'
                        )
                    );
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }

        if (isset($contextheader->imagedata)) {
            $html .= html_writer::end_div();
        }
        return $html;
    }

    /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null)
    {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            if (!$loginpage) {
                $returnstr .= "<a class=\"rui-topbar-btn rui-login-btn\" href=\"$loginurl\"><span class=\"rui-login-btn-txt\">" . get_string('login') . '</span><svg class="ml-2" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 8.75L13.25 12L9.75 15.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H9.75"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 12H4.75"></path></svg></a>';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $icon = '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 12C10 12.5523 9.55228 13 9 13C8.44772 13 8 12.5523 8 12C8 11.4477 8.44772 11 9 11C9.55228 11 10 11.4477 10 12Z" fill="currentColor" /><path d="M15 13C15.5523 13 16 12.5523 16 12C16 11.4477 15.5523 11 15 11C14.4477 11 14 11.4477 14 12C14 12.5523 14.4477 13 15 13Z" fill="currentColor" /><path fill-rule="evenodd" clip-rule="evenodd" d="M12.0244 2.00003L12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.74235 17.9425 2.43237 12.788 2.03059L12.7886 2.0282C12.5329 2.00891 12.278 1.99961 12.0244 2.00003ZM12 20C16.4183 20 20 16.4183 20 12C20 11.3014 19.9105 10.6237 19.7422 9.97775C16.1597 10.2313 12.7359 8.52461 10.7605 5.60246C9.31322 7.07886 7.2982 7.99666 5.06879 8.00253C4.38902 9.17866 4 10.5439 4 12C4 16.4183 7.58172 20 12 20ZM11.9785 4.00003L12.0236 4.00003L12 4L11.9785 4.00003Z" fill="currentColor" /></svg>';
            $returnstr = '<div class="rui-badge-guest">' . $icon . get_string('loggedinasguest') . '</div>';
            if (!$loginpage && $withlinks) {
                $returnstr .= "<a class=\"rui-topbar-btn rui-login-btn\" href=\"$loginurl\"><span class=\"rui-login-btn-txt\">" . get_string('login') . '</span><svg class="ml-2" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 8.75L13.25 12L9.75 15.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H9.75"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 12H4.75"></path></svg></a>';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page, array('avatarsize' => 56));

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = '<span class="rui-fullname">' . $opts->metadata['userfullname'] . '</span>';
        $usertextmail = $user->email;
        $usernick = '<svg class="mr-1" width="16" height="16" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
        <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
        </svg>' . $user->username;
        /* TODO: Add quick user card
        $useraddress = $user->address;
        $usercity = $user->city;
        $usercountry = $user->country;
        $userinstitution = $user->institution;
        $userdepartment = $user->department;
        $userphone1 = $user->phone1;
        $userphone2 = $user->phone2;*/

        // Other user.
        $usermeta = '';
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usermeta .= $opts->metadata['realuserfullname'];
            $usermeta .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usermeta .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usermeta .= html_writer::div(
                '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usermeta .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            //html_writer::span($usermeta, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger(
            $returnstr
        );
        $am->set_action_label(get_string('usermenu'));
        $am->set_nowrap_on_items();

        $am->add(
            '<div class="dropdown-user-wrapper"><div class="dropdown-user">' . $usertextcontents . '</div>'
                . '<div class="dropdown-user-mail text-truncate" title="' . $usertextmail . '">' . $usertextmail . '</div>'
                . '<span class="dropdown-user-nick badge badge-xs badge-info mr-2">' . $usernick . '</span>'
                . '<div class="dropdown-user-meta badge badge-sq badge-xs badge-warning mx-0">' . $usermeta . '</div>'
                . '</div><div class="dropdown-divider dropdown-divider-user"></div>
        <div class="dropdown-item-wrapper"><a class="dropdown-item" href="' . new moodle_url('/my/') . '" data-identifier="dashboard,moodle" title="dashboard,moodle">' . get_string('myhome', 'moodle') . '</a></div>
        '
        );

        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        $al = '<a class="dropdown-item" href="' . $value->url . '" data-identifier="' . $value->titleidentifier . '" title="' . $value->titleidentifier . '">' . $value->title . '</a>';
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }


    /**
     * Returns standard main content placeholder.
     * Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function main_content()
    {
        // This is here because it is the only place we can inject the "main" role over the entire main content area
        // without requiring all theme's to manually do it, and without creating yet another thing people need to
        // remember in the theme.
        // This is an unfortunate hack. DO NO EVER add anything more here.
        // DO NOT add classes.
        // DO NOT add an id.
        return '<div class="main-content" role="main">' . $this->unique_main_content_token . '</div>';
    }

    /**
     * Outputs a heading
     *
     * @param string $text The text of the heading
     * @param int $level The level of importance of the heading. Defaulting to 2
     * @param string $classes A alpha-separated list of CSS classes. Defaulting to null
     * @param string $id An optional ID
     * @return string the HTML to output.
     */
    public function heading($text, $level = 2, $classes = null, $id = null)
    {
        $level = (int) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        return html_writer::tag('div', html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => renderer_base::prepare_classes($classes) . ' rui-main-content-title rui-main-content-title--h' . $level)), array('class' => 'rui-title-container'));
    }


    public function headingwithavatar($text, $level = 2, $classes = null, $id = null)
    {
        $level = (int) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        return html_writer::tag('div', html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => renderer_base::prepare_classes($classes) . ' rui-main-content-title-with-avatar')), array('class' => 'rui-title-container-with-avatar'));
    }

    /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form)
    {
        global $CFG, $SITE, $PAGE;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string(
            $SITE->fullname,
            true,
            ['context' => context_course::instance(SITEID), "escape" => false]
        );

        $templatecontext = [];

        if ($PAGE->theme->settings->setloginlayout == 1) {
            $context->loginlayout1 = 1;
        } elseif ($PAGE->theme->settings->setloginlayout == 2) {
            $context->loginlayout2 = 1;
            if (isset($PAGE->theme->settings->loginbg)) {
                $context->loginlayoutimg = 1;
            }
        } elseif ($PAGE->theme->settings->setloginlayout == 3) {
            $context->loginlayout3 = 1;
            if (isset($PAGE->theme->settings->loginbg)) {
                $context->loginlayoutimg = 1;
            }
        }

        if (isset($PAGE->theme->settings->loginlogooutside)) {
            $context->loginlogooutside = $PAGE->theme->settings->loginlogooutside;
        }

        if (isset($PAGE->theme->settings->customsignupoutside)) {
            $context->customsignupoutside = $PAGE->theme->settings->customsignupoutside;
        }

        if (isset($PAGE->theme->settings->stringca)) {

            $lang = current_language();
            if ($lang == 'en') {
                $context->stringca = format_text(($PAGE->theme->settings->stringca), FORMAT_HTML, array('noclean' => true));
            } else {
                $context->stringca = format_text(("Bạn chưa có tài khoản?"), FORMAT_HTML, array('noclean' => true));
            }
        }

        if (isset($PAGE->theme->settings->loginhtmlcontent1)) {
            $context->loginhtmlcontent1 = format_text(($PAGE->theme->settings->loginhtmlcontent1), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->loginhtmlcontent2)) {
            $context->loginhtmlcontent2 = format_text(($PAGE->theme->settings->loginhtmlcontent2), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->loginhtmlcontent3)) {
            $context->loginhtmlcontent3 = format_text(($PAGE->theme->settings->loginhtmlcontent3), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->loginhtmlcontent2)) {
            $context->loginhtmlcontent2 = format_text(($PAGE->theme->settings->loginhtmlcontent2), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->loginfootercontent)) {
            $context->loginfootercontent = format_text(($PAGE->theme->settings->loginfootercontent), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->loginintrotext)) {
            $context->loginintrotext = format_text(($PAGE->theme->settings->loginintrotext), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->loginintrotext)) {
            $context->loginintrotext = format_text(($PAGE->theme->settings->loginintrotext), FORMAT_HTML, array('noclean' => true));
        }

        if (isset($PAGE->theme->settings->customloginlogo)) {
            $context->customloginlogo = $PAGE->theme->setting_file_url('customloginlogo', 'customloginlogo');
        }

        if (isset($PAGE->theme->settings->loginbg)) {
            $context->loginbg = $PAGE->theme->setting_file_url('loginbg', 'loginbg');
        }

        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Render the login signup form into a nice template for the theme.
     *
     * @param mform $form
     * @return string
     */
    public function render_login_signup_form($form)
    {
        global $CFG, $SITE, $PAGE;

        $context = $form->export_for_template($this);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context['logourl'] = $url;
        $context['sitename'] = format_string(
            $SITE->fullname,
            true,
            ['context' => context_course::instance(SITEID), "escape" => false]
        );

        if (isset($PAGE->theme->settings->stringbacktologin)) {
            $context['stringbacktologin'] = format_text(($PAGE->theme->settings->stringbacktologin), FORMAT_HTML, array('noclean' => true));
        }
        if (isset($PAGE->theme->settings->signupintrotext)) {
            $context['signupintrotext'] = format_text(($PAGE->theme->settings->signupintrotext), FORMAT_HTML, array('noclean' => true));
        }
        if (isset($PAGE->theme->settings->signuptext)) {
            $context['signuptext'] = format_text(($PAGE->theme->settings->signuptext), FORMAT_HTML, array('noclean' => true));
        }

        if (!empty($this->page->theme->settings->customloginlogo)) {
            $url = $this->page->theme->setting_file_url('customloginlogo', 'customloginlogo');
            $context['customloginlogo'] = $url;
        }

        return $this->render_from_template('core/signup_form_layout', $context);
    }


    /**
     * See if this is the first view of the current cm in the session if it has fake blocks.
     *
     * (We track up to 100 cms so as not to overflow the session.)
     * This is done for drawer regions containing fake blocks so we can show blocks automatically.
     *
     * @return boolean true if the page has fakeblocks and this is the first visit.
     */
    public function firstview_fakeblocks(): bool
    {
        global $SESSION;

        $firstview = false;
        if ($this->page->cm) {
            if (!$this->page->blocks->region_has_fakeblocks('side-pre')) {
                return false;
            }
            if (!property_exists($SESSION, 'firstview_fakeblocks')) {
                $SESSION->firstview_fakeblocks = [];
            }
            if (array_key_exists($this->page->cm->id, $SESSION->firstview_fakeblocks)) {
                $firstview = false;
            } else {
                $SESSION->firstview_fakeblocks[$this->page->cm->id] = true;
                $firstview = true;
                if (count($SESSION->firstview_fakeblocks) > 100) {
                    array_shift($SESSION->firstview_fakeblocks);
                }
            }
        }
        return $firstview;
    }

    // function theme_alpha_get_course_information_banners() {
    //     global $CFG, $COURSE, $PAGE, $USER;

    //     // Initialize HTML code.
    //     $html = '';

    //     // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
    //     // a hint for the visibility will be shown.
    //     if (get_config('theme_alpha', 'showhintcoursehidden') == 'yes'
    //             && $PAGE->has_set_url()
    //             && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
    //             && $COURSE->visible == false) {
    //         $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning wrapper-md'));
    //         $html .= html_writer::start_tag('div', array('class' => 'media'));
    //         $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
    //         $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
    //         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
    //         <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
    //         <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
    //         </svg>';
    //         $html .= html_writer::end_tag('div');
    //         $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
    //         $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
    //         // If the user has the capability to change the course settings, an additional link to the course settings is shown.
    //         if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
    //             $html .= html_writer::tag('div', get_string('showhintcoursehiddensettingslink',
    //                     'theme_alpha', array('url' => $CFG->wwwroot.'/course/edit.php?id='. $COURSE->id)));
    //         }
    //         $html .= html_writer::end_tag('div');
    //         $html .= html_writer::end_tag('div');
    //         $html .= html_writer::end_tag('div');
    //     }

    //     // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
    //     // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
    //     // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
    //     // intended.
    //     if (get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 'yes'
    //             && is_guest(\context_course::instance($COURSE->id), $USER->id)
    //             && $PAGE->has_set_url()
    //             && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
    //             && !is_role_switched($COURSE->id)) {
    //         $html .= html_writer::start_tag('div', array('class' => 'rui-course-guestaccess-infobox alert alert-warning mt-4'));
    //         $html .= html_writer::start_tag('div', array('class' => 'media'));
    //         $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
    //         $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
    //         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path>
    //         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path>
    //         <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
    //         </svg>';
    //         $html .= html_writer::end_tag('div');
    //         $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
    //         $html .= get_string('showhintcourseguestaccesssettinggeneral', 'theme_alpha',
    //                 array('role' => role_get_name(get_guest_role())));
    //         $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
    //         $html .= html_writer::end_tag('div');
    //         $html .= html_writer::end_tag('div');
    //         $html .= html_writer::end_tag('div');
    //     }

    //     // If the setting showhintcourseselfenrol is set, a hint for users is shown that the course allows unrestricted self
    //     // enrolment. This hint is only shown if the course is visible, the self enrolment is visible and if the user has the
    //     // capability "theme/alpha:viewhintcourseselfenrol".
    //     if (get_config('theme_alpha', 'showhintcourseselfenrol') == 'yes'
    //             && $PAGE->has_set_url()
    //             && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
    //             && $COURSE->visible == true) {
    //         // Get the active enrol instances for this course.
    //         $enrolinstances = enrol_get_instances($COURSE->id, true);
    //         // Prepare to remember when self enrolment is / will be possible.
    //         $selfenrolmentpossiblecurrently = false;
    //         $selfenrolmentpossiblefuture = false;
    //         foreach ($enrolinstances as $instance) {
    //             // Check if unrestricted self enrolment is possible currently or in the future.
    //             $now = (new \DateTime("now", \core_date::get_server_timezone_object()))->getTimestamp();
    //             if ($instance->enrol == 'self' && empty($instance->password) && $instance->customint6 == 1 &&
    //                     (empty($instance->enrolenddate) || $instance->enrolenddate > $now)) {

    //                 // Build enrol instance object with all necessary information for rendering the note later.
    //                 $instanceobject = new stdClass();

    //                 // Remember instance name.
    //                 if (empty($instance->name)) {
    //                     $instanceobject->name = get_string('pluginname', 'enrol_self') .
    //                             " (" . get_string('defaultcoursestudent', 'core') . ")";
    //                 } else {
    //                     $instanceobject->name = $instance->name;
    //                 }

    //                 // Remember type of unrestrictedness.
    //                 if (empty($instance->enrolenddate) && empty($instance->enrolstartdate)) {
    //                     $instanceobject->unrestrictedness = 'unlimited';
    //                     $selfenrolmentpossiblecurrently = true;
    //                 } else if (empty($instance->enrolstartdate) &&
    //                         !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
    //                     $instanceobject->unrestrictedness = 'until';
    //                     $selfenrolmentpossiblecurrently = true;
    //                 } else if (empty($instance->enrolenddate) &&
    //                         !empty($instance->enrolstartdate) && $instance->enrolstartdate > $now) {
    //                     $instanceobject->unrestrictedness = 'from';
    //                     $selfenrolmentpossiblefuture = true;
    //                 } else if (empty($instance->enrolenddate) &&
    //                         !empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now) {
    //                     $instanceobject->unrestrictedness = 'since';
    //                     $selfenrolmentpossiblecurrently = true;
    //                 } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate > $now &&
    //                         !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
    //                     $instanceobject->unrestrictedness = 'fromuntil';
    //                     $selfenrolmentpossiblefuture = true;
    //                 } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now &&
    //                         !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
    //                     $instanceobject->unrestrictedness = 'sinceuntil';
    //                     $selfenrolmentpossiblecurrently = true;
    //                 } else {
    //                     // This should not happen, thus continue to next instance.
    //                     continue;
    //                 }

    //                 // Remember enrol start date.
    //                 if (!empty($instance->enrolstartdate)) {
    //                     $instanceobject->startdate = $instance->enrolstartdate;
    //                 } else {
    //                     $instanceobject->startdate = null;
    //                 }

    //                 // Remember enrol end date.
    //                 if (!empty($instance->enrolenddate)) {
    //                     $instanceobject->enddate = $instance->enrolenddate;
    //                 } else {
    //                     $instanceobject->enddate = null;
    //                 }

    //                 // Remember this instance.
    //                 $selfenrolinstances[$instance->id] = $instanceobject;
    //             }
    //         }

    //         // If there is at least one unrestricted enrolment instance,
    //         // show the hint with information about each unrestricted active self enrolment in the course.
    //         if (!empty($selfenrolinstances) &&
    //                 ($selfenrolmentpossiblecurrently == true || $selfenrolmentpossiblefuture == true)) {
    //             // Start hint box.
    //             $html .= html_writer::start_tag('div', array('class' => 'rui-course-selfenrol-infobox alert alert-info mt-4'));
    //             $html .= html_writer::start_tag('div', array('class' => 'media'));
    //             $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
    //             $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
    //             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 8.75L13.25 12L9.75 15.25"></path>
    //             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H9.75"></path>
    //             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 12H4.75"></path>
    //             </svg>';
    //             $html .= html_writer::end_tag('div');
    //             $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));

    //             // Show the start of the hint depending on the fact if enrolment is already possible currently or
    //             // will be in the future.
    //             if ($selfenrolmentpossiblecurrently == true) {
    //                 $html .= get_string('showhintcourseselfenrolstartcurrently', 'theme_alpha');
    //             } else if ($selfenrolmentpossiblefuture == true) {
    //                 $html .= get_string('showhintcourseselfenrolstartfuture', 'theme_alpha');
    //             }
    //             $html .= html_writer::empty_tag('br');

    //             // Iterate over all enrolment instances to output the details.
    //             foreach ($selfenrolinstances as $selfenrolinstanceid => $selfenrolinstanceobject) {
    //                 // If the user has the capability to config self enrolments, enrich the instance name with the settings link.
    //                 if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
    //                     $url = new moodle_url('/enrol/editinstance.php', array('courseid' => $COURSE->id,
    //                             'id' => $selfenrolinstanceid, 'type' => 'self'));
    //                     $selfenrolinstanceobject->name = html_writer::link($url, $selfenrolinstanceobject->name);
    //                 }

    //                 // Show the enrolment instance information depending on the instance configuration.
    //                 if ($selfenrolinstanceobject->unrestrictedness == 'unlimited') {
    //                     $html .= get_string('showhintcourseselfenrolunlimited', 'theme_alpha',
    //                             array('name' => $selfenrolinstanceobject->name));
    //                 } else if ($selfenrolinstanceobject->unrestrictedness == 'until') {
    //                     $html .= get_string('showhintcourseselfenroluntil', 'theme_alpha',
    //                             array('name' => $selfenrolinstanceobject->name,
    //                                     'until' => userdate($selfenrolinstanceobject->enddate)));
    //                 } else if ($selfenrolinstanceobject->unrestrictedness == 'from') {
    //                     $html .= get_string('showhintcourseselfenrolfrom', 'theme_alpha',
    //                             array('name' => $selfenrolinstanceobject->name,
    //                                     'from' => userdate($selfenrolinstanceobject->startdate)));
    //                 } else if ($selfenrolinstanceobject->unrestrictedness == 'since') {
    //                     $html .= get_string('showhintcourseselfenrolsince', 'theme_alpha',
    //                             array('name' => $selfenrolinstanceobject->name,
    //                                     'since' => userdate($selfenrolinstanceobject->startdate)));
    //                 } else if ($selfenrolinstanceobject->unrestrictedness == 'fromuntil') {
    //                     $html .= get_string('showhintcourseselfenrolfromuntil', 'theme_alpha',
    //                             array('name' => $selfenrolinstanceobject->name,
    //                                     'until' => userdate($selfenrolinstanceobject->enddate),
    //                                     'from' => userdate($selfenrolinstanceobject->startdate)));
    //                 } else if ($selfenrolinstanceobject->unrestrictedness == 'sinceuntil') {
    //                     $html .= get_string('showhintcourseselfenrolsinceuntil', 'theme_alpha',
    //                             array('name' => $selfenrolinstanceobject->name,
    //                                     'until' => userdate($selfenrolinstanceobject->enddate),
    //                                     'since' => userdate($selfenrolinstanceobject->startdate)));
    //                 }

    //                 // Add a trailing alpha to separate this instance from the next one.
    //                 $html .= ' ';
    //             }

    //             // If the user has the capability to config self enrolments, add the call for action.
    //             if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
    //                 $html .= html_writer::empty_tag('br');
    //                 $html .= get_string('showhintcourseselfenrolinstancecallforaction', 'theme_alpha');
    //             }

    //             // End hint box.
    //             $html .= html_writer::end_tag('div');
    //             $html .= html_writer::end_tag('div');
    //             $html .= html_writer::end_tag('div');
    //         }
    //     }

    //     // Return HTML code.
    //     return $html;
    // }

    /**
     * Build the guest access hint HTML code.
     *
     * @param int $courseid The course ID.
     * @return string.
     */
    function theme_alpha_get_course_guest_access_hint($courseid)
    {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/self/lib.php');

        $html = '';
        $instances = enrol_get_instances($courseid, true);
        $plugins = enrol_get_plugins(true);
        foreach ($instances as $instance) {
            if (!isset($plugins[$instance->enrol])) {
                continue;
            }
            $plugin = $plugins[$instance->enrol];
            if ($plugin->show_enrolme_link($instance)) {
                $html = html_writer::tag(
                    'div',
                    get_string(
                        'showhintcourseguestaccesssettinglink',
                        'theme_alpha',
                        array('url' => $CFG->wwwroot . '/enrol/index.php?id=' . $courseid)
                    )
                );
                break;
            }
        }

        return $html;
    }
}
