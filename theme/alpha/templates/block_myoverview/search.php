<?php

use gradereport_singleview\local\ui\element;
use context_course;
require_once('../../../../config.php');
global $DB;
$input = json_decode(file_get_contents('php://input'),true);
if (isset($input['keyword']) && $input['keyword'] != "") {
    
    $search = $input['keyword'];
    $arr = array();
    $courses = $DB->get_records_sql("SELECT c.id, c.fullname, cc.name as category, COUNT(DISTINCT u.id) as student_count
    FROM mdl_course c
    LEFT JOIN mdl_course_categories cc ON cc.id = c.category
    LEFT JOIN mdl_enrol e ON e.courseid = c.id
    LEFT JOIN mdl_user_enrolments ue ON ue.enrolid = e.id
    LEFT JOIN mdl_user u ON u.id = ue.userid
    WHERE c.fullname LIKE '%$search%' AND c.visible = 1
    GROUP BY c.id, c.fullname, cc.name
    ");

    foreach($courses as $course){
        $arr[] = [
            'id' => $course->id,
            'fullname' => $course->fullname,
            'category' => $course->category,
            'student_count' => $course->student_count
        ];
    }
    echo json_encode($arr);
}

