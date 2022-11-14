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
$block20wrapperalign = theme_alpha_get_setting('block20wrapperalign');
$block20count = theme_alpha_get_setting('block20count');
$block20wrapperbg = theme_alpha_get_setting('block20sliderwrapperbg');
$block20class = theme_alpha_get_setting('block20class');
$block20introtitle = format_text(theme_alpha_get_setting('block20introtitle'),FORMAT_HTML, array('noclean' => true));
$block20introcontent = format_text(theme_alpha_get_setting('block20introcontent'),FORMAT_HTML, array('noclean' => true));
$block20html = format_text(theme_alpha_get_setting('block20htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block20footer = format_text(theme_alpha_get_setting('block20footercontent'),FORMAT_HTML, array('noclean' => true));

$title = format_text(theme_alpha_get_setting("block20herotitle"),FORMAT_HTML, array('noclean' => true));
$caption = format_text(theme_alpha_get_setting("block20herocaption"),FORMAT_HTML, array('noclean' => true));
$css = theme_alpha_get_setting("block20herocss");
$img = $PAGE->theme->setting_file_url("block20videoposter", "block20videoposter");
$mp4 = $PAGE->theme->setting_file_url("block20videomp4", "block20videomp4");
$webm = $PAGE->theme->setting_file_url("block20videowebm", "block20videowebm");

if(theme_alpha_get_setting('showblock20sliderwrapper') == '1') {
    $class = 'rui-hero-content-backdrop';
} else {
    $class = '';
}
echo '<!-- Start Block #1 -->';
    echo '<div class="wrapper-xl rui-fp-block--20 rui-fp-margin-bottom '.$block20class.'">';

        if(!empty($block20introtitle) || !empty($block20introcontent)) {
        echo '<div class="wrapper-md">';
        }
        if(!empty($block20introtitle)) {
        echo '<h3 class="rui-block-title">'.$block20introtitle.'</h3>';
        }
        if(!empty($block20introcontent)) {
        echo '<div class="rui-block-desc">'.$block20introcontent.'</div>';
        }
        if(!empty($block20introtitle) || !empty($block20introcontent)) {
        echo '</div>';
        }

        echo '<div class="rui-hero-video">';
        echo '<div class="rui-hero-content '.$class.' rui-hero-content-position rui-hero-content-left rui-hero-content-backdrop">';
            if(!empty($title)) {
                echo '<h3 class="rui-hero-title">'.$title.'</h3>';
            }

            if(!empty($caption)) {
                echo '<div class="rui-hero-desc">'.$caption.'</div>';
            }
        echo '</div>';
        echo '</div>';

        echo $block20html;
        if(!empty($block20footer)) {
        echo '<div class="rui-block-footer wrapper-fw">'.$block20footer.'</div>';
        }
echo '</div>';
if(theme_alpha_get_setting("displayhrblock20") == '1') {
          echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 20 -->';

    

echo '<script src="theme/alpha/addons/vidbg/vidbg.js"></script>';
echo "<script>var instance = new vidbg('.rui-hero-video', {mp4: '".$mp4."',webm: '".$webm."',poster: '".$img."',})</script>";
