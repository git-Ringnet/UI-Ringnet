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
$block1wrapperalign = theme_alpha_get_setting('block1wrapperalign');
$block1count = theme_alpha_get_setting('block1count');
$block1wrapperbg = theme_alpha_get_setting('block1sliderwrapperbg');
$block1class = theme_alpha_get_setting('block1class');

if(theme_alpha_get_setting('showblock1sliderwrapper') == '1') {
    $class = 'rui-hero-content-backdrop';
} else {
    $class = '';
}

echo '<!-- Start Block #1 -->';
    echo '<div class="wrapper-xl rui-fp-block--1 rui-fp-margin-bottom '.$block1class.'">';
        echo '<div class="swiper swiper-block--1 pb-0">';
            echo '<div class="swiper-wrapper">';

                for ($i = 1; $i <= $block1count; $i++) {

                    $title = format_text(theme_alpha_get_setting("block1slidetitle" . $i),FORMAT_HTML, array('noclean' => true));
                    $caption = format_text(theme_alpha_get_setting("block1slidecaption" . $i),FORMAT_HTML, array('noclean' => true));
                    $css = theme_alpha_get_setting("block1slidecss" . $i);
                    $img = $PAGE->theme->setting_file_url("block1slideimg" . $i, "block1slideimg" . $i);

                    if(!empty($css)) {
                        echo '<div class="rui-hero-bg swiper-slide">';
                    } else {
                        echo '<div class="rui-hero-bg swiper-slide" style="'.$css.'">';
                    }

                    echo '<div class="rui-hero-content rui-hero--slide '.$class.' rui-hero-content-position rui-hero-content-left">';
                        if(!empty($title)) {
                            echo '<h3 class="rui-hero-title">'.$title.'</h3>';
                        }

                        if(!empty($caption)) {
                            echo '<div class="rui-hero-desc">'.$caption.'</div>';
                        }
                    echo '</div>';
                    echo '<img class="d-flex img-fluid w-100" src="'.$img.'" alt="'.$title.'" />';
                    echo '</div>';

                }

            echo '</div>';
            echo '<div class="d-none d-md-flex swiper-button-next"></div>';
            echo '<div class="d-none d-md-flex swiper-button-prev"></div>';
            echo '<div class="swiper-pagination"></div>';
        echo '</div>';
    echo '</div>';

    /*
        function reportWindowSize() {
            // Align center content of the hero
            var el = document.getElementsByClassName("rui-hero-content");
            for (var i=0, len=el.length|0; i<len; i=i+1|0) {
                var sidebarContentHeight = el[i].offsetHeight;
                el[i].style.top = "calc(50% - " + sidebarContentHeight * 0.5+ "px)";
            }
        }

        window.addEventListener("resize", reportWindowSize);
        window.onload = reportWindowSize();
    */
    echo '<script>function reportWindowSize(){for(var e=document.getElementsByClassName("rui-hero--slide"),o=0,t=0|e.length;o<t;o=o+1|0){var n=e[o].offsetHeight;e[o].style.top="calc(50% - "+n/2+"px)"}}window.addEventListener("resize",reportWindowSize),window.onload=reportWindowSize();</script>';
echo '<!-- End Block #1 -->';

echo '<script>var swiper=new Swiper(".swiper-block--1",{slidesPerView:1,pagination:{el:".swiper-pagination",type:"progressbar"},navigation:{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"},keyboard:{enabled:!0},mousewheel:{releaseOnEdges:!0},effect:"creative",autoHeight:!0,creativeEffect:{prev:{shadow:!0,translate:["-20%",0,-1]},next:{translate:["100%",0,0]}},breakpoints:{}});</script>';
