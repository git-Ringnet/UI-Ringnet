<?php
require_once('../config.php');
require_once('lib.php');
require_once('edit_form.php');

require_login();
global $CFG, $COURSE, $DB, $PAGE;
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

if ($course->summary == "") {
    $msg = get_string('courdescription', 'moodle');
} else {
    $msg = $course->summary;
}
$output = "
<div class='show_course_stu' style='margin-top:24px;'>
    <div class='row'>
        <div class='col-md-4'>  
            <div id='stu_id_overviewfiles_filemanager w-100'>
                <label style='
                width: -webkit-fill-available;
            '>" . get_string('courseoverviewfiles', 'moodle') . "</label>
                <img src='$img' style='width:70%;'>
            </div>
        </div>
        <div class='col-md-8'>
            <div id='stu_id_fullname w-100'>
                <label>" . get_string('fullnamecourse', 'moodle') . "</label>
                <input class='form-control ' size='50' maxlength='254'
                value='$course->fullname' readonly>
            </div>
            <div id='stu_id_category w-100' style='margin-top:24px;'>
                <label>" . get_string('coursecategory', 'moodle') . "</label>
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
                <label>" . get_string('coursesummary', 'moodle') . "</label>
                <div  class='w-100' style='padding:5px 20px; border-radius:5px; border: 1px solid #D6D6D6; background:#fff; display:inline-block;'>
                <div id='summary'>";
if (strlen($course->summary) < 1) {
    $output .= get_string('courdescription', 'moodle');
} else $output .= substr($course->summary, 0, 500);
$output .= "
                </div>";
if (strlen($course->summary) > 500) {
    $output .= "<button id='show-more'>Hiện thêm</button>";
}
$output .= "     
            </div>
        </div>
    </div>
</div>
";
$button = "
<div id='btn_sty' style='text-align:center; margin-top:24px;'>
    <div id='share' class='btn btn-share'>
    <svg stroke='currentColor' fill='currentColor' stroke-width='0' 
    viewBox='0 0 1024 1024' height='1em' width='1em' 
    xmlns='http://www.w3.org/2000/svg' style='font-size:20px;'>
    <path d='M752 664c-28.5 0-54.8 10-75.4 26.7L469.4 540.8a160.68 160.68 0 0 0 0-57.6l207.2-149.9C697.2 350 723.5 360 752 360c66.2 0 120-53.8 120-120s-53.8-120-120-120-120 53.8-120 120c0 11.6 1.6 22.7 4.7 33.3L439.9 415.8C410.7 377.1 364.3 352 312 352c-88.4 0-160 71.6-160 160s71.6 160 160 160c52.3 0 98.7-25.1 127.9-63.8l196.8 142.5c-3.1 10.6-4.7 21.8-4.7 33.3 0 66.2 53.8 120 120 120s120-53.8 120-120-53.8-120-120-120zm0-476c28.7 0 52 23.3 52 52s-23.3 52-52 52-52-23.3-52-52 23.3-52 52-52zM312 600c-48.5 0-88-39.5-88-88s39.5-88 88-88 88 39.5 88 88-39.5 88-88 88zm440 236c-28.7 0-52-23.3-52-52s23.3-52 52-52 52 23.3 52 52-23.3 52-52 52z'
    style='margin-right:5px;'>
    </path>
    </svg>
    Chia sẻ</div>
    <div id='save_course' class='btn btn-save'>
    <svg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
    <path fill-rule='evenodd' clip-rule='evenodd' d='M16.5801 9.21967C16.873 9.51256 16.873 9.98744 16.5801 10.2803L11.5801 15.2803C11.2872 15.5732 10.8124 15.5732 10.5195 15.2803L7.51947 12.2803C7.22658 11.9874 7.22658 11.5126 7.51947 11.2197C7.81237 10.9268 8.28724 10.9268 8.58013 11.2197L11.0498 13.6893L15.5195 9.21967C15.8124 8.92678 16.2872 8.92678 16.5801 9.21967Z' fill='#0095F6'/>
    <path fill-rule='evenodd' clip-rule='evenodd' d='M6.25641 5.23077C5.68961 5.23077 5.23077 5.68961 5.23077 6.25641V17.7436C5.23077 18.3104 5.68961 18.7692 6.25641 18.7692H17.7436C18.3104 18.7692 18.7692 18.3104 18.7692 17.7436V6.25641C18.7692 5.68961 18.3104 5.23077 17.7436 5.23077H6.25641ZM4 6.25641C4 5.00988 5.00988 4 6.25641 4H17.7436C18.9901 4 20 5.00988 20 6.25641V17.7436C20 18.9901 18.9901 20 17.7436 20H6.25641C5.00988 20 4 18.9901 4 17.7436V6.25641Z' fill='#0095F6'/>
    </svg>    
    Đánh dấu</div>
</div>
";

// echo substr($course->summary, 0, 500);

$course = $DB->get_record('course', ['id' => $COURSE->id]);
$content = html_writer::start_div('course-teachers-box');
$pages = new stdClass();
$content = html_writer::start_div('course-navigation');
if (is_siteadmin()) {
    $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id . '&returnto=catmanage';
} else if (is_teacher()) {
    if (is_course_creator($COURSE->id)) {
        $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id . '&returnto=catmanage';
    } else {
        $urledit = $CFG->wwwroot . '/course/show.php?id=' . $course->id;
    }
} else {
    $urledit = $CFG->wwwroot . '/course/show.php?id=' . $course->id;
}
$content = html_writer::start_div('course-navigation');
//$urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id . '&returnto=catmanage';
$urlcontent = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
$urlparticipant = $CFG->wwwroot . '/user/index.php?id=' . $course->id;
$urlbades = $CFG->wwwroot . '/badges/view.php?type=2&id=' . $course->id;
$urlgrades = $CFG->wwwroot . '/grade/report/grader/index.php?id=' . $course->id;
$urlforum = '#';

$pages = new stdClass();
$pages->urledit = ['title' => 'Thông tin', 'url' => $urledit];
$pages->urlcontent = ['title' => 'Bài học', 'url' => $urlcontent];
$pages->urlforum = ['title' => 'Thảo luận', 'url' => $urlforum];
if(is_siteadmin() || is_teacher()){
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
?>
<script>
    const html = document.getElementById("summary");
    const x = document.getElementById("show-more");
    x.addEventListener("click", function() {
        if (html.innerHTML === '<?php echo $msg ?>') {
            html.innerHTML = '<?php echo substr($msg, 0, 500) ?>';
            x.innerHTML = 'Hiện thêm';
        } else {
            html.innerHTML = '<?php echo $msg ?>';
            x.innerHTML = 'Ẩn bớt';
        }
    })
</script>
<?php
//Thêm button
echo $button;
//echo $OUTPUT->heading($pagedesc);

//$editform->display();

echo $OUTPUT->footer();
