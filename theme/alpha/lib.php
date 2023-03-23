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
 * Theme functions.
 *
 * @package   theme_alpha
 * @copyright 2022 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get theme setting
 *
 * @param string $setting
 * @param bool $format
 * @return string
 */
function theme_alpha_get_setting($setting, $format = false) {
    $theme = theme_config::load('alpha');

    if (empty($theme->settings->$setting)) {
        return false;
    }

    if (!$format) {
        return $theme->settings->$setting;
    }

    if ($format === 'format_text') {
        return format_text($theme->settings->$setting, FORMAT_PLAIN);
    }

    if ($format === 'format_html') {
        return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true, 'noclean' => true));
    }

    return format_string($theme->settings->$setting);
}

/**
 * Post process the CSS tree.
 *
 * @param string $tree The CSS tree.
 * @param theme_config $theme The theme config object.
 */
function theme_alpha_css_tree_post_processor($tree, $theme) {
    error_log('theme_alpha_css_tree_post_processor() is deprecated. Required' .
        'prefixes for Bootstrap are now in theme/alpha/scss/moodle/prefixes.scss');
    $prefixer = new theme_alpha\autoprefixer($tree);
    $prefixer->prefix();
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_alpha_get_extra_scss($theme) {
    $content = '';

    // Sets the login background image.
    $loginbackgroundimageurl = $theme->setting_file_url('loginbg', 'loginbg');
    if (!empty($loginbackgroundimageurl)) {
        $content .= 'body.pagelayout-login { ';
        $content .= "background-image: url('$loginbackgroundimageurl'); background-size: cover;";   
        $content .= ' }';
    }

    // Always return the background image with the scss when we have it.
    return !empty($theme->settings->scss) ? $theme->settings->scss . ' ' . $content : $content;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_alpha_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('alpha');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        if ($filearea === 'favicon') {
            return $theme->setting_file_serve('favicon', $args, $forcedownload, $options);
        } else if ($filearea === 'logo') {
            return $theme->setting_file_serve('logo', $args, $forcedownload, $options); 
        } else if ($filearea === 'loginbg') {
            return $theme->setting_file_serve('loginbg', $args, $forcedownload, $options);  
        } else if ($filearea === 'customloginlogo') {
            return $theme->setting_file_serve('customloginlogo', $args, $forcedownload, $options);            
        } else if ($filearea === 'customlogo') {
            return $theme->setting_file_serve('customlogo', $args, $forcedownload, $options);
        } else if ($filearea === 'customdmlogo') {
            return $theme->setting_file_serve('customdmlogo', $args, $forcedownload, $options);    
        } else if ($filearea === 'customsidebarlogo') {
            return $theme->setting_file_serve('customsidebarlogo', $args, $forcedownload, $options);  
        } else if ($filearea === 'customsidebardmlogo') {
            return $theme->setting_file_serve('customsidebardmlogo', $args, $forcedownload, $options);                
        } else if ($filearea === 'fontfiles') {
            return $theme->setting_file_serve('fontfiles', $args, $forcedownload, $options);
        } else if ($filearea === 'alphasettingsimgs') {
            return $theme->setting_file_serve('alphasettingsimgs', $args, $forcedownload, $options);
        } else if (preg_match("/^block1slideimg[1-9][0-9]?$/", $filearea)!== false) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if (preg_match("/^block5itemimg[1-9][0-9]?$/", $filearea)!== false) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);    
        } else if ($filearea === 'block2videoposter') {
            return $theme->setting_file_serve('block2videoposter', $args, $forcedownload, $options);  
        } else if ($filearea === 'block2img') {
            return $theme->setting_file_serve('block2img', $args, $forcedownload, $options);  
        } else if ($filearea === 'block2videomp4') {
            return $theme->setting_file_serve('block2videomp4', $args, $forcedownload, $options);  
        } else if ($filearea === 'block2videowebm') {
            return $theme->setting_file_serve('block2videowebm', $args, $forcedownload, $options);                                    
        } else if ($filearea === 'footerbgimg') {
            return $theme->setting_file_serve('footerbgimg', $args, $forcedownload, $options);                                    
        } else {
            send_file_not_found();
        }
    } 
}

/**
 * Get the URL of files from theme settings.
 *
 * @param $setting
 * @param $filearea
 * @param $theme
 * @return array|false|string|string[]|null
 * @throws dml_exception
 */
function theme_alpha_setting_file_url($setting, $filearea, $theme) {
    global $CFG;

    $component  = 'theme_alpha';
    $itemid     = 0;
    $filepath   = $theme->settings->$filearea;

    if (empty($filepath)) {
        return false;
    }
    $syscontext = context_system::instance();

    $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/$component/$filearea/$itemid".$filepath);

    // Now this is tricky because the we can not hardcode http or https here, lets use the relative link.
    // Note: unfortunately moodle_url does not support //urls yet.

    $url = preg_replace('|^https?://|i', '//', $url->out(false));

    return $url;
}


/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_alpha_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/alpha/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/alpha/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_alpha', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/alpha/scss/preset/default.scss');
    }

    return $scss;
}

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_alpha_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/alpha/style/moodle.css');
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_alpha_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'colorloginbgtext' => ['colorloginbgtext'],
        //Block 1
        'block1sliderwrapperbg' => ['block1slidewrapperbg'],
        // Customization
        'fontweightheadings' => ['headings-font-weight'],
        'fontbody' => ['font-family-base'],
        'fontweightregular' => ['font-weight-normal'],
        'fontweightmedium' => ['font-weight-medium'],
        'fontweightbold' => ['font-weight-bold'],
        'fontheadings' => ['fontheadings'],
        //Text
        'colorbody' => ['body-color'],
        'colorbodysecondary' => ['body-color-secondary'],
        'colorbodylight' => ['body-color-light'],
        'colorlink' => ['link-color'],
        'colorlinkhover' => ['link-color-hover'],
        //Grays
        'white' => ['white'],
        'black' => ['black'],
        'colorgray100' => ['gray-100'],
        'colorgray200' => ['gray-200'],
        'colorgray300' => ['gray-300'],
        'colorgray400' => ['gray-400'],
        'colorgray500' => ['gray-500'],
        'colorgray600' => ['gray-600'],
        'colorgray700' => ['gray-700'],
        'colorgray800' => ['gray-800'],
        'colorgray900' => ['gray-900'],
        //Primary
        'colorprimary100' => ['primary-color-100'],
        'colorprimary200' => ['primary-color-200'],
        'colorprimary300' => ['primary-color-300'],
        'colorprimary400' => ['primary-color-400'],
        'colorprimary500' => ['primary-color-500'],
        'colorprimary600' => ['primary-color-600'],
        'colorprimary700' => ['primary-color-700'],
        'colorprimary800' => ['primary-color-800'],
        'colorprimary900' => ['primary-color-900'],
        //Others
        'colorbodybg' => ['body-bg'],
        'colorborder' => ['border-color'],
        //Topbar
        'colortopbarbg' => ['topbar-bg'],
        'colortopbartext' => ['topbar-text'],
        'colortopbarbtn' => ['topbar-btn'],
        'colortopbarbtntext' => ['topbar-btn-text'],
        'colortopbarbtnhover' => ['topbar-btn-hover'],
        'colortopbarbtnhovertext' => ['topbar-btn-hover-text'],
        //Buttons
        'btnborderradius' => ['btn-border-radius'],
        //Sidebar
        'colordrawerbg' => ['drawer-bg'],
        'colordrawertext' => ['drawer-text'],
        'colordrawernavcontainer' => ['drawer-nav-container'],
        'colordrawernavbtntext' => ['drawer-nav-btn-text'],
        'colordrawernavbtntexth' => ['drawer-nav-btn-text-hover'],
        'colordrawernavbtnbgh' => ['drawer-nav-btn-bg-hover'],
        'colordrawernavbtntextlight' => ['drawer-nav-btn-text-light'],
        'colordrawerscrollbar' => ['drawer-scroll-bg-track'],
        'colordrawerlink' => ['drawer-link'],
        'colordrawerlinkh' => ['drawer-link-h'],
        //Footer
        'colorfooterbg' => ['footer-bg'],
        'colorfooterborder' => ['footer-border'],
        'colorfootertext' => ['footer-text'],
        'colorfooterlink' => ['footer-link'],
        'colorfooterlinkhover' => ['footer-link-color-hover'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}



/**
 * Build the guest access hint HTML code.
 *
 * @param int $courseid The course ID.
 * @return string.
 */
function theme_alpha_get_course_guest_access_hint($courseid) {
    global $CFG;
    require_once($CFG->dirroot . '/enrol/self/lib.php');

    $html = '';
    $instances = enrol_get_instances($courseid, true);
    $plugins = enrol_get_plugins(true);
    foreach ($instances as $instance) {
        if (!isset($plugins[$instance->enrol])) {
            continue;
        }
        $plugin = $plugins[$instance->enrol];
        if ($plugin->show_enrolme_link($instance)) {
            $html = html_writer::tag('div', get_string('showhintcourseguestaccesslink',
                'theme_alpha', array('url' => $CFG->wwwroot . '/enrol/index.php?id=' . $courseid)));
            break;
        }
    }

    return $html;
}


/**
 * Build the course page information banners HTML code.
 * This function evaluates and composes all information banners which may appear on a course page below the full header.
 *
 * @return string.
 */
function theme_alpha_get_course_information_banners() {
    global $CFG, $COURSE, $PAGE, $USER;

    // Require user library.
    require_once($CFG->dirroot.'/user/lib.php');

    // Initialize HTML code.
    $html = '';


    // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
    // a hint for the visibility will be shown.
    if (get_config('theme_alpha', 'showhintcoursehidden') == 'yes'
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == false) {
        $html .= html_writer::start_tag('div', array('class' => 'rui-course-hidden-infobox alert alert-warning wrapper-fw mt-4'));
        $html .= html_writer::start_tag('div', array('class' => 'media'));
        $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
        $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path>
        <circle cx="12" cy="9" r="1" fill="currentColor"></circle>
        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
        </svg>';
        $html .= html_writer::end_tag('div');
        $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
        $html .= get_string('showhintcoursehiddengeneral', 'theme_alpha', $COURSE->id);
        // If the user has the capability to change the course settings, an additional link to the course settings is shown.
        if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
            $html .= html_writer::tag('div', get_string('showhintcoursehiddensettingslink',
                    'theme_alpha', array('url' => $CFG->wwwroot.'/course/edit.php?id='. $COURSE->id)));
        }
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
    }

    // If the setting showhintcourseguestaccesssetting is set, a hint for users that view the course with guest access is shown.
    // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
    // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
    // intended.
    if (get_config('theme_alpha', 'showhintcourseguestaccesssetting') == 'yes'
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)) {
        $html .= html_writer::start_tag('div', array('class' => 'rui-course-guestaccess-infobox alert alert-warning wrapper-fw mt-4'));
        $html .= html_writer::start_tag('div', array('class' => 'media'));
        $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
        $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path>
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path>
        <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
        </svg>';
        $html .= html_writer::end_tag('div');
        $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
        $html .= get_string('showhintcourseguestaccesssettinggeneral', 'theme_alpha',
                array('role' => role_get_name(get_guest_role())));
        $html .= theme_alpha_get_course_guest_access_hint($COURSE->id);
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
    }

    // If the setting showhintcourseselfenrol is set, a hint for users is shown that the course allows unrestricted self
    // enrolment. This hint is only shown if the course is visible, the self enrolment is visible and if the user has the
    // capability "theme/alpha:viewhintcourseselfenrol".
    if (get_config('theme_alpha', 'showhintcourseselfenrol') == 'yes'
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == true) {
        // Get the active enrol instances for this course.
        $enrolinstances = enrol_get_instances($COURSE->id, true);
        // Prepare to remember when self enrolment is / will be possible.
        $selfenrolmentpossiblecurrently = false;
        $selfenrolmentpossiblefuture = false;
        foreach ($enrolinstances as $instance) {
            // Check if unrestricted self enrolment is possible currently or in the future.
            $now = (new \DateTime("now", \core_date::get_server_timezone_object()))->getTimestamp();
            if ($instance->enrol == 'self' && empty($instance->password) && $instance->customint6 == 1 &&
                    (empty($instance->enrolenddate) || $instance->enrolenddate > $now)) {

                // Build enrol instance object with all necessary information for rendering the note later.
                $instanceobject = new stdClass();

                // Remember instance name.
                if (empty($instance->name)) {
                    $instanceobject->name = get_string('pluginname', 'enrol_self') .
                            " (" . get_string('defaultcoursestudent', 'core') . ")";
                } else {
                    $instanceobject->name = $instance->name;
                }

                // Remember type of unrestrictedness.
                if (empty($instance->enrolenddate) && empty($instance->enrolstartdate)) {
                    $instanceobject->unrestrictedness = 'unlimited';
                    $selfenrolmentpossiblecurrently = true;
                } else if (empty($instance->enrolstartdate) &&
                        !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                    $instanceobject->unrestrictedness = 'until';
                    $selfenrolmentpossiblecurrently = true;
                } else if (empty($instance->enrolenddate) &&
                        !empty($instance->enrolstartdate) && $instance->enrolstartdate > $now) {
                    $instanceobject->unrestrictedness = 'from';
                    $selfenrolmentpossiblefuture = true;
                } else if (empty($instance->enrolenddate) &&
                        !empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now) {
                    $instanceobject->unrestrictedness = 'since';
                    $selfenrolmentpossiblecurrently = true;
                } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate > $now &&
                        !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                    $instanceobject->unrestrictedness = 'fromuntil';
                    $selfenrolmentpossiblefuture = true;
                } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now &&
                        !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                    $instanceobject->unrestrictedness = 'sinceuntil';
                    $selfenrolmentpossiblecurrently = true;
                } else {
                    // This should not happen, thus continue to next instance.
                    continue;
                }

                // Remember enrol start date.
                if (!empty($instance->enrolstartdate)) {
                    $instanceobject->startdate = $instance->enrolstartdate;
                } else {
                    $instanceobject->startdate = null;
                }

                // Remember enrol end date.
                if (!empty($instance->enrolenddate)) {
                    $instanceobject->enddate = $instance->enrolenddate;
                } else {
                    $instanceobject->enddate = null;
                }

                // Remember this instance.
                $selfenrolinstances[$instance->id] = $instanceobject;
            }
        }

        // If there is at least one unrestricted enrolment instance,
        // show the hint with information about each unrestricted active self enrolment in the course.
        if (!empty($selfenrolinstances) &&
                ($selfenrolmentpossiblecurrently == true || $selfenrolmentpossiblefuture == true)) {
            // Start hint box.
            $html .= html_writer::start_tag('div', array('class' => 'rui-course-selfenrol-infobox alert alert-info wrapper-fw mt-4'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3'));
            $html .= '<svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 8.75L13.25 12L9.75 15.25"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H9.75"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 12H4.75"></path>
            </svg>';
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));

            // Show the start of the hint depending on the fact if enrolment is already possible currently or
            // will be in the future.
            if ($selfenrolmentpossiblecurrently == true) {
                $html .= get_string('showhintcourseselfenrolstartcurrently', 'theme_alpha');
            } else if ($selfenrolmentpossiblefuture == true) {
                $html .= get_string('showhintcourseselfenrolstartfuture', 'theme_alpha');
            }
            $html .= html_writer::empty_tag('br');

            // Iterate over all enrolment instances to output the details.
            foreach ($selfenrolinstances as $selfenrolinstanceid => $selfenrolinstanceobject) {
                // If the user has the capability to config self enrolments, enrich the instance name with the settings link.
                if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                    $url = new moodle_url('/enrol/editinstance.php', array('courseid' => $COURSE->id,
                            'id' => $selfenrolinstanceid, 'type' => 'self'));
                    $selfenrolinstanceobject->name = html_writer::link($url, $selfenrolinstanceobject->name);
                }

                // Show the enrolment instance information depending on the instance configuration.
                if ($selfenrolinstanceobject->unrestrictedness == 'unlimited') {
                    $html .= get_string('showhintcourseselfenrolunlimited', 'theme_alpha',
                            array('name' => $selfenrolinstanceobject->name));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'until') {
                    $html .= get_string('showhintcourseselfenroluntil', 'theme_alpha',
                            array('name' => $selfenrolinstanceobject->name,
                                    'until' => userdate($selfenrolinstanceobject->enddate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'from') {
                    $html .= get_string('showhintcourseselfenrolfrom', 'theme_alpha',
                            array('name' => $selfenrolinstanceobject->name,
                                    'from' => userdate($selfenrolinstanceobject->startdate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'since') {
                    $html .= get_string('showhintcourseselfenrolsince', 'theme_alpha',
                            array('name' => $selfenrolinstanceobject->name,
                                    'since' => userdate($selfenrolinstanceobject->startdate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'fromuntil') {
                    $html .= get_string('showhintcourseselfenrolfromuntil', 'theme_alpha',
                            array('name' => $selfenrolinstanceobject->name,
                                    'until' => userdate($selfenrolinstanceobject->enddate),
                                    'from' => userdate($selfenrolinstanceobject->startdate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'sinceuntil') {
                    $html .= get_string('showhintcourseselfenrolsinceuntil', 'theme_alpha',
                            array('name' => $selfenrolinstanceobject->name,
                                    'until' => userdate($selfenrolinstanceobject->enddate),
                                    'since' => userdate($selfenrolinstanceobject->startdate)));
                }

                // Add a trailing alpha to separate this instance from the next one.
                $html .= ' ';
            }

            // If the user has the capability to config self enrolments, add the call for action.
            if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                $html .= html_writer::empty_tag('br');
                $html .= get_string('showhintcourseselfenrolinstancecallforaction', 'theme_alpha');
            }

            // End hint box.
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
    }

    // Return HTML code.
    return $html;
}
