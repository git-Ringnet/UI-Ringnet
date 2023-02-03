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
 * Course renderer.
 *
 * @package   theme_alpha
 * @copyright 2022 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 */

namespace theme_alpha\output\core;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use html_writer;
use core_course_category;
use coursecat_helper;
use stdClass;
use core_course_list_element;
use single_select;
use lang_string;
use context_system;
use curl;
use single_button;
use cm_info;
use core_text;
use completion_info;
use course_handler;


/**
 * @package    theme_alpha
 * @copyright  2022 Marcin Czaja - Rosea Themes - rosea.io
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \core_course_renderer
{


    /**
     * Renders html to display a course search form
     *
     * @param string $value default value to populate the search field
     * @return string
     */
    public function course_search_form($value = '')
    {

        $data = [
            'action' => \core_search\manager::get_course_search_url(),
            'btnclass' => 'btn-primary',
            'inputname' => 'q',
            'searchstring' => get_string('searchcourses'),
            'hiddenfields' => (object) ['name' => 'areaids', 'value' => 'core_course-course'],
            'query' => $value
        ];

        return $this->render_from_template('core/search_input_fw', $data);
    }


    /**
     * Renders the list of courses
     *
     * This is internal function, please use core_course_renderer::courses_list() or another public
     * method from outside of the class
     *
     * If list of courses is specified in $courses; the argument $chelper is only used
     * to retrieve display options and attributes, only methods get_show_courses(),
     * get_courses_display_option() and get_and_erase_attributes() are called.
     *
     * @param coursecat_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param int|null $totalcount total number of courses (affects display mode if it is AUTO or pagination if applicable),
     *     defaulted to count($courses)
     * @return string
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null)
    {
        global $CFG;

        $theme = \theme_config::load('alpha');

        if (!empty($theme->settings->courselistview)) {
            return parent::coursecat_courses($chelper, $courses, $totalcount);
        }

        if ($totalcount === null) {
            $totalcount = count($courses);
        }

        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit.
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // Prepare content of paging bar if it is needed.
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // There are more results that can fit on one page.
            if ($paginationurl) {
                // The option paginationurl was specified, display pagingbar.
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // There are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode.
            $pagingbar = html_writer::tag(
                'div',
                html_writer::link(
                    $paginationurl->out(
                        false,
                        array('perpage' => $CFG->coursesperpage)
                    ),
                    get_string('showperpage', '', $CFG->coursesperpage)
                ),
                array('class' => 'paging paging-showperpage')
            );
        }

        // Display list of courses.
        $attributes = $chelper->get_and_erase_attributes('courses');

        $content = html_writer::start_tag('div', $attributes);
        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $coursecount = 1;
        $content .= html_writer::start_tag('div', array('class' => 'rui-course-card-deck rui-course-card-deck--4 mt-2'));
        foreach ($courses as $course) {
            $content .= $this->coursecat_coursebox($chelper, $course);

            if ($coursecount % 4 == 0) {
                $content .= html_writer::end_tag('div');
                $content .= html_writer::start_tag('div', array('class' => 'rui-course-card-deck rui-course-card-deck--4 mt-2'));
            }

            $coursecount++;
        }

        $content .= html_writer::end_tag('div');

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // End courses.
        return $content;
    }

    function delete_moodle_category($categoryid)
    {
        global $CFG, $USER, $DB;

        // Xác thực người dùng có quyền để xóa category hay không
        require_login();
        $context = context_system::instance();
        require_capability('moodle/category:manage', $context);

        // Lấy thông tin category cần xóa
        $category = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);

        // Gọi Moodle web service function core_course_delete_categories để xóa category
        $functionname = 'core_course_delete_categories';
        $serverurl = $CFG->wwwroot . '/webservice/rest/server.php' . '?wstoken=' . $USER->token . '&wsfunction=' . $functionname;
        $restformat = '&moodlewsrestformat=json';
        $categories = array($categoryid);
        $params = array('categories' => $categories);
        $options = array(
            'CURLOPT_POST' => 1,
            'CURLOPT_POSTFIELDS' => http_build_query($params)
        );
        $curl = new curl;
        $response = $curl->post($serverurl . $restformat, $params, $options);
        $response = json_decode($response);
        if (!empty($response->exception)) {
            // Xảy ra lỗi khi gọi Moodle web service function
            return $response->message;
        } else {
            // Xóa category thành công
            return true;
        }
    }



    protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth)
    {
        global $CFG;
        $subcategories = array();
        if (!$chelper->get_categories_display_option('nodisplay')) {
            $subcategories = $coursecat->get_children($chelper->get_categories_display_options());
        }
        $totalcount = $coursecat->get_children_count();
        if (!$totalcount) {
            // Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children()
            // to avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        // prepare content of paging bar or more link if it is needed
        $paginationurl = $chelper->get_categories_display_option('paginationurl');
        $paginationallowall = $chelper->get_categories_display_option('paginationallowall');
        if ($totalcount > count($subcategories)) {
            if ($paginationurl) {
                // the option 'paginationurl was specified, display pagingbar
                $perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_categories_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
                // the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                    $viewmoreurl->param('categoryid', $coursecat->id);
                }
                $viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext, array('class' => 'btn btn-secondary w-100')),
                    array('class' => 'paging paging-morelink wrapper-fw')
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of subcategories
        $content = html_writer::start_tag('div', array('class' => 'subcategories'));

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        $content .= "<form method='POST' action='$CFG->wwwroot/course/index.php'>";
        $content .= ' <table class="table table-striped projects">
        <thead>
            <tr>
                <th style="width: 10%;font-size: 1rem;">
                <input type="checkbox" id="checkall" onclick="checkAll()">
                </th>
                <th style="width: 30%;font-size: 1rem;">
                    Danh mục
                </th>
                <th style="width: 8%;font-size: 1rem;" class="text-right">
                   Khóa học
                </th>
                <th style="width: 50%;font-size: 1rem;" class="text-center">

                </th>   
            </tr>
        </thead>
    </table>';
        foreach ($subcategories as $subcategory) {
            $content .= $this->coursecat_category($chelper, $subcategory, $depth);
        }
        $url = new moodle_url('/your/delete_script.php', array('sesskey' => sesskey()));
        $button = new single_button($url, get_string('delete'), 'post');
        $button->add_confirm_action(get_string('confirmdelete', 'your_plugin'));
        global $SESSION;
        // Lấy danh sách các người dùng được chọn
        $selected_categories = optional_param_array('selected_categories', array(), PARAM_INT);
        // Lấy thao tác hàng loạt được chọn
        $bulk_action = optional_param('bulk_action', '', PARAM_ALPHA);
     
            // Nếu có người dùng được chọn và thao tác hàng loạt được chọn, thực hiện thao tác
            switch ($bulk_action) {
                case 'delete':
                    unset($SESSION->bulk_category);
                    // Xóa các người dùng được chọn
                    foreach ($selected_categories as $category) {
                        $SESSION->bulk_category[$category] = $category;
                    }
                    redirect($CFG->wwwroot . '/course/bulkdelete_categories.php');
                    break;
            }
            // var_dump($SESSION->bulk_category);
        
        $content .= "<p>
        <label for='bulk_action'>Bulk action:</label>
        <select name='bulk_action' id='bulk_action'>
          <option value=''>-- Choose an action --</option>
          <option value='delete'>Delete</option>
          <option value='sendessage'>Send Message</option>
        </select>
        <input type='submit' value='Go'/>
        </p>";
        $content .= "</form>";



        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div');
        return $content;
    }




    /**
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_coursebox() and may be re-used in AJAX
     *
     * @param coursecat_helper $chelper various display options
     * @param stdClass|core_course_list_element $course
     *
     * @return string
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course)
    {
        global $PAGE;
        $theme = \theme_config::load('alpha');

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $coursename = $chelper->get_course_formatted_name($course);
        $courselink = new moodle_url('/course/view.php', array('id' => $course->id));
        $coursenamelink = html_writer::link($courselink, $coursename);

        $content = html_writer::start_tag('div', ['class' => 'rui-course-card-icons']);
        $content .= $this->course_enrolment_icons($course);
        $content .= html_writer::end_tag('div');

        $content .= html_writer::start_tag('div', ['class' => 'rui-course-box']);
        $content .= $this->get_course_summary_image($course, $courselink);

        $content .= html_writer::start_tag('div', ['class' => 'rui-course-content-absolute']);
        $content .= $this->course_category_name($chelper, $course);
        $content .= $this->course_custom_fields($course);
        $content .= $this->course_card_body($chelper, $course, $coursenamelink);

        $content .= html_writer::end_tag('div');

        if ($PAGE->theme->settings->cccteachers == 1) {
            $content .= $this->course_contacts($course);
        }
        if ($PAGE->theme->settings->cccfooter == 1) {
            $content .= $this->course_card_footer($course);
        }
        $content .= html_writer::end_tag('div');

        return $content;
    }


    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use core_course_renderer::course_info_box()
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '')
    {
        $theme = \theme_config::load('alpha');

        if (!empty($theme->settings->courselistview)) {
            return parent::coursecat_coursebox($chelper, $course, $additionalclasses);
        }

        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }

        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        //
        $additionalclass = '';
        $courselink = new moodle_url('/course/view.php', array('id' => $course->id));
        if ($this->get_course_summary_image($course, $courselink) == '') {
            $additionalclass .= 'rui-course-card--noimg';
        }
        if ($course->visible === '0') {
            $additionalclass .= ' rui-course-card--dimmed';
        }
        //


        $classes = trim('rui-course-card ' . $additionalclass . ' ' . $additionalclasses);
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $classes .= ' collapsed';
        }

        // End coursebox.
        $content = html_writer::start_tag('div', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => self::COURSECAT_TYPE_COURSE,
        ));

        $content .= $this->coursecat_coursebox_content($chelper, $course);

        $content .= html_writer::end_tag('div'); // End coursebox.

        return $content;
    }


    /**
     * Returns HTML to display a tree of subcategories and courses in the given category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat top category (this category's name and description will NOT be added to the tree)
     * @return string
     */
    protected function coursecat_tree(coursecat_helper $chelper, $coursecat)
    {
        // Reset the category expanded flag for this course category tree first.
        $this->categoryexpandedonload = false;
        $categorycontent = $this->coursecat_category_content($chelper, $coursecat, 1);
        if (empty($categorycontent)) {
            return '';
        }

        // Start content generation
        $content = '';
        $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
        $content .= html_writer::start_tag('div', $attributes);

        if ($coursecat->get_children_count()) {
            $classes = array(
                'collapseexpand', 'aabtn'
            );

            // Check if the category content contains subcategories with children's content loaded.
            if ($this->categoryexpandedonload) {
                $classes[] = 'collapse-all';
                $linkname = get_string('collapseall');
            } else {
                $linkname = get_string('expandall');
            }

            // Only show the collapse/expand if there are children to expand.
            $content .= html_writer::start_tag('div', array('class' => 'wrapper-fw collapsible-actions position-relative'));
            $content .= html_writer::link('#', $linkname, array('class' => implode(' ', $classes)));
            $content .= html_writer::end_tag('div');
            $this->page->requires->strings_for_js(array('collapseall', 'expandall'), 'moodle');
        }

        $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));

        $content .= html_writer::end_tag('div'); // .course_category_tree

        return $content;
    }


    /**
     * Returns HTML to display a course category as a part of a tree
     *
     * This is an internal function, to display a particular category and all its contents
     * use {@link core_course_renderer::course_category()}
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of this category in the current tree
     * @return string
     */
    protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth)
    {
        global $CFG;
        // open category tag
        $classes = array('category');
        if (empty($coursecat->visible)) {
            $classes[] = 'dimmed_category';
        }
        if ($chelper->get_subcat_depth() > 0 && $depth >= $chelper->get_subcat_depth()) {
            // do not load content
            $categorycontent = '';
            $classes[] = 'notloaded';
            if (
                $coursecat->get_children_count() ||
                ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_COLLAPSED && $coursecat->get_courses_count())
            ) {
                $classes[] = 'with_children';
                $classes[] = 'collapsed';
            }
        } else {
            // load category content
            $categorycontent = $this->coursecat_category_content($chelper, $coursecat, $depth);
            $classes[] = 'loaded';
            if (!empty($categorycontent)) {
                $classes[] = 'with_children';
                // Category content loaded with children.
                $this->categoryexpandedonload = true;
            }
        }

        // Make sure JS file to expand category content is included.
        $this->coursecat_include_js();

        $content = html_writer::start_tag('div', array(
            'class' => join(' ', $classes),
            'data-categoryid' => $coursecat->id,
            'data-depth' => $depth,
            'data-showcourses' => $chelper->get_show_courses(),
            'data-type' => self::COURSECAT_TYPE_CATEGORY,
        ));
        // category name
        $categoryname = html_writer::link(
            new moodle_url(
                '/course/index.php',
                array('categoryid' => $coursecat->id)
            ),
            $coursecat->get_formatted_name(),
            array('class' => 'rui-category-link')
        );

        $icon = '<svg class="mr-2" width="20" height="20" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.25 5.75C19.25 5.19772 18.8023 4.75 18.25 4.75H14C12.8954 4.75 12 5.64543 12 6.75V19.25L12.8284 18.4216C13.5786 17.6714 14.596 17.25 15.6569 17.25H18.25C18.8023 17.25 19.25 16.8023 19.25 16.25V5.75Z"></path>
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.75 5.75C4.75 5.19772 5.19772 4.75 5.75 4.75H10C11.1046 4.75 12 5.64543 12 6.75V19.25L11.1716 18.4216C10.4214 17.6714 9.40401 17.25 8.34315 17.25H5.75C5.19772 17.25 4.75 16.8023 4.75 16.25V5.75Z"></path>
        </svg>';
        //Số khóa học trong 1 danh mục
        $coursescount = $coursecat->get_courses_count();

        $content .= html_writer::start_tag('div', array('class' => 'wrapper-fw info'));
        $content .= ' <table class="listcourse table table-striped" style="margin-bottom: 0 !important;">
        <thead>
            <tr>
            <th style="width: 10%;border-bottom: 0 !important;">';
        $attributes = array('class' => 'checkbox', 'onchange' => 'uncheckAll()');
        $content .= html_writer::checkbox('selected_categories[]', $coursecat->id, false, '', $attributes);
        $content .= '
            </th>
                <th style="width: 30%;border-bottom: 0 !important;">
                ' . $categoryname . '
                </th>
                <th style="width: 8%;border-bottom: 0 !important" class="text text-right">
                ' . $coursescount . '
                </th>
                <th style="width: 50%;border-bottom: 0 !important" class="text-center">
                <a href="' . $CFG->wwwroot . '/course/editcategory.php?id=' . $coursecat->id . '" class="dropdown-item dropdown-item-wrapper action-edit menu-action" data-action="edit" role="menuitem" tabindex="-1">
                                <span class="rui-icon-container"><img class="icon " alt="Edit" title="Edit" src="https://localhost/ringnet/theme/image.php/alpha/core/1672911781/t/edit"></span>
                                <span class="dropdown-item--text"></span>
                        </a>
                   <a href="' . $CFG->wwwroot . '/course/management.php?categoryid=' . $coursecat->id . '&amp;sesskey=' . sesskey() . '&amp;action=deletecategory" class="dropdown-item dropdown-item-wrapper action-delete menu-action" data-action="delete" role="menuitem" tabindex="-1" aria-labelledby="actionmenuaction-8">
                                <span class="rui-icon-container"><img class="icon " alt="Delete" title="Delete" src="https://localhost/ringnet/theme/image.php/alpha/core/1672911781/t/delete"></span>
                                <span class="dropdown-item--text"></span>
                        </a>
                </th>
            </tr>
        </thead>
    </table>';
        //     $content .= html_writer::start_tag('h5', array('class' => 'categoryname aabtn'));
        //         $content .= $categoryname;
        //         $acontent = '';
        //         if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
        //             $icon = '<svg class="ml-2" width="20" height="20" fill="none" viewBox="0 0 24 24">
        //             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.75 6.75L19.25 12L13.75 17.25"></path>
        //             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H4.75"></path>
        //             </svg>';
        //             // The option for 'View more' link was specified, display more link.
        //             $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new \lang_string('viewmore'));
        //                             // the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
        //             if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
        //                 $viewmoreurl->param('categoryid', $coursecat->id);
        //             }
        //             $acontent .= html_writer::tag('div', html_writer::link($viewmoreurl, $viewmoretext . $icon, array('class' => 'btn btn-sm btn-info ml-2')));
        //         }
        //         $content .= '<div class="rui-number-of-courses d-inline-flex">' . html_writer::tag('span', $coursescount, array('class' => 'badge-sq badge-secondary')) . $acontent . '</div>';

        //     $content .= html_writer::end_tag('h5');

        $content .= html_writer::end_tag('div'); // .info

        // add category content to the output
        $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));

        $content .= html_writer::end_tag('div'); // .category

        // Return the course category tree HTML
        return $content;
    }


    /**
     * Returns HTML to display course summary.
     *
     * @param coursecat_helper $chelper
     * @param core_course_list_element $course
     * @return string
     */
    protected function course_summary(coursecat_helper $chelper, core_course_list_element $course): string
    {
        global $PAGE;
        $theme = \theme_config::load('alpha');

        if (!empty($theme->settings->courselistview)) {
            return parent::course_summary($chelper, $course);
        }

        $content = '';
        if ($course->has_summary()) {
            //$content .= html_writer::start_tag('div', ['class' => 'rui-course-card-text']);
            $content .= '<div class="rui-course-card-text rui-course-card-text--a">';
            if (!empty($PAGE->theme->settings->coursecarddesclimit)) {
                //info https://github.com/moodle/moodle/blob/master/lib/classes/output/mustache_shorten_text_helper.php
                $content .= shorten_text($chelper->get_course_formatted_summary(
                    $course,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false)
                ), $PAGE->theme->settings->coursecarddesclimit);
            } else {
                $content .= $chelper->get_course_formatted_summary(
                    $course,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false)
                );
            }

            $content .= html_writer::end_tag('div'); // End summary.
        }

        return $content;
    }

    /**
     * Returns HTML to display course contacts.
     *
     * @param core_course_list_element $course
     *
     * @return string
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function course_contacts(core_course_list_element $course)
    {
        global $CFG, $DB;

        $theme = \theme_config::load('alpha');

        if (!empty($theme->settings->courselistview)) {
            return parent::course_contacts($course);
        }

        $content = '';
        if ($course->has_course_contacts()) {
            $content .= '<div class="rui-card-course-contacts">';

            $instructors = $course->get_course_contacts();
            foreach ($instructors as $key => $instructor) {
                $role = $instructor['rolename'];
                $roleshortname = $instructor['role']->shortname;

                $name = $instructor['username'];
                $url = $CFG->wwwroot . '/user/profile.php?id=' . $key;
                $picture = $this->get_user_picture($DB->get_record('user', array('id' => $key)));

                $content .= "<a href='{$url}' class='rui-card-contact rui-user-{$roleshortname}' data-toggle='tooltip' title='{$name} - {$role}'>";
                $content .= "<img src='{$picture}' class='rui-card-avatar' alt='{$name}'  title='{$name} - {$role}'/>";
                $content .= "</a>";
            }

            $content .= '</div>';
        }

        return $content;
    }



    /**
     * Returns the first course's summary issue
     *
     * @param \core_course_list_element $course
     * @param string $courselink
     *
     * @return string
     */
    public static function get_course_summary_image($course, $courselink)
    {
        global $CFG;

        $contentimage = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = moodle_url::make_file_url(
                "$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                !$isimage
            );
            if ($isimage) {
                $contentimage .= '<a href="' . $courselink . '"><figure class="rui-course-card-img-top" style="background-image: url(' . $url . ');"></figure></a>';
                break;
            }
        }

        if (empty($contentimage)) {
            //$url = $CFG->wwwroot . "/theme/alpha/pix/default_course.jpg";

            /*$contentimage = \html_writer::link($courselink, \html_writer::empty_tag('img', array(
                'src' => $url,
                'alt' => $course->fullname,
                'class' => 'rui-course-card-img-top w-100')))';*/
            $contentimage = '';
        }

        return $contentimage;
    }


    /**
     * Returns the user picture
     *
     * @param null $userobject
     * @param int $imgsize
     *
     * @return \moodle_url
     * @throws \coding_exception
     */
    public static function get_user_picture($userobject = null, $imgsize = 100)
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
     * Generates the course card body html
     *
     * @param coursecat_helper $chelper
     * @param core_course_list_element $course
     * @param string $coursenamelink
     *
     * @return string
     *
     * @throws \moodle_exception
     */
    protected function course_card_body(coursecat_helper $chelper, core_course_list_element $course, $coursenamelink)
    {
        $content = html_writer::start_tag('div', ['class' => 'rui-course-card-body']);
        $content .= html_writer::tag('h4', $coursenamelink, ['class' => 'rui-course-card-title']);
        $content .= $this->course_summary($chelper, $course);
        $content .= html_writer::end_tag('div');

        return $content;
    }


    /**
     * Returns HTML to display course category name.
     *
     * @param coursecat_helper $chelper
     * @param core_course_list_element $course
     *
     * @return string
     *
     * @throws \moodle_exception
     */
    protected function course_category_name(coursecat_helper $chelper, core_course_list_element $course): string
    {
        $theme = \theme_config::load('alpha');

        if (!empty($theme->settings->courselistview)) {
            return parent::course_category_name($chelper, $course);
        }

        $content = '';

        if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
            $content .= html_writer::start_tag('div', ['class' => 'rui-course-cat']);
            $content .= html_writer::link(
                new moodle_url('/course/index.php', ['categoryid' => $cat->id]),
                $cat->get_formatted_name(),
                ['class' => $cat->visible ? 'rui-course-cat-badge' : 'dimmed']
            );
            $content .= html_writer::end_tag('div');
        }

        return $content;
    }


    /**
     * Generates the course card footer html
     *
     * @param core_course_list_element $course
     *
     * @return string
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function course_card_footer(core_course_list_element $course)
    {
        $content = '';

        if (isloggedin()) {
            $stringaccess = theme_alpha_get_setting('stringaccess');
            $content .= html_writer::start_tag('div', ['class' => 'rui-course-card-footer']);
            $content .= html_writer::link(
                new moodle_url('/course/view.php', ['id' => $course->id]),
                '<span class="rui-course-card-link-text">' . format_text($stringaccess, FORMAT_HTML, array('noclean' => true)) . '</span><svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.75 6.75L19.25 12L13.75 17.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H4.75"></path></svg>',
                ['class' => 'rui-course-card-link']
            );
            $content .= html_writer::end_tag('div'); // End card-footer.
        }

        return $content;
    }



    /**
     * Returns HTML to display course enrolment icons.
     *
     * @param core_course_list_element $course
     * @return string
     */
    protected function course_enrolment_icons(core_course_list_element $course): string
    {
        $content = '';

        if ($icons = enrol_get_course_info_icons($course)) {
            foreach ($icons as $icon) {
                $content .= $this->render($icon);
            }
        }

        return $content;
    }
}
