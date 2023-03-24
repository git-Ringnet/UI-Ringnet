<?php

require_once('../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/user/filters/lib.php');
require_once($CFG->dirroot . '/user/lib.php');



$delete       = optional_param('delete', 0, PARAM_INT);
$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$confirmuser  = optional_param('confirmuser', 0, PARAM_INT);
$sort         = optional_param('sort', 'name', PARAM_ALPHANUMEXT);
$dir          = optional_param('dir', 'ASC', PARAM_ALPHA);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 12, PARAM_INT);        // how many per page
$ru           = optional_param('ru', '2', PARAM_INT);            // show remote users
$lu           = optional_param('lu', '2', PARAM_INT);            // show local users
$acl          = optional_param('acl', '0', PARAM_INT);           // id of user to tweak mnet ACL (requires $access)
$suspend      = optional_param('suspend', 0, PARAM_INT);
$unsuspend    = optional_param('unsuspend', 0, PARAM_INT);
$unlock       = optional_param('unlock', 0, PARAM_INT);
$resendemail  = optional_param('resendemail', 0, PARAM_INT);

admin_externalpage_setup('editusers');

$sitecontext = context_system::instance();
$site = get_site();

if (!has_capability('moodle/user:update', $sitecontext) and !has_capability('moodle/user:delete', $sitecontext)) {
    print_error('nopermissions', 'error', '', 'edit/delete users');
}

$stredit   = get_string('edit');
$strdelete = get_string('delete');
$strdeletecheck = get_string('deletecheck');
$strshowallusers = get_string('showallusers');
$strsuspend = get_string('suspenduser', 'admin');
$strunsuspend = get_string('unsuspenduser', 'admin');
$strunlock = get_string('unlockaccount', 'admin');
$strconfirm = get_string('confirm');
$strresendemail = get_string('resendemail');

$returnurl = new moodle_url('/admin/user.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'page' => $page));

$PAGE->set_primary_active_tab('siteadminnode');
$PAGE->navbar->add(get_string('userlist', 'admin'), $PAGE->url);

// The $user variable is also used outside of these if statements.
$user = null;
if ($confirmuser and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);
    if (!$user = $DB->get_record('user', array('id' => $confirmuser, 'mnethostid' => $CFG->mnet_localhost_id))) {
        print_error('nousers');
    }

    $auth = get_auth_plugin($user->auth);

    $result = $auth->user_confirm($user->username, $user->secret);

    if ($result == AUTH_CONFIRM_OK or $result == AUTH_CONFIRM_ALREADY) {
        redirect($returnurl);
    } else {
        echo $OUTPUT->header();
        redirect($returnurl, get_string('usernotconfirmed', '', fullname($user, true)));
    }
} else if ($resendemail && confirm_sesskey()) {
if (!$user = $DB->get_record('user', ['id' => $resendemail, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0])) {
        print_error('nousers');
    }

    // Prevent spamming users who are already confirmed.
    if ($user->confirmed) {
        print_error('alreadyconfirmed');
    }

    $returnmsg = get_string('emailconfirmsentsuccess');
    $messagetype = \core\output\notification::NOTIFY_SUCCESS;
    if (!send_confirmation_email($user)) {
        $returnmsg = get_string('emailconfirmsentfailure');
        $messagetype = \core\output\notification::NOTIFY_ERROR;
    }

    redirect($returnurl, $returnmsg, null, $messagetype);
} else if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation
    require_capability('moodle/user:delete', $sitecontext);

    $user = $DB->get_record('user', array('id' => $delete, 'mnethostid' => $CFG->mnet_localhost_id), '*', MUST_EXIST);

    if ($user->deleted) {
        print_error('usernotdeleteddeleted', 'error');
    }
    if (is_siteadmin($user->id)) {
        print_error('useradminodelete', 'error');
    }

    if ($confirm != md5($delete)) {
        echo $OUTPUT->header();
        $fullname = fullname($user, true);
        echo $OUTPUT->heading(get_string('deleteuser', 'admin'));

        $optionsyes = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey());
        $deleteurl = new moodle_url($returnurl, $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), $deletebutton, $returnurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {
        if (delete_user($user)) {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($returnurl);
        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            echo $OUTPUT->header();
            echo $OUTPUT->notification($returnurl, get_string('deletednot', '', fullname($user, true)));
        }
    }
} else if ($acl and confirm_sesskey()) {
    if (!has_capability('moodle/user:update', $sitecontext)) {
        print_error('nopermissions', 'error', '', 'modify the NMET access control list');
    }
    if (!$user = $DB->get_record('user', array('id' => $acl))) {
        print_error('nousers', 'error');
    }
    if (!is_mnet_remote_user($user)) {
        print_error('usermustbemnet', 'error');
    }
    $accessctrl = strtolower(required_param('accessctrl', PARAM_ALPHA));
    if ($accessctrl != 'allow' and $accessctrl != 'deny') {
        print_error('invalidaccessparameter', 'error');
    }
    $aclrecord = $DB->get_record('mnet_sso_access_control', array('username' => $user->username, 'mnet_host_id' => $user->mnethostid));
    if (empty($aclrecord)) {
        $aclrecord = new stdClass();
        $aclrecord->mnet_host_id = $user->mnethostid;
$aclrecord->username = $user->username;
        $aclrecord->accessctrl = $accessctrl;
        $DB->insert_record('mnet_sso_access_control', $aclrecord);
    } else {
        $aclrecord->accessctrl = $accessctrl;
        $DB->update_record('mnet_sso_access_control', $aclrecord);
    }
    $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
    redirect($returnurl);
} else if ($suspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id' => $suspend, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {
        if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
            $user->suspended = 1;
            // Force logout.
            \core\session\manager::kill_user_sessions($user->id);
            user_update_user($user, false);
        }
    }
    redirect($returnurl);
} else if ($unsuspend and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id' => $unsuspend, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {
        if ($user->suspended != 0) {
            $user->suspended = 0;
            user_update_user($user, false);
        }
    }
    redirect($returnurl);
} else if ($unlock and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id' => $unlock, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {
        login_unlock_account($user);
    }
    redirect($returnurl);
}

// create the user filter form
$ufiltering = new user_filtering();
echo $OUTPUT->header();

// Carry on with the user listing
$context = context_system::instance();
// These columns are always shown in the users list.
$requiredcolumns = array('city', 'country', 'lastaccess');
// Extra columns containing the extra user fields, excluding the required columns (city and country, to be specific).
$userfields = \core_user\fields::for_identity($context, true)->excluding(...$requiredcolumns);
$extracolumns = $userfields->get_required_fields();
// Get all user name fields as an array, but with firstname and lastname first.
$allusernamefields = \core_user\fields::get_name_fields(true);
$columns = array_merge($allusernamefields, $extracolumns, $requiredcolumns);

foreach ($columns as $column) {
    $string[$column] = \core_user\fields::get_display_name($column);
    if ($sort != $column) {
        $columnicon = "";
        if ($column == "lastaccess") {
            $columndir = "DESC";
        } else {
            $columndir = "ASC";
        }
    } else {
        $columndir = $dir == "ASC" ? "DESC" : "ASC";
        if ($column == "lastaccess") {
            $columnicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
        } else {
            $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
        }
        $columnicon = $OUTPUT->pix_icon(
't/' . $columnicon,
            get_string(strtolower($columndir)),
            'core',
            ['class' => 'iconsort']
        );
    }
    $$column = "<a href=\"user.php?sort=$column&amp;dir=$columndir\">" . $string[$column] . "</a>$columnicon";
}

// We need to check that alternativefullnameformat is not set to '' or language.
// We don't need to check the fullnamedisplay setting here as the fullname function call further down has
// the override parameter set to true.
$fullnamesetting = $CFG->alternativefullnameformat;
// If we are using language or it is empty, then retrieve the default user names of just 'firstname' and 'lastname'.
if ($fullnamesetting == 'language' || empty($fullnamesetting)) {
    // Set $a variables to return 'firstname' and 'lastname'.
    $a = new stdClass();
    $a->firstname = 'firstname';
    $a->lastname = 'lastname';
    // Getting the fullname display will ensure that the order in the language file is maintained.
    $fullnamesetting = get_string('fullnamedisplay', null, $a);
}

// Order in string will ensure that the name columns are in the correct order.
$usernames = order_in_string($allusernamefields, $fullnamesetting);
$fullnamedisplay = array();
foreach ($usernames as $name) {
    // Use the link from $$column for sorting on the user's name.
    $fullnamedisplay[] = ${$name};
}
// All of the names are in one column. Put them into a string and separate them with a /.
$fullnamedisplay = implode(' / ', $fullnamedisplay);
// If $sort = name then it is the default for the setting and we should use the first name to sort by.
if ($sort == "name") {
    // Use the first item in the array.
    $sort = reset($usernames);
}

list($extrasql, $params) = $ufiltering->get_sql_filter();
$users = get_users_listing(
    $sort,
    $dir,
    $page * $perpage,
    $perpage,
    '',
    '',
    '',
    $extrasql,
    $params,
    $context
);
$usercount = get_users(false);
$usersearchcount = get_users(false, '', false, null, "", '', '', '', '', '*', $extrasql, $params);

if ($extrasql !== '') {
    echo $OUTPUT->heading("$usersearchcount / $usercount " . get_string('users'));
    $usercount = $usersearchcount;
} else {
    echo $OUTPUT->heading("$usercount " . get_string('users'));
}

$strall = get_string('all');
// Pagination on top
// $baseurl = new moodle_url('/admin/user.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
// echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);

flush();


if (!$users) {
    $match = array();
    echo $OUTPUT->heading(get_string('nousersfound'));

    $table = NULL;
} else {

    $countries = get_string_manager()->get_list_of_countries(true);
    if (empty($mnethosts)) {
        $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
    }

    foreach ($users as $key => $user) {
        if (isset($countries[$user->country])) {
            $users[$key]->country = $countries[$user->country];
        }
    }
if ($sort == "country") {
        // Need to resort by full country name, not code.
        foreach ($users as $user) {
            $susers[$user->id] = $user->country;
        }
        // Sort by country name, according to $dir.
        if ($dir === 'DESC') {
            arsort($susers);
        } else {
            asort($susers);
        }
        foreach ($susers as $key => $value) {
            $nusers[] = $users[$key];
        }
        $users = $nusers;
    }

    $table = new html_table();
    $table->head = array();
    $table->colclasses = array();
    // add check box
    // $bulkoperations = has_capability('moodle/course:bulkmessaging', $this->context);
    // if ($bulkoperations) {
    //     $mastercheckbox = new \core\output\checkbox_toggleall('participants-table', true, [
    //         'id' => 'select-all-participants',
    //         'name' => 'select-all-participants',
    //         'label' => get_string('selectall'),
    //         'labelclasses' => 'sr-only',
    //         'classes' => 'm-1',
    //         'checked' => false,
    //     ]);
    //     $table->head[] = $OUTPUT->render($mastercheckbox);
    // }
    // $table->head[] = $fullnamedisplay;
    $table->head[] = "<input type='checkbox' id='checkall' onclick='checkAll()'>";

    $table->head[] = get_string('fullnametest');
    // add button
    // $url1 = $CFG->wwwroot."/admin/roles/manage.php";
    // echo "<div id='hover_tag_a' style='display:flex; border-bottom:1px solid gray; padding:0 5px 0 5px;' class='action_bar_userManagement'>"."
    // <a href='$url' style='color:#001' class='a_hover active1'>".get_string('fullnametest')."</a>
    // <a href='$url1' style='margin-left:20px;color:#001;' class='a_hover'>".get_string('roles')."</a>
    // <a href='#' style='margin-left:20px;color:#001;' class='a_hover'>".get_string('group')."</a>
    // "."</div>";

    //Việt comments navigation bar
    $urlroles = $CFG->wwwroot . '/admin/roles/manage.php';
    $urluser = $CFG->wwwroot . '/admin/user.php';
    $urlgroup = $CFG->wwwroot . '/cohort/index.php?contextid=1&showall=1';
    $pages = new stdClass();
    if(is_siteadmin()){
        $pages->urluser = ['title' => get_string('fullnametest'), 'url' => $urluser];
        $pages->urlroles = ['title' => get_string('roles'), 'url' => $urlroles];
        $pages->urlgroup = ['title' => get_string('group'), 'url' => $urlgroup];
    }
    else{
        $pages->urlgroup = ['title' => get_string('group'), 'url' => $urlgroup];
    }
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
    if (has_capability('moodle/user:create', $sitecontext)) {
        $url = new moodle_url('/user/editadvanced.php', array('id' => -1));
        echo "<div class='btn-addNewsUsers' style='margin-top:24px; display:flex; justify-content:space-between;'>" ."<div>
        <div data-region='filter' class='d-flex align-items-center'
        aria-label='{{#str}} aria:controls, block_myoverview {{/str}}' style='margin-top:5px;'>
        <svg xmlns='http://www.w3.org/2000/svg' width='20' height='14' viewBox='0 0 20 14' fill='none'>
            <path
                d='M6.66667 13C6.66667 12.7348 6.75446 12.4804 6.91074 12.2929C7.06702 12.1054 7.27899 12 7.5 12H12.5C12.721 12 12.933 12.1054 13.0893 12.2929C13.2455 12.4804 13.3333 12.7348 13.3333 13C13.3333 13.2652 13.2455 13.5196 13.0893 13.7071C12.933 13.8946 12.721 14 12.5 14H7.5C7.27899 14 7.06702 13.8946 6.91074 13.7071C6.75446 13.5196 6.66667 13.2652 6.66667 13ZM3.33333 7C3.33333 6.73478 3.42113 6.48043 3.57741 6.29289C3.73369 6.10536 3.94565 6 4.16667 6H15.8333C16.0543 6 16.2663 6.10536 16.4226 6.29289C16.5789 6.48043 16.6667 6.73478 16.6667 7C16.6667 7.26522 16.5789 7.51957 16.4226 7.70711C16.2663 7.89464 16.0543 8 15.8333 8H4.16667C3.94565 8 3.73369 7.89464 3.57741 7.70711C3.42113 7.51957 3.33333 7.26522 3.33333 7ZM0 1C0 0.734784 0.0877975 0.48043 0.244078 0.292893C0.400358 0.105357 0.61232 0 0.833333 0H19.1667C19.3877 0 19.5996 0.105357 19.7559 0.292893C19.9122 0.48043 20 0.734784 20 1C20 1.26522 19.9122 1.51957 19.7559 1.70711C19.5996 1.89464 19.3877 2 19.1667 2H0.833333C0.61232 2 0.400358 1.89464 0.244078 1.70711C0.0877975 1.51957 0 1.26522 0 1Z'
                fill='#555555'>
        </svg>
        <select name='selectedOption' id='selectedOption'
            style='border:none; outline:none; height:24px; margin-left:8px; font-size:14px; appearance:none; font-weight: 400;line-height:24px;'>
            <option value=''>Bộ lọc</option>
            <option value='user_name'>Tên Người dùng</option>
            <option value='user_email'>Địa chỉ email</option>
        </select>
        <input placeholder='Nhập từ khóa cần tìm kiếm' type='text' id='filter-keyword'
            style='display:none; margin-left:8px; outline:none; padding:5px 5px;''>
    </div>
        </div>". $OUTPUT->single_button($url, get_string('addnewuser'), 'get') . "</div>";
        // button default
        // echo $OUTPUT->single_button($url, get_string('addnewuser'), 'get');
    }
    // $url1 = "https://localhost/ringnet/admin/roles/manage.php";
    // echo $OUTPUT->single_button($url1,'Quản lý roles');
    echo "<form id='myform' method='POST' action='$CFG->wwwroot/admin/user.php'>";
    $table->attributes['class'] = 'admintable generaltable table-sm';
foreach ($extracolumns as $field) {
        $table->head[] = ${$field};
    }
   
    // $table->head[] = $country;
    // $table->head[] = $lastaccess;
    $table->head[] = get_string('roles');
    $table->head[] = "";
    $table->head[] = get_string('edit');
    $table->colclasses[] = 'centeralign';
    
    $table->colclasses[] = 'centeralign';

    $table->id = "users";
    foreach ($users as $user) {
        $buttons = array();
        // $lastcolumn = '';
        $ses = sesskey();
        $buttons[] = "<div class='dropdown'> 
        <button class='btn btn-secondary dropdown-toggle bg-none' type='button' id='dropdownMenu2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' style='background:none;width:24px;height:24px;padding:0;'>
        <svg width='16' height='4' viewBox='0 0 16 4' fill='none' xmlns='http://www.w3.org/2000/svg'>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M15.2577 2.005C15.2577 1.31453 14.698 0.754791 14.0075 0.754791C13.3171 0.754791 12.7573 1.31453 12.7573 2.005C12.7573 2.69547 13.3171 3.25521 14.0075 3.25521C14.698 3.25521 15.2577 2.69547 15.2577 2.005Z' fill='black'></path>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M9.2553 2.005C9.2553 1.31453 8.69556 0.754791 8.00509 0.754791C7.31462 0.754791 6.75488 1.31453 6.75488 2.005C6.75488 2.69547 7.31462 3.25521 8.00509 3.25521C8.69556 3.25521 9.2553 2.69547 9.2553 2.005Z' fill='black'></path>
            <path fill-rule='evenodd' clip-rule='evenodd' d='M3.25286 2.005C3.25286 1.31453 2.69312 0.754791 2.00265 0.754791C1.31218 0.754791 0.752441 1.31453 0.752441 2.005C0.752441 2.69547 1.31218 3.25521 2.00265 3.25521C2.69312 3.25521 3.25286 2.69547 3.25286 2.005Z' fill='black'></path>
        </svg>
        </button>
        <div class='dropdown-menu' aria-labelledby='dropdownMenu2' style='max-width:60px;min-width:1rem;overflow-x:hidden'>
        <a href='$CFG->wwwroot/user/editadvanced.php?id=$user->id&course=$site->id'
        class='dropdown-item dropdown-item-wrapper action-edit menu-action' data-action='edit'>
        <svg stroke='currentColor' fill='none' stroke-width='2' viewBox='0 0 24 24' stroke-linecap='round' stroke-linejoin='round' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
        <path d='M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z'>
        </path>
        </svg>
        </a>
        <a href='$CFG->wwwroot/admin/user.php?sort=$sort&dir=$dir&perpage=$perpage&page=$page&delete=$user->id&sesskey=$ses'
        class='dropdown-item dropdown-item-wrapper action-delete menu-action' data-action='delete'>
        <svg stroke='currentColor' fill='none' stroke-width='0' viewBox='0 0 24 24' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
<path fill-rule='evenodd' clip-rule='evenodd' d='M17 6V5C17 3.89543 16.1046 3 15 3H9C7.89543 3 7 3.89543 7 5V6H4C3.44772 6 3 6.44772 3 7C3 7.55228 3.44772 8 4 8H5V19C5 20.6569 6.34315 22 8 22H16C17.6569 22 19 20.6569 19 19V8H20C20.5523 8 21 7.55228 21 7C21 6.44772 20.5523 6 20 6H17ZM15 5H9V6H15V5ZM17 8H7V19C7 19.5523 7.44772 20 8 20H16C16.5523 20 17 19.5523 17 19V8Z' fill='currentColor'></path></svg>
        </a>
        </div>
        </div>";
        // delete button
        // if (has_capability('moodle/user:delete', $sitecontext)) {
        //     if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
        //         // no deleting of self, mnet accounts or admins allowed
        //     } else {
        //         $url = new moodle_url($returnurl, array('delete' => $user->id, 'sesskey' => sesskey()));
        //         $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
        //     }
        // }

        // suspend button
        // var_dump($CFG->wwwroot.'/admin/user?sort='.$sort.'&dir='.$dir.'&perpage='.$perpage.'&page='.$page.'&suspend='.$user->id.'&sesskey='.sesskey());
        //var_dump($CFG->wwwroot.'/admin/user.php?sort='.$sort.'&dir='.$dir.'&perpage='.$perpage.'&page='.$page.'&delete='.$user->id.'&sesskey='.sesskey());
         // var_dump($CFG->wwwroot.'/user/editadvanced.php?id='.$user->id.'$course='.$site->id);
        // if (has_capability('moodle/user:update', $sitecontext)) {
        //     if (is_mnet_remote_user($user)) {
        //         // mnet users have special access control, they can not be deleted the standard way or suspended
        //         $accessctrl = 'allow';
        //         if ($acl = $DB->get_record('mnet_sso_access_control', array('username' => $user->username, 'mnet_host_id' => $user->mnethostid))) {
        //             $accessctrl = $acl->accessctrl;
        //         }
        //         $changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
        //         $buttons[] = " (<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey=" . sesskey() . "\">" . get_string($changeaccessto, 'mnet') . " access</a>)";
        //     } else {
        //         if ($user->suspended) {
        //             $url = new moodle_url($returnurl, array('unsuspend' => $user->id, 'sesskey' => sesskey()));
        //             $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/show', $strunsuspend));
        //         } else {
        //             if ($user->id == $USER->id or is_siteadmin($user)) {
        //                 // no suspending of admins or self!
        //             } else {
        //                 $url = new moodle_url($returnurl, array('suspend' => $user->id, 'sesskey' => sesskey()));
        //                 $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/hide', $strsuspend));
        //             }
        //         }
//         if (login_is_lockedout($user)) {
        //             $url = new moodle_url($returnurl, array('unlock' => $user->id, 'sesskey' => sesskey()));
        //             $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/unlock', $strunlock));
        //         }
        //     }
        // }
      
        // // edit button
        // if (has_capability('moodle/user:update', $sitecontext)) {
        //     // prevent editing of admins by non-admins
        //     if (is_siteadmin($USER) or !is_siteadmin($user)) {
        //         $url = new moodle_url('/user/editadvanced.php', array('id' => $user->id, 'course' => $site->id));
        //         $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit));
        //     }
        // }

        // the last column - confirm or mnet info
        if (is_mnet_remote_user($user)) {
            // all mnet users are confirmed, let's print just the name of the host there
            if (isset($mnethosts[$user->mnethostid])) {
                $lastcolumn = get_string($accessctrl, 'mnet') . ': ' . $mnethosts[$user->mnethostid]->name;
            } else {
                $lastcolumn = get_string($accessctrl, 'mnet');
            }
        } else if ($user->confirmed == 0) {
            if (has_capability('moodle/user:update', $sitecontext)) {
                $lastcolumn = html_writer::link(new moodle_url($returnurl, array('confirmuser' => $user->id, 'sesskey' => sesskey())), $strconfirm);
            } else {
                $lastcolumn = "<span class=\"dimmed_text\">" . get_string('confirm') . "</span>";
            }

            $lastcolumn .= ' | ' . html_writer::link(new moodle_url(
                $returnurl,
                [
                    'resendemail' => $user->id,
                    'sesskey' => sesskey()
                ]
            ), $strresendemail);
        }

        if ($user->lastaccess) {
            $strlastaccess = format_time(time() - $user->lastaccess);
        } else {
            $strlastaccess = get_string('never');
        }
        $fullname = fullname($user, true);

        $row = array();
        $attributes = array('class' => 'checkbox', 'onchange' => 'uncheckAll()');
        $row[] = html_writer::checkbox('selected_users[]', $user->id, false, '', $attributes);
        // $row[] ="<input type='checkbox' name='userid[]' value='$user->id'/>";

        // $row[] = "select";
        $row[] = "<a href=\"../user/view.php?id=$user->id&amp;course=$site->id\">$fullname</a>";
        foreach ($extracolumns as $field) {
            $row[] = s($user->{$field});
        }
       
        // $row[] = $user->country;
        // $row[] = $strlastaccess;
        $c = $user->id;
        $context = context_user::instance($c);
        $roles = get_user_roles($context, $user->id, true);
        $role = key($roles);

        $rolename = $roles[$role]->name;

        $row[] = $rolename;
        if ($user->suspended) {
foreach ($row as $k => $v) {
                $row[$k] = html_writer::tag('span', $v, array('class' => 'usersuspended'));
            }
        }
        $row[]= "";
        $row[] = implode(' ', $buttons);
        // $row[] = $lastcolumn;
        $table->data[] = $row;
    }
}

// add filters
// $ufiltering->display_add();
// $ufiltering->display_active();

if (!empty($table)) {
    echo html_writer::start_tag('div', array('class' => 'no-overflow'));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo "<div>" . $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl) . "</div>";
}
// Lấy danh sách các người dùng được chọn
$selected_users = optional_param_array('selected_users', array(), PARAM_INT);

// Lấy thao tác hàng loạt được chọn
$bulk_action = optional_param('bulk_action', '', PARAM_ALPHA);

if (!empty($selected_users) && !empty($bulk_action)) {
    // Nếu có người dùng được chọn và thao tác hàng loạt được chọn, thực hiện thao tác
    switch ($bulk_action) {
        case 'delete':
            unset($SESSION->bulk_users);
            // Xóa các người dùng được chọn
            foreach ($selected_users as $user_id) {
                $SESSION->bulk_users[$user_id] = $user_id;
            }
            redirect($CFG->wwwroot . '/admin/user/user_bulk_delete.php');

            break;
        case 'sendessage':
            // Gửi tin nhắn
            unset($SESSION->bulk_users);
        // $message_content = $_POST['message_content'];
            foreach ($selected_users as $user_id) {
                // message_post_message($USER, $user_id, $message_content, FORMAT_HTML);
                // $SESSION->bulk_users[] = $user_id; 
                $SESSION->bulk_users[$user_id] = $user_id;
                // echo 'dajddlkas'.$SESSION->bulk_users;
                // var_dump($SESSION->bulk_users);
            }
            redirect($CFG->wwwroot . '/admin/bulk_message.php');
            break;
        case 'unsuspend':
            unset($SESSION->bulk_users);
            // Mở khóa các người dùng được chọn
            foreach ($selected_users as $user_id) {
                $SESSION->bulk_users[$user_id] = $user_id;
            }
            redirect($CFG->wwwroot . '/admin/user/user_bulk_download.php');
            
            break;
    }
    
}
echo '
<p>
<select class"custom selectcheckbox ml-2" name="bulk_action" id="bulk_action">
  <option value="">Chỉnh sửa hàng loạt</option>
  <option value="delete">Delete</option>
  <option value="sendessage">Send Message</option>
  <option value="unsuspend">Download</option>
</select>
</p>
</form>';

// var_dump(!empty($CFG->messaging));
// echo '<br/>';
// var_dump(has_all_capabilities(['moodle/site:sendmessage', 'moodle/course:bulkmessaging'], $context));

// echo '<br /><div class="buttons"><div class="form-inline">';
//     $displaylist = array();
//     if (!empty($CFG->messaging) && has_all_capabilities(['moodle/site:sendmessage', 'moodle/course:bulkmessaging'], $context)) {
//         $displaylist['#messageselect'] = get_string('messageselectadd');
//     }
//     $label = html_writer::tag(
//         'label',
//         get_string("withselectedusers"),
//         ['for' => 'formactionid', 'class' => 'col-form-label d-inline']
//     );
//     $selectactionparams = array(
//         'id' => 'formactionid',
//         'class' => 'ml-2',
//         'data-action' => 'toggle',
//         'data-togglegroup' => 'participants-table',
//         'data-toggle' => 'action',
//     );
//     $select = html_writer::select($displaylist, 'formaction', '', ['' => 'choosedots'], $selectactionparams);
//     echo html_writer::tag('div', $label . $select);

// echo '<input type="hidden" name="id" value="' . $course->id . '" />';
// echo '<div class="d-none" data-region="state-help-icon">' . $OUTPUT->help_icon('publishstate', 'notes') . '</div>';
// echo '</div></div></div>';
// $PAGE->requires->js_call_amd('core_user/participants', 'init', [$bulkoptions]);
// echo '</div>';  // Userlist.
// echo '</form>';


echo $OUTPUT->footer();
?>
</script>
<script>
  // Lấy ra element select và form
  const select = document.getElementById('bulk_action');
  const form = document.getElementById('myform');

  // Gắn sự kiện "change" cho select
  select.addEventListener('change', function() {
    // Submit form bằng JavaScript
    form.submit();
  });
</script>
<script>
    // var header = document.getElementById("hover_tag_a");
    // var btns = header.getElementsByClassName("a_hover");
    // for (var i = 0; i < btns.length; i++) {
    //     btns[i].addEventListener("click", function() {
    //         var current = document.getElementsByClassName("active1");
    //         current[0].className = current[0].className.replace("active1", "");
    //         this.className += " active1";
    //     });
    // }
    
    function checkAll() {
        var checkboxes = document.getElementsByClassName("checkbox");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = document.getElementById("checkall").checked;
        }
    }
    function uncheckAll() {
        var checkall = document.querySelector('#checkall');
        checkall.checked = false;
    }
    $("#selectedOption").on("change", function () {
        var selectedOption = this.value,
            input = $("#filter-keyword");
        $('#filter-keyword').css("display", $.inArray(selectedOption, ["user_name", "user_email"]) !== -1 ? "block" : "none");
          if (selectedOption !== "keyword") {
            input.val("");
        }
        input.trigger("keyup");
        input.on("keyup", function () {
            var filter = input.val().toUpperCase(),
                table = $("#users"),    
                tr = table.find("tr"),
                td, txtValue;
            tr.each(function (i, el) {
if (i !== 0) {
                    td = $(el).find("td").eq(selectedOption === "user_name" ? 1 : (selectedOption === "user_email" ? 2 : 3));
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
