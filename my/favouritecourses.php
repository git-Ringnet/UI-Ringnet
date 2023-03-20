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
 * My Courses.
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 *
 * @package    core
 * @subpackage my
 * @copyright  2021 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

redirect_if_major_upgrade_required();

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$context = context_system::instance();

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page(null, MY_PAGE_PUBLIC, MY_PAGE_COURSES)) {
    throw new Exception('mymoodlesetup');
}

// Start setting up the page.
$PAGE->set_context($context);
$PAGE->set_url('/my/courses.php');
$PAGE->add_body_classes(['limitedwidth', 'page-mycourses']);
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title(get_string('mycourses'));
$PAGE->set_heading(get_string('mycourses'));

// No blocks can be edited on this page (including by managers/admins) because:
// - Course overview is a fixed item on the page and cannot be moved/removed.
// - We do not want new blocks on the page.
// - Only global blocks (if any) should be visible on the site panel, and cannot be moved int othe centre pane.
$PAGE->force_lock_all_blocks();

// Force the add block out of the default area.
$PAGE->theme->addblockposition  = BLOCK_ADDBLOCK_POSITION_CUSTOM;

// Add course management if the user has the capabilities for it.
$coursecat = core_course_category::user_top();
$coursemanagemenu = [];
if ($coursecat && ($category = core_course_category::get_nearest_editable_subcategory($coursecat, ['create']))) {
    // The user has the capability to create course.
    $coursemanagemenu['newcourseurl'] = new moodle_url('/course/edit.php', ['category' => $category->id]);
}
if ($coursecat && ($category = core_course_category::get_nearest_editable_subcategory($coursecat, ['manage']))) {
    // The user has the capability to manage the course category.
    $coursemanagemenu['manageurl'] = new moodle_url('/course/management.php', ['categoryid' => $category->id]);
}
if (!empty($coursemanagemenu)) {
    // Render the course management menu.
    $PAGE->add_header_action($OUTPUT->render_from_template('my/dropdown', $coursemanagemenu));
}

echo $OUTPUT->header();
$mustache = new Mustache_Engine;

if (core_userfeedback::should_display_reminder()) {
    core_userfeedback::print_reminder_block();
}

$courses = get_courses();

// Lấy danh sách các khóa học yêu thích
$favourite_courses = array();
foreach ($courses as $course) {
    // Kiểm tra xem itemid của khóa học có trong danh sách itemid của bảng favourite hay không
    $favourite = $DB->get_record('favourite', array(
        'userid' => $USER->id,
        'itemtype' => 'courses',
        'itemid' => $course->id
    ));
    if ($favourite) {
        $favourite_courses[] = $course;
    }
}

$courses = array();
$course_records = $DB->get_records('course');
$name = 'a';
$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
$checkRole = false;
if (is_siteadmin() == true || is_teacher() == true) {
    $checkRole = true;
} else {
    $checkRole = false;
}

$a = count($teachers);
// var_dump($items);
$status = array(
    'status' => true
);
$results = $DB->get_records('favourite', array(
    'userid' => $USER->id,
    'itemtype' => 'courses'
));

$item_ids = array();
foreach ($results as $favourite) {
    $item_ids[] = $favourite->itemid;
}
$related=false;

foreach ($favourite_courses as $course) {
    $context = context_course::instance($course->id);
    $teachers = get_role_users($role->id, $context);
    foreach ($teachers as $teacher) {
        $name = fullname($teacher);
    }
    if (in_array($course->id, $item_ids)) {
        // $idcourse is in $item_ids array
        $related = true;
    } else {
        // $idcourse is not in $item_ids array
        $related = false;
    }
    $rolestudent = $DB->get_record('role', array('shortname' => 'student'));
    $student = get_role_users($rolestudent->id, $context);
    $count = count($student);
    $coursecategory = \core_course_category::get($course->category, MUST_EXIST, true);
    $courses[] = array(
        'viewurl' => (new moodle_url('/course/view.php', array('id' => $course->id)))->out(false),
        'id' => $course->id,
        'fullname' => $course->fullname,
        'name' =>  $name,
        'student' =>  $count,
        'coursecategory' => $coursecategory->name,
        'student' =>  $count,
        'checkRole' => $checkRole,
        'status' => true,
        'isfavourite' => $related,
        'checkRole' => checkRole(),
        'hidden' => boolval(get_user_preferences('block_myoverview_hidden_course_' . $course->id, 0)),
    );
}


// echo $OUTPUT->render_from_template('block_myoverview/nav-course',$status);

// Chỉ truyền dữ liệu vào mà không render ra
// var_dump($courses);  
echo $OUTPUT->render_from_template('block_myoverview/main-all-course', array(
    'courses' => $courses,
    'status' => is_siteadmin() || is_teacher(),
    'activefavourites' => true,
    'creator' => (new moodle_url('/my/coursesall.php') || new moodle_url('/my/courses.php')),

));

echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\mycourses_viewed::create($eventparams);
$event->trigger();
