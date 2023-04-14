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


// QUIZ
include_once($CFG->dirroot . "/mod/quiz/renderer.php");
class theme_alpha_mod_quiz_renderer extends mod_quiz_renderer
{
    /**
     * Outputs a box.
     *
     * @param string $contents The contents of the box
     * @param string $classes A alpha-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function alpha_box($contents, $classes = 'generalbox', $id = null, $attributes = array())
    {
        return $this->alpha_box_start($classes, $id, $attributes) . $contents . $this->alpha_box_end();
    }

    /**
     * Outputs the opening section of a box.
     *
     * @param string $classes A alpha-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function alpha_box_start($classes = 'generalbox', $id = null, $attributes = array())
    {
        $this->opencontainers->push('box', html_writer::end_tag('div'));
        $attributes['id'] = $id;
        $attributes['class'] = 'box ' . renderer_base::prepare_classes($classes);
        return html_writer::start_tag('div', $attributes);
    }

    /**
     * Outputs the closing section of a box.
     *
     * @return string the HTML to output.
     */
    public function alpha_box_end()
    {
        return $this->opencontainers->pop('box');
    }


    /**
     * Render the tertiary navigation for pages during the attempt.
     *
     * @param string|moodle_url $quizviewurl url of the view.php page for this quiz.
     * @return string HTML to output.
     */
    public function during_attempt_tertiary_nav($quizviewurl): string
    {
        $output = '';
        $output .= html_writer::start_div('tertiary-navigation mb-4');
        $output .= html_writer::start_div('row no-gutters');
        $output .= html_writer::start_div('navitem');
        $output .= html_writer::link(
            $quizviewurl,
            get_string('back'),
            ['class' => 'btn btn-secondary']
        );
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;
    }


    /**
     * Render the tertiary navigation for the view page.
     *
     * @param mod_quiz_view_object $viewobj the information required to display the view page.
     * @return string HTML to output.
     */
    public function view_page_tertiary_nav(mod_quiz_view_object $viewobj): string
    {
        $content = '';

        if ($viewobj->buttontext) {
            $attemptbtn = $this->start_attempt_button(
                $viewobj->buttontext,
                $viewobj->startattempturl,
                $viewobj->preflightcheckform,
                $viewobj->popuprequired,
                $viewobj->popupoptions
            );
            $content .= $attemptbtn;
        }

        if ($viewobj->canedit && !$viewobj->quizhasquestions) {
            $content .= html_writer::link(
                $viewobj->editurl,
                get_string('addquestion', 'quiz'),
                ['class' => 'btn btn-primary']
            );
        }


        if ($content) {
            return html_writer::div(html_writer::div($content, 'row no-gutters d-flex justify-content-center'), 'tertiary-navigation');
        } else {
            return '';
        }
    }

    /**
     * Output the page information
     *
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @param context $context the quiz context.
     * @param array $messages any access messages that should be described.
     * @param bool $quizhasquestions does quiz has questions added.
     * @return string HTML to output.
     */
    public function view_information($quiz, $cm, $context, $messages, bool $quizhasquestions = false)
    {
        $output = '';
        $svg = '<svg width="164" height="164" viewBox="0 0 164 164" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M117.32 164C114.758 163.487 112.153 163.188 109.675 162.462C95.7525 158.491 86.1005 149.693 80.6338 136.325C80.164 135.172 79.6515 134.83 78.4557 134.83C67.0098 134.873 55.564 134.873 44.0755 134.873C40.1463 134.873 38.3525 133.036 38.3525 129.107C38.3525 88.0645 38.3525 47.0217 38.3525 6.02175C38.3525 2.09258 40.1463 0.298828 44.1182 0.298828C76.9182 0.298828 109.761 0.298828 142.561 0.298828C146.533 0.298828 148.326 2.04987 148.326 6.06445C148.326 32.7145 148.326 59.3645 148.326 86.0145C148.326 86.9968 148.497 87.7655 149.309 88.5343C157.68 95.8801 162.591 105.062 163.701 116.167C163.744 116.466 163.872 116.764 163.958 117.063C163.958 119.199 163.958 121.334 163.958 123.47C163.744 124.836 163.573 126.203 163.317 127.527C159.815 145.166 149.864 157.209 132.396 162.505C129.876 163.274 127.229 163.573 124.623 164.085C122.232 164 119.755 164 117.32 164ZM48.09 9.95091C48.09 48.4738 48.09 86.8259 48.09 125.221C58.0838 125.221 67.9921 125.221 77.815 125.221C76.6619 108.778 82.5557 95.4957 95.8379 85.9718C109.078 76.4478 123.556 75.0811 138.674 81.402C138.674 57.528 138.674 33.7822 138.674 9.95091C108.48 9.95091 78.3275 9.95091 48.09 9.95091ZM120.908 87.4238C103.782 87.4238 87.8088 99.254 87.339 121.078C86.9546 139.229 102.415 154.348 120.737 154.391C139.187 154.433 154.605 139.4 154.348 121.121C154.049 99.8947 138.546 87.5093 120.908 87.4238Z" fill="#555555"/>
        <path d="M29.0848 68.5896C29.0848 81.3167 29.0848 94.0437 29.0421 106.771C29.0421 107.839 28.8712 109.034 28.4441 110.017C25.4546 117.405 22.3796 124.751 19.2618 132.14C18.066 135.001 15.5035 136.282 12.9837 135.129C11.8733 134.617 10.7202 133.549 10.2504 132.439C6.96185 124.879 3.84414 117.234 0.683724 109.632C0.384766 108.864 0.256641 107.967 0.256641 107.155C0.213932 81.6156 0.213932 56.1187 0.256641 30.5792C0.256641 22.7635 5.97956 16.5281 13.4962 15.9729C21.0983 15.4177 27.6327 20.5854 28.9139 28.1875C29.0848 29.2125 29.0848 30.3229 29.0848 31.3906C29.0848 43.776 29.0848 56.2042 29.0848 68.5896ZM19.39 102.842C19.39 83.1958 19.39 63.6781 19.39 44.1604C16.1868 44.1604 13.1546 44.1604 9.99414 44.1604C9.99414 63.7635 9.99414 83.2812 9.99414 102.842C13.1546 102.842 16.2296 102.842 19.39 102.842ZM19.4754 34.4229C19.4754 32.8854 19.5181 31.5187 19.4754 30.1521C19.39 27.675 17.4681 25.7104 15.0337 25.5396C12.5993 25.3687 10.2931 27.1625 9.99414 29.6396C9.82331 31.1771 9.95143 32.8 9.95143 34.4229C13.1546 34.4229 16.2296 34.4229 19.4754 34.4229ZM14.7348 118.259C15.6316 116.124 16.315 114.416 17.041 112.622C15.4181 112.622 13.966 112.622 12.3858 112.622C13.1546 114.458 13.8379 116.167 14.7348 118.259Z" fill="#555555"/>
        <path d="M93.3175 19.475C103.525 19.475 113.775 19.475 123.982 19.475C127.74 19.475 130.004 22.55 128.851 25.8813C128.168 27.8032 126.758 28.7427 124.794 28.999C124.366 29.0417 123.939 29.0417 123.512 29.0417C103.397 29.0417 83.281 29.0844 63.1227 28.999C61.756 28.999 60.1331 28.4438 59.0654 27.6323C57.4425 26.3938 57.229 24.4719 57.8696 22.55C58.5102 20.7136 60.1758 19.5177 62.3112 19.475C65.1727 19.4323 68.0769 19.475 70.9383 19.475C78.4123 19.475 85.8863 19.475 93.3175 19.475Z" fill="#555555"/>
        <path d="M93.446 38.6509C103.611 38.6509 113.775 38.6509 123.94 38.6509C127.698 38.6509 130.004 41.7259 128.851 45.0571C128.211 46.8936 126.844 47.8759 124.965 48.1748C124.709 48.2175 124.452 48.2175 124.153 48.2175C103.611 48.2175 83.0679 48.2175 62.4825 48.2175C59.5783 48.2175 57.571 46.1675 57.5283 43.4769C57.5283 40.7009 59.5783 38.6936 62.5679 38.6509C72.9033 38.6509 83.196 38.6509 93.446 38.6509Z" fill="#555555"/>
        <path d="M93.3175 67.4365C83.1529 67.4365 72.9883 67.4365 62.8238 67.4365C59.0654 67.4365 56.7592 64.4469 57.8696 61.073C58.5102 59.1511 59.9196 58.1688 61.8842 57.8699C62.3113 57.8272 62.7383 57.8271 63.1654 57.8271C83.3238 57.8271 103.525 57.8271 123.683 57.8271C125.819 57.8271 127.57 58.3824 128.595 60.3896C130.303 63.7209 127.997 67.3511 124.153 67.3938C118.686 67.4365 113.177 67.3938 107.71 67.3938C102.927 67.4365 98.1008 67.4365 93.3175 67.4365Z" fill="#555555"/>
        <path d="M69.9564 86.6124C67.522 86.6124 65.0449 86.6551 62.6105 86.6124C59.621 86.5697 57.5282 84.5624 57.571 81.829C57.571 79.053 59.6637 77.0884 62.6532 77.0457C67.5647 77.003 72.4335 77.003 77.3449 77.0457C80.2491 77.0457 82.4272 79.1384 82.4272 81.829C82.4272 84.5197 80.2491 86.5697 77.3022 86.6124C74.8678 86.6551 72.4335 86.6124 69.9564 86.6124Z" fill="#555555"/>
        <path d="M117.533 125.05C121.334 120.181 125.05 115.441 128.766 110.7C129.876 109.291 130.986 107.839 132.139 106.429C133.976 104.251 136.837 103.909 138.973 105.575C140.98 107.198 141.364 110.102 139.656 112.323C133.634 120.053 127.612 127.783 121.548 135.471C119.541 138.033 116.252 138.076 114.074 135.642C110.828 131.969 107.625 128.253 104.464 124.538C102.628 122.359 102.799 119.413 104.806 117.661C106.899 115.868 109.803 116.167 111.768 118.345C112.707 119.413 113.647 120.523 114.586 121.591C115.483 122.701 116.423 123.769 117.533 125.05Z" fill="#555555"/>
        </svg>
        ';


        $output .= '';

        $divsvg = html_writer::div($svg, 'd-flex justify-content-center');
        $output .= html_writer::div(html_writer::tag('h2', $quiz->name), 'd-flex justify-content-center');
        $output .= html_writer::empty_tag('hr');
        // Output any access messages.
        if ($messages) {
            $output .= html_writer::div($this->box($messages[0], 'rui-quizinfo mt-2'), 'd-flex justify-content-center');
        }
        // var_dump($messages[0]);

        // Show number of attempts summary to those who can view reports.
        if (has_capability('mod/quiz:viewreports', $context)) {
            if ($strattemptnum = $this->quiz_attempt_summary_link_to_reports(
                $quiz,
                $cm,
                $context
            )) {
                $output .= html_writer::tag(
                    'div',
                    $strattemptnum,
                    array('class' => 'rui-quizattemptcounts my-4 d-none')
                );
            }
        }

        if (has_any_capability(['mod/quiz:manageoverrides', 'mod/quiz:viewoverrides'], $context)) {
            if ($overrideinfo = $this->quiz_override_summary_links($quiz, $cm)) {
                $output .= html_writer::tag('div', $overrideinfo, ['class' => 'rui-quizattemptcounts my-4']);
            }
        }

        return $divsvg . $output;
    }

    /**
     * Output the quiz intro.
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @return string HTML to output.
     */
    public function quiz_intro($quiz, $cm)
    {
        if (html_is_blank($quiz->intro)) {
            return '';
        }

        return html_writer::tag(
            'div',
            format_module_intro('quiz', $quiz, $cm->id),
            array('id' => 'intro')
        );
    }

    /**
     * Generates data pertaining to quiz results
     *
     * @param array $quiz Array containing quiz data
     * @param int $context The page context ID
     * @param int $cm The Course Module Id
     * @param mod_quiz_view_object $viewobj
     */
    public function view_result_info($quiz, $context, $cm, $viewobj)
    {
        $output = '';
        if (!$viewobj->numattempts && !$viewobj->gradecolumn && is_null($viewobj->mygrade)) {
            return $output;
        }
        $resultinfo = '';

        if ($viewobj->overallstats) {
            if ($viewobj->moreattempts) {
                $a = new stdClass();
                $a->method = quiz_get_grading_option_name($quiz->grademethod);
                $a->mygrade = quiz_format_grade($quiz, $viewobj->mygrade);
                $a->quizgrade = quiz_format_grade($quiz, $quiz->grade);
                $resultinfo .= '<span class="d-block lead mb-4">' . get_string('gradesofar', 'quiz', $a) . '</span>';
            } else {
                $a = new stdClass();
                $a->grade = quiz_format_grade($quiz, $viewobj->mygrade);
                $a->maxgrade = quiz_format_grade($quiz, $quiz->grade);
                $a = get_string('outofshort', 'quiz', $a);
                $resultinfo .= '<span class="d-block lead mb-4">' . get_string('yourfinalgradeis', 'quiz', $a) . '</span>';
            }
        }

        if ($viewobj->mygradeoverridden) {

            $resultinfo .= html_writer::tag(
                'p',
                get_string('overriddennotice', 'grades'),
                array('class' => 'overriddennotice')
            ) . "\n";
        }
        if ($viewobj->gradebookfeedback) {
            $resultinfo .= $this->heading(get_string('comment', 'quiz'), 5);
            $resultinfo .= html_writer::div($viewobj->gradebookfeedback, 'rui-quizteacherfeedback') . "\n";
        }
        if ($viewobj->feedbackcolumn) {
            $resultinfo .= $this->heading(get_string('overallfeedback', 'quiz'), 5);
            $resultinfo .= html_writer::div(
                quiz_feedback_for_grade($viewobj->mygrade, $quiz, $context),
                'rui-quizgradefeedback mb-3 border-bottom'
            ) . "\n";
        }

        if ($resultinfo) {
            $output .= $this->alpha_box($resultinfo, 'generalbox', 'rui-feedback');
        }
        return $output;
    }


    /**
     * Generates the table of data
     *
     * @param array $quiz Array contining quiz data
     * @param int $context The page context ID
     * @param mod_quiz_view_object $viewobj
     */
    public function view_table($quiz, $context, $viewobj)
    {
        if (!$viewobj->attempts) {
            return '';
        }

        // Prepare table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable rui-quizattemptsummary my-4';
        $table->head = array();
        $table->align = array();
        $table->size = array();
        if ($viewobj->attemptcolumn) {
            $table->head[] = get_string('attemptnumber', 'quiz');
            $table->size[] = '';
        }
        $table->head[] = get_string('attemptstate', 'quiz');
        $table->align[] = 'left';
        $table->size[] = '';
        if ($viewobj->markcolumn) {
            $table->head[] = get_string('marks', 'quiz') . ' / ' .
                quiz_format_grade($quiz, $quiz->sumgrades);
            $table->size[] = '';
        }
        if ($viewobj->gradecolumn) {
            $table->head[] = get_string('grade', 'quiz') . ' / ' .
                quiz_format_grade($quiz, $quiz->grade);
            $table->size[] = '';
        }
        if ($viewobj->canreviewmine) {
            $table->head[] = get_string('review', 'quiz');
            $table->size[] = '';
        }
        if ($viewobj->feedbackcolumn) {
            $table->head[] = get_string('feedback', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }

        // One row for each attempt.
        foreach ($viewobj->attemptobjs as $attemptobj) {
            $attemptoptions = $attemptobj->get_display_options(true);
            $row = array();

            // Add the attempt number.
            if ($viewobj->attemptcolumn) {
                if ($attemptobj->is_preview()) {
                    $row[] = get_string('preview', 'quiz');
                } else {
                    $row[] = $attemptobj->get_attempt_number();
                }
            }

            $row[] = $this->attempt_state($attemptobj);

            if ($viewobj->markcolumn) {
                if (
                    $attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                    $attemptobj->is_finished()
                ) {
                    $row[] = quiz_format_grade($quiz, $attemptobj->get_sum_marks());
                } else {
                    $row[] = '';
                }
            }

            // Ouside the if because we may be showing feedback but not grades.
            $attemptgrade = quiz_rescale_grade($attemptobj->get_sum_marks(), $quiz, false);

            if ($viewobj->gradecolumn) {
                if (
                    $attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                    $attemptobj->is_finished()
                ) {

                    // Highlight the highest grade if appropriate.
                    if (
                        $viewobj->overallstats && !$attemptobj->is_preview()
                        && $viewobj->numattempts > 1 && !is_null($viewobj->mygrade)
                        && $attemptobj->get_state() == quiz_attempt::FINISHED
                        && $attemptgrade == $viewobj->mygrade
                        && $quiz->grademethod == QUIZ_GRADEHIGHEST
                    ) {
                        $table->rowclasses[$attemptobj->get_attempt_number()] = 'bestrow';
                    }

                    $row[] = quiz_format_grade($quiz, $attemptgrade);
                } else {
                    $row[] = '';
                }
            }

            if ($viewobj->canreviewmine) {
                $row[] = $viewobj->accessmanager->make_review_link(
                    $attemptobj->get_attempt(),
                    $attemptoptions,
                    $this
                );
            }

            if ($viewobj->feedbackcolumn && $attemptobj->is_finished()) {
                if ($attemptoptions->overallfeedback) {
                    $row[] = quiz_feedback_for_grade($attemptgrade, $quiz, $context);
                } else {
                    $row[] = '';
                }
            }

            if ($attemptobj->is_preview()) {
                $table->data['preview'] = $row;
            } else {
                $table->data[$attemptobj->get_attempt_number()] = $row;
            }
        } // End of loop over attempts.
        $output = '';
        $output .= $this->view_table_heading();
        $output .= html_writer::start_tag('div', array('class' => 'table-overflow'));
        $output .= html_writer::table($table);
        $output .= html_writer::end_tag('div');


        return $output;
    }

    /*
     * View Page
     */
    /**
     * Generates the view page
     *
     * @param stdClass $course the course settings row from the database.
     * @param stdClass $quiz the quiz settings row from the database.
     * @param stdClass $cm the course_module settings row from the database.
     * @param context_module $context the quiz context.
     * @param mod_quiz_view_object $viewobj
     * @return string HTML to display
     */
    public function view_page($course, $quiz, $cm, $context, $viewobj)
    {
        $output = '';
        $output .= $this->view_information($quiz, $cm, $context, $viewobj->infomessages);
        // $output .= $this->view_table($quiz, $context, $viewobj);
        $output .= $this->view_result_info($quiz, $context, $cm, $viewobj);
        $output .= $this->box($this->view_page_buttons($viewobj), 'rui-quizattempt');
        $output .= $this->view_page_tertiary_nav($viewobj);

        return $output;
    }

    /**
     * Work out, and render, whatever buttons, and surrounding info, should appear
     * at the end of the review page.
     *
     * @param mod_quiz_view_object $viewobj the information required to display the view page.
     * @return string HTML to output.
     */
    public function view_page_buttons(mod_quiz_view_object $viewobj)
    {
        $output = '';

        if (!$viewobj->quizhasquestions) {
            $output .= html_writer::div(
                $this->notification(get_string('noquestions', 'quiz'), 'warning', false),
                'text-left mb-3'
            );
        }
        $output .= $this->access_messages($viewobj->preventmessages);

        if ($viewobj->showbacktocourse) {
            $output .= $this->single_button(
                $viewobj->backtocourseurl,
                get_string('backtocourse', 'quiz'),
                'get',
                array('class' => 'rui-quiz-continuebutton')
            );
        }

        return $output;
    }


    /**
     * Generates the view attempt button
     *
     * @param string $buttontext the label to display on the button.
     * @param moodle_url $url The URL to POST to in order to start the attempt.
     * @param mod_quiz_preflight_check_form $preflightcheckform deprecated.
     * @param bool $popuprequired whether the attempt needs to be opened in a pop-up.
     * @param array $popupoptions the options to use if we are opening a popup.
     * @return string HTML fragment.
     */
    public function start_attempt_button(
        $buttontext,
        moodle_url $url,
        mod_quiz_preflight_check_form $preflightcheckform = null,
        $popuprequired = false,
        $popupoptions = null
    ) {

        if (is_string($preflightcheckform)) {
            // Calling code was not updated since the API change.
            debugging('The third argument to start_attempt_button should now be the ' .
                'mod_quiz_preflight_check_form from ' .
                'quiz_access_manager::get_preflight_check_form, not a warning message string.');
        }

        $button = new single_button($url, $buttontext, '', true);
        $button->class .= 'rui-quizstartbuttondiv quizstartbuttondiv mb-3';
        if ($popuprequired) {
            $button->class .= ' quizsecuremoderequired';
        }

        $popupjsoptions = null;
        if ($popuprequired && $popupoptions) {
            $action = new popup_action('click', $url, 'popup', $popupoptions);
            $popupjsoptions = $action->get_js_options();
        }

        if ($preflightcheckform) {
            $checkform = $preflightcheckform->render();
        } else {
            $checkform = null;
        }

        $this->page->requires->js_call_amd(
            'mod_quiz/preflightcheck',
            'init',
            array(
                '.quizstartbuttondiv [type=submit]', get_string('startattempt', 'quiz'),
                '#mod_quiz_preflight_form', $popupjsoptions
            )
        );

        return $this->render($button) . $checkform;
    }

    /**
     * Outputs the table containing data from summary data array
     *
     * @param array $summarydata contains row data for table
     * @param int $page contains the current page number
     */
    public function review_summary_table($summarydata, $page)
    {
        $summarydata = $this->filter_review_summary_table($summarydata, $page);
        if (empty($summarydata)) {
            return '';
        }

        $output = '';

        $output .= html_writer::start_tag('div', array('class' => 'rui-summary-table'));

        $output .= html_writer::start_tag('div', array('class' => 'rui-info-container rui-quizreviewsummary'));


        foreach ($summarydata as $rowdata => $val) {

            $csstitle = $rowdata;

            if ($val['title'] instanceof renderable) {
                $title = $this->render($val['title']);
            } else {
                $title = $val['title'];
            }

            if ($val['content'] instanceof renderable) {
                $content = $this->render($val['content']);
            } else {
                $content = $val['content'];
            }

            if ($val['title'] instanceof renderable) {
                $output .= html_writer::tag(
                    'div',
                    html_writer::tag('h5', $title, array('class' => 'rui-infobox-title')) .
                        html_writer::tag('div', $content, array('class' => 'rui-infobox-content--small')),
                    array('class' => 'rui-infobox rui-infobox--avatar')
                );
            } else {
                $output .= html_writer::tag(
                    'div',
                    html_writer::tag('h5', $title, array('class' => 'rui-infobox-title')) .
                        html_writer::tag('div', $content, array('class' => 'rui-infobox-content--small')),
                    array('class' => 'rui-infobox rui-infobox--' . strtolower(str_replace(' ', '', $csstitle)))
                );
            }
        }

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }


    /**
     * Creates any controls a the page should have.
     *
     * @param quiz_attempt $attemptobj
     */
    public function summary_page_controls($attemptobj)
    {
        $output = '';

        // Return to place button.
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button = new single_button(
                new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                get_string('returnattempt', 'quiz')
            );
            $output .= $this->container($this->container(
                $this->render($button),
                'rui-controls'
            ), 'rui-submitbtns rui-submitbtns--back');
        }
        // Finish attempt button.
        $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'cmid' => $attemptobj->get_cmid(),
            'sesskey' => sesskey(),
        );

        $button = new single_button(
            new moodle_url($attemptobj->processattempt_url(), $options),
            get_string('submitallandfinish', 'quiz'),
            null,
            true
        );
        $button->id = 'responseform';
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button->add_action(new confirm_action(
                get_string('confirmclose', 'quiz'),
                null,
                get_string('submitallandfinish', 'quiz')
            ));
        }
        $button->primary = true;

        $duedate = $attemptobj->get_due_date();

        $output .= $this->countdown_timer($attemptobj, time());

        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));
            $output .= '<div class="alert alert-warning d-flex align-items-center"><svg class="mr-2" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . $message . '</div>';
        } else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
            $output .= '<div class="alert alert-info d-flex align-items-center"><svg class="mr-2" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . $message . '</div>';
        }

        $output .= $this->container($this->container($this->render($button), 'rui-controls'), 'rui-submitbtns');

        return $output;
    }



    /*
    * Summary Page
    */
    /**
     * Create the summary page
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_page($attemptobj, $displayoptions)
    {
        $output = '';
        $output .= $this->header();
        $output .= $this->during_attempt_tertiary_nav($attemptobj->view_url());
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= $this->heading(get_string('summaryofattempt', 'quiz'), 4, array('class' => 'mt-3 mb-2'));
        $output .= $this->summary_table($attemptobj, $displayoptions);
        $output .= $this->summary_page_controls($attemptobj);
        $output .= $this->footer();
        return $output;
    }

    /**
     * Generates the table of summarydata
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_table($attemptobj, $displayoptions)
    {
        // Prepare the summary table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable quizsummaryofattempt';
        $table->head = array(get_string('question', 'quiz'), get_string('status', 'quiz'));
        $table->align = array('left', 'left');
        $table->size = array('', '');
        $markscolumn = $displayoptions->marks >= question_display_options::MARK_AND_MAX;
        if ($markscolumn) {
            $table->head[] = get_string('marks', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }
        $tablewidth = count($table->align);
        $table->data = array();

        // Get the summary info for each question.
        $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
            // Add a section headings if we need one here.
            $heading = $attemptobj->get_heading_before_slot($slot);

            if ($heading !== null) {
                // There is a heading here.
                $rowclasses = 'quizsummaryheading';
                if ($heading) {
                    $heading = format_string($heading);
                } else if (count($attemptobj->get_quizobj()->get_sections()) > 1) {
                    // If this is the start of an unnamed section, and the quiz has more
                    // than one section, then add a default heading.
                    $heading = get_string('sectionnoname', 'quiz');
                    $rowclasses .= ' dimmed_text';
                }
                $cell = new html_table_cell(format_string($heading));
                $cell->header = true;
                $cell->colspan = $tablewidth;
                $table->data[] = array($cell);
                $table->rowclasses[] = $rowclasses;
            }


            // Don't display information items.
            if (!$attemptobj->is_real_question($slot)) {
                continue;
            }

            $flag = '';

            // Real question, show it.
            if ($attemptobj->is_question_flagged($slot)) {
                // Quiz has custom JS manipulating these image tags - so we can't use the pix_icon method here.
                $flag = '<svg class="ml-2" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.75 5.75V19.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M4.75 15.25V5.75C4.75 5.75 6 4.75 9 4.75C12 4.75 13.5 6.25 16 6.25C18.5 6.25 19.25 5.75 19.25 5.75L15.75 10.5L19.25 15.25C19.25 15.25 18.5 16.25 16 16.25C13.5 16.25 11.5 14.25 9 14.25C6.5 14.25 4.75 15.25 4.75 15.25Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>';
            }
            if ($attemptobj->can_navigate_to($slot)) {
                $row = array(
                    html_writer::link(
                        $attemptobj->attempt_url($slot),
                        $attemptobj->get_question_number($slot) . $flag
                    ),
                    $attemptobj->get_question_status($slot, $displayoptions->correctness)
                );
            } else {
                $row = array(
                    $attemptobj->get_question_number($slot) . $flag,
                    $attemptobj->get_question_status($slot, $displayoptions->correctness)
                );
            }
            if ($markscolumn) {
                $row[] = $attemptobj->get_question_mark($slot);
            }
            $table->data[] = $row;
            $table->rowclasses[] = 'quizsummary' . $slot . ' ' . $attemptobj->get_question_state_class(
                $slot,
                $displayoptions->correctness
            );
        }

        // Print the summary table.
        $output = html_writer::table($table);

        return $output;
    }
}

include_once($CFG->dirroot . "/question/engine/renderer.php");
class theme_alpha_core_question_renderer extends core_question_renderer
{
    /**
     * Generate the information bit of the question display that contains the
     * metadata like the question number, current state, and mark.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function info(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        qtype_renderer $qtoutput,
        question_display_options $options,
        $number
    ) {
        $output = '';
        $output .= '<div class="d-inline-flex align-items-center">' . $this->number($number) . '<div class="d-inline-flex align-items-center">' . $this->status($qa, $behaviouroutput, $options) . $this->mark_summary($qa, $behaviouroutput, $options) . '</div></div>';
        $output .= '<div>' . $this->question_flag($qa, $options->flags) . $this->edit_question_link($qa, $options) . '</div>';
        return $output;
    }

    /**
     * Generate the display of the question number.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function number($number)
    {
        if (trim($number) === '') {
            return '';
        }
        $numbertext = '';
        if (trim($number) === 'i') {
            $numbertext = get_string('information', 'question');
        } else {
            $numbertext = get_string(
                'questionx',
                'question',
                html_writer::tag('span', $number, array('class' => 'rui-qno'))
            );
        }
        return html_writer::tag('h4', $numbertext, array('class' => 'rui-question-no mb-0'));
    }


    /**
     * Generate the display of the status line that gives the current state of
     * the question.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return HTML fragment.
     */
    protected function status(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        question_display_options $options
    ) {
        return html_writer::tag(
            'div',
            $qa->get_state_string($options->correctness),
            array('class' => 'state mx-2 d-none')
        );
    }

    /**
     * Render the question flag, assuming $flagsoption allows it.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param int $flagsoption the option that says whether flags should be displayed.
     */
    protected function question_flag(question_attempt $qa, $flagsoption)
    {
        global $CFG;

        $divattributes = array('class' => 'questionflag mx-1 d-none');

        switch ($flagsoption) {
            case question_display_options::VISIBLE:
                $flagcontent = $this->get_flag_html($qa->is_flagged());
                break;

            case question_display_options::EDITABLE:
                $id = $qa->get_flag_field_name();
                // The checkbox id must be different from any element name, because
                // of a stupid IE bug:
                // http://www.456bereastreet.com/archive/200802/beware_of_id_and_name_attribute_mixups_when_using_getelementbyid_in_internet_explorer/
                $checkboxattributes = array(
                    'type' => 'checkbox',
                    'id' => $id . 'checkbox',
                    'name' => $id,
                    'value' => 1,
                );
                if ($qa->is_flagged()) {
                    $checkboxattributes['checked'] = 'checked';
                }
                $postdata = question_flags::get_postdata($qa);

                $flagcontent = html_writer::empty_tag(
                    'input',
                    array('type' => 'hidden', 'name' => $id, 'value' => 0)
                ) .
                    html_writer::empty_tag('input', $checkboxattributes) .
                    html_writer::empty_tag(
                        'input',
                        array('type' => 'hidden', 'value' => $postdata, 'class' => 'questionflagpostdata')
                    ) .
                    html_writer::tag(
                        'label',
                        $this->get_flag_html($qa->is_flagged(), $id . 'img'),
                        array('id' => $id . 'label', 'for' => $id . 'checkbox')
                    ) . "\n";

                $divattributes = array(
                    'class' => 'questionflag editable',
                    'aria-atomic' => 'true',
                    'aria-relevant' => 'text',
                    'aria-live' => 'assertive',
                );

                break;

            default:
                $flagcontent = '';
        }

        return html_writer::nonempty_tag('div', $flagcontent, $divattributes);
    }


    protected function edit_question_link(
        question_attempt $qa,
        question_display_options $options
    ) {
        global $CFG;

        if (empty($options->editquestionparams)) {
            return '';
        }

        $params = $options->editquestionparams;
        if ($params['returnurl'] instanceof moodle_url) {
            $params['returnurl'] = $params['returnurl']->out_as_local_url(false);
        }
        $params['id'] = $qa->get_question_id();
        $editurl = new moodle_url('/question/question.php', $params);

        $icon = '<svg width="19" height="19" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.75 19.25L9 18.25L18.2929 8.95711C18.6834 8.56658 18.6834 7.93342 18.2929 7.54289L16.4571 5.70711C16.0666 5.31658 15.4334 5.31658 15.0429 5.70711L5.75 15L4.75 19.25Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.25 19.25H13.75"></path></svg>';

        return html_writer::link($editurl, $icon, array('class' => 'btn btn-sm btn-success editquestion line-height-1 d-none'));
    }
}

include_once($CFG->dirroot . "/question/type/multichoice/renderer.php");
class theme_alpha_qtype_multichoice_single_renderer extends qtype_multichoice_single_renderer
{
    public function after_choices(question_attempt $qa, question_display_options $options)
    {
        // Only load the clear choice feature if it's not read only.
        if ($options->readonly) {
            return '';
        }

        $question = $qa->get_question();
        $response = $question->get_response($qa);
        $hascheckedchoice = false;
        foreach ($question->get_order($qa) as $value => $ansid) {
            if ($question->is_choice_selected($response, $value)) {
                $hascheckedchoice = true;
                break;
            }
        }

        $clearchoiceid = $this->get_input_id($qa, -1);
        $clearchoicefieldname = $qa->get_qt_field_name('clearchoice');
        $clearchoiceradioattrs = [
            'type' => $this->get_input_type(),
            'name' => $qa->get_qt_field_name('answer'),
            'id' => $clearchoiceid,
            'value' => -1,
            'class' => 'sr-only',
            'aria-hidden' => 'true'
        ];
        $clearchoicewrapperattrs = [
            'id' => $clearchoicefieldname,
            'class' => 'qtype_multichoice_clearchoice',
        ];

        // When no choice selected during rendering, then hide the clear choice option.
        // We are using .sr-only and aria-hidden together so while the element is hidden
        // from both the monitor and the screen-reader, it is still tabbable.
        $linktabindex = 0;
        if (!$hascheckedchoice && $response == -1) {
            $clearchoicewrapperattrs['class'] .= ' sr-only';
            $clearchoicewrapperattrs['aria-hidden'] = 'true';
            $clearchoiceradioattrs['checked'] = 'checked';
            $linktabindex = -1;
        }
        // Adds an hidden radio that will be checked to give the impression the choice has been cleared.
        $clearchoiceradio = html_writer::empty_tag('input', $clearchoiceradioattrs);
        $clearchoice = html_writer::link(
            '#',
            get_string('clearchoice', 'qtype_multichoice'),
            ['tabindex' => $linktabindex, 'role' => 'button', 'class' => 'btn btn-sm btn-outline-danger']
        );
        $clearchoiceradio .= html_writer::label($clearchoice, $clearchoiceid);

        // Now wrap the radio and label inside a div.
        $result = html_writer::tag('div', $clearchoiceradio, $clearchoicewrapperattrs);

        // Load required clearchoice AMD module.
        $this->page->requires->js_call_amd(
            'qtype_multichoice/clearchoice',
            'init',
            [$qa->get_outer_question_div_unique_id(), $clearchoicefieldname]
        );

        return $result;
    }
}
