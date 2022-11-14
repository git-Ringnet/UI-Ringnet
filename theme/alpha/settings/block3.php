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
 * @copyright 2021 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 *
 */


defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_alpha_block3', get_string('settingsblock3', 'theme_alpha'));

        $name = 'theme_alpha/displayblock3';
        $title = get_string('turnon', 'theme_alpha');
        $description = get_string('displayblock3_desc', 'theme_alpha');
        $default = 1;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_alpha/block3class';
        $title = get_string('additionalclass', 'theme_alpha');
        $description = get_string('additionalclass_desc', 'theme_alpha');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_alpha/block3wrapperalign';
        $title = get_string('block3wrapperalign', 'theme_alpha');
        $description = get_string('block3wrapperalign_desc', 'theme_alpha');
        $default = 1;
        $choices = array(0 => 'Left', 1 => 'Middle', 2 => 'Right');
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $page->add($setting);

        $name = 'theme_alpha/showblock3wrapper';
        $title = get_string('showblock3wrapper', 'theme_alpha');
        $description = get_string('showblock3wrapper_desc', 'theme_alpha');
        $default = 1;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_alpha/block3wrapperbg';
        $title = get_string('block3wrapperbg', 'theme_alpha');
        $description = get_string('block3wrapperbg_desc', 'theme_alpha');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_alpha/block3herotitle';
        $title = get_string('block3herotitle', 'theme_alpha');
        $description = get_string('block3herotitle_desc', 'theme_alpha');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
        $page->add($setting);

        $name = 'theme_alpha/block3herocaption';
        $title = get_string('block3herocaption', 'theme_alpha');
        $description = get_string('block3herocaption_desc', 'theme_alpha');
        $default = '<div class="rui-hero-desc">From its medieval origins to the digital era, learn everything there is to know about the ubiquitous lorem ipsum passage.</div>
        <div class="rui-hero-btns d-inline-flex flex-wrap hideforloggedin">
            <a href="https://demo.rosea.io/alpha/1/login/index.php" class="btn btn-lg btn-primary">Sign up</a>
        </div>';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_alpha/block3img';
        $title = get_string('block3img', 'theme_alpha');
        $description = get_string('block3img_desc', 'theme_alpha');
        $opts = array('accepted_types' => array('.jpg, .png, .gif'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'block3img', 0, $opts);
        $page->add($setting);


        $name = 'theme_alpha/block3htmlcontent';
        $title = get_string('blockhtmlcontent', 'theme_alpha');
        $description = get_string('blockhtmlcontent_desc', 'theme_alpha');
        $default = '<div class="mt-4"><a href="https://rosea.io/alpha" class="btn btn-lg btn-dark">Get this theme for $99*</a></div><div class="mt-2"><a href="https://alpha.rosea.io/doc" class="btn btn-sm btn-secondary">Documentation</a></div>';
        $setting = new alpha_setting_confightmleditor($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_alpha/block3footercontent';
        $title = get_string('blockfootercontent', 'theme_alpha');
        $description = get_string('blockfootercontent_desc', 'theme_alpha');
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description, $default);
        $page->add($setting);
        
$settings->add($page);