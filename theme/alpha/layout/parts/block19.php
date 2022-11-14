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
$block19introtitle = format_text(theme_alpha_get_setting('block19introtitle'),FORMAT_HTML, array('noclean' => true));
$block19introcontent = format_text(theme_alpha_get_setting('block19introcontent'),FORMAT_HTML, array('noclean' => true));
$block19html = format_text(theme_alpha_get_setting('block19htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block19footer = format_text(theme_alpha_get_setting('block19footercontent'),FORMAT_HTML, array('noclean' => true));
$block19css = theme_alpha_get_setting('block19customcss');
$block19img  = $PAGE->theme->setting_file_url("block19bg", "block19bg");
$block19textalign = theme_alpha_get_setting('block19textalign');
$block19class = theme_alpha_get_setting('block19class');

echo '<!-- Start Block 19 -->';
if(!empty($block19img)) {
   echo '<div class="wrapper-xl rui-fp-block--19 rui-fp-block-mt rui-fp-block-mb rounded '.$block19class.'" style="background-image: url(' . $block19img . '); ' . $block19css . '">';
} else {
   echo '<div class="wrapper-xl rui-fp-block--19 rui-fp-block-mt rui-fp-block-mb rounded" style="' . $block19css . '">';
}
          echo '<div class="rui-cta-wrapper wrapper-md text-' . $block19textalign . '">';
                    echo '<h3 class="rui-cta-title">'.$block19introtitle.'</h3>';
                    echo '<div class="rui-cta-content">'.$block19introcontent.'</div>';
                    echo $block19html;
                    echo '<div class="rui-cta-small">'.$block19footer .'</div>';
          echo '</div>';
echo '</div>';
if(theme_alpha_get_setting("displayhrblock19") == '1') {
   echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 19 -->';