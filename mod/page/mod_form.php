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
 * Page configuration form
 *
 * @package mod_page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_page_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('page');

        //-------------------------------------------------------
        // $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('html','<div id="menu_create">');
        $mform->addElement("html","<button class='active_nav' id='btn_content'>Nội dung</button>");
        $mform->addElement("html","<button class='ml-3' id='btn_settings1'>Cài đặt</button>");
        $mform->addElement('html','</div>');
        $mform->addElement('html','<div id="content">');
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        // $mform->addElement('header', 'contentsection', get_string('contentheader', 'page'));
        $mform->addElement('html','<div class="position-relative">');
        $mform->addElement('editor', 'page', get_string('content', 'page'), null, page_get_editor_options($this->context));
        $mform->addRule('page', get_string('required'), 'required', null, 'client');
        $mform->addElement('html', '<div class="position-absolute div_upload_video"><button id="btn_upload_videos">Tải video lên</button></div>');
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');
        //-------------------------------------------------------
        // $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        // if ($this->current->instance) {
        //     $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        // } else {
        //     $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        // }
        // if (count($options) == 1) {
        //     $mform->addElement('hidden', 'display');
        //     $mform->setType('display', PARAM_INT);
        //     reset($options);
        //     $mform->setDefault('display', key($options));
        // } else {
        //     $mform->addElement('select', 'display', get_string('displayselect', 'page'), $options);
        //     $mform->setDefault('display', $config->display);
        // }

        // if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
        //     $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'page'), array('size'=>3));
        //     if (count($options) > 1) {
        //         $mform->hideIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
        //     }
        //     $mform->setType('popupwidth', PARAM_INT);
        //     $mform->setDefault('popupwidth', $config->popupwidth);

        //     $mform->addElement('text', 'popupheight', get_string('popupheight', 'page'), array('size'=>3));
        //     if (count($options) > 1) {
        //         $mform->hideIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
        //     }
        //     $mform->setType('popupheight', PARAM_INT);
        //     $mform->setDefault('popupheight', $config->popupheight);
        // }

        // $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'page'));
        // $mform->setDefault('printintro', $config->printintro);
        // $mform->addElement('advcheckbox', 'printlastmodified', get_string('printlastmodified', 'page'));
        // $mform->setDefault('printlastmodified', $config->printlastmodified);

        // // add legacy files flag only if used
        // if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
        //     $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'page'),
        //                      RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'page'));
        //     $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'page'), $options);
        //     $mform->setAdvanced('legacyfiles', 1);
        // }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements1();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $defaultvalues['page']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['page']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_page',
                    'content', 0, page_get_editor_options($this->context), $defaultvalues['content']);
            $defaultvalues['page']['itemid'] = $draftitemid;
        }
        if (!empty($defaultvalues['displayoptions'])) {
            $displayoptions = (array) unserialize_array($defaultvalues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultvalues['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printlastmodified'])) {
                $defaultvalues['printlastmodified'] = $displayoptions['printlastmodified'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $defaultvalues['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $defaultvalues['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}
?>
<style>
    #page-mod-page-mod #fitem_id_name,
    #page-mod-page-mod #fitem_id_introeditor,
    #page-mod-page-mod #fitem_id_cmidnumber,
    #page-mod-page-mod #fgroup_id_visible{
        display: block;
    }
    #page-mod-page-mod #fitem_id_page{
        display: block;
    }
    #page-mod-page-mod #fitem_id_introeditor,
    #page-mod-page-mod #fitem_id_page{
        padding-top: 24px;
    }
    #page-mod-page-mod #fitem_id_name .col-md-3,
    #page-mod-page-mod #fitem_id_introeditor .col-md-3,
    #page-mod-page-mod #fitem_id_page .col-md-3
    {
        text-align: left !important;
    }
    #page-mod-page-mod #fitem_id_name .col-md-9,
    #page-mod-page-mod #fitem_id_introeditor .col-md-9,
    #page-mod-page-mod #fitem_id_page .col-md-9
    {
        padding-top: 12px;
    }
    #page-mod-page-mod #content{
        padding: 24px 0 10px 20%;
    }
    #page-mod-page-mod #content_settings{
        padding: 24px 0 0 0;
    }
    #page-mod-page-mod #nav_menu_create{
        border-right: 1px solid #D6D6D6;
    }
    #page-mod-page-mod #btn_content,
    #page-mod-page-mod #btn_settings1,
    #page-mod-page-mod #btn_setting_all,
    #page-mod-page-mod #btn_setting_grade,
    #page-mod-page-mod #btn_setting_limit{
        color: #555555;
        padding: 0 4px;
        border: none;
        background: #FCFCFC;
    }
    #page-mod-page-mod #fitem_id_completion{
        display: none;
    }
    #page-mod-page-mod #id_modstandardgrade legend{
        display: none !important;
    }
    #page-mod-page-mod #content_setting_all,
    #page-mod-page-mod #content_setting_limit,
    #page-mod-page-mod #content_grade
    {
        margin: 0 0 0 10%;
    }
    #page-mod-page-mod #content_grade #fitem_fgroup_id_grade .col-md-3,
    #page-mod-page-mod #fitem_fgroup_id_grade .col-md-9 label:first-child,
    #page-mod-page-mod #id_grade_modgrade_type
    {
        display: none;
    }
    #page-mod-page-mod #content_grade #fitem_fgroup_id_grade .col-md-9 fieldset
    {
        border: none !important;
    }
    #page-mod-page-mod #content_require .form-group .col-md-3{
        display: none;
    }
    #page-mod-page-mod #fitem_id_cmidnumber .col-md-3,
    #page-mod-page-mod #fgroup_id_visible .col-md-3,
    #page-mod-page-mod #fgroup_id_completiontimespentgroup .col-md-3,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .col-md-3{
        text-align: left !important;
    }
    #page-mod-page-mod #fgroup_id_completiontimespentgroup,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson{
        display: block;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .col-md-9,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .col-md-9 .availability-field,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .col-md-9 .availability-field .availability-inner
    {
        width: 100%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .col-md-9{
        max-width: 100%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-header,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-connector
    {
        display: none;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-inner,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-inner .availability-item,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-inner .availability-item .availability_completion,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-item .availability_date,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-item .availability_grade,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-item .availability_profile
    {
        border: none;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_completion .form-group label:last-child,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_profile  .availability-group label:nth-child(2) {
        display: none;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_completion,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_date,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade
    {
        display: block;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade label{
        justify-content: normal;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade .custom-select{
        width: 80%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_completion .availability-group,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade .availability-group,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_completion,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_date,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile{
        width: 100%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile .availability-group{
        width: 100%;
        display: block;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile .custom-select{
        width: 30%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile .custom-select{
        width: 30%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile #user-profile-f label{
        width: 60%;
    }
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile #user-profile-f label input{
        width: 100% !important;
    }
    #page-mod-page-mod .availability-delete img{
        margin-top: 10px;
    }
    #page-mod-page-mod #fgroup_id_completiontimespentgroup .ios-switch-label{
        display: none;
    }
    #page-mod-page-mod #fgroup_id_completiontimespentgroup .col-md-9,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_completion .availability-group,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_date  .availability-group,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_grade  .availability-group,
    #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_profile  #user-profile-f
    {
        margin-top: 8px;
    }
    @media only screen and (min-width: 1900px) {
        #page-mod-page-mod .availability-children {
            width: 60%;
        } 
        #page-mod-page-mod .availability-button{
            max-width: 60%;
        }
        #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_completion,
        #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_grade{
            display: block;
        }
        #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_completion .availability-group,
        #page-mod-page-mod #fitem_id_availabilityconditionsjson .availability_grade .availability-group{
            width: 100%;
        }
        #page-mod-page-mod #fitem_id_gradepass .col-md-3{
            flex: 0 0 11% !important;
        }
        #page-mod-page-mod #fitem_id_gradepass .col-md-9 input{
            width: 24%;
        }
    }
    #page-mod-page-mod #fgroup_id_visible,
    #page-mod-page-mod .pl-3
    {
        margin-top: 24px;
    }
    #page-mod-page-mod #fitem_id_cmidnumber .col-md-9,
    #page-mod-page-mod #fgroup_id_visible .col-md-9
    {
        margin-top: 8px;
    }
    #page-mod-page-mod #content_require .form-group .col-md-9 .form-check,
    #page-mod-page-mod .form-group .col-md-9 .form-check{
        background: none;
    }
    #page-mod-page-mod .custom-control .ios-switch-control-input:checked ~.ios-switch-control-indicator{
        background-color: #0095F6 !important;
        border :2px solid #0095F6 !important;
    }
    #page-mod-page-mod #content_require .form-check .text{
        color: #1D1C20 !important;
    }
    #page-mod-page-mod .availability-delete img{
        width: 12px;
    }
    #page-mod-page-mod .availability-delete{
        background-color: none !important;
    }
    #page-mod-page-mod .availability-children .availability-eye{
        margin-top: 0.5% ;
    }
    #page-mod-page-mod .availability-item > :nth-child(2){
        align-self: start;
    }
    #page-mod-page-mod #fitem_fgroup_id_grade .col-md-9 .d-flex.flex-wrap.align-items-center.rui-form-element-group{
        padding-left: 0 !important;
    }
    #page-mod-page-mod #id_modstandardgradecontainer,
    #page-mod-page-mod #switch-i{
        margin-left: -3%;
    }
    #page-mod-page-mod #switch-i p{
        font-size: 13px;
        font-weight: 600;
    }
    #page-mod-page-mod #id_grade_modgrade_point{
        height: calc(1.5em + 1.5rem + 5px);
        padding: 0.75rem 1.25rem;
    }
    #page-mod-page-mod #fitem_id_gradepass .col-md-3{
        text-align: left !important;
        padding: 0 !important;
        max-width: 17% !important;
    }
    #page-mod-page-mod #fitem_fgroup_id_grade .col-md-9 fieldset{
        padding: 0 !important;
    }
    #page-mod-page-mod #fitem_fgroup_id_grade .col-md-9 .form-group span{
        padding-left: 5%;
    }
    #page-mod-page-mod #fitem_id_gradepass .col-md-9 input{
        width: 38%;
    }
    #page-mod-page-mod #fitem_fgroup_id_grade{
        display: none;
    }
    #page-mod-page-mod #fitem_id_timelimit,
    #page-mod-page-mod #fitem_id_attempts,
    #page-mod-page-mod #fgroup_id_completionminattemptsgroup,
    #page-mod-page-mod #fitem_id_timeopen,
    #page-mod-page-mod #fitem_id_timeclose
    {
        display: block;
    }
    #page-mod-page-mod #fitem_id_timelimit .col-md-3,
    #page-mod-page-mod #fitem_id_attempts .col-md-3,
    #page-mod-page-mod #fgroup_id_completionminattemptsgroup .col-md-3,
    #page-mod-page-mod #fitem_id_timeopen .col-md-3,
    #page-mod-page-mod #fitem_id_timeclose .col-md-3
    {
        text-align: left !important;
    }
    #page-mod-page-mod #fitem_id_timelimit .col-md-9 .d-flex.flex-wrap.align-items-center.rui-form-element-group
    {
        padding-left: 0 !important;
    }
    #page-mod-page-mod #fitem_id_timeopen .col-md-9,
    #page-mod-page-mod #fitem_id_timeclose .col-md-9{
        max-width: 100%;
    }
    #page-mod-page-mod #fitem_id_timeopen,
    #page-mod-page-mod #fitem_id_timeclose{
        margin-left: 10px;
    }
    #page-mod-page-mod #fgroup_id_questionsperpagegrp,
    #page-mod-page-mod #fitem_id_navmethod, #page-mod-page-mod #fitem_id_shuffleanswers,
    #page-mod-page-mod #fitem_id_preferredbehaviour, #page-mod-page-mod #fitem_id_attemptonlast,
    #page-mod-page-mod #id_reviewoptionshdr,
    #page-mod-page-mod #id_seb , #page-mod-page-mod #id_security , #page-mod-page-mod #id_overallfeedbackhdr legend,
    #page-mod-page-mod #id_overallfeedbackhdr #fitem_id_gradeboundarystatic1,
    #page-mod-page-mod #id_overallfeedbackhdr #fitem_id_feedbacktext_0,
    #page-mod-page-mod #id_overallfeedbackhdr #fitem_id_feedbackboundaries_0,
    #page-mod-page-mod #id_overallfeedbackhdr #fitem_id_feedbacktext_1, 
    #page-mod-page-mod #id_overallfeedbackhdr #fitem_id_gradeboundarystatic2,
    #page-mod-page-mod #id_overallfeedbackhdr #fitem_id_boundary_add_fields,
    #page-mod-page-mod #id_competenciessection{
        display: none !important;
    }
    #page-mod-page-mod .div_upload_video{
        top: 60px;
        left: 78%;
    }
    #page-mod-page-mod #btn_upload_videos{
        border-radius: 6px;
        background: #555555;
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 500;
        padding: 4px 16px 4px 16px;
    }
    #page-mod-page-mod #id_page_atto_media_form a,
    #page-mod-page-mod #id_page_atto_media_form h3 {
        display: none !important;
    }
    .moodle-dialogue-wrap .rui-nav-col ul:nth-child(1),
    .moodle-dialogue-wrap .rui-nav-col ul:nth-child(2),
    .moodle-dialogue-wrap .rui-nav-col ul:nth-child(3),
    .moodle-dialogue-wrap .rui-nav-col ul:nth-child(6),
    .moodle-dialogue-wrap .rui-nav-col ul:nth-child(7)
    {
        display: none;
    }
    .moodle-dialogue-wrap .moodle-dialogue-bd .fp-navbar,
    .moodle-dialogue-wrap .moodle-dialogue-bd .fp-saveas,
    .moodle-dialogue-wrap .moodle-dialogue-bd .fp-setauthor,
    .moodle-dialogue-wrap .moodle-dialogue-bd .fp-setlicense{
        display: none;
    }
    #page-mod-page-mod #fitem_id_introeditor{
        display: none;
    }
</style>
<script>
     window.onload = function() {
        var btn_upload_video = document.getElementById('btn_upload_videos');
        if (btn_upload_video) {
            btn_upload_video.addEventListener('click', function(e) {
                e.preventDefault();
                var auto_media = document.querySelector('.atto_media_button');
                if (auto_media) {
                    auto_media.click();
                }
            });
        }
     }
</script>
