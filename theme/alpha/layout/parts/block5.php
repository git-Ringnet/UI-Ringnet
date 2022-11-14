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
$block5introtitle = format_text(theme_alpha_get_setting('block5introtitle'),FORMAT_HTML, array('noclean' => true));
$block5introcontent = format_text(theme_alpha_get_setting('block5introcontent'),FORMAT_HTML, array('noclean' => true));
$block5html = format_text(theme_alpha_get_setting('block5htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block5footer = format_text(theme_alpha_get_setting('block5footercontent'),FORMAT_HTML, array('noclean' => true));
$block5count = theme_alpha_get_setting('block5count');
$block5slidesperrow = theme_alpha_get_setting('block5slidesperrow');
$block5class = theme_alpha_get_setting('block5class');

echo '<!-- Start Block 5-->';
echo '<div class="wrapper-xl rui-fp-block--5 '.$block5class.'">';
$lang = $_GET['lang'];
	$text = current_language();
	if($text == "en"){
			echo '<h3 style="text-align: center; "><span style="font-size: 3rem; font-family: ui-sans-serif;">Partners</span><br></h3>';
	}else{
		
			echo '<h3 style="text-align: center; "><span style="font-size: 3rem; font-family: ui-sans-serif;">Hỗ trợ các hãng</span><br></h3>';
		
	}

          if(!empty($block5introtitle) || !empty($block5introcontent)) {
          echo '<div class="wrapper-md">';
          }
          if(!empty($block5introtitle)) {
                echo '<h3 class="rui-block-title">'.$block5introtitle.'</h3>';
          }
          if(!empty($block5introcontent)) {
                echo '<div class="rui-block-desc">'.$block5introcontent.'</div>';
          }
          if(!empty($block5introtitle) || !empty($block5introcontent)) {
          echo '</div>';
          }
          echo $block5html;
          echo '<div class="swiper swiper-block--5 mt-5">';
                    echo '<div class="swiper-wrapper mb-4">';

                    for ($i = 1; $i <= $block5count; $i++) {

                              $title = format_text(theme_alpha_get_setting("block5itemtitle" . $i),FORMAT_HTML, array('noclean' => true));
                              $url = format_text(theme_alpha_get_setting("block5itemurl" . $i),FORMAT_HTML, array('noclean' => true));
                              $img = $PAGE->theme->setting_file_url("block5itemimg" . $i, "block5itemimg" . $i);

                              echo '<div class="swiper-slide text-center">';
                                        echo '<img class="wrapper-xl rui-fp-block--5-item img-fluid rounded" src="'.$img.'" alt="'.$title.'" />';
                              echo '</div>';

                    }
                    echo '</div>';
                    echo '<div class="swiper-pagination"></div>';
          echo '</div>';
          if(!empty($block5footer)) {
          echo '<div class="rui-block-footer wrapper-fw">'.$block5footer.'</div>';
          }
echo '</div>';
if(theme_alpha_get_setting("displayhrblock5") == '1') {
          echo '<hr class="rui-block-hr" />';
}
echo '<script>var swiper5=new Swiper(".swiper-block--5",{slidesPerView:1,alphaBetween:40,keyboard:{enabled:!0},pagination:{el:".swiper-pagination",clickable:!0},mousewheel:{releaseOnEdges:!0},breakpoints:{320:{slidesPerView:2},768:{slidesPerView:3},1100:{slidesPerView:'.$block5slidesperrow.'}}});</script>';
echo '<!-- End Block 5 -->';