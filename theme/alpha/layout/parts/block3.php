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
$block3wrapperalign = theme_alpha_get_setting('block3wrapperalign');
$block3count = theme_alpha_get_setting('block3count');
$block3wrapperbg = theme_alpha_get_setting('block3sliderwrapperbg');
$block3class = theme_alpha_get_setting('block3class');
$block3html = format_text(theme_alpha_get_setting('block3htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block3footer = format_text(theme_alpha_get_setting('block3footercontent'),FORMAT_HTML, array('noclean' => true));

$title = format_text(theme_alpha_get_setting("block3herotitle"),FORMAT_HTML, array('noclean' => true));
$caption = format_text(theme_alpha_get_setting("block3herocaption"),FORMAT_HTML, array('noclean' => true));
$css = theme_alpha_get_setting("block3herocss");
$img = $PAGE->theme->setting_file_url("block3img", "block3img");

if(theme_alpha_get_setting('showblock3sliderwrapper') == '1') {
    $class = 'rui-hero-content-backdrop';
} else {
    $class = '';
}
echo '<!-- Start Block #1 -->';
    echo '<div class="wrapper-fw rui-fp-block--3 rui-fp-margin-bottom '.$block3class.'">';

        echo '<div class="rui-hero-img">';
            echo '<div class="rui-hero-content rui-hero-content--img '.$class.' rui-hero-content-position rui-hero-content-left rui-hero-content-backdrop">';
                if(!empty($title)) {
                    echo '<h3 class="rui-hero-title">'.$title.'</h3>';
                }

                if(!empty($caption)) {
                    echo '<div class="rui-hero-desc">'.$caption.'</div>';
                }
            echo '</div>';
            echo '<img class="d-flex img-fluid w-100" src="'.$img.'" alt="'.$title.'" />';
        echo '</div>';

        echo $block3html;
        if(!empty($block3footer)) {
        echo '<div class="rui-block-footer wrapper-md">'.$block3footer.'</div>';
        }
echo '</div>';
if(theme_alpha_get_setting("displayhrblock3") == '1') {
          echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 20 -->';
    
echo '<script>function reportWindowSize(){for(var e=document.getElementsByClassName("rui-hero-content--img"),o=0,t=0|e.length;o<t;o=o+1|0){var n=e[o].offsetHeight;e[o].style.top="calc(50% - "+n/2+"px)"}}window.addEventListener("resize",reportWindowSize),window.onload=reportWindowSize();</script>';