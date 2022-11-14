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

$page = new admin_settingpage('theme_alpha_block1', get_string('settingsblock1', 'theme_alpha'));

          $name = 'theme_alpha/displayblock1';
          $title = get_string('turnon', 'theme_alpha');
          $description = get_string('displayblock1_desc', 'theme_alpha');
          $default = 0;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block1class';
          $title = get_string('additionalclass', 'theme_alpha');
          $description = get_string('additionalclass_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block1wrapperalign';
          $title = get_string('block1wrapperalign', 'theme_alpha');
          $description = get_string('block1wrapperalign_desc', 'theme_alpha');
          $default = 1;
          $choices = array(0 => 'Left', 1 => 'Middle', 2 => 'Right');
          $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
          $page->add($setting);

          $name = 'theme_alpha/showblock1sliderwrapper';
          $title = get_string('showblock1sliderwrapper', 'theme_alpha');
          $description = get_string('showblock1sliderwrapper_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block1sliderwrapperbg';
          $title = get_string('block1sliderwrapperbg', 'theme_alpha');
          $description = get_string('block1sliderwrapperbg_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/block1count';
          $title = get_string('block1count', 'theme_alpha');
          $description = get_string('block1count_desc', 'theme_alpha');
          $default = 1;
          $options = array();
          for ($i = 1; $i <= 7; $i++) {
                    $options[$i] = $i;
          }
          $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          // If we don't have an slide yet, default to the preset.
          $slidercount = get_config('theme_alpha', 'block1count');

          if (!$slidercount) {
                    $slidercount = 1;
          }

          for ($sliderindex = 1; $sliderindex <= $slidercount; $sliderindex++) {
                    $name = 'theme_alpha/hblock1slide' . $sliderindex;
                    $heading = get_string('hblock1slide', 'theme_alpha');
                    $setting = new admin_setting_heading($name, '<span class="rui-admin-no">'. $sliderindex . '</span>'. $heading, format_text(get_string('hblock1slide_desc', 'theme_alpha'), FORMAT_MARKDOWN));
                    $page->add($setting);

                    $fileid = 'block1slideimg' . $sliderindex;
                    $name = 'theme_alpha/block1slideimg' . $sliderindex;
                    $title = get_string('block1slideimg', 'theme_alpha');
                    $description = get_string('block1slideimg_desc', 'theme_alpha');
                    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
                    $setting = new admin_setting_configstoredfile($name, '<span class="rui-admin-no">'. $sliderindex . '</span>'. $title, $description, $fileid, 0, $opts);
                    $setting->set_updatedcallback('theme_reset_all_caches');
                    $page->add($setting);

                    $name = 'theme_alpha/block1slidetitle' . $sliderindex;
                    $title = get_string('block1slidetitle', 'theme_alpha');
                    $description = get_string('block1slidetitle_desc', 'theme_alpha');
                    $setting = new admin_setting_configtext($name, '<span class="rui-admin-no">'. $sliderindex . '</span>'. $title, $description, '', PARAM_TEXT);
                    $page->add($setting);

                    $name = 'theme_alpha/block1slidecaption' . $sliderindex;
                    $title = get_string('block1slidecaption', 'theme_alpha');
                    $description = get_string('block1slidecaption_desc', 'theme_alpha');
                    $default = '<div class="rui-hero-desc">From its medieval origins to the digital era, learn everything there is to know about the ubiquitous lorem ipsum passage.</div>
                    <div class="rui-hero-btns d-inline-flex flex-wrap hideforloggedin">
                        <a href="https://demo.rosea.io/alpha/1/login/index.php" class="btn btn-lg btn-primary">Sign up</a>
                    </div>';
                    $setting = new admin_setting_confightmleditor($name, '<span class="rui-admin-no">'. $sliderindex . '</span>'. $title, $description, $default);
                    $page->add($setting);

                    $name = 'theme_alpha/block1slidecss' . $sliderindex;
                    $title = get_string('block1slidecss', 'theme_alpha');
                    $description = get_string('block1slidecss_desc', 'theme_alpha');
                    $setting = new admin_setting_configtextarea($name, '<span class="rui-admin-no">'. $sliderindex . '</span>'. $title, $description, '', PARAM_TEXT);
                    $page->add($setting);
          }

$settings->add($page);