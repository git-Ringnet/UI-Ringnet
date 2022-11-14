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

$page = new admin_settingpage('theme_alpha_block18', get_string('settingsblock18', 'theme_alpha'));

          $name = 'theme_alpha/displayblock18';
          $title = get_string('turnon', 'theme_alpha');
          $description = get_string('displayblock18_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/displayhrblock18';
          $title = get_string('displayblockhr', 'theme_alpha');
          $description = get_string('displayblockhr_desc', 'theme_alpha');
          $default = 1;
          $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block18class';
          $title = get_string('additionalclass', 'theme_alpha');
          $description = get_string('additionalclass_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtext($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block18introtitle';
          $title = get_string('blockintrotitle', 'theme_alpha');
          $description = get_string('blockintrotitle_desc', 'theme_alpha');
          $default = '';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block18introcontent';
          $title = get_string('blockintrocontent', 'theme_alpha');
          $description = get_string('blockintrocontent_desc', 'theme_alpha');
          $default = '<div class="wrapper-md mb-5"><h3 class="rui-block-title">Team</h3><div class="rui-block-desc text-center">It prepares them to thrive in today’s world — and to shape tomorrow’s. Apple is constantly creating resources to help educators do just that. Not only powerful products, but also tools, inspiration, and curricula to create magical learning experiences and make every moment of screen time worth it.</div></div>';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block18htmlcontent';
          $title = get_string('blockhtmlcontent', 'theme_alpha');
          $description = get_string('blockhtmlcontent_desc', 'theme_alpha');
          $default = '<!-- Start - Block - Team #2 -->
          <div class="wrapper-fw rui-block-team-2">
              <div class="row">
          
                  <!-- Start item -->
                  <div class="rui-block-team-item text-left col-md-3 px-4 text-center">
                      <img src="https://roseathemes.com/alpha/1/pluginfile.php/1/theme_alpha/alphasettingsimgs/0/avatar-4.png" alt="Team #1" width="440" height="440" class="img-fluid atto_image_button_middle">
                      <h4 class="lead-4 mt-3 mb-0">Adam Smith</h4>
                      <div class="rui-block-text--3 rui-block-text--light">Senior Coordinator for Faculty Support</div>
                      <div class="rui-block-text--2 mt-2">From its medieval origins to the digital era, learn everything there is to know about the ubiquitous lorem ipsum passage.</div>
                  </div>
                  <!-- End item -->
          
                  <!-- Start item -->
                  <div class="rui-block-team-item text-left col-md-3 px-4 text-center">
                      <img src="https://roseathemes.com/alpha/1/pluginfile.php/1/theme_alpha/alphasettingsimgs/0/avatar-3.png" alt="Team #1" width="440" height="440" class="img-fluid atto_image_button_middle">
                      <h4 class="lead-4 mt-3 mb-0">Christa McAuliffe</h4>
                      <div class="rui-block-text--3 rui-block-text--light">Program Assistant, Middle East Professional Learning Initiative</div>
                      <div class="rui-block-text--2 mt-2">Lorem ipsum began as scrambled, nonsensical Latin derived from Ciceros 1st-century BC text De Finibus Bonorum et Malorum</div>
                  </div>
                  <!-- End item -->
          
                  <!-- Start item -->
                  <div class="rui-block-team-item text-left col-md-3 px-4 text-center">
                      <img src="https://roseathemes.com/alpha/1/pluginfile.php/1/theme_alpha/alphasettingsimgs/0/avatar-2.png" alt="ku" width="440" height="440" class="img-fluid atto_image_button_text-bottom">
                      <h4 class="lead-4 mt-3 mb-0">Helen Keller</h4>
                      <div class="rui-block-text--3 rui-block-text--light">IT Service Center Support Technician</div>
                      <div class="rui-block-text--2 mt-2">The placeholder text, beginning with the line “Lorem ipsum dolor sit amet, consectetur adipiscing elit”, looks like Latin because in its youth, centuries ago, it was Latin.</div>
                  </div>
                  <!-- End item -->
          
                  <!-- Start item -->
                  <div class="rui-block-team-item text-left col-md-3 px-4 text-center">
                      <img src="https://roseathemes.com/alpha/1/pluginfile.php/1/theme_alpha/alphasettingsimgs/0/avatar-5.png" alt="team #3" width="1320" height="1320" class="img-fluid atto_image_button_text-bottom">
                      <h4 class="lead-4 mt-3 mb-0">Mark Twain</h4>
                      <div class="rui-block-text--3 rui-block-text--light">Audio Visual Technology Infrastructure Specialist</div>
                      <div class="rui-block-text--2 mt-2">Lorem ipsum was purposefully designed to have no meaning, but appear like real text, making it the perfect placeholder.</div>
                  </div>
                  <!-- End item -->
          
              </div>
          </div>
          <!-- End - Block - Team #2 -->';
          $setting = new alpha_setting_confightmleditor($name, $title, $description, $default);
          $page->add($setting);

          $name = 'theme_alpha/block18footercontent';
          $title = get_string('blockfootercontent', 'theme_alpha');
          $description = get_string('blockfootercontent_desc', 'theme_alpha');
          $default = '<hr class="hr-small" />
          <div class="wrapper-md mt-5 text-center">
              <h4 class="lead-4">Join our team!</h4>
              <div class="rui-block-text--2">Help us on our quest to make good software even better.</div>
              <a href="#" class="btn btn-link">See courrent openings</a>
          </div>';
          $setting = new admin_setting_configtextarea($name, $title, $description, $default);
          $page->add($setting);

$settings->add($page);