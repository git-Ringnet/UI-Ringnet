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

// Variables - Settings
$block13introtitle = format_text(theme_alpha_get_setting('block13introtitle'),FORMAT_HTML, array('noclean' => true));
$block13introcontent = format_text(theme_alpha_get_setting('block13introcontent'),FORMAT_HTML, array('noclean' => true));
$block13html = format_text(theme_alpha_get_setting('block13htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block13footer = format_text(theme_alpha_get_setting('block13footercontent'),FORMAT_HTML, array('noclean' => true));
$block13css = theme_alpha_get_setting('block13customcss');
$block13img  = $PAGE->theme->setting_file_url("block13bg", "block13bg");
$block13class = theme_alpha_get_setting('block13class');

echo '<!-- Start Block 13 -->';
if(!empty($block13img)) {
   echo '<div class="wrapper-xl rui-fp-block--13 rui-fp-block-mt rui-fp-block-mb rounded '.$block13class.'" style="background-image: url(' . $block13img . '); ' . $block13css . '">';
} else {
   echo '<div class="wrapper-xl rui-fp-block--13 rui-fp-block-mt rui-fp-block-mb rounded" style="' . $block13css . '">';
}
          echo '<div class="rui-cta-wrapper wrapper-md row">';
                    echo '<div class="col m-auto">';
                              if(!empty($block13introtitle)) { echo '<h3 class="rui-cta-title lead-2">'.$block13introtitle.'</h3>'; }
                              if(!empty($block13introcontent)) { echo '<div class="rui-cta-content">'.$block13introcontent.'</div>'; }
                              echo $block13html;
                              if(!empty($block13footer)) { echo '<div class="rui-cta-small">'.$block13footer .'</div>'; }
                    echo '</div>';
          echo '</div>';
echo '</div>';
if(theme_alpha_get_setting("displayhrblock13") == '1') {
   echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 13 -->';