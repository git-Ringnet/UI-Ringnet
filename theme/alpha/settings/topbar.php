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
 *
 * @package   theme_alpha
 * @copyright 2022 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 *
 */


defined('MOODLE_INTERNAL') || die();


$page = new admin_settingpage('theme_alpha_settingstopbar', get_string( 'settingstopbar', 'theme_alpha'));

    $name = 'theme_alpha/topbarlogoareaonon';
    $title = get_string('topbarlogoareaonon', 'theme_alpha');
    $description = get_string('topbarlogoareaonon_desc', 'theme_alpha');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_alpha/customlogo';
    $title = get_string('customlogo', 'theme_alpha');
    $description = get_string('customlogo_desc', 'theme_alpha');
    $opts = array('accepted_types' => array('.png', '.jpg', '.svg', 'gif'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'customlogo', 0, $opts);
    $page->add($setting);

    // $name = 'theme_alpha/customdmlogo';
    // $title = get_string('customdmlogo', 'theme_alpha');
    // $description = get_string('customdmlogo_desc', 'theme_alpha');
    // $opts = array('accepted_types' => array('.png', '.jpg', '.svg', 'gif'));
    // $setting = new admin_setting_configstoredfile($name, $title, $description, 'customdmlogo', 0, $opts);
    // $page->add($setting);

    $name = 'theme_alpha/customlogotxt';
    $title = get_string('customlogotxt', 'theme_alpha');
    $description = get_string('customlogotxt_desc', 'theme_alpha');
    $default = 'alpha';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_alpha/topbarcustomhtml';
    $title = get_string('topbarcustomhtml', 'theme_alpha');
    $description = get_string('topbarcustomhtml_desc', 'theme_alpha');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $page->add($setting);

    // Colors
    $name = 'theme_alpha/htopbarcolors';
    $heading = get_string('htopbarcolors', 'theme_alpha');
    $setting = new admin_setting_heading($name, $heading, format_text(get_string('htopbarcolors_desc', 'theme_alpha'), FORMAT_MARKDOWN));
    $page->add($setting);

    $name = 'theme_alpha/colortopbarbg';
    $title = get_string('colortopbarbg', 'theme_alpha');
    $description = get_string('color_desc', 'theme_alpha');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_alpha/colortopbartext';
    $title = get_string('colortopbartext', 'theme_alpha');
    $description = get_string('color_desc', 'theme_alpha');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_alpha/colortopbarbtn';
    $title = get_string('colortopbarbtn', 'theme_alpha');
    $description = get_string('color_desc', 'theme_alpha');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_alpha/colortopbarbtntext';
    $title = get_string('colortopbarbtntext', 'theme_alpha');
    $description = get_string('color_desc', 'theme_alpha');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_alpha/colortopbarbtnhover';
    $title = get_string('colortopbarbtnhover', 'theme_alpha');
    $description = get_string('color_desc', 'theme_alpha');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_alpha/colortopbarbtnhovertext';
    $title = get_string('colortopbarbtnhovertext', 'theme_alpha');
    $description = get_string('color_desc', 'theme_alpha');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);

