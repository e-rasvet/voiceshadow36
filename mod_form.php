<?php //$Id: mod_form.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');  
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');

$PAGE->requires->js('/mod/voiceshadow/js/jquery.min.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/swfobject.js', true);


class mod_voiceshadow_mod_form extends moodleform_mod {
    function definition() {
        global $COURSE, $CFG, $form, $USER, $update;
        $mform    =& $this->_form;
        
        $mform->updateAttributes(array('enctype' => 'multipart/form-data'));

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('textarea', 'embedvideo', get_string("embedvideo", "voiceshadow"), 'wrap="virtual" rows="10" cols="80"');
        
        $mform->addElement('date_time_selector', 'timeavailable', get_string('availabledate', 'voiceshadow'), array('optional'=>true));
        $mform->setDefault('timeavailable', time());
        $mform->addElement('date_time_selector', 'timedue', get_string('duedate', 'voiceshadow'), array('optional'=>true));
        $mform->setDefault('timedue', time()+7*24*3600);

        //$recorder = array( 0 => get_string('autoswitch', 'voiceshadow'), 1 => get_string('html5', 'voiceshadow'), 2 => get_string('flash', 'voiceshadow'));
        $recorder = array( 0 => get_string('autoswitch', 'voiceshadow'), 1 => get_string('html5', 'voiceshadow'));
        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $speedoptions = array( 0 => get_string('no'), 1 => get_string('fixedspeed', 'voiceshadow'), 2 => get_string('userspeed', 'voiceshadow'), 3 => get_string('fixedanduserspeed', 'voiceshadow'));
        $allowmultiple = array( 1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5");

        $speechtotextlangoptions = array( "en-US" => "English (United States)", "en-GB" => "English (Great Britain)", "en-AU" => "English (Australia)",
            "de-DE" => "Deutsch (Deutschland)", "es-ES" => "Español (España)", "fr-FR" => "Français (France)", "it-IT" => "Italiano (Italia)", "ru-RU" => "Русский (Россия)",
            "ko-KR" => "한국어 (대한민국)", "ar-SA" => "العربية (السعودية)", "yue-Hant-HK" => "廣東話 (香港)",
            "zh-TW"=>"國語（台灣）", "zh"=>"普通话（中国大陆）", "ja-JP"=>"日本語（日本）");

        $mform->addElement('select', 'preventlate', get_string('preventlate', 'voiceshadow'), $ynoptions);
        $mform->addElement('select', 'speechtotext', get_string('usespeechtotext', 'voiceshadow'), $ynoptions);
        $mform->addElement('select', 'showspeedbox', get_string('speedchange', 'voiceshadow'), $speedoptions);
        $mform->addElement('select', 'speechtotextlang', get_string('speechtotextlang', 'voiceshadow'), $speechtotextlangoptions);
        $mform->setDefault('showspeedbox', 0);
        $mform->setDefault('preventlate', 0);
        $mform->addElement('select', 'allowmultiple', get_string('allowmultiple', 'voiceshadow'), $allowmultiple);
        $mform->setDefault('allowmultiple', 3);
        $mform->addElement('select', 'recorder', get_string('recordertype', 'voiceshadow'), $recorder);
        $mform->setDefault('recorder', 0);
        
        
        $mform->addElement('header', 'typedesc', get_string("participantsgrading", 'voiceshadow'));

        /* Disable Computerized score temporarily */
        //$mform->addElement('select', 'showscore', get_string('showscore', 'voiceshadow'), $ynoptions);
        
        $mform->addElement('select', 'grade', get_string('grade'), array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5'));
        $mform->setDefault('grade', 5);
        
        $mform->addElement('select', 'grademethod', get_string('grademethod', "voiceshadow"), array('default'=>get_string('default', "voiceshadow"), 'like'=>get_string('thisnewlike', "voiceshadow")));
        $mform->setDefault('grademethod', 'default');
        
        
        $mform->addElement('header', 'typedesc', get_string("teachergrading", 'voiceshadow'));
        
        $mform->addElement('select', 'gradet', get_string('grade'), array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5'));
        $mform->setDefault('gradet', 5);
        
        $mform->addElement('select', 'grademethodt', get_string('grademethod', "voiceshadow"), array('default'=>get_string('default', "voiceshadow"), 'rubrics'=>get_string('rubrics', "voiceshadow")));
        $mform->setDefault('grademethodt', 'default');
        
        
        
        $filepickeroptions = array();
        $filepickeroptions['maxbytes']  = get_max_upload_file_size($CFG->maxbytes);
        $mform->addElement('header', 'mp3upload', get_string('mp3upload', 'voiceshadow'));

        $mform->addElement('select', 'shadowingmode', get_string('shadowingmode', 'voiceshadow'), Array('1'=>'Shadowing', '2'=>'Non-Shadowing'));
        $mform->setDefault('shadowingmode', 1);
        
        $mform->addElement('select', 'countofrecords', get_string('tocreateaslideshow', 'voiceshadow'), Array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5'), 'onchange="fpresetimages(this);return false;"');
        $mform->setDefault('countofrecords', 1);
        
        $time = time();
//File 1
        for ($i=1;$i<=5;$i++){
          $filename = str_replace(" ", "_", $USER->username)."_".date("Ymd_Hi", $time)."_".$i;
          
          $mform->addElement('text', 'var'.$i.'text', get_string('vartext', 'voiceshadow'), array('size'=>'64'));
          
          $mform->addElement('textarea', 'var'.$i.'transcript', get_string("transcript", "voiceshadow"), 'wrap="virtual" rows="2" cols="180"');
          
          $mform->addElement('filepicker', 'submitfile_'.$i, get_string('uploadmp3', 'voiceshadow'), null, $filepickeroptions);
          
          /*
          $mediadatavoice  = html_writer::script('var fn = function() {var att = { data:"'.(new moodle_url("/mod/voiceshadow/js/recorder.swf")).'", width:"350", height:"200"};var par = { flashvars:"rate=44&gain=50&prefdevice=&loopback=no&echosupression=yes&silencelevel=0&updatecontrol=poodll_recorded_file&callbackjs=poodllcallback&posturl='.(new moodle_url("/mod/voiceshadow/uploadmp3.php")).'&p1='.$update.'&p2='.$USER->id.'&p3="+$(\'#id_submitfile_'.$i.'\').attr(\'value\')+"&p4='.$filename.'&autosubmit=true&debug=false&lzproxied=false" };var id = "mp3_flash_recorder_'.$i.'";var myObject = swfobject.createSWF(att, par, id);};swfobject.addDomLoadEvent(fn);function poodllcallback(args){console.log(args);}');
          $mediadatavoice .= '<div id="mp3_flash_header_recorder_'.$i.'" style="display:none"><div id="mp3_flash_recorder_'.$i.'"></div><div>';
          */
          
          $mediadatavoice  = html_writer::script('var flashvars = {};flashvars.gain=35;flashvars.rate=44;flashvars.name = "'.$filename.'";flashvars.p = "'.str_replace("IIIII",'"+$(\'#id_submitfile_'.$i.'\').attr(\'value\')+"', urlencode(json_encode(array("id"=>$update, "userid"=>$USER->id, "itemid"=>"IIIII")))).'";flashvars.url = "'.urlencode(new moodle_url("/mod/voiceshadow/uploadmp3.php")).'";swfobject.embedSWF("'.(new moodle_url("/mod/voiceshadow/js/recorder.swf")).'", "mp3_flash_record_'.$i.'", "220", "200", "9.0.0", "expressInstall.swf", flashvars);');
          $mediadatavoice .= '<div id="mp3_flash_header_recorder_'.$i.'" style="display:none"><div id="mp3_flash_record_'.$i.'" style="margin:20px 0;"></div><div>';

          $mform->addelEment('hidden', 'filename_'.$i, $filename);
          $mform->addElement('html', $mediadatavoice);
        }
///
        
        
//-------------------------------------------------------------------------------

        if (!isset($CFG->assignment_maxbytes))
            $CFG->assignment_maxbytes = 10485760;

        $mform->addElement('header', 'typedesc', get_string("typeupload", 'voiceshadow'));
        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit', 'voiceshadow') . ' ('.display_size($COURSE->maxbytes).')';
        $mform->addElement('select', 'maxbytes', get_string('maximumsize', 'voiceshadow'), $choices);
        $mform->setDefault('maxbytes', $CFG->assignment_maxbytes);

        $mform->addElement('select', 'resubmit', get_string('allowdeleting', 'voiceshadow'), $ynoptions);
        $mform->addHelpButton('resubmit', 'allowdeleting', 'voiceshadow');
        $mform->setDefault('resubmit', 0);
        $mform->setDefault('maxbytes', 10485760);
        
        
        $mform->addElement('html', '<script language="JavaScript">
            function fpresetimages(e){
              if($(e).val() == 1){
                $(\'#fitem_id_submitfile_1\').show();
                $(\'#mp3_flash_header_recorder_1\').show();
                $(\'#fitem_id_var1text\').show();
                $(\'#fitem_id_var1transcript\').show();
                $(\'#fitem_id_submitfile_2\').hide();
                $(\'#mp3_flash_header_recorder_2\').hide();
                $(\'#fitem_id_var2text\').hide();
                $(\'#fitem_id_var2transcript\').hide();
                $(\'#fitem_id_submitfile_3\').hide();
                $(\'#mp3_flash_header_recorder_3\').hide();
                $(\'#fitem_id_var3text\').hide();
                $(\'#fitem_id_var3transcript\').hide();
                $(\'#fitem_id_submitfile_4\').hide();
                $(\'#mp3_flash_header_recorder_4\').hide();
                $(\'#fitem_id_var4text\').hide();
                $(\'#fitem_id_var4transcript\').hide();
                $(\'#fitem_id_submitfile_5\').hide();
                $(\'#mp3_flash_header_recorder_5\').hide();
                $(\'#fitem_id_var5text\').hide();
                $(\'#fitem_id_var5transcript\').hide();
              } else if($(e).val() == 2){
                $(\'#fitem_id_submitfile_1\').show();
                $(\'#mp3_flash_header_recorder_1\').show();
                $(\'#fitem_id_var1text\').show();
                $(\'#fitem_id_var1transcript\').show();
                $(\'#fitem_id_submitfile_2\').show();
                $(\'#mp3_flash_header_recorder_2\').show();
                $(\'#fitem_id_var2text\').show();
                $(\'#fitem_id_var2transcript\').show();
                $(\'#fitem_id_submitfile_3\').hide();
                $(\'#mp3_flash_header_recorder_3\').hide();
                $(\'#fitem_id_var3text\').hide();
                $(\'#fitem_id_var3transcript\').hide();
                $(\'#fitem_id_submitfile_4\').hide();
                $(\'#mp3_flash_header_recorder_4\').hide();
                $(\'#fitem_id_var4text\').hide();
                $(\'#fitem_id_var4transcript\').hide();
                $(\'#fitem_id_submitfile_5\').hide();
                $(\'#mp3_flash_header_recorder_5\').hide();
                $(\'#fitem_id_var5text\').hide();
                $(\'#fitem_id_var5transcript\').hide();
              } else if($(e).val() == 3){
                $(\'#fitem_id_submitfile_1\').show();
                $(\'#mp3_flash_header_recorder_1\').show();
                $(\'#fitem_id_var1text\').show();
                $(\'#fitem_id_var1transcript\').show();
                $(\'#fitem_id_submitfile_2\').show();
                $(\'#mp3_flash_header_recorder_2\').show();
                $(\'#fitem_id_var2text\').show();
                $(\'#fitem_id_var2transcript\').show();
                $(\'#fitem_id_submitfile_3\').show();
                $(\'#mp3_flash_header_recorder_3\').show();
                $(\'#fitem_id_var3text\').show();
                $(\'#fitem_id_var3transcript\').show();
                $(\'#fitem_id_submitfile_4\').hide();
                $(\'#mp3_flash_header_recorder_4\').hide();
                $(\'#fitem_id_var4text\').hide();
                $(\'#fitem_id_var4transcript\').hide();
                $(\'#fitem_id_submitfile_5\').hide();
                $(\'#mp3_flash_header_recorder_5\').hide();
                $(\'#fitem_id_var5text\').hide();
                $(\'#fitem_id_var5transcript\').hide();
              } else if($(e).val() == 4){
                $(\'#fitem_id_submitfile_1\').show();
                $(\'#mp3_flash_header_recorder_1\').show();
                $(\'#fitem_id_var1text\').show();
                $(\'#fitem_id_var1transcript\').show();
                $(\'#fitem_id_submitfile_2\').show();
                $(\'#mp3_flash_header_recorder_2\').show();
                $(\'#fitem_id_var2text\').show();
                $(\'#fitem_id_var2transcript\').show();
                $(\'#fitem_id_submitfile_3\').show();
                $(\'#mp3_flash_header_recorder_3\').show();
                $(\'#fitem_id_var3text\').show();
                $(\'#fitem_id_var3transcript\').show();
                $(\'#fitem_id_submitfile_4\').show();
                $(\'#mp3_flash_header_recorder_4\').show();
                $(\'#fitem_id_var4text\').show();
                $(\'#fitem_id_var4transcript\').show();
                $(\'#fitem_id_submitfile_5\').hide();
                $(\'#mp3_flash_header_recorder_5\').hide();
                $(\'#fitem_id_var5text\').hide();
                $(\'#fitem_id_var5transcript\').hide();
              } else if($(e).val() == 5){
                $(\'#fitem_id_submitfile_1\').show();
                $(\'#mp3_flash_header_recorder_1\').show();
                $(\'#fitem_id_var1text\').show();
                $(\'#fitem_id_var1transcript\').show();
                $(\'#fitem_id_submitfile_2\').show();
                $(\'#mp3_flash_header_recorder_2\').show();
                $(\'#fitem_id_var2text\').show();
                $(\'#fitem_id_var2transcript\').show();
                $(\'#fitem_id_submitfile_3\').show();
                $(\'#mp3_flash_header_recorder_3\').show();
                $(\'#fitem_id_var3text\').show();
                $(\'#fitem_id_var3transcript\').show();
                $(\'#fitem_id_submitfile_4\').show();
                $(\'#mp3_flash_header_recorder_4\').show();
                $(\'#fitem_id_var4text\').show();
                $(\'#fitem_id_var4transcript\').show();
                $(\'#fitem_id_submitfile_5\').show();
                $(\'#mp3_flash_header_recorder_5\').show();
                $(\'#fitem_id_var5text\').show();
                $(\'#fitem_id_var5transcript\').show();
              }
            }
            
            $(document).ready(function() {
              $(\'#fitem_id_submitfile_1\').show();
              $(\'#mp3_flash_header_recorder_1\').show();
              $(\'#fitem_id_var1text\').show();
              $(\'#fitem_id_var1transcript\').show();
              $(\'#fitem_id_submitfile_2\').hide();
              $(\'#mp3_flash_header_recorder_2\').hide();
              $(\'#fitem_id_var2text\').hide();
              $(\'#fitem_id_var2transcript\').hide();
              $(\'#fitem_id_submitfile_3\').hide();
              $(\'#mp3_flash_header_recorder_3\').hide();
              $(\'#fitem_id_var3text\').hide();
              $(\'#fitem_id_var3transcript\').hide();
              $(\'#fitem_id_submitfile_4\').hide();
              $(\'#mp3_flash_header_recorder_4\').hide();
              $(\'#fitem_id_var4text\').hide();
              $(\'#fitem_id_var4transcript\').hide();
              $(\'#fitem_id_submitfile_5\').hide();
              $(\'#mp3_flash_header_recorder_5\').hide();
              $(\'#fitem_id_var5text\').hide();
              $(\'#fitem_id_var5transcript\').hide();
            });
            
            $("#id_timeavailable_enabled").prop("checked", false);
            
            $("#id_timedue_enabled").prop("checked", false);
            </script>
            <style>
            #fitem_id_submitfile_1{float:left;width:600px;}
            #fitem_id_submitfile_2{float:left;width:600px;}
            #fitem_id_submitfile_3{float:left;width:600px;}
            #fitem_id_submitfile_4{float:left;width:600px;}
            #fitem_id_submitfile_5{float:left;width:600px;}
            </style>');
//-------------------------------------------------------------------------------
        $mform->setType('var1text', PARAM_TEXT);
        $mform->setType('var2text', PARAM_TEXT);
        $mform->setType('var3text', PARAM_TEXT);
        $mform->setType('var4text', PARAM_TEXT);
        $mform->setType('var5text', PARAM_TEXT);
        $mform->setType('filename_1', PARAM_TEXT);
        $mform->setType('filename_2', PARAM_TEXT);
        $mform->setType('filename_3', PARAM_TEXT);
        $mform->setType('filename_4', PARAM_TEXT);
        $mform->setType('filename_5', PARAM_TEXT);

        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }
}
