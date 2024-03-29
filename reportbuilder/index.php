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
 * List of custom reports
 *
 * @package   core_reportbuilder
 * @copyright 2021 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

use core_reportbuilder\permission;
use core_reportbuilder\system_report_factory;
use core_reportbuilder\local\systemreports\reports_list;

require_once(__DIR__ . '/../config.php');
require_once("{$CFG->libdir}/adminlib.php");

admin_externalpage_setup('customreports');

$PAGE->requires->js_call_amd('core_reportbuilder/reports_list', 'init');
//get count course
$sqlCountCourse = 'SELECT COUNT(*) FROM mdl_course';
$resultCountCourse = $DB->get_records_sql($sqlCountCourse);
foreach ($resultCountCourse as $row) {
    $countCourse = intval($row->{'count(*)'});
}
//get count user
$sqlCountUser = 'SELECT COUNT(*) FROM mdl_user';
$resultCountUser = $DB->get_records_sql($sqlCountUser);
foreach ($resultCountUser as $row) {
    $countUser = intval($row->{'count(*)'});
}
//get count cohort
$sqlCountCohort = 'SELECT COUNT(*) FROM `mdl_cohort`';
$resultCountCohort = $DB->get_records_sql($sqlCountCohort);
foreach ($resultCountCohort as $row) {
    $countCohort = intval($row->{'count(*)'});
}

echo $OUTPUT->header();
echo '
<div class="container">
<div class="row py-5">
<div class="col-md-4">
<div class="px-4 pt-3 pb-5 shadow-lg" style="border-radius: 8px;">
    <p class="my-0 h1 fw-bold" style="font-size: 32px;">' . $countCourse . '</p>
    <span style="font-size: 14px;">Tổng số khóa học</span>
</div>
</div>
<div class="col-md-4">
<div class="px-4 pt-3 pb-5 shadow-lg" style="border-radius: 8px;">
    <p class="my-0 h1 fw-bold" style="font-size: 32px;">' . $countUser . '</p>
    <span style="font-size: 14px;">Tổng số người dùng</span>
</div>
</div>
<div class="col-md-4">
<div class="px-4 pt-3 pb-5 shadow-lg" style="border-radius: 8px;">
    <p class="my-0 h1 fw-bold" style="font-size: 32px;">' . $countCohort . '</p>
    <span style="font-size: 14px;">Tổng số lượng nhóm</span>
</div>
</div>
</div>
</div>';
echo html_writer::start_div('d-flex justify-content-between mb-2');
echo $OUTPUT->heading(get_string('customreports', 'core_reportbuilder'));

if (permission::can_create_report()) {
    /** @var \core_reportbuilder\output\renderer $renderer */
    $renderer = $PAGE->get_renderer('core_reportbuilder');
    echo $renderer->render_new_report_button();
}

echo html_writer::end_div();

$report = system_report_factory::create(reports_list::class, context_system::instance());
echo $report->output();

echo $OUTPUT->footer();
