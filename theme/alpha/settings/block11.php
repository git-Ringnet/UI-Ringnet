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

$page = new admin_settingpage('theme_alpha_block11', get_string('settingsblock11', 'theme_alpha'));

          $name = 'theme_alpha/displayblock11';
          $title = get_string('turnon', 'theme_alpha');
          $description = get_string('displayblock11_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/displayhrblock11';
          $title = get_string('displayblockhr', 'theme_alpha');
          $description = get_string('displayblockhr_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block11class';
          $title = get_string('additionalclass', 'theme_alpha');
          $description = get_string('additionalclass_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block11introtitle';
          $title = get_string('blockintrotitle', 'theme_alpha');
          $description = get_string('blockintrotitle_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block11introcontent';
          $title = get_string('blockintrocontent', 'theme_alpha');
          $description = get_string('blockintrocontent_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block11htmlcontent';
          $title = get_string('blockhtmlcontent', 'theme_alpha');
          $description = get_string('blockhtmlcontent_desc', 'theme_alpha');
          $default = '<!-- Start item -->
          <div class="wrapper-xxl row m-0">
              <!-- Col #1 -->
              <div class="col-lg-5 pr-lg-6 mr-lg-3 align-self-center">
                  <div class="rui-card--blank">
                      <div class="rui-icon-box rui-icon-box--primary mb-3">
                          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M4.75 10L12 5.75L19.2501 10L12 14.25L4.75 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                              <path d="M12.5 10C12.5 10.2761 12.2761 10.5 12 10.5C11.7239 10.5 11.5 10.2761 11.5 10C11.5 9.72386 11.7239 9.5 12 9.5C12.2761 9.5 12.5 9.72386 12.5 10Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                              <path d="M6.75 11.5V16.25C6.75 16.25 8 18.25 12 18.25C16 18.25 17.25 16.25 17.25 16.25V11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                          </svg>
                      </div>
                      <h4 class="lead-3 mt-3">Get real-time insights on your performance.</h4>
                      <p class="rui-card-text">Auto-generated reports: Get accurate insights on your performance with auto-generated reports that help create successful campaigns.</p>
                      <ul class="rui-special-list rui-special-list--light">
                          <li>Personal asset watchlists</li>
                          <li>Curated market data feed</li>
                          <li>20+ on-chain, social &amp; dev metrics</li>
                          <li>Low-latency market signals and alerts</li>
                          <li class="list-icon-x">Sansheets plugin with pre-made templates</li>
                      </ul>
                      <a href="https://rosea.io" class="rui-card-btn-link mt-2">Learn more about the Smart Keyboard</a>
                  </div>
              </div>
              <!-- End Col #1 -->
          
              <!-- Col #2 -->
              <div class="col mt-sm-5 mt-lg-0 rui-img--rounded-fluid">
                  <img src="https://demo.rosea.io/baz/1/pluginfile.php/1/theme_baz/bazsettingsimgs/0/windows-72LU8QlGs5E-unsplash.jpg" alt="pic" width="800" height="533">
              </div>
              <!-- End Col #2 -->
          </div>
          <!-- End item -->
          
          <!-- Start item -->
          <hr class="hr-small">
          
          <div class="wrapper-xxl row no-gutters mt-5">
          
              <!-- Col #1 -->
              <div class="col mt-sm-5 mt-lg-0 rui-img--rounded-fluid">
                  <img src="https://demo.rosea.io/baz/1/pluginfile.php/1/theme_baz/bazsettingsimgs/0/img.jpg" alt="pic" width="800" height="533">
              </div>
              <!-- End Col #1 -->
          
              <!-- Col #2 -->
              <div class="col-lg-5 pl-lg-6 ml-lg-3 align-self-center">
                  <div class="rui-card--blank">
                      <div class="rui-icon-box rui-icon-box--primary mb-3">
                          <svg width="32" height="32" fill="none" viewBox="0 0 24 24">
                              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 5.75C19.25 5.19772 18.8023 4.75 18.25 4.75H14C12.8954 4.75 12 5.64543 12 6.75V19.25L12.8284 18.4216C13.5786 17.6714 14.596 17.25 15.6569 17.25H18.25C18.8023 17.25 19.25 16.8023 19.25 16.25V5.75Z"></path>
                              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 5.75C4.75 5.19772 5.19772 4.75 5.75 4.75H10C11.1046 4.75 12 5.64543 12 6.75V19.25L11.1716 18.4216C10.4214 17.6714 9.40401 17.25 8.34315 17.25H5.75C5.19772 17.25 4.75 16.8023 4.75 16.25V5.75Z"></path>
                          </svg>
                      </div>
                      <h4 class="lead-3 mt-3">Get real-time insights on your performance.</h4>
                      <p class="rui-card-text">Auto-generated reports: Get accurate insights on your performance with auto-generated reports that help create successful campaigns.</p>
                      <ul class="rui-special-list rui-special-list--primary">
                          <li>Personal asset watchlists</li>
                          <li>Curated market data feed</li>
                          <li>20+ on-chain, social &amp; dev metrics</li>
                          <li>Low-latency market signals and alerts</li>
                          <li class="list-icon-x">Sansheets plugin with pre-made templates</li>
                      </ul>
                      <a href="https://rosea.io" class="rui-card-btn-link mt-2">Learn more about the Smart Keyboard</a>
                  </div>
              </div>
              <!-- End Col #2 -->
          
          </div>
          <!-- End item -->';
          $setting = new alpha_setting_confightmleditor($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block11footercontent';
          $title = get_string('blockfootercontent', 'theme_alpha');
          $description = get_string('blockfootercontent_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

$settings->add($page);