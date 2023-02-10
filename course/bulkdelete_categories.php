<?php

/**
 * script for bulk user delete operations
 */

require_once('../config.php');
require_once($CFG->libdir . '/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_capability('moodle/category:manage', context_system::instance());
$return = '';
$return = $CFG->wwwroot . '/course/index.php';
if (empty($SESSION->bulk_category)) {
    redirect($return);
}

$PAGE->set_primary_active_tab('siteadminnode');
echo $confirm;
echo $OUTPUT->header();

echo $categorynames;
if ($confirm) {
    foreach ($SESSION->bulk_category as $id) {
        $DB->execute("DELETE FROM mdl_course_categories WHERE id = $id");
        $course_ids = $DB->get_fieldset_select('course', 'id', "category = $id");
        foreach ($course_ids as $course_id) {
            delete_course($course_id, false);
        }
    }
    unset($SESSION->bulk_category);
    $continue = new single_button(new moodle_url($return), get_string('continue'), 'post');
    echo $OUTPUT->render($continue);
    echo $OUTPUT->box_end();
} else {
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_category);
    $categorylist = $DB->get_records_select_menu('course_categories', "id $in", $params, 'name', 'id, name');
    $categorynames = implode(', ', $categorylist);
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(new moodle_url('bulkdelete_categories.php', array('confirm' => 1)), get_string('yes'));
    $formcancel = new single_button(new moodle_url('index.php'), get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('deletecheckfullcate', '', $categorynames), $formcontinue, $formcancel);
}
echo $OUTPUT->footer();
