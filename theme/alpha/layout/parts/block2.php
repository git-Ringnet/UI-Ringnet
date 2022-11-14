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
$block2wrapperalign = theme_alpha_get_setting('block2wrapperalign');
$block2count = theme_alpha_get_setting('block2count');
$block2wrapperbg = theme_alpha_get_setting('block2sliderwrapperbg');
$block2class = theme_alpha_get_setting('block2class');
$block2introtitle = format_text(theme_alpha_get_setting('block2introtitle'),FORMAT_HTML, array('noclean' => true));
$block2introcontent = format_text(theme_alpha_get_setting('block2introcontent'),FORMAT_HTML, array('noclean' => true));
$block2html = format_text(theme_alpha_get_setting('block2htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block2footer = format_text(theme_alpha_get_setting('block2footercontent'),FORMAT_HTML, array('noclean' => true));

$title = format_text(theme_alpha_get_setting("block2herotitle"),FORMAT_HTML, array('noclean' => true));
$caption = format_text(theme_alpha_get_setting("block2herocaption"),FORMAT_HTML, array('noclean' => true));
$css = theme_alpha_get_setting("block2herocss");
$img = $PAGE->theme->setting_file_url("block2videoposter", "block2videoposter");
$mp4 = $PAGE->theme->setting_file_url("block2videomp4", "block2videomp4");
$webm = $PAGE->theme->setting_file_url("block2videowebm", "block2videowebm");

if(theme_alpha_get_setting('showblock2sliderwrapper') == '1') {
    $class = 'rui-hero-content-backdrop';
} else {
    $class = '';
}
echo '<!-- Start Block #1 -->';
    echo '<div class="wrapper-fw rui-fp-block--2 rui-fp-margin-bottom '.$block2class.'">';

        if(!empty($block2introtitle) || !empty($block2introcontent)) {
        echo '<div class="wrapper-md">';
        }
        if(!empty($block2introtitle)) {
        echo '<h3 class="rui-block-title">'.$block2introtitle.'</h3>';
        }
        if(!empty($block2introcontent)) {
        echo '<div class="rui-block-desc">'.$block2introcontent.'</div>';
        }
        if(!empty($block2introtitle) || !empty($block2introcontent)) {
        echo '</div>';
        }

        echo '<div class="rui-hero-video">';
        echo '<div class="rui-hero-content rui-hero-content--video '.$class.' rui-hero-content-position rui-hero-content-left rui-hero-content-backdrop">';
            if(!empty($title)) {
                echo '<h3 class="rui-hero-title">'.$title.'</h3>';
            }

            if(!empty($caption)) {
                echo '<div class="rui-hero-desc">'.$caption.'</div>';
            }
        echo '</div>';
        echo '</div>';

        echo $block2html;
        if(!empty($block2footer)) {
        echo '<div class="rui-block-footer wrapper-fw">'.$block2footer.'</div>';
        }
echo '</div>';
if(theme_alpha_get_setting("displayhrblock2") == '1') {
          echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 20 -->';
    

echo '<script src="theme/alpha/addons/vidbg/vidbg.js"></script>';
echo "<script>var instance = new vidbg('.rui-hero-video', {mp4: '".$mp4."',webm: '".$webm."',poster: '".$img."',})</script>";
echo '<script>function reportWindowSize(){for(var e=document.getElementsByClassName("rui-hero-content--video"),o=0,t=0|e.length;o<t;o=o+1|0){var n=e[o].offsetHeight;e[o].style.top="calc(50% - "+n/2+"px)"}}window.addEventListener("resize",reportWindowSize),window.onload=reportWindowSize();</script>';