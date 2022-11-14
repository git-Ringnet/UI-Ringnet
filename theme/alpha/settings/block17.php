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

$page = new admin_settingpage('theme_alpha_block17', get_string('settingsblock17', 'theme_alpha'));

          $name = 'theme_alpha/displayblock17';
          $title = get_string('turnon', 'theme_alpha');
          $description = get_string('displayblock17_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/displayhrblock17';
          $title = get_string('displayblockhr', 'theme_alpha');
          $description = get_string('displayblockhr_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block17class';
          $title = get_string('additionalclass', 'theme_alpha');
          $description = get_string('additionalclass_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block17introtitle';
          $title = get_string('blockintrotitle', 'theme_alpha');
          $description = get_string('blockintrotitle_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block17introcontent';
          $title = get_string('blockintrocontent', 'theme_alpha');
          $description = get_string('blockintrocontent_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block17htmlcontent';
          $title = get_string('blockhtmlcontent', 'theme_alpha');
          $description = get_string('blockhtmlcontent_desc', 'theme_alpha');
          $default = '<!-- Start - Block - Stats #2 -->
          <!-- Stats -->
          <div class="wrapper-fw rui-block-stats-1">
              <div class="w-lg-85 mx-lg-auto">
                  <div class="row align-items-md-center col-md-divider">
                      <div class="col-md-4">
                          <div class="pr-lg-5 border-right">
                              <div class="d-flex align-items-end">
                                  <span class="display-2 text-primary">100%</span>
                                  <span class="badge-sm badge-primary mb-4 ml-2">50% less CSS - 1.1MB<br></span>
                              </div>
                              <p>faster then previous version of the alpha theme.</p>
                          </div>
                      </div>
                      <!-- End Col -->
          
                      <div class="col-md-8 pt-5 pt-md-0">
                          <div class="pl-lg-5">
                              <div class="row">
                                  <div class="col-sm">
                                      <span class="h2 text-primary">1600+</span>
                                      <p>customers using alpha Moodle theme</p>
                                  </div>
                                  <!-- End Col -->
          
                                  <div class="col-sm"><span class="h2 text-primary">5 stars</span>
                                      <p>people love this theme!</p>
                                  </div>
                                  <!-- End Col -->
          
                                  <div class="col-sm">
                                      <span class="h2 text-primary">2.0</span>
                                      <p>completely redesigned, dedicated for Moodle 4.0</p>
                                  </div>
                                  <!-- End Col -->
                              </div>
                              <!-- End Row -->
                          </div>
                      </div>
                  </div>
              </div>
              <!-- End Row -->
          </div>
          <!-- End Stats -->
          <!-- End - Block - Stats #1 -->';
          $setting = new alpha_setting_confightmleditor($name, $title, $description, $default);
          $page->add($setting);


          $name = 'theme_alpha/block17footercontent';
          $title = get_string('blockfootercontent', 'theme_alpha');
          $description = get_string('blockfootercontent_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

$settings->add($page);