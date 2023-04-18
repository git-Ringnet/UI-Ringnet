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
 * Resource configuration form
 *
 * @package    mod_resource
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/resource/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_resource_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;

        $config = get_config('resource');

        if ($this->current->instance and $this->current->tobemigrated) {
            // resource not migrated yet
            $resource_old = $DB->get_record('resource_old', array('oldid'=>$this->current->instance));
            $mform->addElement('static', 'warning', '', get_string('notmigrated', 'resource', $resource_old->type));
            $mform->addElement('cancel');
            $this->standard_hidden_coursemodule_elements();
            return;
        }

        //-------------------------------------------------------
        // $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement("html","<button id='btn_content'>Nội dung</button>");
        $mform->addElement("html","<button class='ml-3' id='btn_settings1'>Cài đặt</button>");
        $mform->addElement('html','<div id="content">');
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        // $this->standard_intro_elements();
        // $element = $mform->getElement('introeditor');
        // $attributes = $element->getAttributes();
        // $attributes['rows'] = 5;
        // $element->setAttributes($attributes);
        $filemanager_options = array();
        $filemanager_options['accepted_types'] = '*';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = -1;
        $filemanager_options['mainfile'] = true;
        $mform->addElement('html','<div class="position-relative">');
        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);
        if($this->current->legacyfiles == null){
            $mform->addElement('html','
            <div class="col-md-10 form-inline felement px-0"> 
            <input size="48" class="form-control" id="select_file" placeholder="Chưa chọn nội dung file tải lên" disabled></input>
            </div>
            ');
        }
        if($this->current->legacyfiles != null){
            $mform->addElement('html','<div class="position-absolute div_img">');
            $mform->addElement('html','<button id="btn_edit" class="delete_image btn btn-danger">Xóa file tải lên</button>');
            $mform->addElement('html','<button id="btn_upload" class="upload_image" style="display:none;">Tải lên file mới</button>');
            $mform->addElement('html','</div>');
        }else{
            $mform->addElement('html','<div class="position-absolute div_img">');
            $mform->addElement('html','<button id="btn_edit" class="delete_image btn btn-danger" style="display:none;">Xóa file tải lên</button>');
            $mform->addElement('html','<button id="btn_upload" class="upload_image"">Tải lên file mới</button>');
            $mform->addElement('html','</div>');
        }
        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'resource'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'resource'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'resource'), $options);
        }
        $mform->addElement('html','</div>');

        //-------------------------------------------------------
        // $mform->addElement('header', 'optionssection', get_string('appearance'));

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
        //     $mform->addElement('select', 'display', get_string('displayselect', 'resource'), $options);
        //     $mform->setDefault('display', $config->display);
        //     $mform->addHelpButton('display', 'displayselect', 'resource');
        // }

        // $mform->addElement('checkbox', 'showsize', get_string('showsize', 'resource'));
        // $mform->setDefault('showsize', $config->showsize);
        // $mform->addHelpButton('showsize', 'showsize', 'resource');
        // $mform->addElement('checkbox', 'showtype', get_string('showtype', 'resource'));
        // $mform->setDefault('showtype', $config->showtype);
        // $mform->addHelpButton('showtype', 'showtype', 'resource');
        // $mform->addElement('checkbox', 'showdate', get_string('showdate', 'resource'));
        // $mform->setDefault('showdate', $config->showdate);
        // $mform->addHelpButton('showdate', 'showdate', 'resource');

        // if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
        //     $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'resource'), array('size'=>3));
        //     if (count($options) > 1) {
        //         $mform->hideIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
        //     }
        //     $mform->setType('popupwidth', PARAM_INT);
        //     $mform->setDefault('popupwidth', $config->popupwidth);
        //     $mform->setAdvanced('popupwidth', true);

        //     $mform->addElement('text', 'popupheight', get_string('popupheight', 'resource'), array('size'=>3));
        //     if (count($options) > 1) {
        //         $mform->hideIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
        //     }
        //     $mform->setType('popupheight', PARAM_INT);
        //     $mform->setDefault('popupheight', $config->popupheight);
        //     $mform->setAdvanced('popupheight', true);
        // }

        // if (array_key_exists(RESOURCELIB_DISPLAY_AUTO, $options) or
        //   array_key_exists(RESOURCELIB_DISPLAY_EMBED, $options) or
        //   array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
        //     $mform->addElement('checkbox', 'printintro', get_string('printintro', 'resource'));
        //     $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
        //     $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_DOWNLOAD);
        //     $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
        //     $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
        //     $mform->setDefault('printintro', $config->printintro);
        // }

        // $options = array('0' => get_string('none'), '1' => get_string('allfiles'), '2' => get_string('htmlfilesonly'));
        // $mform->addElement('select', 'filterfiles', get_string('filterfiles', 'resource'), $options);
        // $mform->setDefault('filterfiles', $config->filterfiles);
        // $mform->setAdvanced('filterfiles', true);
        $mform->addElement('html','</div>');
        //-------------------------------------------------------
        $this->standard_coursemodule_elements1();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance and !$this->current->tobemigrated) {
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_resource', 'content', 0, array('subdirs'=>true));
            $default_values['files'] = $draftitemid;
        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = (array) unserialize_array($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
            if (!empty($displayoptions['showsize'])) {
                $default_values['showsize'] = $displayoptions['showsize'];
            } else {
                // Must set explicitly to 0 here otherwise it will use system
                // default which may be 1.
                $default_values['showsize'] = 0;
            }
            if (!empty($displayoptions['showtype'])) {
                $default_values['showtype'] = $displayoptions['showtype'];
            } else {
                $default_values['showtype'] = 0;
            }
            if (!empty($displayoptions['showdate'])) {
                $default_values['showdate'] = $displayoptions['showdate'];
            } else {
                $default_values['showdate'] = 0;
            }
        }
    }

    function definition_after_data() {
        if ($this->current->instance and $this->current->tobemigrated) {
            // resource not migrated yet
            return;
        }

        parent::definition_after_data();
    }

    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['files'], 'sortorder, id', false)) {
            $errors['files'] = get_string('required');
            return $errors;
        }
        if (count($files) == 1) {
            // no need to select main file if only one picked
            return $errors;
        } else if(count($files) > 1) {
            $mainfile = false;
            foreach($files as $file) {
                if ($file->get_sortorder() == 1) {
                    $mainfile = true;
                    break;
                }
            }
            // set a default main file
            if (!$mainfile) {
                $file = reset($files);
                file_set_sortorder($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                                   $file->get_filepath(), $file->get_filename(), 1);
            }
        }
        return $errors;
    }
}
?>
<style>
    #page-mod-resource-mod #fitem_id_files{
        width: 80%;
    }
    #page-mod-resource-mod #fitem_id_files .col-md-9 .fp-navbar{
        display: none;
    }
    #page-mod-resource-mod #fitem_id_files .col-md-9 .filemanager .fp-hascontextmenu a div:first-child .fp-thumbnail{
        display: none;
    }
    #page-mod-resource-mod #fitem_id_files .col-md-9 .fp-hascontextmenu .fp-filename-field{
        overflow: auto;
        position: inherit;
    }
    #page-mod-resource-mod #fitem_id_files .col-md-9 .fp-hascontextmenu .fp-filename-field .fp-filename{
        width: 100% !important;
    }
    #page-mod-resource-mod #fitem_id_files .col-md-9 .filemanager-container,
    #page-mod-resource-mod #fitem_id_files .col-md-9 .filemanager-container .fp-content
    {
        min-height: auto !important;
    }
    #page-mod-resource-mod #fitem_id_files .col-md-9 .fp-hascontextmenu{
        margin: 5px;
    }


    #page-mod-resource-mod #fitem_id_name,
    #page-mod-resource-mod #fitem_id_introeditor,
    #page-mod-resource-mod #fitem_id_cmidnumber,
    #page-mod-resource-mod #fgroup_id_visible,
    #page-mod-resource-mod #fitem_id_files{
        display: block;
    }
    
    #page-mod-resource-mod #fitem_id_introeditor,
    #page-mod-resource-mod #fitem_id_files{
        padding-top: 24px;
    }
    #page-mod-resource-mod #fitem_id_name .col-md-3,
    #page-mod-resource-mod #fitem_id_introeditor .col-md-3,
    #page-mod-resource-mod #fitem_id_files .col-md-3
    {
        text-align: left !important;
    }
    #page-mod-resource-mod #fitem_id_name .col-md-9,
    #page-mod-resource-mod #fitem_id_introeditor .col-md-9,
    #page-mod-resource-mod #fitem_id_files .col-md-9
    {
        padding-top: 12px;
    }
    #page-mod-resource-mod #content{
        padding: 24px 0 10px 20%;
    }
    #page-mod-resource-mod #content_settings{
        padding: 24px 0 0 0;
    }
    #page-mod-resource-mod #nav_menu_create{
        border-right: 1px solid #D6D6D6;
    }
    #page-mod-resource-mod #btn_content,
    #page-mod-resource-mod #btn_settings1,
    #page-mod-resource-mod #btn_setting_all,
    #page-mod-resource-mod #btn_setting_grade,
    #page-mod-resource-mod #btn_setting_limit{
        color: #555555;
        padding: 0 4px;
        border: none;
        background: #FCFCFC;
    }
    #page-mod-resource-mod #fitem_id_completion{
        display: none;
    }
    #page-mod-resource-mod #id_modstandardgrade legend{
        display: none !important;
    }
    #page-mod-resource-mod #content_setting_all,
    #page-mod-resource-mod #content_setting_limit,
    #page-mod-resource-mod #content_grade
    {
        margin: 0 0 0 10%;
    }
    #page-mod-resource-mod #content_grade #fitem_fgroup_id_grade .col-md-3,
    #page-mod-resource-mod #fitem_fgroup_id_grade .col-md-9 label:first-child,
    #page-mod-resource-mod #id_grade_modgrade_type
    {
        display: none;
    }
    #page-mod-resource-mod #content_grade #fitem_fgroup_id_grade .col-md-9 fieldset
    {
        border: none !important;
    }
    #page-mod-resource-mod #content_require .form-group .col-md-3{
        display: none;
    }
    #page-mod-resource-mod #fitem_id_cmidnumber .col-md-3,
    #page-mod-resource-mod #fgroup_id_visible .col-md-3,
    #page-mod-resource-mod #fgroup_id_completiontimespentgroup .col-md-3,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .col-md-3{
        text-align: left !important;
    }
    #page-mod-resource-mod #fgroup_id_completiontimespentgroup,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson{
        display: block;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .col-md-9,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .col-md-9 .availability-field,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .col-md-9 .availability-field .availability-inner
    {
        width: 100%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .col-md-9{
        max-width: 100%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-header,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-connector
    {
        display: none;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-inner,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-inner .availability-item,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-inner .availability-item .availability_completion,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-item .availability_date,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-item .availability_grade,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-item .availability_profile
    {
        border: none;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_completion .form-group label:last-child,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_profile  .availability-group label:nth-child(2) {
        display: none;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_completion,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_date,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade
    {
        display: block;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade label{
        justify-content: normal;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade .custom-select{
        width: 80%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_completion .availability-group,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade .availability-group,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_completion,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_grade,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_date,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile{
        width: 100%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile .availability-group{
        width: 100%;
        display: block;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile .custom-select{
        width: 30%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile .custom-select{
        width: 30%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile #user-profile-f label{
        width: 60%;
    }
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability-children .availability_profile #user-profile-f label input{
        width: 100% !important;
    }
    #page-mod-resource-mod .availability-delete img{
        margin-top: 10px;
    }
    #page-mod-resource-mod #fgroup_id_completiontimespentgroup .ios-switch-label{
        display: none;
    }
    #page-mod-resource-mod #fgroup_id_completiontimespentgroup .col-md-9,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_completion .availability-group,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_date  .availability-group,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_grade  .availability-group,
    #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_profile  #user-profile-f
    {
        margin-top: 8px;
    }
    #page-mod-resource-mod #files_select{
        width: 80%;
    }
    @media only screen and (min-width: 1900px) {
        #page-mod-resource-mod #files_select{
            width: 55%;
        }
        #page-mod-resource-mod .availability-children {
            width: 60%;
        } 
        #page-mod-resource-mod .availability-button{
            max-width: 60%;
        }
        #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_completion,
        #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_grade{
            display: block;
        }
        #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_completion .availability-group,
        #page-mod-resource-mod #fitem_id_availabilityconditionsjson .availability_grade .availability-group{
            width: 100%;
        }
        #page-mod-resource-mod #fitem_id_gradepass .col-md-3{
            flex: 0 0 11% !important;
        }
        #page-mod-resource-mod #fitem_id_gradepass .col-md-9 input{
            width: 24%;
        }
        #page-mod-resource-mod .div_img{
        top: 63%;
        left: 67%;
    }
    }
    #page-mod-resource-mod #fgroup_id_visible,
    #page-mod-resource-mod .pl-3
    {
        margin-top: 24px;
    }
    #page-mod-resource-mod #fitem_id_cmidnumber .col-md-9,
    #page-mod-resource-mod #fgroup_id_visible .col-md-9
    {
        margin-top: 8px;
    }
    #page-mod-resource-mod #content_require .form-group .col-md-9 .form-check,
    #page-mod-resource-mod .form-group .col-md-9 .form-check{
        background: none;
    }
    #page-mod-resource-mod .custom-control .ios-switch-control-input:checked ~.ios-switch-control-indicator{
        background-color: #0095F6 !important;
        border :2px solid #0095F6 !important;
    }
    #page-mod-resource-mod #content_require .form-check .text{
        color: #1D1C20 !important;
    }
    #page-mod-resource-mod .availability-delete img{
        width: 12px;
    }
    #page-mod-resource-mod .availability-delete{
        background-color: none !important;
    }
    #page-mod-resource-mod .availability-children .availability-eye{
        margin-top: 0.5% ;
    }
    #page-mod-resource-mod .availability-item > :nth-child(2){
        align-self: start;
    }
    #page-mod-resource-mod #fitem_fgroup_id_grade .col-md-9 .d-flex.flex-wrap.align-items-center.rui-form-element-group{
        padding-left: 0 !important;
    }
    #page-mod-resource-mod #id_modstandardgradecontainer,
    #page-mod-resource-mod #switch-i{
        margin-left: -3%;
    }
    #page-mod-resource-mod #switch-i p{
        font-size: 13px;
        font-weight: 600;
    }
    #page-mod-resource-mod #id_grade_modgrade_point{
        height: calc(1.5em + 1.5rem + 5px);
        padding: 0.75rem 1.25rem;
    }
    #page-mod-resource-mod #fitem_id_gradepass .col-md-3{
        text-align: left !important;
        padding: 0 !important;
        max-width: 17% !important;
    }
    #page-mod-resource-mod #fitem_fgroup_id_grade .col-md-9 fieldset{
        padding: 0 !important;
    }
    #page-mod-resource-mod #fitem_fgroup_id_grade .col-md-9 .form-group span{
        padding-left: 5%;
    }
    #page-mod-resource-mod #fitem_id_gradepass .col-md-9 input{
        width: 38%;
    }
    #page-mod-resource-mod #fitem_fgroup_id_grade{
        display: none;
    }
    #page-mod-resource-mod #fitem_id_timelimit,
    #page-mod-resource-mod #fitem_id_attempts,
    #page-mod-resource-mod #fgroup_id_completionminattemptsgroup,
    #page-mod-resource-mod #fitem_id_timeopen,
    #page-mod-resource-mod #fitem_id_timeclose
    {
        display: block;
    }
    #page-mod-resource-mod #fitem_id_timelimit .col-md-3,
    #page-mod-resource-mod #fitem_id_attempts .col-md-3,
    #page-mod-resource-mod #fgroup_id_completionminattemptsgroup .col-md-3,
    #page-mod-resource-mod #fitem_id_timeopen .col-md-3,
    #page-mod-resource-mod #fitem_id_timeclose .col-md-3
    {
        text-align: left !important;
    }
    #page-mod-resource-mod #fitem_id_timelimit .col-md-9 .d-flex.flex-wrap.align-items-center.rui-form-element-group
    {
        padding-left: 0 !important;
    }
    #page-mod-resource-mod #fitem_id_timeopen .col-md-9,
    #page-mod-resource-mod #fitem_id_timeclose .col-md-9{
        max-width: 100%;
    }
    #page-mod-resource-mod #fitem_id_timeopen,
    #page-mod-resource-mod #fitem_id_timeclose{
        margin-left: 10px;
    }
    #page-mod-resource-mod #fgroup_id_questionsperpagegrp,
    #page-mod-resource-mod #fitem_id_navmethod, #page-mod-page-mod #fitem_id_shuffleanswers,
    #page-mod-resource-mod #fitem_id_preferredbehaviour, #page-mod-page-mod #fitem_id_attemptonlast,
    #page-mod-resource-mod #id_reviewoptionshdr,
    #page-mod-resource-mod #id_seb , #page-mod-page-mod #id_security , #page-mod-page-mod #id_overallfeedbackhdr legend,
    #page-mod-resource-mod #id_overallfeedbackhdr #fitem_id_gradeboundarystatic1,
    #page-mod-resource-mod #id_overallfeedbackhdr #fitem_id_feedbacktext_0,
    #page-mod-resource-mod #id_overallfeedbackhdr #fitem_id_feedbackboundaries_0,
    #page-mod-resource-mod #id_overallfeedbackhdr #fitem_id_feedbacktext_1, 
    #page-mod-resource-mod #id_overallfeedbackhdr #fitem_id_gradeboundarystatic2,
    #page-mod-resource-mod #id_overallfeedbackhdr #fitem_id_boundary_add_fields,
    #page-mod-resource-mod #id_competenciessection{
        display: none !important;
    }
    #page-mod-resource-mod .div_img{
        top: 63%;
        left: 67%;
    }
    #page-mod-resource-mod .div_img .upload_image,
    #page-mod-resource-mod .div_img .delete_image{
        background: #555555;
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 500;
        padding: 6px 14px 6px 14px;
    }
</style>
<script>
    window.onload = function(){
        var delete1 =  document.querySelector('.delete_image');
    var upload_image = document.getElementById('btn_upload');
    if(delete1){
        delete1.addEventListener('click',function(e){
            e.preventDefault();
            var click_image = document.querySelector('.fp-thumbnail');
            if(click_image){
                click_image.click();
                var file_delete = document.querySelector('.fp-file-delete');
                if(file_delete){
                    file_delete.click();
                    var dlg_butconfirm = document.querySelector('.fp-dlg-butconfirm');
                    if(dlg_butconfirm){
                        dlg_butconfirm.click();
                    }
                }
            }
            var select_file = document.getElementById('select_file');
            if(select_file){
                select_file.style.display = 'block';
            }
            upload_image.style.display = "block";
            delete1.style.display = "none";
        });
    }
    if(upload_image){
        upload_image.addEventListener('click',function(e){
            e.preventDefault();
                upload = document.querySelector('.dndupload-arrow').click();
                setTimeout(function(){
                    var up = document.querySelector('.fp-formset');
                if(up){
                    var img_extent = up.querySelector('input');
                    if(img_extent){
                        img_extent.click();
                        img_extent.addEventListener('change',function(){
                        var upload = document.querySelector('.fp-upload-btn');
                        if(upload){
                            upload.addEventListener('click',function(){
                        var select_file = document.getElementById('select_file');
                            if(select_file){
                                select_file.style.display = 'none';
                            }
                        });
                        upload.click();
                        }
                        upload_image.style.display = "none";
                        delete1.style.display = "block";
                    });
                    }
                }
                },300)
            });
        }
    }
</script>