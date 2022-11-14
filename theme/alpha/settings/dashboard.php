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

// Dashboard settings.
$page = new admin_settingpage('theme_alpha_settingsdashboard', get_string('settingsdashboard', 'theme_alpha'));

	$name = 'theme_alpha/setdashboardlayout';
	$title = get_string('setdashboardlayout', 'theme_alpha');
	$description = get_string('setdashboardlayout_desc', 'theme_alpha');
	$options = [];
	$options[1] = get_string('loginlayout1', 'theme_alpha');
	$options[2] = get_string('loginlayout2', 'theme_alpha');
	$options[3] = get_string('loginlayout3', 'theme_alpha');
	$setting = new admin_setting_configselect($name, $title, $description, 2, $options);
	$page->add($setting);

	$name = 'theme_alpha/customdcolsize';
	$title = get_string('customdcolsize', 'theme_alpha');
	$description = get_string('customdcolsize_desc', 'theme_alpha');
	$default = '';
	$setting = new admin_setting_configtext($name, $title, $description, $default);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$page->add($setting);

$settings->add($page);