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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$contextid = optional_param('contextid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$searchquery  = optional_param('search', '', PARAM_RAW);
$showall = optional_param('showall', false, PARAM_BOOL);

require_login();

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
} else {
    $context = context_system::instance();
}

if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
    print_error('invalidcontext');
}

$category = null;
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id' => $context->instanceid), '*', MUST_EXIST);
}

$manager = has_capability('moodle/cohort:manage', $context);
$canassign = has_capability('moodle/cohort:assign', $context);
if (!$manager) {
    require_capability('moodle/cohort:view', $context);
}

$strcohorts = get_string('cohorts', 'cohort');

if ($category) {
    $PAGE->set_pagelayout('admin');
    $PAGE->set_context($context);
    $PAGE->set_url('/cohort/index.php', array('contextid' => $context->id));

    core_course_category::page_setup();
    // Set the cohorts node active in the settings navigation block.
    if ($cohortsnode = $PAGE->settingsnav->find('cohort', navigation_node::TYPE_SETTING)) {
        $cohortsnode->make_active();
    }

    $PAGE->set_title($strcohorts);
    $showall = false;
} else {
    admin_externalpage_setup('cohorts', '', null, '', array('pagelayout' => 'report'));
    $PAGE->set_primary_active_tab('siteadminnode');
    if ($showall == 1) {
        $PAGE->navbar->add(get_string('allcohorts', 'cohort'), $PAGE->url);
    } else if (!$showall) {
        $PAGE->navbar->add(get_string('systemcohorts', 'cohort'), $PAGE->url);
    }
}


echo $OUTPUT->header();
//Việt comments navigation bar
$urlroles = $CFG->wwwroot . '/admin/roles/manage.php';
$urluser = $CFG->wwwroot . '/admin/user.php';
$urlgroup = $CFG->wwwroot . '/cohort/index.php?contextid=1&showall=1';
$urlgroup1 = $CFG->wwwroot . '/cohort/index.php?contextid=1';
$pages = new stdClass();
if(is_siteadmin()){
    $pages->urluser = ['title' => get_string('fullnametest'), 'url' => $urluser];
    $pages->urlroles = ['title' => get_string('roles'), 'url' => $urlroles];
    $pages->urlgroup = ['title' => get_string('group'), 'url' => $urlgroup, 'url1' => $urlgroup1];
}
else{
    $pages->urlgroup = ['title' => get_string('group'), 'url' => $urlgroup, 'url1' => $urlgroup1];
}
echo "<nav class='navbar navbar-expand-lg navbar-light'>
<div class='collapse navbar-collapse' id='navbarNav'>
<ul class='navbar-nav'>";
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$urltest = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
foreach ($pages as $key => $value) {
    $active = $urltest === $value['url'] ||  $value['url1'] ? 'active' : 'before';
    echo "<li class='nav-item {$active}  mr-2'>
 <a class='nav-link title' href='{$value['url']}'>{$value['title']} <span class='sr-only'>(current)</span></a>
 </li>";
}
echo "</ul>
</div>
</nav> <hr/> <br>";

if ($showall) {
    $cohorts = cohort_get_all_cohorts($page, 25, $searchquery);
} else {
    $cohorts = cohort_get_cohorts($context->id, $page, 25, $searchquery);
}

$count = '';
if ($cohorts['allcohorts'] > 0) {
    if ($searchquery === '') {
        $count = ' (' . $cohorts['allcohorts'] . ')';
    } else {
        $count = ' (' . $cohorts['totalcohorts'] . '/' . $cohorts['allcohorts'] . ')';
    }
}

echo $OUTPUT->heading(get_string('cohortsin', 'cohort', $context->get_context_name()) . $count);

$params = array('page' => $page);
if ($contextid) {
    $params['contextid'] = $contextid;
}
if ($searchquery) {
    $params['search'] = $searchquery;
}
if ($showall) {
    $params['showall'] = true;
}
$baseurl = new moodle_url('/cohort/index.php', $params);
echo "<div style='display:flex;justify-content:space-between;'>
    <div data-region='filter' class='d-flex align-items-center'
        aria-label='{{#str}} aria:controls, block_myoverview {{/str}}' style='margin-top:-10px;'>
        <svg xmlns='http://www.w3.org/2000/svg' width='20' height='14' viewBox='0 0 20 14' fill='none'>
            <path
                d='M6.66667 13C6.66667 12.7348 6.75446 12.4804 6.91074 12.2929C7.06702 12.1054 7.27899 12 7.5 12H12.5C12.721 12 12.933 12.1054 13.0893 12.2929C13.2455 12.4804 13.3333 12.7348 13.3333 13C13.3333 13.2652 13.2455 13.5196 13.0893 13.7071C12.933 13.8946 12.721 14 12.5 14H7.5C7.27899 14 7.06702 13.8946 6.91074 13.7071C6.75446 13.5196 6.66667 13.2652 6.66667 13ZM3.33333 7C3.33333 6.73478 3.42113 6.48043 3.57741 6.29289C3.73369 6.10536 3.94565 6 4.16667 6H15.8333C16.0543 6 16.2663 6.10536 16.4226 6.29289C16.5789 6.48043 16.6667 6.73478 16.6667 7C16.6667 7.26522 16.5789 7.51957 16.4226 7.70711C16.2663 7.89464 16.0543 8 15.8333 8H4.16667C3.94565 8 3.73369 7.89464 3.57741 7.70711C3.42113 7.51957 3.33333 7.26522 3.33333 7ZM0 1C0 0.734784 0.0877975 0.48043 0.244078 0.292893C0.400358 0.105357 0.61232 0 0.833333 0H19.1667C19.3877 0 19.5996 0.105357 19.7559 0.292893C19.9122 0.48043 20 0.734784 20 1C20 1.26522 19.9122 1.51957 19.7559 1.70711C19.5996 1.89464 19.3877 2 19.1667 2H0.833333C0.61232 2 0.400358 1.89464 0.244078 1.70711C0.0877975 1.51957 0 1.26522 0 1Z'
                fill='#555555'>
        </svg>
        <select name='selectedOption' id='selectedOption'
            style='border:none; outline:none; height:24px; margin-left:8px; font-size:14px; appearance:none; font-weight: 400;line-height:24px;'>
            <option value=''>Bộ lọc</option>
            <option value='user_group'>Tên nhóm</option>
            <option value='user_createG'>Ngươi tạo nhóm</option>
        </select>
        <input placeholder='Nhập từ khóa cần tìm kiếm' type='text' id='filter-keyword'
            style='display:none;margin-left:8px; outline:none; padding:5px 5px;''>
    </div>
</div>";
if ($editcontrols = cohort_edit_controls($context, $baseurl)) {
    echo $OUTPUT->render($editcontrols);
}

// Add search form.
$hiddenfields = [
    (object) ['name' => 'contextid', 'value' => $contextid],
    (object) ['name' => 'showall', 'value' => $showall]
];

$data = [
    'action' => new moodle_url('/cohort/index.php'),
    'inputname' => 'search',
    'searchstring' => get_string('search', 'cohort'),
    'query' => $searchquery,
    'hiddenfields' => $hiddenfields,
    'extraclasses' => 'mb-3'
];

echo $OUTPUT->render_from_template('core/search_input', $data);

// Output pagination bar.
// Phân trang đầu table
// echo $OUTPUT->paging_bar($cohorts['totalcohorts'], $page, 25, $baseurl);

$data = array();
$editcolumnisempty = true;

foreach ($cohorts['cohorts'] as $cohort) {
    $line = array();
    $cohortcontext = context::instance_by_id($cohort->contextid);
    $cohort->description = file_rewrite_pluginfile_urls(
        $cohort->description,
        'pluginfile.php',
        $cohortcontext->id,
        'cohort',
        'description',
        $cohort->id
    );
    // if ($showall) {
    // if ($cohortcontext->contextlevel == CONTEXT_COURSECAT) {
    //     $line[] = html_writer::link(new moodle_url(
    //         '/cohort/index.php',
    //         array('contextid' => $cohort->contextid)
    //     ), $cohortcontext->get_context_name(false));
    // } else {
    //     $line[] = $cohortcontext->get_context_name(false);
    // }
    // }
    $line[] = "";
    $tmpl = new \core_cohort\output\cohortname($cohort);
    $line[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
    // $tmpl = new \core_cohort\output\cohortidnumber($cohort);
    // $line[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
    $line[] = format_text($cohort->description, $cohort->descriptionformat);

    //Thêm người tạo nhóm Việt
    $user = $DB->get_record('user', array('id' => $cohort->iduser));
    $username = fullname($user);
    $line[] = $username;


    $line[] = $DB->count_records('cohort_members', array('cohortid' => $cohort->id));



    // if (empty($cohort->component)) {
    //     $line[] = get_string('nocomponent', 'cohort');
    // } else {
    //     $line[] = get_string('pluginname', $cohort->component);
    // }
    $urledit = $baseurl->out_as_local_url(false);

    if (empty($cohort->component)) {
        $cohortmanager = has_capability('moodle/cohort:manage', $cohortcontext);
        $cohortcanassign = has_capability('moodle/cohort:assign', $cohortcontext);

        $urlparams = array('id' => $cohort->id, 'returnurl' => $baseurl->out_as_local_url(false));
        $showhideurl = new moodle_url('/cohort/edit.php', $urlparams + array('sesskey' => sesskey()));

        if ($cohortmanager) {
            $buttons = array();
            $buttons[] = "
            <div class='dropdown'>
                <button class='btn btn-secondary dropdown-toggle bg-none' type='button' id='dropdownMenu2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' style='background:none;width:24px;height:24px;padding:0;'>
                    <svg width='16' height='4' viewBox='0 0 16 4' fill='none' xmlns='http://www.w3.org/2000/svg'>
                        <path fill-rule='evenodd' clip-rule='evenodd' d='M15.2577 2.005C15.2577 1.31453 14.698 0.754791 14.0075 0.754791C13.3171 0.754791 12.7573 1.31453 12.7573 2.005C12.7573 2.69547 13.3171 3.25521 14.0075 3.25521C14.698 3.25521 15.2577 2.69547 15.2577 2.005Z' fill='black'></path>
                        <path fill-rule='evenodd' clip-rule='evenodd' d='M9.2553 2.005C9.2553 1.31453 8.69556 0.754791 8.00509 0.754791C7.31462 0.754791 6.75488 1.31453 6.75488 2.005C6.75488 2.69547 7.31462 3.25521 8.00509 3.25521C8.69556 3.25521 9.2553 2.69547 9.2553 2.005Z' fill='black'></path>
                        <path fill-rule='evenodd' clip-rule='evenodd' d='M3.25286 2.005C3.25286 1.31453 2.69312 0.754791 2.00265 0.754791C1.31218 0.754791 0.752441 1.31453 0.752441 2.005C0.752441 2.69547 1.31218 3.25521 2.00265 3.25521C2.69312 3.25521 3.25286 2.69547 3.25286 2.005Z' fill='black'></path>
                    </svg>
                </button>
                <div class='dropdown-menu' aria-labelledby='dropdownMenu2' style='max-width:60px;min-width:1rem;overflow-x:hidden'>
                    <a href='$CFG->wwwroot/cohort/edit.php?id=$cohort->id&returnurl=%2Fcohort%2Findex.php%3Fpage%3D0%26contextid%3D1%26showall%3D1'
                    class='dropdown-item dropdown-item-wrapper action-edit menu-action' data-action='edit'>
                    <svg stroke='currentColor' fill='none' stroke-width='2' viewBox='0 0 24 24' stroke-linecap='round' stroke-linejoin='round' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
                    <path d='M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z'>
                    </path>
                    </svg>
                    </a>
                    <a href='$CFG->wwwroot/cohort/edit.php?id=$cohort->id&returnurl=%2Fcohort%2Findex.php%3Fpage%3D0%26contextid%3D1%26showall%3D1&delete=1'
                    class='dropdown-item dropdown-item-wrapper action-delete menu-action' data-action='delete'>
                    <svg stroke='currentColor' fill='none' stroke-width='0' viewBox='0 0 24 24' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
                    <path fill-rule='evenodd' clip-rule='evenodd' d='M17 6V5C17 3.89543 16.1046 3 15 3H9C7.89543 3 7 3.89543 7 5V6H4C3.44772 6 3 6.44772 3 7C3 7.55228 3.44772 8 4 8H5V19C5 20.6569 6.34315 22 8 22H16C17.6569 22 19 20.6569 19 19V8H20C20.5523 8 21 7.55228 21 7C21 6.44772 20.5523 6 20 6H17ZM15 5H9V6H15V5ZM17 8H7V19C7 19.5523 7.44772 20 8 20H16C16.5523 20 17 19.5523 17 19V8Z' fill='currentColor'></path></svg>
                    </a>
                    <a href='$CFG->wwwroot/cohort/assign.php?id=$cohort->id&returnurl=%2Fcohort%2Findex.php%3Fpage%3D0%26contextid%3D1%26showall%3D1'
                    class='dropdown-item dropdown-item-wrapper action-add menu-action' data-action='add'>
                    <svg stroke='currentColor' fill='none' stroke-width='1.5' viewBox='0 0 24 24' aria-hidden='true' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
                    <path stroke-linecap='round' stroke-linejoin='round' d='M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z'>
                    </path></svg>
                    </a>
                </div>
            </div>
            ";
            // if ($cohort->visible) {
            //     $showhideurl->param('hide', 1);
            //     $visibleimg = $OUTPUT->pix_icon('t/hide', get_string('hide'));
            //     $buttons[] = html_writer::link($showhideurl, $visibleimg, array('title' => get_string('hide')));
            // } else {
            //     $showhideurl->param('show', 1);
            //     $visibleimg = $OUTPUT->pix_icon('t/show', get_string('show'));
            //     $buttons[] = html_writer::link($showhideurl, $visibleimg, array('title' => get_string('show')));
            // }
            // $buttons[] = html_writer::link(
            //     new moodle_url('/cohort/edit.php', $urlparams + array('delete' => 1)),
            //     $OUTPUT->pix_icon('t/delete', get_string('delete')),
            //     array('title' => get_string('delete'))
            // );
            // $buttons[] = html_writer::link(
            //     new moodle_url('/cohort/edit.php', $urlparams),
            //     $OUTPUT->pix_icon('t/edit', get_string('edit')),
            //     array('title' => get_string('edit'))
            // );
            $editcolumnisempty = false;
        }
        // if ($cohortcanassign) {
        //     $buttons[] = html_writer::link(
        //         new moodle_url('/cohort/assign.php', $urlparams),
        //         $OUTPUT->pix_icon('i/users', get_string('assign', 'core_cohort')),
        //         array('title' => get_string('assign', 'core_cohort'))
        //     );
        //     $editcolumnisempty = false;
        // }
    }
    $line[] = implode(' ', $buttons);

    $data[] = $row = new html_table_row($line);
    if (!$cohort->visible) {
        $row->attributes['class'] = 'dimmed_text';
    }
}
$table = new html_table();
$table->head  = array(
    '', get_string('name', 'cohort'), get_string('description', 'cohort'), get_string('creater', 'cohort'), get_string('memberscount', 'cohort'), get_string('edit')
);
$table->colclasses = array('leftalign checkbox', 'leftalign name', 'leftalign description', 'leftalign creater', 'centeralign size');
// if ($showall) {
//     array_unshift($table->head, get_string('category'));
//     array_unshift($table->colclasses, 'leftalign category');
// }
if (!$editcolumnisempty) {
    // $table->head[] = get_string('edit');
    // $table->colclasses[] = 'centeralign action';
} else {
    // Remove last column from $data.
    foreach ($data as $row) {
        array_pop($row->cells);
    }
}
$table->id = 'cohorts';
$table->attributes['class'] = 'admintable generaltable';
$table->data  = $data;
echo html_writer::table($table);
echo $OUTPUT->paging_bar($cohorts['totalcohorts'], $page, 25, $baseurl);
echo $OUTPUT->footer();
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script>
    $("#selectedOption").on("change", function () {
        var selectedOption = this.value,
            input = $("#filter-keyword");
        $('#filter-keyword').css("display", $.inArray(selectedOption, ["user_group", "user_createG"]) !== -1 ? "block" : "none");
        if (selectedOption !== "keyword") {
            input.val("");
        }
        input.trigger("keyup");
        input.on("keyup", function () {
            var filter = input.val().toUpperCase(),
                table = $("#cohorts"),
                tr = table.find("tr"),
                td, txtValue;
            console.log(table);

            tr.each(function (i, el) {
                if (i !== 0) {
                    td = $(el).find("td").eq(selectedOption === "user_group" ? 1 : (selectedOption === "user_createG" ? 3: 4));
                    if (td.length) {
                        txtValue = td.text();
                        $(el).closest('tr').css("display", txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none");
                    }
                }
            });
        });
    });
</script>
<?php
