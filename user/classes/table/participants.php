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
 * Contains the class used for the displaying the participants table.
 *
 * @package    core_user
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
declare(strict_types=1);

namespace core_user\table;

use DateTime;
use context;
use core_table\dynamic as dynamic_table;
use core_table\local\filter\filterset;
use core_user\output\status_field;
use core_user\table\participants_search;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Class for the displaying the participants table.
 *
 * @package    core_user
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class participants extends \table_sql implements dynamic_table {

    /**
     * @var int $courseid The course id
     */
    protected $courseid;

    /**
     * @var string[] The list of countries.
     */
    protected $countries;

    /**
     * @var \stdClass[] The list of groups with membership info for the course.
     */
    protected $groups;

    /**
     * @var string[] Extra fields to display.
     */
    protected $extrafields;

    /**
     * @var \stdClass $course The course details.
     */
    protected $course;

    /**
     * @var  context $context The course context.
     */
    protected $context;

    /**
     * @var \stdClass[] List of roles indexed by roleid.
     */
    protected $allroles;

    /**
     * @var \stdClass[] List of roles indexed by roleid.
     */
    protected $allroleassignments;

    /**
     * @var \stdClass[] Assignable roles in this course.
     */
    protected $assignableroles;

    /**
     * @var \stdClass[] Profile roles in this course.
     */
    protected $profileroles;

    /**
     * @var filterset Filterset describing which participants to include.
     */
    protected $filterset;

    /** @var \stdClass[] $viewableroles */
    private $viewableroles;

    /** @var moodle_url $baseurl The base URL for the report. */
    public $baseurl;

    /**
     * Render the participants table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $CFG, $OUTPUT, $PAGE;

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $bulkoperations = has_capability('moodle/course:bulkmessaging', $this->context);
        if ($bulkoperations) {
            $mastercheckbox = new \core\output\checkbox_toggleall('participants-table', true, [
                'id' => 'select-all-participants',
                'name' => 'select-all-participants',
                'label' => get_string('selectall'),
                'labelclasses' => 'sr-only',
                'classes' => 'm-1',
                'checked' => false,
            ]);
            // Check box
            $headers[] = $OUTPUT->render($mastercheckbox);
            $columns[] = 'select';
        }

        $headers[] = get_string('fullnametest');
        $columns[] = 'fullname';

        $extrafields = \core_user\fields::get_identity_fields($this->context);
        foreach ($extrafields as $field) {
            $headers[] = \core_user\fields::get_display_name($field);
            $columns[] = $field;
        }

        $headers[] = get_string('roles');
        $columns[] = 'roles';

        // Get the list of fields we have to hide.
        $hiddenfields = array();
        if (!has_capability('moodle/course:viewhiddenuserfields', $this->context)) {
            $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
        }

        // Add column for groups if the user can view them.
        $canseegroups = !isset($hiddenfields['groups']);
        // if ($canseegroups) {
        //     $headers[] = get_string('groups');
        //     $columns[] = 'groups';
        // }

        // Lần truy cập cuối cùng
        // Do not show the columns if it exists in the hiddenfields array.
        // if (!isset($hiddenfields['lastaccess'])) {
        //     if ($this->courseid == SITEID) {
        //         $headers[] = get_string('lastsiteaccess');
        //     } else {
        //         $headers[] = get_string('lastcourseaccess');
        //     }
        //     $columns[] = 'lastaccess';
        // }

        $canreviewenrol = has_capability('moodle/course:enrolreview', $this->context);
        if ($canreviewenrol && $this->courseid != SITEID) {
            $columns[] = 'status';
            $headers[] = get_string('participationstatus', 'enrol');
            $this->no_sorting('status');
        };

        $this->define_columns($columns);
        $this->define_headers($headers);

        // The name column is a header.
        $this->define_header_column('fullname');

        // Make this table sorted by last name by default.
        $this->sortable(true, 'lastname');

        $this->no_sorting('select');
        $this->no_sorting('roles');
        if ($canseegroups) {
            $this->no_sorting('groups');
        }

        $this->set_default_per_page(10);

        $this->set_attribute('id', 'participants');

        $this->countries = get_string_manager()->get_list_of_countries(true);
        $this->extrafields = $extrafields;
        if ($canseegroups) {
            $this->groups = groups_get_all_groups($this->courseid, 0, 0, 'g.*', true);
        }

        // If user has capability to review enrol, show them both role names.
        $allrolesnamedisplay = ($canreviewenrol ? ROLENAME_BOTH : ROLENAME_ALIAS);
        $this->allroles = role_fix_names(get_all_roles($this->context), $this->context, $allrolesnamedisplay);
        $this->assignableroles = get_assignable_roles($this->context, ROLENAME_BOTH, false);
        $this->profileroles = get_profile_roles($this->context);
        $this->viewableroles = get_viewable_roles($this->context);

        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);

        if (has_capability('moodle/course:enrolreview', $this->context)) {
            $params = [
                'contextid' => $this->context->id,
                'uniqueid' => $this->uniqueid,
            ];
            $PAGE->requires->js_call_amd('core_user/status_field', 'init', [$params]);
        }
    }

    /**
     * Generate the select column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_select($data) {
        global $OUTPUT;

        $checkbox = new \core\output\checkbox_toggleall('participants-table', false, [
            'classes' => 'usercheckbox m-1',
            'id' => 'user' . $data->id,
            'name' => 'user' . $data->id,
            'checked' => false,
            'label' => get_string('selectitem', 'moodle', fullname($data)),
            'labelclasses' => 'accesshide',
        ]);

        return $OUTPUT->render($checkbox);
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        return $OUTPUT->user_picture($data, array('size' => 35, 'courseid' => $this->course->id, 'includefullname' => true));
    }

    /**
     * User roles column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_roles($data) {
        global $OUTPUT;

        $roles = isset($this->allroleassignments[$data->id]) ? $this->allroleassignments[$data->id] : [];
        $editable = new \core_user\output\user_roles_editable($this->course,
                                                              $this->context,
                                                              $data,
                                                              $this->allroles,
                                                              $this->assignableroles,
                                                              $this->profileroles,
                                                              $roles,
                                                              $this->viewableroles);

        return $OUTPUT->render_from_template('core/inplace_editable', $editable->export_for_template($OUTPUT));
    }

    /**
     * Generate the groups column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_groups($data) {
        global $OUTPUT;

        $usergroups = [];
        foreach ($this->groups as $coursegroup) {
            if (isset($coursegroup->members[$data->id])) {
                $usergroups[] = $coursegroup->id;
            }
        }
        $editable = new \core_group\output\user_groups_editable($this->course, $this->context, $data, $this->groups, $usergroups);
        return $OUTPUT->render_from_template('core/inplace_editable', $editable->export_for_template($OUTPUT));
    }

    /**
     * Generate the country column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_country($data) {
        if (!empty($this->countries[$data->country])) {
            return $this->countries[$data->country];
        }
        return '';
    }

    /**
     * Generate the last access column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_lastaccess($data) {
        if ($data->lastaccess) {
            return format_time(time() - $data->lastaccess);
        }

        return get_string('never');
    }

    /**
     * Generate the status column.
     *
     * @param \stdClass $data The data object.
     * @return string
     */
    public function col_status($data) {
        global $CFG, $OUTPUT, $PAGE,$USER;
        $page         = optional_param('page', 0, PARAM_INT); // Which page to show.
        $perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
        $contextid    = optional_param('contextid', 0, PARAM_INT);
        $courseid     = optional_param('id', 0, PARAM_INT); // This are required.
        $newcourse    = optional_param('newcourse', false, PARAM_BOOL);         
        $enrolstatusoutput = '';
        $canreviewenrol = has_capability('moodle/course:enrolreview', $this->context);
        if ($canreviewenrol) {
            $canviewfullnames = has_capability('moodle/site:viewfullnames', $this->context);
            $fullname = fullname($data, $canviewfullnames);
            $coursename = format_string($this->course->fullname, true, array('context' => $this->context));
            require_once($CFG->dirroot . '/enrol/locallib.php');
            $manager = new \course_enrolment_manager($PAGE, $this->course);
            $userenrolments = $manager->get_user_enrolments($data->id);
            foreach ($userenrolments as $ue) {
                $timestart = $ue->timestart;
                $timeend = $ue->timeend;
                $timeenrolled = $ue->timecreated;
                $actions = $ue->enrolmentplugin->get_user_enrolment_actions($manager, $ue);
                $instancename = $ue->enrolmentinstancename;

                // Show active
                // Default status field label and value.
                // $status = get_string('participationactive', 'enrol');
                // $statusval = status_field::STATUS_ACTIVE;
                // switch ($ue->status) {
                //     case ENROL_USER_ACTIVE:
                //         $currentdate = new DateTime();
                //         $now = $currentdate->getTimestamp();
                //         $isexpired = $timestart > $now || ($timeend > 0 && $timeend < $now);
                //         $enrolmentdisabled = $ue->enrolmentinstance->status == ENROL_INSTANCE_DISABLED;
                //         // If user enrolment status has not yet started/already ended or the enrolment instance is disabled.
                //         if ($isexpired || $enrolmentdisabled) {
                //             $status = get_string('participationnotcurrent', 'enrol');
                //             $statusval = status_field::STATUS_NOT_CURRENT;
                //         }
                //         break;
                //     case ENROL_USER_SUSPENDED:
                //         $status = get_string('participationsuspended', 'enrol');
                //         $statusval = status_field::STATUS_SUSPENDED;
                //         break;
                // }

                $statusfield = new status_field($instancename, $coursename, $fullname, $status, $timestart, $timeend,
                    $actions, $timeenrolled);
                $statusfielddata = $statusfield->set_status($statusval)->export_for_template($OUTPUT);
                // Edit cũ
                // $enrolstatusoutput .= $OUTPUT->render_from_template('core_user/status_field', $statusfielddata);
                $enrolstatusoutput = "<div class='dropdown'>
                    <button class='btn btn-secondary dropdown-toggle bg-none' type='button' id='dropdownMenu2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' style='background:none;width:24px;height:24px;padding:0;'>
                        <svg width='16' height='4' viewBox='0 0 16 4' fill='none' xmlns='http://www.w3.org/2000/svg'>
                            <path fill-rule='evenodd' clip-rule='evenodd' d='M15.2577 2.005C15.2577 1.31453 14.698 0.754791 14.0075 0.754791C13.3171 0.754791 12.7573 1.31453 12.7573 2.005C12.7573 2.69547 13.3171 3.25521 14.0075 3.25521C14.698 3.25521 15.2577 2.69547 15.2577 2.005Z' fill='black'></path>
                            <path fill-rule='evenodd' clip-rule='evenodd' d='M9.2553 2.005C9.2553 1.31453 8.69556 0.754791 8.00509 0.754791C7.31462 0.754791 6.75488 1.31453 6.75488 2.005C6.75488 2.69547 7.31462 3.25521 8.00509 3.25521C8.69556 3.25521 9.2553 2.69547 9.2553 2.005Z' fill='black'></path>
                            <path fill-rule='evenodd' clip-rule='evenodd' d='M3.25286 2.005C3.25286 1.31453 2.69312 0.754791 2.00265 0.754791C1.31218 0.754791 0.752441 1.31453 0.752441 2.005C0.752441 2.69547 1.31218 3.25521 2.00265 3.25521C2.69312 3.25521 3.25286 2.69547 3.25286 2.005Z' fill='black'></path>
                        </svg>
                    </button>
                    <div class='dropdown-menu' aria-labelledby='dropdownMenu2' style='max-width:60px;min-width:1rem;overflow-x:hidden'>
                        <a href='$CFG->wwwroot/enrol/editenrolment.php?page=$page&perpage=$perpage&contextid=$contextid&id=$courseid&newcourse&ue=$ue->id'
                        class='dropdown-item dropdown-item-wrapper action-edit menu-action' data-action='edit'>
                        <svg stroke='currentColor' fill='none' stroke-width='2' viewBox='0 0 24 24' stroke-linecap='round' stroke-linejoin='round' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
                        <path d='M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z'>
                        </path>
                        </svg>
                        </a>
                        <a href='$CFG->wwwroot/enrol/unenroluser.php?page=$page&perpage=$perpage&contextid=$contextid&id=$courseid&newcourse&ue=$ue->id'
                        class='dropdown-item dropdown-item-wrapper action-delete menu-action' data-action='delete'>
                        <svg stroke='currentColor' fill='none' stroke-width='0' viewBox='0 0 24 24' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'>
                        <path fill-rule='evenodd' clip-rule='evenodd' d='M17 6V5C17 3.89543 16.1046 3 15 3H9C7.89543 3 7 3.89543 7 5V6H4C3.44772 6 3 6.44772 3 7C3 7.55228 3.44772 8 4 8H5V19C5 20.6569 6.34315 22 8 22H16C17.6569 22 19 20.6569 19 19V8H20C20.5523 8 21 7.55228 21 7C21 6.44772 20.5523 6 20 6H17ZM15 5H9V6H15V5ZM17 8H7V19C7 19.5523 7.44772 20 8 20H16C16.5523 20 17 19.5523 17 19V8Z' fill='currentColor'></path></svg>
                        </a>
                    </div>
                    </div>";    
            }
        }
        return $enrolstatusoutput;
    }

    /**
     * This function is used for the extra user fields.
     *
     * These are being dynamically added to the table so there are no functions 'col_<userfieldname>' as
     * the list has the potential to increase in the future and we don't want to have to remember to add
     * a new method to this class. We also don't want to pollute this class with unnecessary methods.
     *
     * @param string $colname The column name
     * @param \stdClass $data
     * @return string
     */
    public function other_cols($colname, $data) {
        // Do not process if it is not a part of the extra fields.
        if (!in_array($colname, $this->extrafields)) {
            return '';
        }

        return s($data->{$colname});
    }

    /**
     * Query the database for results to display in the table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        list($twhere, $tparams) = $this->get_sql_where();
        $psearch = new participants_search($this->course, $this->context, $this->filterset);

        $total = $psearch->get_total_participants_count($twhere, $tparams);

        $this->pagesize($pagesize, $total);

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = 'ORDER BY ' . $sort;
        }

        $rawdata = $psearch->get_participants($twhere, $tparams, $sort, $this->get_page_start(), $this->get_page_size());

        $this->rawdata = [];
        foreach ($rawdata as $user) {
            $this->rawdata[$user->id] = $user;
        }
        $rawdata->close();

        if ($this->rawdata) {
            $this->allroleassignments = get_users_roles($this->context, array_keys($this->rawdata),
                    true, 'c.contextlevel DESC, r.sortorder ASC');
        } else {
            $this->allroleassignments = [];
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }

    /**
     * Override the table show_hide_link to not show for select column.
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        if ($index > 0) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }

    /**
     * Set filters and build table structure.
     *
     * @param filterset $filterset The filterset object to get the filters from.
     */
    public function set_filterset(filterset $filterset): void {
        // Get the context.
        $this->courseid = $filterset->get_filter('courseid')->current();
        $this->course = get_course($this->courseid);
        $this->context = \context_course::instance($this->courseid, MUST_EXIST);

        // Process the filterset.
        parent::set_filterset($filterset);
    }

    /**
     * Guess the base url for the participants table.
     */
    public function guess_base_url(): void {
        $this->baseurl = new moodle_url('/user/index.php', ['id' => $this->courseid]);
    }

    /**
     * Get the context of the current table.
     *
     * Note: This function should not be called until after the filterset has been provided.
     *
     * @return context
     */
    public function get_context(): context {
        return $this->context;
    }
}