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
 * Lets the user define and edit roles.
 *
 * Responds to actions:
 *   [blank]   - list roles.
 *   delete    - delete a role (with are-you-sure)
 *   moveup    - change the sort order
 *   movedown  - change the sort order
 *
 * For all but the first two of those, you also need a roleid parameter, and
 * possibly some other data.
 *
 * @package    core_role
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
$action = optional_param('action', '', PARAM_ALPHA);
if ($action) {
    $roleid = required_param('roleid', PARAM_INT);
} else {
    $roleid = 0;
}

// Get the base URL for this and related pages into a convenient variable.
$baseurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/manage.php';
$defineurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/define.php';

admin_externalpage_setup('defineroles');

// Check access permissions.
$systemcontext = context_system::instance();
require_capability('moodle/role:manage', $systemcontext);

// Get some basic data we are going to need.
$roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);

$undeletableroles = array();
$undeletableroles[$CFG->notloggedinroleid] = 1;
$undeletableroles[$CFG->guestroleid] = 1;
$undeletableroles[$CFG->defaultuserroleid] = 1;

$PAGE->set_primary_active_tab('siteadminnode');
$PAGE->navbar->add(get_string('defineroles', 'role'), $PAGE->url);

// Process submitted data.
$confirmed = (optional_param('confirm', false, PARAM_BOOL) && data_submitted() && confirm_sesskey());
switch ($action) {
    case 'delete':
        if (isset($undeletableroles[$roleid])) {
            print_error('cannotdeletethisrole', '', $baseurl);
        }
        if (!$confirmed) {
            // Show confirmation.
            echo $OUTPUT->header();
            $optionsyes = array('action' => 'delete', 'roleid' => $roleid, 'sesskey' => sesskey(), 'confirm' => 1);
            $a = new stdClass();
            $a->id = $roleid;
            $a->name = $roles[$roleid]->name;
            $a->shortname = $roles[$roleid]->shortname;
            $a->count = $DB->count_records_select(
                'role_assignments',
                'roleid = ?',
                array($roleid),
                'COUNT(DISTINCT userid)'
            );

            $formcontinue = new single_button(new moodle_url($baseurl, $optionsyes), get_string('yes'));
            $formcancel = new single_button(new moodle_url($baseurl), get_string('no'), 'get');
            echo $OUTPUT->confirm(get_string('deleterolesure', 'core_role', $a), $formcontinue, $formcancel);
            echo $OUTPUT->footer();
            die;
        }
        if (!delete_role($roleid)) {
            // The delete failed.
            print_error('cannotdeleterolewithid', 'error', $baseurl, $roleid);
        }
        // Deleted a role sitewide...
        redirect($baseurl);
        break;

    case 'moveup':
        if (confirm_sesskey()) {
            $prevrole = null;
            $thisrole = null;
            foreach ($roles as $role) {
                if ($role->id == $roleid) {
                    $thisrole = $role;
                    break;
                } else {
                    $prevrole = $role;
                }
            }
            if (is_null($thisrole) || is_null($prevrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
            if (!switch_roles($thisrole, $prevrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
        }

        redirect($baseurl);
        break;

    case 'movedown':
        if (confirm_sesskey()) {
            $thisrole = null;
            $nextrole = null;
            foreach ($roles as $role) {
                if ($role->id == $roleid) {
                    $thisrole = $role;
                } else if (!is_null($thisrole)) {
                    $nextrole = $role;
                    break;
                }
            }
            if (is_null($nextrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
            if (!switch_roles($thisrole, $nextrole)) {
                print_error('cannotmoverolewithid', 'error', '', $roleid);
            }
        }

        redirect($baseurl);
        break;
}

// Print the page header and tabs.
echo $OUTPUT->header();
//Việt comments navigation bar
$urlroles = $CFG->wwwroot . '/admin/roles/manage.php';
$urluser = $CFG->wwwroot . '/admin/user.php';
$urlgroup = $CFG->wwwroot . '/cohort/index.php?contextid=1&showall=1';
$pages = new stdClass();
$pages->urluser = ['title' => get_string('fullnametest'), 'url' => $urluser];
$pages->urlroles = ['title' => get_string('roles'), 'url' => $urlroles];
$pages->urlgroup = ['title' => get_string('group'), 'url' => $urlgroup];
echo "<nav class='navbar navbar-expand-lg navbar-light'>
<div class='collapse navbar-collapse' id='navbarNav'>
<ul class='navbar-nav'>";
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$urltest = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
foreach ($pages as $key => $value) {
    $active = $urltest === $value['url'] ? 'active' : 'before';
    echo "<li class='nav-item {$active}  mr-2'>
 <a class='nav-link title' href='{$value['url']}'>{$value['title']} <span class='sr-only'>(current)</span></a>
 </li>";
}
echo "</ul>
</div>
</nav> <hr/>";

// $currenttab = 'manage';
// require('managetabs.php');



// Initialise table.
$table = new html_table();
$table->colclasses = array('leftalign', 'leftalign', 'leftalign', 'leftalign');
$table->id = 'roles';
$table->attributes['class'] = 'admintable generaltable';
// button add new role
echo $OUTPUT->container_start('buttons');

echo
"<div class='d-flex mt-2' style='justify-content:space-between;'>
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
        <option value='user_role'>Vai trò hệ thống</option>
        <option value='user_desc'>Mô tả vai trò</option>
    </select>
    <input placeholder='Nhập từ khóa cần tìm kiếm' type='text' id='filter-keyword'
        style='display:none;margin-left:8px; outline:none; padding:5px 5px;''>
    </div>
    <div class='btn-addNewsRole pt-2'>" .
    $OUTPUT->single_button(new moodle_url($defineurl, array('action' => 'add')), get_string('addrole', 'core_role'), 'get')
    ."</div>
   
</div>
";

$table->head = array(
    '',
    get_string('role'),
    // . ' ' . $OUTPUT->help_icon('roles', 'core_role'),
    get_string('description'),
    '',
    '',
    // roleshortname
    // get_string('roleshortname', 'core_role'),
    get_string('edit')
);

// Get some strings outside the loop.
$stredit = get_string('edit');
$strdelete = get_string('delete');
$strmoveup = get_string('moveup');
$strmovedown = get_string('movedown');

// Print a list of roles with edit/copy/delete/reorder icons.
$table->data = array();
$firstrole = reset($roles);
$lastrole = end($roles);
foreach ($roles as $role) {
    // Basic data.
    $row = array(
        '',
        '<a href="' . $defineurl . '?action=view&amp;roleid=' . $role->id . '">' . $role->localname . '</a>',
        role_get_description($role),
        // s($role->shortname),
        '',
        ''
    );

    // Move up.
    // if ($role->sortorder != $firstrole->sortorder) {
    //     $row[3] .= get_action_icon($baseurl . '?action=moveup&amp;roleid=' . $role->id . '&amp;sesskey=' . sesskey(), 'up', $strmoveup, $strmoveup);
    // } else {
    //     $row[3] .= get_spacer();
    // }
    // // Move down.
    // if ($role->sortorder != $lastrole->sortorder) {
    //     $row[3] .= get_action_icon($baseurl . '?action=movedown&amp;roleid=' . $role->id . '&amp;sesskey=' . sesskey(), 'down', $strmovedown, $strmovedown);
    // } else {
    //     $row[3] .= get_spacer();
    // }
    // Edit.
    $row[5] .= "
    <div class='dropdown'>
        <button class='btn btn-secondary dropdown-toggle bg-none' type='button' id='dropdownMenu2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' style='background:none;width:24px;height:24px;padding:0;'>
        <svg width='16' height='4' viewBox='0 0 16 4' fill='none' xmlns='http://www.w3.org/2000/svg'>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M15.2577 2.005C15.2577 1.31453 14.698 0.754791 14.0075 0.754791C13.3171 0.754791 12.7573 1.31453 12.7573 2.005C12.7573 2.69547 13.3171 3.25521 14.0075 3.25521C14.698 3.25521 15.2577 2.69547 15.2577 2.005Z' fill='black'></path>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M9.2553 2.005C9.2553 1.31453 8.69556 0.754791 8.00509 0.754791C7.31462 0.754791 6.75488 1.31453 6.75488 2.005C6.75488 2.69547 7.31462 3.25521 8.00509 3.25521C8.69556 3.25521 9.2553 2.69547 9.2553 2.005Z' fill='black'></path>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M3.25286 2.005C3.25286 1.31453 2.69312 0.754791 2.00265 0.754791C1.31218 0.754791 0.752441 1.31453 0.752441 2.005C0.752441 2.69547 1.31218 3.25521 2.00265 3.25521C2.69312 3.25521 3.25286 2.69547 3.25286 2.005Z' fill='black'></path>
        </svg>
        </button>
        <div class='dropdown-menu' aria-labelledby='dropdownMenu2' style='max-width:60px;min-width:1rem;overflow-x:hidden'>
            <a href='$defineurl?action=edit&amp;roleid=$role->id'
            class='dropdown-item dropdown-item-wrapper action-edit menu-action' data-action='edit'>
            <svg stroke='currentColor' fill='none' stroke-width='2' viewBox='0 0 24 24' stroke-linecap='round' stroke-linejoin='round' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
            <path d='M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z'>
            </path>
            </svg>
            </a>
            <a href='$baseurl?action=delete&amp;roleid=$role->id'
            class='dropdown-item dropdown-item-wrapper action-delete menu-action' data-action='delete'>
            <svg stroke='currentColor' fill='none' stroke-width='0' viewBox='0 0 24 24' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M17 6V5C17 3.89543 16.1046 3 15 3H9C7.89543 3 7 3.89543 7 5V6H4C3.44772 6 3 6.44772 3 7C3 7.55228 3.44772 8 4 8H5V19C5 20.6569 6.34315 22 8 22H16C17.6569 22 19 20.6569 19 19V8H20C20.5523 8 21 7.55228 21 7C21 6.44772 20.5523 6 20 6H17ZM15 5H9V6H15V5ZM17 8H7V19C7 19.5523 7.44772 20 8 20H16C16.5523 20 17 19.5523 17 19V8Z' fill='currentColor'></path></svg>
            </a>
        </div>
    </div>";
    // $row[3] .= get_action_icon($defineurl . '?action=edit&amp;roleid=' . $role->id,
    //         'edit', $stredit, get_string('editxrole', 'core_role', $role->localname));
    // // Delete.
    // if (isset($undeletableroles[$role->id])) {
    //     $row[3] .= get_spacer();
    // } else {
    //     $row[3] .= get_action_icon($baseurl . '?action=delete&amp;roleid=' . $role->id,
    //           'delete', $strdelete, get_string('deletexrole', 'core_role', $role->localname));
    // }

    $table->data[] = $row;
}
echo html_writer::table($table);
echo $OUTPUT->container_end();

echo $OUTPUT->footer();
// die;

function get_action_icon($url, $icon, $alt, $tooltip)
{
    global $OUTPUT;
    return '<a title="' . $tooltip . '" href="' . $url . '">' .
        $OUTPUT->pix_icon('t/' . $icon, $alt) . '</a> ';
}
function get_spacer()
{
    global $OUTPUT;
    return $OUTPUT->spacer();
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script>
    $("#selectedOption").on("change", function () {
        var selectedOption = this.value,
            input = $("#filter-keyword");
        $('#filter-keyword').css("display", $.inArray(selectedOption, ["user_role", "user_desc"]) !== -1 ? "block" : "none");
          if (selectedOption !== "keyword") {
            input.val("");
        }
        input.trigger("keyup");
        input.on("keyup", function () {
            var filter = input.val().toUpperCase(),
                table = $("#roles"),    
                tr = table.find("tr"),
                td, txtValue;
            tr.each(function (i, el) {
                if (i !== 0) {
                    td = $(el).find("td").eq(selectedOption === "user_role" ? 1 : (selectedOption === "user_desc" ? 2 : 3));
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
