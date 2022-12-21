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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>


<?php require_once './components/header.php'; ?>

<body class="tl-body">
	<?php
	require_once './components/checkrole.php';
	echo '<br>';

	?>
	<div class="container ">
		<div class="row">
			<div class="span12">
				<div class="tl-onboarding-container">
					<div class="tl-onboarding-completed-screen active" data-onboard-screen="completed">
						<div class="d-row align-items-stretch">
							<div class="d-col tl-onboarding-end-item-cont">

								<a href="<?php echo $CFG->wwwroot . '/home.php' ?>" data-create-course-button>
									<div class="tl-onboarding-end-item tl-onboarding-end-item--hoverable">
										<div class="tl-onboarding-end-item_img">
											<img src="https://d3j0t7vrtr92dk.cloudfront.net/images/onboarding/final/CreateCourse.svg" width="100" height="100" alt="">
										</div>
										<div class="tl-onboarding-end-item_content">
											<b>Trang chủ</b>
										</div>
									</div>
								</a>


							</div>
							<div class="d-col tl-onboarding-end-item-cont">
								<a href="<?php echo $CFG->wwwroot . '/my/courses.php' ?>">
									<div class="tl-onboarding-end-item tl-onboarding-end-item--hoverable tl-onboarding-end-item--bordered">
										<div class="tl-onboarding-end-item_img">
											<img src="https://d3j0t7vrtr92dk.cloudfront.net/images/onboarding/final/SampleCourse.svg" width="100" height="100" alt="">
										</div>
										<div class="tl-onboarding-end-item_content">
											<b>Khoá học của tôi</b>
										</div>
									</div>
								</a>
							</div>
							<div class="d-col tl-onboarding-end-item-cont">
								<a href="<?php echo $CFG->wwwroot . '/course/edit.php?category=1&returnto=catmanage' ?>">
									<div class="tl-onboarding-end-item tl-onboarding-end-item--hoverable">
										<div class="tl-onboarding-end-item_img">
											<img src="https://d3j0t7vrtr92dk.cloudfront.net/images/onboarding/final/CustomizePortal.svg" width="100" height="100" alt="">
										</div>
										<div class="tl-onboarding-end-item_content">
											<b>Báo Cáo</b>
										</div>
									</div>
								</a>
							</div>
							<div class="d-col tl-onboarding-end-item-cont">
								<a href="<?php echo $CFG->wwwroot . '/admin/search.php' ?>">
									<div class="tl-onboarding-end-item tl-onboarding-end-item--hoverable">
										<div class="tl-onboarding-end-item_img">
											<img src="https://d3j0t7vrtr92dk.cloudfront.net/images/onboarding/final/Admin.svg" width="100" height="100" alt="">
										</div>
										<div class="tl-onboarding-end-item_content">
											<b>Administrator dashboard</b>
										</div>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Student -->

	<?php echo $OUTPUT->footer(); ?>

</body>

</html>