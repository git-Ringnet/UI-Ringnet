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

$page = new admin_settingpage('theme_alpha_customization', get_string('settingscustomization', 'theme_alpha'));
          $name = 'theme_alpha/hgooglefont';
          $heading = get_string('hgooglefont', 'theme_alpha');
          $setting = new admin_setting_heading($name, $heading, format_text(get_string('hgooglefont_desc', 'theme_alpha'), FORMAT_MARKDOWN));
          $page->add($setting);

          // Google Font.
          $name = 'theme_alpha/googlefonturl';
          $title = get_string('googlefonturl', 'theme_alpha');
          $description = get_string('googlefonturl_desc', 'theme_alpha');
          $default = 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700&display=swap';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          /*$name = 'theme_alpha/hfontsettings';
          $heading = get_string('hfontsettings', 'theme_alpha');
          $setting = new admin_setting_heading($name, $heading, format_text(get_string('hfontsettings_desc', 'theme_alpha'), FORMAT_MARKDOWN));
          $page->add($setting);
          */
          $name = 'theme_alpha/fontheadings';
          $title = get_string('fontheadings', 'theme_alpha');
          $description = get_string('fontheadings_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/fontweightheadings';
          $title = get_string('fontweightheadings', 'theme_alpha');
          $description = get_string('fontweightheadings_desc', 'theme_alpha');
          $default = '700';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/fontbody';
          $title = get_string('fontbody', 'theme_alpha');
          $description = get_string('fontbody_desc', 'theme_alpha');
          $default = "'Be Vietnam Pro', sans-serif";
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/fontweightregular';
          $title = get_string('fontweightregular', 'theme_alpha');
          $description = get_string('fontweightregular_desc', 'theme_alpha');
          $default = '400';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/fontweightmedium';
          $title = get_string('fontweightmedium', 'theme_alpha');
          $description = get_string('fontweightmedium_desc', 'theme_alpha');
          $default = '500';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/fontweightbold';
          $title = get_string('fontweightbold', 'theme_alpha');
          $description = get_string('fontweightbold_desc', 'theme_alpha');
          $default = '700';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/hgeneral';
          $heading = get_string('hgeneral', 'theme_alpha');
          $setting = new admin_setting_heading($name, $heading, format_text(get_string('hgeneral_desc', 'theme_alpha'), FORMAT_MARKDOWN));
          $page->add($setting);

          $name = 'theme_alpha/colorbodybg';
          $title = get_string('colorbodybg', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorborder';
          $title = get_string('colorborder', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/btnborderradius';
          $title = get_string('btnborderradius', 'theme_alpha');
          $description = get_string('empty_desc', 'theme_alpha');
          $setting = new admin_setting_configtext($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/hcolorstxt';
          $heading = get_string('hcolorstxt', 'theme_alpha');
          $setting = new admin_setting_heading($name, $heading, format_text(get_string('hcolorstxt_desc', 'theme_alpha'), FORMAT_MARKDOWN));
          $page->add($setting);

          $name = 'theme_alpha/colorbody';
          $title = get_string('colorbody', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorbodysecondary';
          $title = get_string('colorbodysecondary', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorbodylight';
          $title = get_string('colorbodylight', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorheadings';
          $title = get_string('colorheadings', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorlink';
          $title = get_string('colorlink', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorlinkhover';
          $title = get_string('colorlinkhover', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/hcolorsprimary';
          $heading = get_string('hcolorsprimary', 'theme_alpha');
          $setting = new admin_setting_heading($name, $heading, format_text(get_string('hcolorsprimary_desc', 'theme_alpha'), FORMAT_MARKDOWN));
          $page->add($setting);

          $name = 'theme_alpha/colorprimary600';
          $title = get_string('colorprimary600', 'theme_alpha');
          $description = get_string('colorprimary_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);
          
          $name = 'theme_alpha/colorprimary100';
          $title = get_string('colorprimary100', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary200';
          $title = get_string('colorprimary200', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary300';
          $title = get_string('colorprimary300', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary400';
          $title = get_string('colorprimary400', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary500';
          $title = get_string('colorprimary500', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary700';
          $title = get_string('colorprimary700', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary800';
          $title = get_string('colorprimary800', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorprimary900';
          $title = get_string('colorprimary900', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/hcolorsgrays';
          $heading = get_string('hcolorsgrays', 'theme_alpha');
          $setting = new admin_setting_heading($name, $heading, format_text(get_string('hcolorsgrays_desc', 'theme_alpha'), FORMAT_MARKDOWN));
          $page->add($setting);

          $name = 'theme_alpha/colorgray100';
          $title = get_string('colorgray100', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray200';
          $title = get_string('colorgray200', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray300';
          $title = get_string('colorgray300', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray400';
          $title = get_string('colorgray400', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray500';
          $title = get_string('colorgray500', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray600';
          $title = get_string('colorgray600', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray700';
          $title = get_string('colorgray700', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray800';
          $title = get_string('colorgray800', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);

          $name = 'theme_alpha/colorgray900';
          $title = get_string('colorgray900', 'theme_alpha');
          $description = get_string('color_desc', 'theme_alpha');
          $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
          $setting->set_updatedcallback('theme_reset_all_caches');
          $page->add($setting);


$settings->add($page);