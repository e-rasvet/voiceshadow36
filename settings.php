<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/voiceshadow/lib.php');

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect('voiceshadow_maxbytes', get_string('maximumsize', 'voiceshadow'),
                           get_string('configmaxbytes', 'voiceshadow'), 1048576, get_max_upload_sizes($CFG->maxbytes)));
    }

    $options = array(VOICESHADOW_COUNT_WORDS   => trim(get_string('numwords', '', '?')),
                     VOICESHADOW_COUNT_LETTERS => trim(get_string('numletters', '', '?')));
    $settings->add(new admin_setting_configselect('voiceshadow_itemstocount', get_string('itemstocount', 'voiceshadow'),
                       get_string('configitemstocount', 'voiceshadow'), VOICESHADOW_COUNT_WORDS, $options));

    $settings->add(new admin_setting_configcheckbox('voiceshadow_showrecentsubmissions', get_string('showrecentsubmissions', 'voiceshadow'),
                       get_string('configshowrecentsubmissions', 'voiceshadow'), 1));
                       
    // Converting method
    /*
     *
    $options = array();
    $options[1] = get_string('usemediaconvert', 'voiceshadow');
    $options[2] = get_string('usethisserver', 'voiceshadow');
    $settings->add(new admin_setting_configselect('voiceshadow_convert',
            get_string('convertmethod', 'voiceshadow'), get_string('descrforconverting', 'voiceshadow'), 1, $options));

    //preplayer
    
    $options = array();
    $options[1] = get_string('yes');
    $options[2] = get_string('no');
    $settings->add(new admin_setting_configselect('voiceshadow_preplayer',
            get_string('preplayer', 'voiceshadow'), get_string('preplayerdescr', 'voiceshadow'), 1, $options));
            
    // Converting url
    $settings->add(new admin_setting_configtext('voiceshadow_convert_url',
            get_string('converturl', 'voiceshadow'), get_string('descrforconvertingurl', 'voiceshadow'), '', PARAM_URL));

    */


    $stt_core = array( "amazon" => "Amazon Transcribe", "google" => "Google Speech to text");

    $settings->add(new admin_setting_configselect('voiceshadow_stt_core',
        new lang_string('stt_core', 'voiceshadow'),
        '', 'amazon', $stt_core));

    $speechtotextlang = array( "en-US" => "US English (en-US)", "en-AU" => "Australian English (en-AU)", "en-GB" => "British English (en-GB)",
        "fr-CA" => "Canadian French (fr-CA)", "fr-FR" => "French (fr-FR)",
        "es-US" => "US Spanish (es-US)");

    $settings->add(new admin_setting_configselect('voiceshadow_speechtotextlang',
        new lang_string('speechtotextlang', 'voiceshadow'),
        '', 'en-US', $speechtotextlang));


    $amazon_region = array( "us-east-1" => "US East (N. Virginia)", "us-east-2" => "US East (Ohio)", "us-west-2" => "US West (Oregon)",
        "ap-southeast-2" => "Asia Pacific (Sydney)", "ca-central-1" => "Canada (Central)",
        "eu-west-1" => "EU (Ireland)");

    $settings->add(new admin_setting_configselect('voiceshadow_amazon_region',
        new lang_string('amazon_region', 'voiceshadow'),
        '', 'ap-southeast-2', $amazon_region));

    $settings->add(new admin_setting_configtext('voiceshadow_amazon_accessid',
        get_string('amazon_accessid', 'voiceshadow'), '', '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('voiceshadow_amazon_secretkey',
        get_string('amazon_secretkey', 'voiceshadow'), '', '', PARAM_TEXT));

}
