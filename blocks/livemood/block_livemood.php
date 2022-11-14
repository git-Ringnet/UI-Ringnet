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
 * livemood block
 *
 * @package    block_livemood
 * @copyright  live-school.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

class block_livemood extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_livemood');
    }
    /**
     * Constrols the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        $this->title = "Live-Mood";
    }
    public function get_content() {
        global $CFG, $USER, $COURSE;
        if ($this->content !== null) {
            return $this->content;
        }
        // Default role is Student so show Student content.
        $headers = '<div style="width:100%; text-align:center; font-size: 0.9em; font-weight:bold">';
        $this->content = new stdclass;
        // Check if the Admin secret key is existing in the config.
        if (isset($CFG->block_livemood_skey)) {
            $body = '<script>
            <!--
                var block_livemood_fadeTimeout;
                var block_livemood_winName = "liveroom";
                var block_livemood_waitForm;
                var block_livemood_fadeTimeout;
                var block_livemood_objOpacity;
                var block_livemood_fadeHighLimit = 1;
                var block_livemood_fadeLowLimit = 0;
                var block_livemood_fadeSpeed = 0.1;
                function block_livemood_go(f, u, n, w, h){
                    function block_livemood_submitForm(){
                        try{
                            if(window[block_livemood_winName]){
                                if(window[block_livemood_winName].closed != true){
                                    window[block_livemood_winName].focus();
                                    if(!f.liveWidth){
                                        var liveWidth = document.createElement("input");
                                        liveWidth.setAttribute(\'type\',"hidden");
                                        liveWidth.setAttribute(\'name\',"liveWidth");
                                        f.appendChild(liveWidth);
                                    }
                                    if(!f.liveHeight){
                                        var liveHeight = document.createElement("input");
                                        liveHeight.setAttribute(\'type\',"hidden");
                                        liveHeight.setAttribute(\'name\',"liveHeight");
                                        f.appendChild(liveHeight);
                                    }
                                    if(f.liveWidth && f.liveHeight){
                                        f.liveWidth.value = window.screen.width;
                                        f.liveHeight.value = window.screen.height;
                                    }
                                    f.submit();
                                }
                                clearInterval(window[\'block_livemood_waitForm\']);
                            }
                        }catch(e){
                            clearInterval(window[\'block_livemood_waitForm\']);
                            alert("block_livemood_go block_livemood_submitForm*:* "+e.toString()+"\n"+JS_ERR_END);
                        }
                    }
                    try{
                        if(n == "_blank"){
                            switch(u){
                                case "a":
                                    f.log_statut.value = "Administrator";
                                    f.action = "https://secure.live-school.net/boss/index.lol";
                                break;
                                case "t":
                                    f.log_statut.value = "Coach";
                                    f.action = "https://secure.live-school.net/boss/index.lol";
                                break;
                                default:
                            }
                            f.target = n;
                            f.submit();
                        } else {
                            var plugin = navigator.plugins["Shockwave Flash"];
                            var isMobile = navigator.userAgent.toLowerCase().match(/mobile/i);
                            if(!f.helper){
                                var helper = document.createElement("input");
                                helper.setAttribute(\'type\',"hidden");
                                helper.setAttribute(\'name\',"helper");
                                f.appendChild(helper);
                            }
                            if(!f.showBack){
                                var showBack = document.createElement("input");
                                showBack.setAttribute(\'type\',"hidden");
                                showBack.setAttribute(\'name\',"showBack");
                                f.appendChild(showBack);
                            }
                            f.statut.value = "Student";
                            f.action = "https://secure.live-school.net/module.lol";
                            f.target = n;
                            if(plugin || isMobile == true){
                                var flashVersion = ((plugin) ? plugin.version : null);
                                if((flashVersion && flashVersion != "0.0.0.0" && flashVersion != "32.0.0.465") || isMobile == true){
                                    if(isMobile == true){
                                        w = window.screen.width;
                                        h = window.screen.height;
                                    } else {
                                        var r = h / w;
                                        h = (window.screen.height < h) ? window.screen.height : h;
                                        w = (window.screen.width < w) ? window.screen.width : w;
                                        if((h/w) != r){
                                            w = h / r;
                                        }
                                        var agent  = navigator.appVersion;
                                        if(agent.indexOf("OPR/") > -1){
                                            h += 40;
                                        }
                                    }
                                    window[n] = window.open(\'\',n,\'toolbar=no,menubar=no,scrollbars=no,status=no,width=\'+w+\',height=\'+h+\',innerWidth=\'+w+\',innerHeight=\'+h,true);
                                    if(window[n]){
                                        if(window[n].location == "about:blank"){
                                            f.helper.value = "";
                                            if(window[\'block_livemood_waitForm\']){
                                                clearInterval(window[\'block_livemood_waitForm\']);
                                            }
                                            window[\'block_livemood_waitForm\'] = setInterval(block_livemood_submitForm,216);
                                        } else {
                                            window[n].focus();
                                        }
                                    } else {
                                        alert("block_livemood_go*:* Please allow popup window in your browser preferences!");
                                    }
                                    return;
                                }
                            }
                            // request herlper to launch Flash modules out of the browser
                            var opSys = block_livemood_sysInfo();
                            if(opSys.name == "Unknown"){
                                alert("block_livemood_go*:* Operating System\n"+window.navigator.userAgent+"\n is not tested to run the modules!");
                                return;
                            }
                            opSys.action = f.action;
                            opSys.target = f.target;
                            f.helper.value = JSON.stringify(opSys);
                            f.action = "https://secure.live-school.net/helper.lol";
                            f.target = "_self";
                            f.submit();
                            f.action = opSys.action;
                            f.target = opSys.target;
                            var msgTable = document.getElementById("block_livemood_alert");
                            var msgContainer = document.getElementById("block_livemood_alertTd");
                            if(msgTable && msgContainer){
                                window.clearTimeout(block_livemood_fadeTimeout);
                                msgTable.style.visibility = "visible";
                                msgContainer.innerHTML = "Open the file once saved";
                                block_livemood_fadeTimeout = setTimeout("block_livemood_fadeObj(\'"+msgTable.id+"\',\'out\')",9000);
                            }
                        }
                    }catch(e){
                        if(window[n]){
                            window[n].focus();
                        }
                    }
                }
                function block_livemood_sysInfo(){
                    var ua = window.navigator.userAgent;
                    var arr = ["Windows","Mac","Linux","iOS","Android"];
                    var os = {name:"Unknown",cpu:"32"};
                    var archArr = ["x86_64","x86-64","Win64","x64;","amd64","AMD64","WOW64","x64_64"];
                    try{
                        for(var n=0;n<arr.length;n++){
                            if(ua.indexOf(arr[n]) > -1){
                                os.name = arr[n];
                            }
                        }
                        for(var x=0;x<archArr.length;x++){
                            if(ua.indexOf(archArr[x]) > -1){
                                os.cpu = "64";
                                break;
                            }
                        }
                    }catch(e){
                        alert("block_livemood_sysInfo*:* "+e.toString());
                    }
                    return os;
                }
                function block_livemood_fadeObj(id,type){
                    try{
                        var child;
                        var element = document.getElementById(id);
                        switch(type){
                            case "in":
                                if(!block_livemood_objOpacity || block_livemood_objOpacity == 1){
                                    block_livemood_objOpacity = 0;
                                }
                                if(block_livemood_objOpacity >= block_livemood_fadeHighLimit){
                                    clearTimeout(block_livemood_fadeTimeout);
                                    block_livemood_objOpacity = 1;
                                    if(element.style.visibility == "hidden"){
                                        element.style.visibility = "visible";
                                    }
                                    if(element.style.display == "none"){
                                        element.style.display = "block";
                                    }
                                } else {
                                    block_livemood_objOpacity += block_livemood_fadeSpeed;
                                    element.style.opacity = block_livemood_objOpacity;
                                    clearTimeout(block_livemood_fadeTimeout);
                                    block_livemood_fadeTimeout = setTimeout("block_livemood_fadeObj(\'"+id+"\',\'"+type+"\')",50);
                                    if(element.style.visibility == "hidden"){
                                        element.style.visibility = "visible";
                                    }
                                    if(element.style.display == "none"){
                                        element.style.display = "block";
                                    }
                                }
                            break;
                            case "out":
                                if(element.style.visibility == "visible" || element.style.visibility == ""){
                                    if(!block_livemood_objOpacity || block_livemood_objOpacity == 0){
                                        block_livemood_objOpacity = 1;
                                    }
                                    if(block_livemood_objOpacity <= block_livemood_fadeLowLimit){
                                        clearTimeout(block_livemood_fadeTimeout);
                                        block_livemood_objOpacity = 0;
                                        element.style.opacity = 1;
                                        element.style.visibility = "hidden";
                                        block_livemood_objOpacity = 0;
                                    } else {
                                        block_livemood_objOpacity -= block_livemood_fadeSpeed;
                                        element.style.opacity = block_livemood_objOpacity;
                                        clearTimeout(block_livemood_fadeTimeout);
                                        block_livemood_fadeTimeout = setTimeout("block_livemood_fadeObj(\'"+id+"\',\'"+type+"\')",50);
                                    }
                                }
                            break;
                            default:
                        }
                    }catch(e){}
                }
            //-->
            </script>';
            // Check first if the user is logged.
            if (isset($USER->id)) {
                /*
                Moodle default Role ID Description
		You are free to adapt the ID to your custom settings
                1 = manager = live-school organization button
                2 = coursecreator = live-school teacher button
                3 = editingteacher = live-school teacher button
                4 = teacher = live-school teacher button
                5 = student = live-school student button
                6 = guest = live-school student button
                7 = user = live-school student button
                8 = frontpage = live-school student button
                */
                $currentrolearray = $this->get_user_role($COURSE->id);
                $currentrole = ($currentrolearray[0] == 0) ? $currentrolearray[1] : $currentrolearray[0];
                if ($currentrole) {
                    switch($currentrole) {
                        case 1:
                            // Admin is the Live-Mood Organization account.
                            $body .= '<form name="block_livemood_form" id="block_livemood_form" action="https://secure.live-school.net/boss/index.lol" method="post" target="_blank">'.
                                '<input type="hidden" name="log_statut" value="Administrator">'.
                                '<input type="hidden" name="login" value="'.$USER->email.'">'.
                                '<input type="hidden" name="log_moodle_req" value="'.$CFG->block_livemood_skey.'">'.
                                '<input type="submit" class="adminbut" name="Submit" value="Admin Live" onclick="javascript:this.blur()" onmouseout="javascript:this.blur()">'.
                                '</form>';
                        break;
                        case 2:
                        case 3:
                        case 4:
                            // Consider all these roles as Live-Mood teacher content.
                            $body .= '<form name="block_livemood_form" id="block_livemood_form" action="https://secure.live-school.net/boss/index.lol" method="post" target="_blank">'.
                                '<input type="hidden" name="log_statut" value="Coach">'.
                                '<input type="hidden" name="login" value="'.$USER->email.'">'.
                                 '<input type="hidden" name="log_moodle_req" value="'.$CFG->block_livemood_skey.'">'.
                                '<input type="submit" class="teacbut" name="Submit" value="Teacher Live" onclick="javascript:this.blur()" onmouseout="javascript:this.blur()">'.
                                '</form>';
                        break;
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                            // All these roles id should be Student.
                            $body .= '<form name="block_livemood_form" id="block_livemood_form" action="" method="post" target="liveroom" onsubmit="javascript:this.target=\'liveroom\'">'.
                                '<input type="hidden" name="log_moodle_req" value="'.$CFG->block_livemood_skey.'">'.
                                '<input type="hidden" name="login" value="'.$USER->email.'">'.
                                '<input type="hidden" name="statut" value="Student">'.
                                '<input type="button" class="studbut" name="goStudent" value="Student Live" onclick="javascript:block_livemood_go(this.form, \'s\', \'liveroom\', 1024, 768);this.blur();" onmouseout="javascript:this.blur()">'.
                                '</form>';
                        break;
                        default:
                    }
                } else {
                    // Moodle standard ID maybe changed.
                    // Show all the three buttons. (Moodle users you can change all the code above and below to change your needs).
                    $body .= '<form name="block_livemood_form" id="block_livemood_form" action="" method="post" target="liveroom" onsubmit="javascript:this.target=\'liveroom\'">'.
                        '<input type="hidden" name="log_statut" value="">'.
                        '<input type="hidden" name="statut" value="">'.
                        '<input type="hidden" name="login" value="'.$USER->email.'">'.
                        '<input type="hidden" name="log_moodle_req" value="'.$CFG->block_livemood_skey.'">'.
                        '<table id="block_livemood_Container"><tr><td>'.
                        '<input type="button" class="adminbut" name="goAdmin" value="Admin Live" style="width:60px;padding:6px;line-height:120%" onclick="javascript:block_livemood_go(this.form, \'a\', \'_blank\', null, null);this.blur()" onmouseout="javascript:this.blur()">'.
                        '</td><td><input type="button" class="teacbut" name="goTeacher" value="Teacher Live" style="width:60px;padding:6px;line-height:120%" onclick="javascript:block_livemood_go(this.form, \'t\', \'_blank\', null, null);this.blur()" onmouseout="javascript:this.blur()">'.
                        '</td><td><table id="block_livemood_alert"><tr><td id="block_livemood_alertTd"></td></tr></table><div id="block_livemood_divForm">'.
                        '<input type="button" class="studbut" name="goStudent" value="Student Live" style="width:60px;padding:6px;line-height:120%" onclick="javascript:block_livemood_go(this.form, \'s\', \'liveroom\', 1024, 768);this.blur();" onmouseout="javascript:this.blur()">'.
                        '</div></td></tr></table>'.
                        '</form>';
                }
            } else {
                // Public side so show Student button.
                $body .= '<table id="block_livemood_container"><tr><td><table id="block_livemood_alert"><tr><td id="block_livemood_alertTd"></td></tr></table>'.
                    '<div id="block_livemood_divForm">'.
                    '<form name="block_livemood_form" id="block_livemood_form" action="" method="post" target="liveroom">'.
                    '<input type="hidden" name="log_moodle_req" value="'.$CFG->block_livemood_skey.'">'.
                    '<input type="hidden" name="statut" value="Student">'.
                    '<input type="button" class="studbut" name="goStudent" value="Student Go Live" onclick="javascript:block_livemood_go(this.form, \'s\', \'liveroom\', 1024, 768);this.blur();" onmouseout="javascript:this.blur()">'.
                    '</form>'.
                    '</div></td></tr></table>';
            }
        } else {
            $body = '<form name="block_livemood_get_key" id="block_livemood_get_key" method="post" action="https://secure.live-school.net/indexOrg.lol" target="_blank">'.
                '<input type="hidden" name="email" value="'.$USER->email.'">'.
                '</form>'.
                '<span style="color:#FF0000">Manager secret key not found</span><br/>'.
                '<a href="#" onclick="javascript:document.block_livemood_get_key.submit()">Get your secret key here</a>';
        }
        $this->content->text = $headers.$body.'</div>';
        $this->content->footer = '<noscript><p style="font-size: 0.9em;">you dont have Javascript enabled which is required to run Live-Mood (live-school) plugin</p></noscript>';
        return $this->content;
    }
    public function instance_allow_config() {
        return false;
    }
    public function has_config() {
        return true;
    }
    public function instance_allow_multiple() {
        return false;
    }
    protected function get_user_role($courseid) {
        global $CFG, $USER, $DB;
        $rolearray = array();
        $sqltxt = "select ra.roleid from ".$CFG->prefix."context, ".$CFG->prefix."role_assignments ra where ".$CFG->prefix."context.id=ra.contextid and ra.userid=".$USER->id;
        $sqlarray = $DB->get_records_sql($sqltxt);
        $sqltxtcourse = "select ra.enrolid from ".$CFG->prefix."context, ".$CFG->prefix."user_enrolments ra where (".$CFG->prefix."context.instanceid=".$courseid." or ".$CFG->prefix."context.instanceid=0) and ra.id=".$courseid." and ra.userid=".$USER->id;
        $sqlarraycourse = $DB->get_records_sql($sqltxtcourse);
        if (empty($sqlarray)) {
            // Current user has no any system role.
            $rolearray[0] = 0;
        } else {
            sort($sqlarray);
            $sqlarray = $sqlarray[0];
            $rolearray[0] = $sqlarray->roleid;
        }
        if (empty($sqlarraycourse)) {
            // Current user has no any system role.
            $rolearray[1] = 0;
        } else {
            sort($sqlarraycourse);
            $sqlarray = $sqlarraycourse[0];
            $rolearray[1] = $sqlarray->enrolid;
        }
        return $rolearray;
    }
}
