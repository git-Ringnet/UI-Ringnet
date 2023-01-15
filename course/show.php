<?php
require_once('../config.php');
require_once('lib.php');
require_once('edit_form.php');

require_login();
global $CFG, $COURSE, $DB,$PAGE;
$id = optional_param('id', 0, PARAM_INT); // Course id.
$categoryid = optional_param('category', 0, PARAM_INT); // Course category - can be changed in edit form.
$returnto = optional_param('returnto', 0, PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL); // A return URL. returnto must also be set to 'url'.
$course = get_course($id);
require_login($course);

$streditcoursesettings = get_string("editcoursesettings");
$title = $streditcoursesettings;
$course = course_get_format($course)->get_course();
$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);


$img = \core_course\external\course_summary_exporter::get_course_image($course);

if($course->summary == ""){
    $msg = get_string('courdescription','moodle');
}else{
    $msg = $course->summary;
}
$output = "
<div class='show_course_stu' style='margin-top:24px;'>
    <div class='row'>
        <div class='col-md-4'>  
            <div id='stu_id_overviewfiles_filemanager w-100'>
                <label>". get_string('courseoverviewfiles', 'moodle')."</label>
                <img src='$img' style='width:70%;'>
            </div>
        </div>
        <div class='col-md-8'>
            <div id='stu_id_fullname w-100'>
                <label>". get_string('fullnamecourse', 'moodle')."</label>
                <input class='form-control ' size='50' maxlength='254'
                value='$course->fullname' readonly>
            </div>
            <div id='stu_id_category w-100' style='margin-top:24px;'>
                <label>". get_string('coursecategory', 'moodle')."</label>
                <div style='width:50%; position: relative;'>
                <input class='form-control '
                value='$category->name' readonly>
                <span class='form-autocomplete-downarrow' id='form_autocomplete_downarrow-1672894373756'>
                <svg width='18' height='18' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
                <path d='M12 15.25L16.25 9.75H7.75L12 15.25Z' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path>
                </svg>
                </span>
                </div>
            </div>
            <div id='stu_id_summary_editor w-100' style='margin-top:24px;'>
                <label>". get_string('coursesummary', 'moodle')."</label>
                <div class='w-100' style='padding:5px 20px; border-radius:5px; border:1px solid white; height:150px; background:#eeedef; display:inline-block; overflow: hidden; overflow-y: scroll;'>"
                .$msg."
                </div>
            </div>
        </div>
    </div>
</div>
";
$button = "
<div id='btn_sty' style='text-align:center; margin-top:24px;'>
    <div id='share' class='btn btn-primary'>
    <svg stroke='currentColor' fill='currentColor' stroke-width='0' 
    viewBox='0 0 1024 1024' height='1em' width='1em' 
    xmlns='http://www.w3.org/2000/svg' style='font-size:20px;'>
    <path d='M752 664c-28.5 0-54.8 10-75.4 26.7L469.4 540.8a160.68 160.68 0 0 0 0-57.6l207.2-149.9C697.2 350 723.5 360 752 360c66.2 0 120-53.8 120-120s-53.8-120-120-120-120 53.8-120 120c0 11.6 1.6 22.7 4.7 33.3L439.9 415.8C410.7 377.1 364.3 352 312 352c-88.4 0-160 71.6-160 160s71.6 160 160 160c52.3 0 98.7-25.1 127.9-63.8l196.8 142.5c-3.1 10.6-4.7 21.8-4.7 33.3 0 66.2 53.8 120 120 120s120-53.8 120-120-53.8-120-120-120zm0-476c28.7 0 52 23.3 52 52s-23.3 52-52 52-52-23.3-52-52 23.3-52 52-52zM312 600c-48.5 0-88-39.5-88-88s39.5-88 88-88 88 39.5 88 88-39.5 88-88 88zm440 236c-28.7 0-52-23.3-52-52s23.3-52 52-52 52 23.3 52 52-23.3 52-52 52z'
    style='margin-right:5px;'>
    </path>
    </svg>
    Share</div>
    <div id='save_course' class='btn btn-primary'>
    <svg stroke='currentColor' fill='currentColor' stroke-width='0' viewBox='0 0 24 24' height='1em' width='1em' 
    xmlns='http://www.w3.org/2000/svg' style='font-size:20px; margin-right:5px;'>
    <g id='Mail'><path d='M19.435,4.065H4.565a2.5,2.5,0,0,0-2.5,2.5v10.87a2.5,2.5,0,0,0,2.5,2.5h14.87a2.5,2.5,0,0,0,2.5-2.5V6.565A2.5,2.5,0,0,0,19.435,4.065Zm-14.87,1h14.87a1.489,1.489,0,0,1,1.49,1.39c-2.47,1.32-4.95,2.63-7.43,3.95a6.172,6.172,0,0,1-1.06.53,2.083,2.083,0,0,1-1.67-.39c-1.42-.75-2.84-1.51-4.25-2.26-1.14-.6-2.3-1.21-3.44-1.82A1.491,1.491,0,0,1,4.565,5.065Zm16.37,12.37a1.5,1.5,0,0,1-1.5,1.5H4.565a1.5,1.5,0,0,1-1.5-1.5V7.6c2.36,1.24,4.71,2.5,7.07,3.75a5.622,5.622,0,0,0,1.35.6,2.872,2.872,0,0,0,2-.41c1.45-.76,2.89-1.53,4.34-2.29,1.04-.56,2.07-1.1,3.11-1.65Z'>
    </path>
    </g>
    </svg>
    Đánh dấu</div>
</div>
";

$course = $DB->get_record('course', ['id' => $COURSE->id]);
$content = html_writer::start_div('course-teachers-box');
$context = context_course::instance($COURSE->id);
$roles = get_user_roles($context, $USER->id, true);
$role = key($roles);
$rolename = $roles[$role]->shortname;
$pages = new stdClass();
$content = html_writer::start_div('course-navigation');

if ($rolename == "student") {
    $urledit = $CFG->wwwroot . '/course/show.php?id=' . $course->id;
    $urlcontent = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
    $urldiscussion = $CFG->wwwroot . '/course/discussion.php?id=' . $course->id;
    $urlparticipant = $CFG->wwwroot . '/user/index.php?id=' . $course->id;
    $urlbades = $CFG->wwwroot . '/badges/view.php?type=2&id=' . $course->id;
    
    $pages->urledit = ['title' => get_string('info', 'moodle'), 'url' => $urledit];
    $pages->urlcontent = ['title' => get_string('content', 'moodle'), 'url' => $urlcontent];
    $pages->urldiscussion = ['title'=>  get_string('discussion', 'moodle'), 'url' => $urldiscussion];
    $pages->urlparticipant = ['title' => get_string('participants', 'moodle'), 'url' => $urlparticipant];
    $pages->urlbades = ['title' => get_string('badges', 'moodle'), 'url' => $urlbades];
} else {
    $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id . '&returnto=catmanage';
    $urlgrades = $CFG->wwwroot . '/grade/report/grader/index.php?id=' . $course->id;
    $urlcontent = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
    $urlparticipant = $CFG->wwwroot . '/user/index.php?id=' . $course->id;
    $urlbades = $CFG->wwwroot . '/badges/view.php?type=2&id=' . $course->id;

    $pages->urledit = ['title' => get_string('info', 'moodle'), 'url' => $urledit];
    $pages->urlcontent = ['title' => get_string('content', 'moodle'), 'url' => $urlcontent];
    $pages->urlparticipant = ['title' => get_string('participants', 'moodle'), 'url' => $urlparticipant];
    $pages->urlbades = ['title' => get_string('badges', 'moodle'), 'url' => $urlbades];
    $pages->urlgrades = ['title' => get_string('grades', 'moodle'), 'url' => $urlgrades];
}
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$urltest = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$content .= "<nav class='navbar navbar-expand-lg navbar-light'>
<div id='navbarNav'>
  <ul class='navbar-nav'>";
foreach ($pages as $key => $value) {
    $active = $urltest === $value['url'] ? 'active' : 'before';
    $content .=
        "<li class='nav-item {$active} mr-2'>
        <a class='nav-link title' href='{$value['url']}'>{$value['title']} <span class='sr-only'>(current)</span></a>
        </li>";
}
$content .= "</ul>
    </div>
  </nav> <hr/>";
//     echo '<br/>';
$content .= html_writer::end_div(); // navigation-box

$PAGE->set_title($title);
$PAGE->add_body_class('limitedwidth');
$PAGE->set_heading($fullname);

echo $OUTPUT->header();

//Thêm thanh navigate tại thông tin
echo $content;
//Thêm thông tin khóa học
echo $output;
//Thêm button
echo $button;
//echo $OUTPUT->heading($pagedesc);

//$editform->display();

echo $OUTPUT->footer();
