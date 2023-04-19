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

$context = context_course::instance($course->id);
$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
$teachers = get_role_users($role->id, $context);
foreach ($teachers as $teacher) {
    $teacher_name = fullname($teacher);
}
$count_content = get_fast_modinfo($course->id);
$total_sections = count($count_content->get_cms());
$num_users = count_enrolled_users(context_course::instance($course->id), '', 0);

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
                <div id='show_img_student'>
                    <img src='$img'>
                </div>
            </div>
            <div id='content_course_student'>
                <div class='d-flex'>
                    <div class='p-1'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
                        <path fill-rule='evenodd' clip-rule='evenodd' d='M12 3.75C9.81196 3.75 7.71354 4.61919 6.16637 6.16637C4.61919 7.71354 3.75 9.81196 3.75 12C3.75 14.188 4.61919 16.2865 6.16637 17.8336C7.71354 19.3808 9.81196 20.25 12 20.25C14.188 20.25 16.2865 19.3808 17.8336 17.8336C19.3808 16.2865 20.25 14.188 20.25 12C20.25 9.81196 19.3808 7.71354 17.8336 6.16637C16.2865 4.61919 14.188 3.75 12 3.75ZM5.10571 5.10571C6.93419 3.27723 9.41414 2.25 12 2.25C14.5859 2.25 17.0658 3.27723 18.8943 5.10571C20.7228 6.93419 21.75 9.41414 21.75 12C21.75 14.5859 20.7228 17.0658 18.8943 18.8943C17.0658 20.7228 14.5859 21.75 12 21.75C9.41414 21.75 6.93419 20.7228 5.10571 18.8943C3.27723 17.0658 2.25 14.5859 2.25 12C2.25 9.41414 3.27723 6.93419 5.10571 5.10571Z' fill='#555555'/>
                        <path fill-rule='evenodd' clip-rule='evenodd' d='M10.9392 8.18934C11.2205 7.90804 11.602 7.75 11.9998 7.75C12.3977 7.75 12.7792 7.90804 13.0605 8.18934C13.3418 8.47065 13.4998 8.85219 13.4998 9.25001C13.4998 9.64784 13.3418 10.0294 13.0605 10.3107C12.7792 10.592 12.3977 10.75 11.9998 10.75C11.602 10.75 11.2205 10.592 10.9392 10.3107C10.6579 10.0294 10.4998 9.64784 10.4998 9.25001C10.4998 8.85219 10.6579 8.47065 10.9392 8.18934ZM11.9998 6.25C12.7955 6.25 13.5585 6.56607 14.1212 7.12868C14.6838 7.6913 14.9998 8.45436 14.9998 9.25001C14.9998 10.0457 14.6838 10.8087 14.1212 11.3713C13.5585 11.934 12.7955 12.25 11.9998 12.25C11.2042 12.25 10.4411 11.934 9.8785 11.3713C9.31589 10.8087 8.99982 10.0457 8.99982 9.25001C8.99982 8.45436 9.31589 7.6913 9.8785 7.12868C10.4411 6.56607 11.2042 6.25 11.9998 6.25ZM13.457 13.338L10.5438 13.338C9.67389 13.3391 8.83192 13.6506 8.17111 14.2164C7.5103 14.7822 7.07302 15.5652 6.93792 16.4245C6.87359 16.8337 7.15316 17.2176 7.56234 17.2819C7.97153 17.3462 8.35539 17.0667 8.41972 16.6575C8.49936 16.1509 8.75713 15.6893 9.14667 15.3558C9.5361 15.0224 10.0317 14.8388 10.5443 14.838H13.4552C13.9678 14.839 14.4633 15.0226 14.8528 15.356C15.2423 15.6896 15.5002 16.1511 15.58 16.6577C15.6444 17.0669 16.0283 17.3463 16.4375 17.2819C16.8467 17.2174 17.1261 16.8335 17.0617 16.4243C16.9263 15.5651 16.489 14.7823 15.8283 14.2166C15.1676 13.6509 14.3268 13.3394 13.457 13.338Z' fill='#555555'/>
                        </svg>
                    </div>
                    <div class='p-1'>
                        <p class='title_course_student'>Giảng viên:</p>
                    </div>
                    <div class='p-1'>
                        <span class='bool_course_student'>$teacher_name</span>
                    </div>
                </div>

                <div class='d-flex'>
                    <div class='p-1'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
                        <path d='M12.2354 6.05867C12.163 6.02015 12.0823 6 12.0004 6C11.9184 6 11.8377 6.02015 11.7654 6.05867L4.26538 10.0587C4.1855 10.1013 4.11872 10.1649 4.07216 10.2426C4.02559 10.3203 4.001 10.4091 4.001 10.4997C4.001 10.5902 4.02559 10.6791 4.07216 10.7568C4.11872 10.8344 4.1855 10.898 4.26538 10.9407L7.18838 12.4997L4.26438 14.0587C4.1845 14.1013 4.11772 14.1649 4.07116 14.2426C4.02459 14.3203 4 14.4091 4 14.4997C4 14.5902 4.02459 14.6791 4.07116 14.7568C4.11772 14.8344 4.1845 14.898 4.26438 14.9407L11.7644 18.9407C11.8367 18.9792 11.9174 18.9993 11.9994 18.9993C12.0813 18.9993 12.162 18.9792 12.2344 18.9407L19.7344 14.9407C19.8142 14.898 19.881 14.8344 19.9276 14.7568C19.9742 14.6791 19.9988 14.5902 19.9988 14.4997C19.9988 14.4091 19.9742 14.3203 19.9276 14.2426C19.881 14.1649 19.8142 14.1013 19.7344 14.0587L16.8134 12.4997L19.7354 10.9407C19.8152 10.898 19.882 10.8344 19.9286 10.7568C19.9752 10.6791 19.9998 10.5902 19.9998 10.4997C19.9998 10.4091 19.9752 10.3203 19.9286 10.2426C19.882 10.1649 19.8152 10.1013 19.7354 10.0587L12.2354 6.05867ZM15.7504 13.0667L18.4384 14.4997L12.0004 17.9327L5.56238 14.4997L8.25038 13.0667L11.7654 14.9407C11.8377 14.9792 11.9184 14.9993 12.0004 14.9993C12.0823 14.9993 12.163 14.9792 12.2354 14.9407L15.7504 13.0667ZM12.0004 13.9327L5.56238 10.4997L12.0004 7.06667L18.4384 10.4997L12.0004 13.9327Z' fill='#555555'/>
                        </svg>
                    </div>
                    <div class='p-1'>
                        <p class='title_course_student'>Danh mục:</p>
                    </div>
                    <div class='p-1'>
                        <span class='bool_course_student'>$category->name</span>
                    </div>
                </div>

                <div class='d-flex'>
                    <div class='p-1'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
                        <path d='M9 14.5C9 14.3674 9.05268 14.2402 9.14645 14.1464C9.24021 14.0527 9.36739 14 9.5 14H11.5C11.6326 14 11.7598 14.0527 11.8536 14.1464C11.9473 14.2402 12 14.3674 12 14.5C12 14.6326 11.9473 14.7598 11.8536 14.8536C11.7598 14.9473 11.6326 15 11.5 15H9.5C9.36739 15 9.24021 14.9473 9.14645 14.8536C9.05268 14.7598 9 14.6326 9 14.5ZM9 12.5C9 12.3674 9.05268 12.2402 9.14645 12.1464C9.24021 12.0527 9.36739 12 9.5 12H14.5C14.6326 12 14.7598 12.0527 14.8536 12.1464C14.9473 12.2402 15 12.3674 15 12.5C15 12.6326 14.9473 12.7598 14.8536 12.8536C14.7598 12.9473 14.6326 13 14.5 13H9.5C9.36739 13 9.24021 12.9473 9.14645 12.8536C9.05268 12.7598 9 12.6326 9 12.5ZM9 10.5C9 10.3674 9.05268 10.2402 9.14645 10.1464C9.24021 10.0527 9.36739 10 9.5 10H14.5C14.6326 10 14.7598 10.0527 14.8536 10.1464C14.9473 10.2402 15 10.3674 15 10.5C15 10.6326 14.9473 10.7598 14.8536 10.8536C14.7598 10.9473 14.6326 11 14.5 11H9.5C9.36739 11 9.24021 10.9473 9.14645 10.8536C9.05268 10.7598 9 10.6326 9 10.5ZM9 8.5C9 8.36739 9.05268 8.24021 9.14645 8.14645C9.24021 8.05268 9.36739 8 9.5 8H14.5C14.6326 8 14.7598 8.05268 14.8536 8.14645C14.9473 8.24021 15 8.36739 15 8.5C15 8.63261 14.9473 8.75979 14.8536 8.85355C14.7598 8.94732 14.6326 9 14.5 9H9.5C9.36739 9 9.24021 8.94732 9.14645 8.85355C9.05268 8.75979 9 8.63261 9 8.5Z' fill='#555555'/>
                        <path d='M7 4H17C17.5304 4 18.0391 4.21071 18.4142 4.58579C18.7893 4.96086 19 5.46957 19 6V18C19 18.5304 18.7893 19.0391 18.4142 19.4142C18.0391 19.7893 17.5304 20 17 20H7C6.46957 20 5.96086 19.7893 5.58579 19.4142C5.21071 19.0391 5 18.5304 5 18V17H6V18C6 18.2652 6.10536 18.5196 6.29289 18.7071C6.48043 18.8946 6.73478 19 7 19H17C17.2652 19 17.5196 18.8946 17.7071 18.7071C17.8946 18.5196 18 18.2652 18 18V6C18 5.73478 17.8946 5.48043 17.7071 5.29289C17.5196 5.10536 17.2652 5 17 5H7C6.73478 5 6.48043 5.10536 6.29289 5.29289C6.10536 5.48043 6 5.73478 6 6V7H5V6C5 5.46957 5.21071 4.96086 5.58579 4.58579C5.96086 4.21071 6.46957 4 7 4Z' fill='#555555'/>
                        <path d='M5 9V8.5C5 8.36739 5.05268 8.24021 5.14645 8.14645C5.24021 8.05268 5.36739 8 5.5 8C5.63261 8 5.75979 8.05268 5.85355 8.14645C5.94732 8.24021 6 8.36739 6 8.5V9H6.5C6.63261 9 6.75979 9.05268 6.85355 9.14645C6.94732 9.24021 7 9.36739 7 9.5C7 9.63261 6.94732 9.75979 6.85355 9.85355C6.75979 9.94732 6.63261 10 6.5 10H4.5C4.36739 10 4.24021 9.94732 4.14645 9.85355C4.05268 9.75979 4 9.63261 4 9.5C4 9.36739 4.05268 9.24021 4.14645 9.14645C4.24021 9.05268 4.36739 9 4.5 9H5ZM5 12V11.5C5 11.3674 5.05268 11.2402 5.14645 11.1464C5.24021 11.0527 5.36739 11 5.5 11C5.63261 11 5.75979 11.0527 5.85355 11.1464C5.94732 11.2402 6 11.3674 6 11.5V12H6.5C6.63261 12 6.75979 12.0527 6.85355 12.1464C6.94732 12.2402 7 12.3674 7 12.5C7 12.6326 6.94732 12.7598 6.85355 12.8536C6.75979 12.9473 6.63261 13 6.5 13H4.5C4.36739 13 4.24021 12.9473 4.14645 12.8536C4.05268 12.7598 4 12.6326 4 12.5C4 12.3674 4.05268 12.2402 4.14645 12.1464C4.24021 12.0527 4.36739 12 4.5 12H5ZM5 15V14.5C5 14.3674 5.05268 14.2402 5.14645 14.1464C5.24021 14.0527 5.36739 14 5.5 14C5.63261 14 5.75979 14.0527 5.85355 14.1464C5.94732 14.2402 6 14.3674 6 14.5V15H6.5C6.63261 15 6.75979 15.0527 6.85355 15.1464C6.94732 15.2402 7 15.3674 7 15.5C7 15.6326 6.94732 15.7598 6.85355 15.8536C6.75979 15.9473 6.63261 16 6.5 16H4.5C4.36739 16 4.24021 15.9473 4.14645 15.8536C4.05268 15.7598 4 15.6326 4 15.5C4 15.3674 4.05268 15.2402 4.14645 15.1464C4.24021 15.0527 4.36739 15 4.5 15H5Z' fill='#555555'/>
                        </svg>
                    </div>
                    <div class='p-1'>
                        <p class='title_course_student'>Bài học:</p>
                    </div>
                    <div class='p-1'>
                        <span class='title_course_student'>$total_sections</span>
                    </div>
                </div>

                <div class='d-flex'>
                    <div class='p-1'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'>
                        <path d='M15.2139 10.429C16.0734 10.3978 16.8876 10.0359 17.4866 9.41879C18.0857 8.80167 18.4232 7.97704 18.4289 7.117C18.4157 6.27738 18.0696 5.47736 17.4667 4.89284C16.8638 4.30832 16.0535 3.98717 15.2139 4' stroke='#555555' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        <path d='M9.42887 10.429C10.2882 10.3976 11.1022 10.0355 11.701 9.41846C12.2998 8.80136 12.6373 7.97687 12.6429 7.117C12.6297 6.27756 12.2838 5.47769 11.6811 4.8932C11.0784 4.30871 10.2683 3.98744 9.42887 4C8.58924 3.98717 7.77889 4.30832 7.17599 4.89284C6.5731 5.47736 6.22703 6.27738 6.21387 7.117C6.21924 7.97711 6.55672 8.80189 7.15582 9.41906C7.75492 10.0362 8.5693 10.3981 9.42887 10.429V10.429Z' stroke='#555555' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        <path d='M16 20V18C16 16.9391 15.5786 15.9217 14.8284 15.1716C14.0783 14.4214 13.0609 14 12 14H7C5.93913 14 4.92172 14.4214 4.17157 15.1716C3.42143 15.9217 3 16.9391 3 18V20' stroke='#555555' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        <path d='M21 20V17C21 16.2044 20.6839 15.4413 20.1213 14.8787C19.5587 14.3161 18.7956 14 18 14' stroke='#555555' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                    </div>
                    <div class='p-1'>
                        <p class='title_course_student'>Số học viên tham gia:</p>
                    </div>
                    <div class='p-1'>
                        <span class='title_course_student'>$num_users</span>
                    </div>
                </div>
            </div>
        </div>

        <div class='col-md-6'>
            <div id='stu_id_fullname w-100'>
               <h1> $course->fullname </h1>
            </div>
            <div id='stu_id_summary_editor w-100' style='margin-top:24px;'>
                <label>" . get_string('coursesummary', 'moodle') . "</label>
                <div  class='w-100' style='padding:5px 20px; border-radius:5px; border: 1px solid #D6D6D6; background:#fff; display:inline-block;'>
            <div id='summary'>
                ";
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
    <div class='col-md-2'>
    </div>
</div>
";
$button = "
<div id='btn_sty' style='text-align:center; margin-top:24px;'>
<button type='button' class='btn btn-info btn-lg' data-toggle='modal' data-target='#myModal'>
    <svg stroke='currentColor' fill='currentColor' stroke-width='0' 
    viewBox='0 0 1024 1024' height='1em' width='1em' 
    xmlns='http://www.w3.org/2000/svg' style='font-size:20px;'>
    <path d='M752 664c-28.5 0-54.8 10-75.4 26.7L469.4 540.8a160.68 160.68 0 0 0 0-57.6l207.2-149.9C697.2 350 723.5 360 752 360c66.2 0 120-53.8 120-120s-53.8-120-120-120-120 53.8-120 120c0 11.6 1.6 22.7 4.7 33.3L439.9 415.8C410.7 377.1 364.3 352 312 352c-88.4 0-160 71.6-160 160s71.6 160 160 160c52.3 0 98.7-25.1 127.9-63.8l196.8 142.5c-3.1 10.6-4.7 21.8-4.7 33.3 0 66.2 53.8 120 120 120s120-53.8 120-120-53.8-120-120-120zm0-476c28.7 0 52 23.3 52 52s-23.3 52-52 52-52-23.3-52-52 23.3-52 52-52zM312 600c-48.5 0-88-39.5-88-88s39.5-88 88-88 88 39.5 88 88-39.5 88-88 88zm440 236c-28.7 0-52-23.3-52-52s23.3-52 52-52 52 23.3 52 52-23.3 52-52 52z'
    style='margin-right:5px;'>
    </path>
    </svg>
    Chia sẻ</button>
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
    $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id;
    // '&returnto=catmanage'
} else if (is_teacher()) {
    if (is_course_creator($COURSE->id)) {
        $urledit = $CFG->wwwroot . '/course/edit.php?id=' . $course->id;
        // '&returnto=catmanage'
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
        "<li class='nav-item {$active} mr-2'>
        <a class='nav-link title' href='{$value['url']}'>{$value['title']} <span class='sr-only'>(current)</span></a>
        </li>";
}
$content .= "</ul>
    </div>
  </nav> <hr/>";
//     echo '<br/>';
$content .= html_writer::end_div(); // navigation-box
$button1 = "
<div class='modal fade' id='myModal' role='dialog'>
  <div class='modal-dialog'>
    <!-- Modal content--> 
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'>&times;</button>
        <h4 class='modal-title'>Chỉa sẻ</h4>
      </div>
      <div class='modal-body'>
        <div class='d-flex justify-content-between'>
            <div id='show_img_modal'>
                <img src='$img'>
            </div>
            <div id='show_namecourse_modal' class='d-flex align-items-center'>$course->fullname</div>
        </div>
        <br>
        <div id='show_title_modal' class='mb-2'>Đường dẫn</div>
        <div class='d-flex justify-content-between'>
        <input id='show_input_modal' readonly type='text' value='$CFG->wwwroot/show.php?id=$course->id'> 
        <button id='show_button_modal' onclick='myFunction()'>
        Sao chép</button> </div>
      </div>
    </div>
  </div>
</div>
";
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
echo $button1;
//echo $OUTPUT->heading($pagedesc);

//$editform->display();
echo $OUTPUT->footer();

?>
<script>
    const html = document.getElementById("summary");
    const x = document.getElementById("show-more");
    if (x) {
        x.addEventListener("click", function() {
            if (html.innerHTML === '<?php echo $msg ?>') {
                html.innerHTML = '<?php echo substr($msg, 0, 500) ?>';
                x.innerHTML = 'Hiện thêm';
            } else {
                html.innerHTML = '<?php echo $msg ?>';
                x.innerHTML = 'Ẩn bớt';
            }
        })
    }

    function myFunction() {
        var copyText = document.getElementById("show_input_modal");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        var tooltip = document.getElementById("myTooltip");
    }
</script>
<?php

