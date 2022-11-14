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

defined('MOODLE_INTERNAL') || die();


//ASSIGN
include_once($CFG->dirroot . "/mod/assign/renderer.php");
class theme_alpha_mod_assign_renderer extends mod_assign_renderer {


        /**
     * Render the header.
     *
     * @param assign_header $header
     * @return string
     */
     public function render_assign_header(assign_header $header) {
        global $USER;

        $o = '';

        if ($header->subpage) {
            $this->page->navbar->add($header->subpage);
            $args = ['contextname' => $header->context->get_context_name(false, true), 'subpage' => $header->subpage];
            $title = get_string('subpagetitle', 'assign', $args);
        } else {
            $title = $header->context->get_context_name(false, true);
        }
        $courseshortname = $header->context->get_course_context()->get_context_name(false, true);
        $title = $courseshortname . ': ' . $title;
        $heading = format_string($header->assign->name, false, array('context' => $header->context));

        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);

        $o .= $this->output->header();

        // Show the activity information output component.
        $modinfo = get_fast_modinfo($header->assign->course);
        $cm = $modinfo->get_cm($header->coursemoduleid);
        $cmcompletion = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
        $activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);

        $o .= $this->output->heading($heading);
        $o .= $this->output->activity_information($cm, $cmcompletion, $activitydates);

        if ($header->preface) {
            $o .= $header->preface;
        }

        if ($header->showintro) {
            $o .= $this->output->container_start('rui-assign-intro lead', 'rui-assign-intro'); // class, id
            $o .= format_module_intro('assign', $header->assign, $header->coursemoduleid);
            $o .= $header->postfix;
            $o .= $this->output->container_end();
        }

        return $o;
    }


    /**
     * Render a table containing the current status of the grading process.
     *
     * @param assign_grading_summary $summary
     * @return string
     */
    public function render_assign_grading_summary(assign_grading_summary $summary) {
        // Create a table for the data.
        $o = '';
        $o .= $this->output->container_start('rui-gradingsummary');
        $o .= $this->output->heading(get_string('gradingsummary', 'assign'), 5);
        $o .= $this->output->container_start('rui-info-container mt-2');

        // Visibility Status.
        $cell1content = get_string('hiddenfromstudents');
        $cell2content = (!$summary->isvisible) ? get_string('yes') : get_string('no');

        $o .= '<div class="rui-infobox rui-infobox--hiddenfromstudents"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';

        // Status.
        if ($summary->teamsubmission) {
            if ($summary->warnofungroupedusers === assign_grading_summary::WARN_GROUPS_REQUIRED) {
                $o .= $this->output->notification(get_string('ungroupedusers', 'assign'));
            } else if ($summary->warnofungroupedusers === assign_grading_summary::WARN_GROUPS_OPTIONAL) {
                $o .= $this->output->notification(get_string('ungroupedusersoptional', 'assign'));
            }
            $cell1content = get_string('numberofteams', 'assign');
        } else {
            $cell1content = get_string('numberofparticipants', 'assign');
        }

        $cell2content = $summary->participantcount;
        $o .= '<div class="rui-infobox rui-infobox--participant"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';

        // Drafts count and dont show drafts count when using offline assignment.
        if ($summary->submissiondraftsenabled && $summary->submissionsenabled) {
            $cell1content = get_string('numberofdraftsubmissions', 'assign');
            $cell2content = $summary->submissiondraftscount;
            $o .= '<div class="rui-infobox rui-infobox--drafts"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';
        }

        // Submitted for grading.
        if ($summary->submissionsenabled) {
            $cell1content = get_string('numberofsubmittedassignments', 'assign');
            $cell2content = $summary->submissionssubmittedcount;
            $o .= '<div class="rui-infobox rui-infobox--submitted"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';

            if (!$summary->teamsubmission) {
                $cell1content = get_string('numberofsubmissionsneedgrading', 'assign');
                $cell2content = $summary->submissionsneedgradingcount;
                $o .= '<div class="rui-infobox rui-infobox--needgrading"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';
            }
        }

        $time = time();
        if ($summary->duedate) {
            // Due date.
            $cell1content = get_string('duedate', 'assign');
            $duedate = $summary->duedate;
            if ($summary->courserelativedatesmode) {
                // Returns a formatted string, in the format '10d 10h 45m'.
                $diffstr = get_time_interval_string($duedate, $summary->coursestartdate);
                if ($duedate >= $summary->coursestartdate) {
                    $cell2content = get_string('relativedatessubmissionduedateafter', 'mod_assign',
                        ['datediffstr' => $diffstr]);
                } else {
                    $cell2content = get_string('relativedatessubmissionduedatebefore', 'mod_assign',
                        ['datediffstr' => $diffstr]);
                }
            } else {
                $cell2content = userdate($duedate);
            }

            $o .= '<div class="rui-infobox rui-infobox--duedate"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content--small"> '. $cell2content . '</span></div>';

            // Time remaining.
            $cell1content = get_string('timeremaining', 'assign');
            if ($summary->courserelativedatesmode) {
                $cell2content = get_string('relativedatessubmissiontimeleft', 'mod_assign');
            } else {
                if ($duedate - $time <= 0) {
                    $cell2content = get_string('assignmentisdue', 'assign');
                } else {
                    $cell2content = format_time($duedate - $time);
                }
            }

            $o .= '<div class="rui-infobox rui-infobox--timeremaining"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';

            if ($duedate < $time) {
                $cell1content = get_string('latesubmissions', 'assign');
                $cutoffdate = $summary->cutoffdate;
                if ($cutoffdate) {
                    if ($cutoffdate > $time) {
                        $cell2content = get_string('latesubmissionsaccepted', 'assign', userdate($summary->cutoffdate));
                    } else {
                        $cell2content = get_string('nomoresubmissionsaccepted', 'assign');
                    }

                    $o .= '<div class="rui-infobox rui-infobox--cutofdate"><h5 class="rui-infobox-title">' . $cell1content . '</h5><span class="rui-infobox-content"> '. $cell2content . '</span></div>';
                }
            }

        }

        // TODO: zakończenie boxa z danymi ... All done - write the table.
        $o .= $this->output->container_end();

        // Link to the grading page.
        $o .= $this->output->container_start('rui-submissionbuttons');
        $urlparams = array('id' => $summary->coursemoduleid, 'action' => 'grading');
        $url = new moodle_url('/mod/assign/view.php', $urlparams);
        $o .= html_writer::link($url, get_string('viewgrading', 'mod_assign'),
            ['class' => 'btn btn-secondary']);
        if ($summary->cangrade) {
            $urlparams = array('id' => $summary->coursemoduleid, 'action' => 'grader');
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $o .= html_writer::link($url, get_string('gradeverb'),
                ['class' => 'btn btn-primary ml-1']);
        }
        $o .= $this->output->container_end();

        // Close the container and insert a spacer.
        $o .= $this->output->container_end();

        return $o;
    }

    /**
     * Render a table containing all the current grades and feedback.
     *
     * @param assign_feedback_status $status
     * @return string
     */
    public function render_assign_feedback_status(assign_feedback_status $status) {
        $o = '';

        $o .= $this->output->container_start('rui-feedback');
        $o .= $this->output->heading(get_string('feedback', 'assign'), 5);
        $o .= $this->output->container_start('rui-feedbacktable');

        // Grade.
        if (isset($status->gradefordisplay)) {
            $cell1content = get_string('grade');
            $cell2content = $status->gradefordisplay;
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';

            // Grade date.
            $cell1content = get_string('gradedon', 'assign');
            $cell2content = userdate($status->gradeddate);
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        if ($status->grader) {
            // Grader.
            $cell1content = get_string('gradedby', 'assign');
            $cell2content = $this->output->user_picture($status->grader) .
                            fullname($status->grader, $status->canviewfullnames);
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        foreach ($status->feedbackplugins as $plugin) {
            if ($plugin->is_enabled() &&
                    $plugin->is_visible() &&
                    $plugin->has_user_summary() &&
                    !empty($status->grade) &&
                    !$plugin->is_empty($status->grade)) {

                $displaymode = assign_feedback_plugin_feedback::SUMMARY;
                $pluginfeedback = new assign_feedback_plugin_feedback($plugin,
                                                                      $status->grade,
                                                                      $displaymode,
                                                                      $status->coursemoduleid,
                                                                      $status->returnaction,
                                                                      $status->returnparams);
                $cell1content = $plugin->get_name();
                $cell2content = $this->render($pluginfeedback);
                $o .= '<dt>' . $cell1content . '</dt>';
                $o .= '<dd>' . $cell2content . '</dd>';
            }
        }

        $o .= $this->output->container_end();

        $o .= $this->output->container_end();
        return $o;
    }

    /**
     * Render a compact view of the current status of the submission.
     *
     * @param assign_submission_status_compact $status
     * @return string
     */
    public function render_assign_submission_status_compact(assign_submission_status_compact $status) {
        $o = '';
        $o .= $this->output->container_start('rui-submissionstatus mb-5');
        $o .= $this->output->heading(get_string('submission', 'assign'), 5);
        $o .= $this->output->container_start('rui-info-container-list mt-2 mb-4');

        $time = time();

        if ($status->teamsubmissionenabled) {
            $group = $status->submissiongroup;
            if ($group) {
                $team = format_string($group->name, false, $status->context);
            } else if ($status->preventsubmissionnotingroup) {
                if (count($status->usergroups) == 0) {
                    $team = '<span class="alert alert-error">' . get_string('noteam', 'assign') . '</span>';
                } else if (count($status->usergroups) > 1) {
                    $team = '<span class="alert alert-error">' . get_string('multipleteams', 'assign') . '</span>';
                }
            } else {
                $team = get_string('defaultteam', 'assign');
            }
            $o .= $this->output->container(get_string('teamname', 'assign', $team), 'teamname');
        }

        if (!$status->teamsubmissionenabled) {
            if ($status->submission && $status->submission->status != ASSIGN_SUBMISSION_STATUS_NEW) {
                $statusstr = get_string('submissionstatus_' . $status->submission->status, 'assign');
                $o .= '<div class="badge-assign rui-badge-assign--' .$status->submission->status . '"><span class="rui-infobox-content--small"> '. $statusstr. '</span></div>';
            } else {
                if (!$status->submissionsenabled) {
                    $o .= '<div class="badge-assign rui-badge-assign--noonlinesubmissions"><span class="rui-infobox-content--small"> '. get_string('noonlinesubmissions', 'assign') . '</span></div>';
                } else {
                    $o .= $this->output->container(get_string('noattempt', 'assign'), 'badge-assign rui-badge-assign-submissionstatus');
                    $o .= '<div class="badge-assign rui-badge-assign--noattempt"><span class="rui-infobox-content--small"> '. get_string('noattempt', 'assign') . '</span></div>';
                }
            }
        } else {
            $group = $status->submissiongroup;
            if (!$group && $status->preventsubmissionnotingroup) {
                $o .= $this->output->container(get_string('nosubmission', 'assign'), 'badge-assign rui-badge-assign-submissionstatus');
            } else if ($status->teamsubmission && $status->teamsubmission->status != ASSIGN_SUBMISSION_STATUS_NEW) {
                $teamstatus = $status->teamsubmission->status;
                $submissionsummary = get_string('submissionstatus_' . $teamstatus, 'assign');
                $groupid = 0;
                if ($status->submissiongroup) {
                    $groupid = $status->submissiongroup->id;
                }

                $members = $status->submissiongroupmemberswhoneedtosubmit;
                $userslist = array();
                foreach ($members as $member) {
                    $urlparams = array('id' => $member->id, 'course' => $status->courseid);
                    $url = new moodle_url('/user/view.php', $urlparams);
                    if ($status->view == assign_submission_status::GRADER_VIEW && $status->blindmarking) {
                        $userslist[] = $member->alias;
                    } else {
                        $fullname = fullname($member, $status->canviewfullnames);
                        $userslist[] = $this->output->action_link($url, $fullname);
                    }
                }
                if (count($userslist) > 0) {
                    $userstr = join(', ', $userslist);
                    $formatteduserstr = get_string('userswhoneedtosubmit', 'assign', $userstr);
                    $submissionsummary .= $this->output->container($formatteduserstr);
                }
                $o .= $this->output->container($submissionsummary, 'submissionstatus' . $status->teamsubmission->status);
            } else {
                if (!$status->submissionsenabled) {
                    $o .= $this->output->container(get_string('noonlinesubmissions', 'assign'), 'rui-infobox submissionstatus');
                } else {
                    $o .= $this->output->container(get_string('nosubmission', 'assign'), 'rui-infobox submissionstatus');
                }
            }
        }

        // Is locked?
        if ($status->locked) {
            $o .= '<div class="badge-assign rui-badge-assign--submissionlocked"><span class="rui-infobox-content--small"> ' . get_string('submissionslocked', 'assign') . '</span></div>';
        }

        // Grading status.
        $statusstr = '';
        $classname = 'gradingstatus';
        if ($status->gradingstatus == ASSIGN_GRADING_STATUS_GRADED ||
            $status->gradingstatus == ASSIGN_GRADING_STATUS_NOT_GRADED) {
            $statusstr = get_string($status->gradingstatus, 'assign');
        } else {
            $gradingstatus = 'markingworkflowstate' . $status->gradingstatus;
            $statusstr = get_string($gradingstatus, 'assign');
        }
        if ($status->gradingstatus == ASSIGN_GRADING_STATUS_GRADED ||
            $status->gradingstatus == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $classname = 'badge-assign rui-badge-assign--submissiongraded';
        } else {
            $classname = 'badge-assign rui-badge-assign--submissionnotgraded';
        }
        $o .= '<div class="' . $classname .'"><span class="rui-infobox-content--small"> '. $statusstr. '</span></div>';

        $submission = $status->teamsubmission ? $status->teamsubmission : $status->submission;
        $duedate = $status->duedate;
        if ($duedate > 0) {

            if ($status->extensionduedate) {
                // Extension date.
                $duedate = $status->extensionduedate;
            }

            // Time remaining. - Grader Page
            $classname = 'badge-assign rui-badge-assign--timeremaining';
            if ($duedate - $time <= 0) {
                if (!$submission ||
                        $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                    if ($status->submissionsenabled) {
                        $remaining = get_string('overdue', 'assign', format_time($time - $duedate));
                        $classname = 'badge-assign rui-badge-assign--overdue';
                    } else {
                        $remaining = get_string('duedatereached', 'assign');
                    }
                } else {
                    if ($submission->timemodified > $duedate) {
                        $remaining = get_string('submittedlate',
                                              'assign',
                                              format_time($submission->timemodified - $duedate));
                        $classname = 'latesubmission';
                    } else {
                        $remaining = get_string('submittedearly',
                                               'assign',
                                               format_time($submission->timemodified - $duedate));
                        $classname = 'earlysubmission';
                    }
                }
            } else {
                $remaining = get_string('paramtimeremaining', 'assign', format_time($duedate - $time));
            }
            $o .= '<div class="' . $classname .'"><span class="rui-infobox-content--small"> '. $remaining. '</span></div>';
        }

        // Show graders whether this submission is editable by students.
        if ($status->view == assign_submission_status::GRADER_VIEW) {
            if ($status->canedit) {
                $o .= '<div class="badge-assign rui-badge-assign--submitted"><span class="rui-infobox-content--small"> '. get_string('submissioneditable', 'assign') . '</span></div>';
            } else {
                $o .= '<div class="badge-assign rui-badge-assign--needgrading"><span class="rui-infobox-content--small"> '. get_string('submissionnoteditable', 'assign') . '</span></div>';
            }
        }

        // Grading criteria preview.
        if (!empty($status->gradingcontrollerpreview)) {
            $o .= $this->output->container($status->gradingcontrollerpreview, 'rui-infobox gradingmethodpreview');
        }

        $o .= $this->output->container_end(); // rui-info-container

        if ($submission) {

            if (!$status->teamsubmission || $status->submissiongroup != false || !$status->preventsubmissionnotingroup) {
                foreach ($status->submissionplugins as $plugin) {
                    $pluginshowsummary = !$plugin->is_empty($submission) || !$plugin->allow_submissions();
                    if ($plugin->is_enabled() &&
                        $plugin->is_visible() &&
                        $plugin->has_user_summary() &&
                        $pluginshowsummary
                    ) {

                        $displaymode = assign_submission_plugin_submission::SUMMARY;
                        $pluginsubmission = new assign_submission_plugin_submission($plugin,
                            $submission,
                            $displaymode,
                            $status->coursemoduleid,
                            $status->returnaction,
                            $status->returnparams);
                        $plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
                        $o .= $this->output->container($this->render($pluginsubmission), 'rui-assignsubmission assignsubmission ' . $plugincomponent);
                    }
                }
            }
        }

        $o .= $this->output->container_end();  //rui-submissionstatus
        return $o;
    }

    /**
     * Render a table containing the current status of the submission.
     *
     * @param assign_submission_status $status
     * @return string
     */
    public function render_assign_submission_status(assign_submission_status $status) {
        $o = '';
        $o .= $this->output->container_start('rui-submissionstatustable');
        //$o .= $this->output->heading(get_string('submissionstatusheading', 'assign'), 5);
        $time = time();

        if ($status->allowsubmissionsfromdate &&
                $time <= $status->allowsubmissionsfromdate) {
            $o .= $this->output->container_start('rui-submissionsalloweddates');
            if ($status->alwaysshowdescription) {
                $date = userdate($status->allowsubmissionsfromdate);
                $o .= get_string('allowsubmissionsfromdatesummary', 'assign', $date);
            } else {
                $date = userdate($status->allowsubmissionsfromdate);
                $o .= get_string('allowsubmissionsanddescriptionfromdatesummary', 'assign', $date);
            }
            $o .= $this->output->container_end();
        }

        $o .= $this->output->container_start('rui-submissionsummarytable');


        $warningmsg = '';
        if ($status->teamsubmissionenabled) {
            $cell1content = get_string('submissionteam', 'assign');
            $group = $status->submissiongroup;
            if ($group) {
                $cell2content = format_string($group->name, false, $status->context);
            } else if ($status->preventsubmissionnotingroup) {
                if (count($status->usergroups) == 0) {
                    $notification = new \core\output\notification(get_string('noteam', 'assign'), 'error');
                    $notification->set_show_closebutton(false);
                    $warningmsg = $this->output->notification(get_string('noteam_desc', 'assign'), 'error');
                } else if (count($status->usergroups) > 1) {
                    $notification = new \core\output\notification(get_string('multipleteams', 'assign'), 'error');
                    $notification->set_show_closebutton(false);
                    $warningmsg = $this->output->notification(get_string('multipleteams_desc', 'assign'), 'error');
                }
                $cell2content = $this->output->render($notification);
            } else {
                $cell2content = get_string('defaultteam', 'assign');
            }

            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        if ($status->attemptreopenmethod != ASSIGN_ATTEMPT_REOPEN_METHOD_NONE) {
            $currentattempt = 1;
            if (!$status->teamsubmissionenabled) {
                if ($status->submission) {
                    $currentattempt = $status->submission->attemptnumber + 1;
                }
            } else {
                if ($status->teamsubmission) {
                    $currentattempt = $status->teamsubmission->attemptnumber + 1;
                }
            }

            $cell1content = get_string('attemptnumber', 'assign');
            $maxattempts = $status->maxattempts;
            if ($maxattempts == ASSIGN_UNLIMITED_ATTEMPTS) {
                $cell2content = get_string('currentattempt', 'assign', $currentattempt);
            } else {
                $cell2content = get_string('currentattemptof', 'assign',
                    array('attemptnumber' => $currentattempt, 'maxattempts' => $maxattempts));
            }

            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        $cell1content = get_string('submissionstatus', 'assign');
        $cell2attributes = [];
        if (!$status->teamsubmissionenabled) {
            if ($status->submission && $status->submission->status != ASSIGN_SUBMISSION_STATUS_NEW) {
                $cell2content = get_string('submissionstatus_' . $status->submission->status, 'assign');
                $cell2attributes = array('class' => 'submissionstatus' . $status->submission->status);
            } else {
                if (!$status->submissionsenabled) {
                    $cell2content = get_string('noonlinesubmissions', 'assign');
                } else {
                    $cell2content = get_string('noattempt', 'assign');
                }
            }
        } else {
            $group = $status->submissiongroup;
            if (!$group && $status->preventsubmissionnotingroup) {
                $cell2content = get_string('nosubmission', 'assign');
            } else if ($status->teamsubmission && $status->teamsubmission->status != ASSIGN_SUBMISSION_STATUS_NEW) {
                $teamstatus = $status->teamsubmission->status;
                $cell2content = get_string('submissionstatus_' . $teamstatus, 'assign');

                $members = $status->submissiongroupmemberswhoneedtosubmit;
                $userslist = array();
                foreach ($members as $member) {
                    $urlparams = array('id' => $member->id, 'course'=>$status->courseid);
                    $url = new moodle_url('/user/view.php', $urlparams);
                    if ($status->view == assign_submission_status::GRADER_VIEW && $status->blindmarking) {
                        $userslist[] = $member->alias;
                    } else {
                        $fullname = fullname($member, $status->canviewfullnames);
                        $userslist[] = $this->output->action_link($url, $fullname);
                    }
                }
                if (count($userslist) > 0) {
                    $userstr = join(', ', $userslist);
                    $formatteduserstr = get_string('userswhoneedtosubmit', 'assign', $userstr);
                    $cell2content .= $this->output->container($formatteduserstr);
                }

                $cell2attributes = array('class' => 'submissionstatus' . $status->teamsubmission->status);
            } else {
                if (!$status->submissionsenabled) {
                    $cell2content = get_string('noonlinesubmissions', 'assign');
                } else {
                    $cell2content = get_string('nosubmission', 'assign');
                }
            }
        }

        $o .= '<dt>' . $cell1content . '</dt>';
        $o .= '<dd>' . $cell2content . '</dd>';
        $o .= '<hr />';

        // Is locked?
        if ($status->locked) {
            $cell1content = '';
            $cell2content = get_string('submissionslocked', 'assign');
            $cell2attributes = array('class' => 'rui-infobox rui-infobox--needgrading submissionlocked');
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        // Grading status.
        $cell1content = get_string('gradingstatus', 'assign');
        if ($status->gradingstatus == ASSIGN_GRADING_STATUS_GRADED ||
            $status->gradingstatus == ASSIGN_GRADING_STATUS_NOT_GRADED) {
            $cell2content = get_string($status->gradingstatus, 'assign');
        } else {
            $gradingstatus = 'markingworkflowstate' . $status->gradingstatus;
            $cell2content = get_string($gradingstatus, 'assign');
        }
        if ($status->gradingstatus == ASSIGN_GRADING_STATUS_GRADED ||
            $status->gradingstatus == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $cell2attributes = array('class' => 'rui-infobox submissiongraded');
        } else {
            $cell2attributes = array('class' => 'rui-infobox submissionnotgraded');
        }
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';

        $submission = $status->teamsubmission ? $status->teamsubmission : $status->submission;
        $duedate = $status->duedate;
        if ($duedate > 0) {
            // Due date.
            $cell1content = get_string('duedate', 'assign');
            $cell2content = userdate($duedate);
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';

            if ($status->view == assign_submission_status::GRADER_VIEW) {
                if ($status->cutoffdate) {
                    // Cut off date.
                    $cell1content = get_string('cutoffdate', 'assign');
                    $cell2content = userdate($status->cutoffdate);
                    $o .= '<dt>' . $cell1content . '</dt>';
                    $o .= '<dd>' . $cell2content . '</dd>';
                    $o .= '<hr />';
                }
            }

            if ($status->extensionduedate) {
                // Extension date.
                $cell1content = get_string('extensionduedate', 'assign');
                $cell2content = userdate($status->extensionduedate);
                $o .= '<dt>' . $cell1content . '</dt>';
                $o .= '<dd>' . $cell2content . '</dd>';
                $o .= '<hr />';

                $duedate = $status->extensionduedate;
            }

            // Time remaining.
            $cell1content = get_string('timeremaining', 'assign');
            $cell2attributes = [];
            if ($duedate - $time <= 0) {
                if (!$submission ||
                        $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                    if ($status->submissionsenabled) {
                        $cell2content = get_string('overdue', 'assign', format_time($time - $duedate));
                        $cell2attributes = array('class' => 'overdue');
                    } else {
                        $cell2content = get_string('duedatereached', 'assign');
                    }
                } else {
                    if ($submission->timemodified > $duedate) {
                        $cell2content = get_string('submittedlate',
                                              'assign',
                                              format_time($submission->timemodified - $duedate));
                        $cell2attributes = array('class' => 'latesubmission');
                    } else {
                        $cell2content = get_string('submittedearly',
                                               'assign',
                                               format_time($submission->timemodified - $duedate));
                        $cell2attributes = array('class' => 'earlysubmission');
                    }
                }
            } else {
                $cell2content = format_time($duedate - $time);
            }
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        // Show graders whether this submission is editable by students.
        if ($status->view == assign_submission_status::GRADER_VIEW) {
            $cell1content = get_string('editingstatus', 'assign');
            if ($status->canedit) {
                $cell2content = get_string('submissioneditable', 'assign');
                $cell2attributes = array('class' => 'submissioneditable');
            } else {
                $cell2content = get_string('submissionnoteditable', 'assign');
                $cell2attributes = array('class' => 'submissionnoteditable');
            }
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        // Grading criteria preview.
        if (!empty($status->gradingcontrollerpreview)) {
            $cell1content = get_string('gradingmethodpreview', 'assign');
            $cell2content = $status->gradingcontrollerpreview;
            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';
            $o .= '<hr />';
        }

        // Last modified.
        if ($submission) {
            $cell1content = get_string('timemodified', 'assign');

            if ($submission->status != ASSIGN_SUBMISSION_STATUS_NEW) {
                $cell2content = userdate($submission->timemodified);
            } else {
                $cell2content = "-";
            }

            $o .= '<dt>' . $cell1content . '</dt>';
            $o .= '<dd>' . $cell2content . '</dd>';

            $o .= $this->output->container_end(); // end .rui-submissionsummarytable

            if (!$status->teamsubmission || $status->submissiongroup != false || !$status->preventsubmissionnotingroup) {
                foreach ($status->submissionplugins as $plugin) {
                    $pluginshowsummary = !$plugin->is_empty($submission) || !$plugin->allow_submissions();
                    if ($plugin->is_enabled() &&
                        $plugin->is_visible() &&
                        $plugin->has_user_summary() &&
                        $pluginshowsummary
                    ) {

                        $cell1content = $plugin->get_name();
                        $displaymode = assign_submission_plugin_submission::SUMMARY;
                        $pluginsubmission = new assign_submission_plugin_submission($plugin,
                            $submission,
                            $displaymode,
                            $status->coursemoduleid,
                            $status->returnaction,
                            $status->returnparams);
                        $cell2content = $this->render($pluginsubmission);

                        $o .= '<h5>' . $cell1content . '</h5>';
                        $o .= '<div>' . $cell2content . '</div>';

                    }
                }
            }
        }

        $o .= $warningmsg;
        $o .= $this->output->container_end();

        // Links.
        if ($status->view == assign_submission_status::STUDENT_VIEW) {
            if ($status->canedit) {
                if (!$submission || $submission->status == ASSIGN_SUBMISSION_STATUS_NEW) {
                    $o .= $this->output->container_start('generalbox mt-2');
                    $urlparams = array('id' => $status->coursemoduleid, 'action' => 'editsubmission');
                    $o .= $this->output->single_button(new moodle_url('/mod/assign/view.php', $urlparams),
                                                       get_string('addsubmission', 'assign'), 'get');
                    $o .= $this->output->container_start('rui-submithelp badge-sq badge-success mt-2 d-block');
                    $o .= get_string('addsubmission_help', 'assign');
                    $o .= $this->output->container_end();
                    $o .= $this->output->container_end();
                } else if ($submission->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                    $o .= $this->output->container_start('rui-assign-statusreopened');
                    $urlparams = array('id' => $status->coursemoduleid,
                                       'action' => 'editprevioussubmission',
                                       'sesskey'=>sesskey());
                    $o .= $this->output->single_button(new moodle_url('/mod/assign/view.php', $urlparams),
                                                       get_string('addnewattemptfromprevious', 'assign'), 'get');
                    $o .= $this->output->container_start('rui-submithelp badge-sq badge-light mt-2 d-block');
                    $o .= get_string('addnewattemptfromprevious_help', 'assign');
                    $o .= $this->output->container_end();
                    $o .= $this->output->container_end();
                    $o .= $this->output->container_start('rui-container-editsubmission mt-2');
                    $urlparams = array('id' => $status->coursemoduleid, 'action' => 'editsubmission');
                    $o .= $this->output->single_button(new moodle_url('/mod/assign/view.php', $urlparams),
                                                       get_string('addnewattempt', 'assign'), 'get');
                    $o .= $this->output->container_start('rui-submithelp badge-sq badge-info mt-2 d-block');
                    $o .= get_string('addnewattempt_help', 'assign');
                    $o .= $this->output->container_end();
                    $o .= $this->output->container_end();
                } else {
                    $o .= $this->output->container_start('rui-assign-btns');
                    $urlparams = array('id' => $status->coursemoduleid, 'action' => 'editsubmission');
                    $o .= $this->output->single_button(new moodle_url('/mod/assign/view.php', $urlparams),
                                                       get_string('editsubmission', 'assign'), 'get');
                    $urlparams = array('id' => $status->coursemoduleid, 'action' => 'removesubmissionconfirm');
                    $o .= $this->output->single_button(new moodle_url('/mod/assign/view.php', $urlparams),
                                                       get_string('removesubmission', 'assign'), 'get');
                    $o .= $this->output->container_start('rui-submithelp badge-sq badge-warning mt-2 d-block');
                    $o .= get_string('editsubmission_help', 'assign');
                    $o .= $this->output->container_end();
                    $o .= $this->output->container_end();
                }
            }

            if ($status->cansubmit) {
                $urlparams = array('id' => $status->coursemoduleid, 'action'=>'submit');
                $o .= $this->output->container_start('mt-4 rui-submissionaction');
                $o .= $this->output->single_button(new moodle_url('/mod/assign/view.php', $urlparams),
                                                   get_string('submitassignment', 'assign'), 'get');
                $o .= $this->output->container_start('rui-submithelp badge-sq badge-success mt-2 d-block');
                $o .= get_string('submitassignment_help', 'assign');
                $o .= $this->output->container_end();
                $o .= $this->output->container_end();
            }
        }

        return $o;
    }

        /**
     * Render a submission plugin submission
     *
     * @param assign_submission_plugin_submission $submissionplugin
     * @return string
     */
     public function render_assign_submission_plugin_submission(assign_submission_plugin_submission $submissionplugin) {
        $o = '';

        if ($submissionplugin->view == assign_submission_plugin_submission::SUMMARY) {
            $showviewlink = false;
            $summary = $submissionplugin->plugin->view_summary($submissionplugin->submission,
                                                               $showviewlink);

            $classsuffix = $submissionplugin->plugin->get_subtype() .
                           '_' .
                           $submissionplugin->plugin->get_type() .
                           '_' .
                           $submissionplugin->submission->id;

            $o .= $this->output->container_start('rui-plugincontentsummary plugincontentsummary summary_' . $classsuffix);

            $link = '';
            if ($showviewlink) {
                $previewstr = get_string('viewsubmission', 'assign');
                $icon = $this->output->pix_icon('t/preview', $previewstr);

                $expandstr = get_string('viewfull', 'assign');
                $expandicon = $this->output->pix_icon('t/switch_plus', $expandstr);
                $options = array(
                    'class' => 'btn btn-sm btn-icon icon-no-margin btn-secondary expandsummaryicon expand_' . $classsuffix,
                    'aria-label' => $expandstr,
                    'role' => 'button',
                    'aria-expanded' => 'false'
                );
                $o .= html_writer::link('', $expandicon, $options);

                $jsparams = array($submissionplugin->plugin->get_subtype(),
                                  $submissionplugin->plugin->get_type(),
                                  $submissionplugin->submission->id);

                $this->page->requires->js_init_call('M.mod_assign.init_plugin_summary', $jsparams);

                $action = 'viewplugin' . $submissionplugin->plugin->get_subtype();
                $returnparams = http_build_query($submissionplugin->returnparams);
                $link .= '<noscript>';
                $urlparams = array('id' => $submissionplugin->coursemoduleid,
                                   'sid'=>$submissionplugin->submission->id,
                                   'plugin'=>$submissionplugin->plugin->get_type(),
                                   'action'=>$action,
                                   'returnaction'=>$submissionplugin->returnaction,
                                   'returnparams'=>$returnparams);
                $url = new moodle_url('/mod/assign/view.php', $urlparams);
                $link .= $this->output->action_link($url, $icon);
                $link .= '</noscript>';

                $link .= $this->output->spacer(array('width'=>15));
            }

            $o .= $link . $summary;
            $o .= $this->output->container_end();
            if ($showviewlink) {
                $o .= $this->output->container_start('rui-hidefull hidefull full_' . $classsuffix);
                $collapsestr = get_string('viewsummary', 'assign');
                $options = array(
                    'class' => 'btn btn-sm btn-icon icon-no-margin btn-secondary expandsummaryicon contract_' . $classsuffix,
                    'aria-label' => $collapsestr,
                    'role' => 'button',
                    'aria-expanded' => 'true'
                );
                $collapseicon = $this->output->pix_icon('t/switch_minus', $collapsestr);
                $o .= html_writer::link('', $collapseicon, $options);

                $o .= $submissionplugin->plugin->view($submissionplugin->submission);
                $o .= $this->output->container_end();
            }
        } else if ($submissionplugin->view == assign_submission_plugin_submission::FULL) {
            $o .= $this->output->container_start('rui-submissionfull submissionfull');
            $o .= $submissionplugin->plugin->view($submissionplugin->submission);
            $o .= $this->output->container_end();
        }

        return $o;
    }

    /**
     * Render a feedback plugin feedback
     *
     * @param assign_feedback_plugin_feedback $feedbackplugin
     * @return string
     */
     public function render_assign_feedback_plugin_feedback(assign_feedback_plugin_feedback $feedbackplugin) {
        $o = '';

        if ($feedbackplugin->view == assign_feedback_plugin_feedback::SUMMARY) {
            $showviewlink = false;
            $summary = $feedbackplugin->plugin->view_summary($feedbackplugin->grade, $showviewlink);

            $classsuffix = $feedbackplugin->plugin->get_subtype() .
                           '_' .
                           $feedbackplugin->plugin->get_type() .
                           '_' .
                           $feedbackplugin->grade->id;
            $o .= $this->output->container_start('rui-plugincontentsummary plugincontentsummary summary_' . $classsuffix);

            $link = '';
            if ($showviewlink) {
                $previewstr = get_string('viewfeedback', 'assign');
                $icon = $this->output->pix_icon('t/preview', $previewstr);

                $expandstr = get_string('viewfull', 'assign');
                $expandicon = $this->output->pix_icon('t/switch_plus', $expandstr);
                $options = array(
                    'class' => 'btn btn-sm btn-icon icon-no-margin btn-secondary expandsummaryicon expand_' . $classsuffix,
                    'aria-label' => $expandstr,
                    'role' => 'button',
                    'aria-expanded' => 'false'
                );
                $o .= html_writer::link('', $expandicon, $options);

                $jsparams = array($feedbackplugin->plugin->get_subtype(),
                                  $feedbackplugin->plugin->get_type(),
                                  $feedbackplugin->grade->id);
                $this->page->requires->js_init_call('M.mod_assign.init_plugin_summary', $jsparams);

                $urlparams = array('id' => $feedbackplugin->coursemoduleid,
                                   'gid'=>$feedbackplugin->grade->id,
                                   'plugin'=>$feedbackplugin->plugin->get_type(),
                                   'action'=>'viewplugin' . $feedbackplugin->plugin->get_subtype(),
                                   'returnaction'=>$feedbackplugin->returnaction,
                                   'returnparams'=>http_build_query($feedbackplugin->returnparams));
                $url = new moodle_url('/mod/assign/view.php', $urlparams);
                $link .= '<noscript>';
                $link .= $this->output->action_link($url, $icon);
                $link .= '</noscript>';

                $link .= $this->output->spacer(array('width'=>15));
            }

            $o .= $link . $summary;
            $o .= $this->output->container_end();
            if ($showviewlink) {
                $o .= $this->output->container_start('rui-hidefull hidefull full_' . $classsuffix);
                $collapsestr = get_string('viewsummary', 'assign');
                $options = array(
                    'class' => 'btn btn-sm btn-icon icon-no-margin btn-secondary expandsummaryicon contract_' . $classsuffix,
                    'aria-label' => $collapsestr,
                    'role' => 'button',
                    'aria-expanded' => 'true'
                );
                $collapseicon = $this->output->pix_icon('t/switch_minus', $collapsestr);
                $o .= html_writer::link('', $collapseicon, $options);

                $o .= $feedbackplugin->plugin->view($feedbackplugin->grade);
                $o .= $this->output->container_end();
            }
        } else if ($feedbackplugin->view == assign_feedback_plugin_feedback::FULL) {
            $o .= $this->output->container_start('rui-feedbackfull feedbackfull');
            $o .= $feedbackplugin->plugin->view($feedbackplugin->grade);
            $o .= $this->output->container_end();
        }

        return $o;
    }


}
