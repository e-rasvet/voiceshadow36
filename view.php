<?php // $Id: view.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $


require_once '../../config.php';
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once 'lib.php';
require_once($CFG->libdir . '/gradelib.php');


$id = optional_param('id', 0, PARAM_INT);
$ids = optional_param('ids', 0, PARAM_INT);
$a = optional_param('a', 'list', PARAM_TEXT);
$summary = optional_param_array('summary', NULL, PARAM_TEXT);
$speechtext = optional_param('speechtext', NULL, PARAM_TEXT);
$filename = optional_param('filename', NULL, PARAM_TEXT);
$fileid = optional_param('fileid', 0, PARAM_INT);
$submitfile = optional_param('submitfile', 0, PARAM_INT);
$commentid = optional_param('commentid', 0, PARAM_INT);
$selectaudiomodel = optional_param('selectaudiomodel', 0, PARAM_INT);
$act = optional_param('act', NULL, PARAM_CLEAN);
$delfilename = optional_param('delfilename', NULL, PARAM_TEXT);
$sort = optional_param('sort', 'firstname', PARAM_CLEAN);
$orderby = optional_param('orderby', 'ASC', PARAM_CLEAN);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$speed = optional_param('speed', 10, PARAM_INT);


if (is_array($summary)) $summary = $summary['text'];

if ($id) {
    if (!$cm = get_coursemodule_from_id('voiceshadow', $id)) {
        error('Course Module ID was incorrect');
    }

    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        error('Course is misconfigured');
    }

    if (!$voiceshadow = $DB->get_record('voiceshadow', array('id' => $cm->instance))) {
        error('Course module is incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

//add_to_log($course->id, "voiceshadow", "view", "view.php?id=$cm->id", "$voiceshadow->id");


if (!empty($delfilename)) {
    $DB->delete_records("voiceshadow_files", array("filename" => $delfilename));
}


//if (voiceshadow_is_ios() && is_dir($CFG->dirroot.'/theme/mymobile')) {} else

//$PAGE->requires->js('/mod/voiceshadow/js/jquery.min.js', true);
$PAGE->requires->jquery();

//$PAGE->requires->js_function_call('M.util.load_flowplayer');
//$PAGE->requires->js('/mod/voiceshadow/js/ajax.js', true);

$PAGE->requires->js('/mod/voiceshadow/js/flowplayer.min.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/swfobject.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/WebAudioRecorder.min.js?3', true);

if ($a == "add") {
    if ($CFG->voiceshadow_stt_core == "google") {
        $PAGE->requires->js('/mod/voiceshadow/js/main_vs_pl.js?8', true);
    }
}


$PAGE->requires->css('/mod/voiceshadow/css/main.css?1');


if ($act == "addlike") {
    if (!$DB->get_record("voiceshadow_likes", array("fileid" => $fileid, "userid" => $USER->id))) {
        $add = new stdClass;
        $add->instance = $id;
        $add->fileid = $fileid;
        $add->userid = $USER->id;
        $add->time = time();

        $DB->insert_record("voiceshadow_likes", $add);
    }
}


if ($act == "dellike") {
    $DB->delete_records("voiceshadow_likes", array("fileid" => $fileid, "userid" => $USER->id));
}


if ($a == 'add' && $act == 'newinstance') {
    $data = new stdClass;
    $data->instance = $id;
    $data->userid = $USER->id;
    $data->summary = $summary;
    $data->speechtext = $speechtext;
    $data->filename = $filename;
    $data->speed = $speed;
    $data->var = $selectaudiomodel;
    $data->time = time();


    if (!empty($submitfile)) {
        if ($file = voiceshadow_getfile($submitfile)) {
            if (mimeinfo('type', $file->filename) == 'audio/wav') {
                $data->itemoldid = $file->id;

                $add = new stdClass;
                $add->itemid = $file->id;
                $add->type = mimeinfo('type', $file->filename);
                $add->status = 'open';
                $add->name = md5($CFG->wwwroot . '_' . time());
                $add->time = time();

                $DB->insert_record("voiceshadow_process", $add);
            } else if (mimeinfo('type', $file->filename) == 'audio/mp3') {
                $data->itemid = $file->id;
            } else if (mimeinfo('type', $file->filename) == 'audio/aac') {
                $data->itemid = $file->id;
            } else {
                echo "Incorrect Audio format " . mimeinfo('type', $file->filename);
                die();
            }
        }
    }


    if (!empty($fileid)) {
        $data->id = $fileid;
        $ids = $DB->update_record("voiceshadow_files", $data);
    } else
        $ids = $DB->insert_record("voiceshadow_files", $data);

    $DB->set_field("voiceshadow_files", "var", $selectaudiomodel, array("id" => $ids));

    redirect("view.php?id={$id}", get_string('postsubmited', 'voiceshadow'));
}


if ($a == 'add' && $act == 'addcomment' && isset($summary)) {
    $data = new stdClass();
    $data->instance = $id;
    $data->userid = $USER->id;
    $data->summary = $summary;
    $data->speechtext = $speechtext;
    $data->filename = $filename;
    $data->fileid = $fileid;
    $data->time = time();


    if (!empty($submitfile)) {
        if ($file = voiceshadow_getfile($submitfile)) {
            if (mimeinfo('type', $file->filename) == 'audio/wav') {
                $data->itemoldid = $file->id;

                $add = new stdClass();
                $add->itemid = $file->id;
                $add->type = mimeinfo('type', $file->filename);
                $add->status = 'open';
                $add->name = md5($CFG->wwwroot . '_' . time());

                $DB->insert_record("voiceshadow_process", $add);
            } else if (mimeinfo('type', $file->filename) == 'audio/mp3')
                $data->itemid = $file->id;
        }
    }


    if (!empty($commentid)) {
        $data->id = $commentid;
        $DB->update_record("voiceshadow_comments", $data);
    } else
        $DB->insert_record("voiceshadow_comments", $data);


    redirect("view.php?id={$id}", get_string('commentsubmited', 'voiceshadow'));
}


if ($act == "deleteentry" && !empty($fileid)) {
    if (has_capability('mod/voiceshadow:teacher', $context))
        $DB->delete_records("voiceshadow_files", array("id" => $fileid));
    else
        $DB->delete_records("voiceshadow_files", array("id" => $fileid, "userid" => $USER->id));
}


if ($act == "deleteentry" && !empty($filename)) {
    $filename = end(explode("/", $filename));
    list($filename) = explode(".", $filename);
    $DB->delete_records("voiceshadow_files", array("filename" => $filename, "userid" => $USER->id));
}


if ($act == "deletecomment" && !empty($fileid)) {
    if (has_capability('mod/voiceshadow:teacher', $context))
        $DB->delete_records("voiceshadow_comments", array("id" => $fileid));
    else
        $DB->delete_records("voiceshadow_comments", array("id" => $fileid, "userid" => $USER->id));
}


/// Print the page header
$strvoiceshadows = get_string('modulenameplural', 'voiceshadow');
$strvoiceshadow = get_string('modulename', 'voiceshadow');

$PAGE->set_url('/mod/voiceshadow/view.php', array('id' => $id));

$title = $course->shortname . ': ' . format_string($voiceshadow->name);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

/// Print the main part of the page

require_once('tabs.php');

if ($a == "list") {
    voiceshadow_view_dates();

    $table = new html_table();
    $table->width = "100%";

    if ($voiceshadow->grademethod == "like")
        $peertext = get_string("cell3::peerfeedback", "voiceshadow") . html_writer::empty_tag("br") . html_writer::empty_tag("img", array("src" => new moodle_url('/mod/voiceshadow/img/flike.png'), "alt" => get_string("likethis", "voiceshadow"), "title" => get_string("dislike", "voiceshadow"), "class" => "vs-like"));
    else
        $peertext = get_string("cell3::peer", "voiceshadow");

    if (!voiceshadow_is_ios()) {
        $titlesarray = array(get_string("cell1::student", "voiceshadow") => 'username', get_string("cell2::", "voiceshadow") => '', $peertext => '', get_string("cell4::teacher", "voiceshadow") => '');

        $table->head = voiceshadow_make_table_headers($titlesarray, $orderby, $sort, 'view.php?id=' . $id);
        //$table->head  = array(get_string("cell1::student", "voiceshadow"), get_string("cell2::", "voiceshadow"), get_string("cell3::peer", "voiceshadow"), get_string("cell4::teacher", "voiceshadow"));
        $table->align = array("left", "center", "center", "center");
    } else {
        $titlesarray = array();
    }


    //$alluserslist  = $DB->get_records("user", array(), $sort." ".$orderby);

    //foreach($alluserslist as $k => $v) {
    $lists = $DB->get_records("voiceshadow_files", array("instance" => $id), 'time DESC');

    foreach ($lists as $list) {
        $name = "var" . $list->var . "text";

        $userdata = $DB->get_record("user", array("id" => $list->userid));
        $picture = $OUTPUT->user_picture($userdata, array('popup' => true));

        $own = $DB->get_record("voiceshadow_ratings", array("fileid" => $list->id, "userid" => $list->userid));

        if (@empty($own->ratingrhythm)) @$own->ratingrhythm = get_string('norateyet', 'voiceshadow');
        if (empty($own->ratingclear)) $own->ratingclear = get_string('norateyet', 'voiceshadow');
        if (empty($own->ratingintonation)) $own->ratingintonation = get_string('norateyet', 'voiceshadow');
        if (empty($own->ratingspeed)) $own->ratingspeed = get_string('norateyet', 'voiceshadow');
        if (empty($own->ratingreproduction)) $own->ratingreproduction = get_string('norateyet', 'voiceshadow');

        //1-cell
        $o = "";
        $o .= html_writer::start_tag('div', array("style" => "text-align:left;margin:10px 0;"));
        $o .= html_writer::tag('span', $picture);
        $o .= html_writer::start_tag('span', array("style" => "margin: 8px;position: absolute;"));
        $o .= html_writer::link(new moodle_url('/user/view.php', array("id" => $userdata->id, "course" => $cm->course)), fullname($userdata));
        $o .= html_writer::end_tag('span');
        $o .= html_writer::end_tag('div');

        $o .= html_writer::tag('div', $list->summary, array('style' => 'margin:10px 0;'));

        $o .= html_writer::tag('div', voiceshadow_player($list->id));

        if (!empty($voiceshadow->{$name}))
            $o .= html_writer::tag('div', "(" . $voiceshadow->{$name} . ")");


        if (!empty($list->speechtext))
            $o .= html_writer::tag('div', '<a href="#" onclick="$(this).parent().find(\'div\').toggle();return false;">Speech text (Show/Hide)</a>: <div style="display:none">' . $list->speechtext . "</div>");

        if ($voiceshadow->showscore == 1)
            $o .= html_writer::tag('div', "(" . html_writer::tag('small', get_string("computerized", "voiceshadow")) . " " . voiceshadow_similar_text($list->speechtext, $voiceshadow->{"var" . $list->var . "transcript"}) . "%)");


        $o .= html_writer::tag('div', html_writer::tag('small', date(get_string("timeformat1", "voiceshadow"), $list->time)), array("style" => "float:left;"));

        if ($list->userid == $USER->id || has_capability('mod/voiceshadow:teacher', $context)) {
            if ($list->userid == $USER->id)
                $editlink = html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $id, "a" => "add", "fileid" => $list->id)), get_string("editlink", "voiceshadow")) . " ";
            else
                $editlink = "";

            if (has_capability('mod/voiceshadow:teacher', $context) || ($voiceshadow->resubmit == 1 && $list->userid == $USER->id))
                $deletelink = html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $id, "act" => "deleteentry", "fileid" => $list->id)), get_string("delete", "voiceshadow"), array("onclick" => "return confirm('" . get_string("confim", "voiceshadow") . "')"));
            else
                $deletelink = "";

            $o .= html_writer::tag('div', html_writer::tag('small', $editlink . $deletelink, array("style" => "margin: 2px 0 0 10px;")));
        }

        //$o .= html_writer::tag('div', get_string('speed', 'voiceshadow') . ': ' . $list->speed, array('style' => 'margin:10px 0;'));

        $cell1 = new html_table_cell($o);

        //2-cell
        $table2 = new html_table();
        $table2->width = "100%";

        if (voiceshadow_is_ios()) {
            $table2->data[] = new html_table_row(array(new html_table_cell(get_string("table2::cell1::pronunciation", "voiceshadow")), new html_table_cell(voiceshadow_set_rait($list->id, 1))));
            $table2->data[] = new html_table_row(array(new html_table_cell(get_string("table2::cell2::fluency", "voiceshadow")), new html_table_cell(voiceshadow_set_rait($list->id, 2))));
            $table2->data[] = new html_table_row(array(new html_table_cell(get_string("table2::cell3::content", "voiceshadow")), new html_table_cell(voiceshadow_set_rait($list->id, 3))));
            $table2->data[] = new html_table_row(array(new html_table_cell(get_string("table2::cell4::organization", "voiceshadow", "voiceshadow")), new html_table_cell(voiceshadow_set_rait($list->id, 4))));
            $table2->data[] = new html_table_row(array(new html_table_cell(get_string("table2::cell5::eye", "voiceshadow")), new html_table_cell(voiceshadow_set_rait($list->id, 5))));
        } else {
            $table2->head = array(get_string("table2::cell1::pronunciation", "voiceshadow"), get_string("table2::cell2::fluency", "voiceshadow"), get_string("table2::cell3::content", "voiceshadow"), get_string("table2::cell4::organization", "voiceshadow"), get_string("table2::cell5::eye", "voiceshadow"));
            //$table2->align = array ("center", "center", "center", "center", "center");
            $table2->align = array("center" . get_string("table2::style", "voiceshadow"), "center" . get_string("table2::style", "voiceshadow"), "center" . get_string("table2::style", "voiceshadow"), "center" . get_string("table2::style", "voiceshadow"), "center" . get_string("table2::style", "voiceshadow"));

            $table2->data[] = array(voiceshadow_set_rait($list->id, 1),
                voiceshadow_set_rait($list->id, 2),
                voiceshadow_set_rait($list->id, 3),
                voiceshadow_set_rait($list->id, 4),
                voiceshadow_set_rait($list->id, 5));
        }

        //----Comment Box-----/
        //if ($list->userid == $USER->id){
        $chtml = "";
        if ($comments = $DB->get_records("voiceshadow_comments", array("fileid" => $list->id))) {
            foreach ($comments as $comment) {
                $chtml .= html_writer::start_tag('div', array("style" => "border:1px solid #333;margin:5px;text-align:left;padding:5px;"));

                $chtml .= html_writer::tag('div', $comment->summary, array('style' => 'margin:10px 0;'));

                //if (!empty($comment->itemid)) {
                $chtml .= html_writer::tag('div', voiceshadow_player($comment->id, "voiceshadow_comments"));
                //}

                $chtml .= html_writer::tag('div', html_writer::tag('small', date(get_string("timeformat1", "voiceshadow"), $comment->time)), array("style" => "float:left;"));

                $student = $DB->get_record("user", array("id" => $comment->userid));
                $studentlink = html_writer::link(new moodle_url('/user/view.php', array("id" => $student->id, "course" => $cm->course)), fullname($student));

                //if ($comment->userid == $USER->id || has_capability('mod/voiceshadow:teacher', $context)) {
                if (has_capability('mod/voiceshadow:teacher', $context) || ($voiceshadow->resubmit == 1 && $comment->userid == $USER->id)) {
                    $deletelink = html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $id, "act" => "deletecomment", "fileid" => $comment->id)), get_string("delete", "voiceshadow"), array("onclick" => "return confirm('" . get_string("confim", "voiceshadow") . "')"));
                } else {
                    $deletelink = "";
                }

                if (has_capability('mod/voiceshadow:teacher', $context) && $comment->userid == $USER->id) {
                    $editlink = html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $id, "a" => "add", "act" => "addcomment", "fileid" => $list->id, "commentid" => $comment->id)), get_string("editlink", "voiceshadow"));
                } else {
                    $editlink = "";
                }
                // }

                $chtml .= html_writer::tag('div', html_writer::tag('small', $studentlink . " " . $editlink . " " . $deletelink, array("style" => "margin: 2px 0 0 10px;")));

                $chtml .= html_writer::tag('div', NULL, array("style" => "clear:both"));

                $chtml .= html_writer::end_tag('div');
            }
        }

        if (has_capability('mod/voiceshadow:teacher', $context)) {
            $addcommentlink = html_writer::tag('div', html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $id, "a" => "add", "act" => "addcomment", "fileid" => $list->id)), get_string("addcomment", "voiceshadow")));
        } else {
            $addcommentlink = "";
        }

        /*
      } else
        $addcomment = "";*/
        //--------------------/

        if (voiceshadow_is_ios()) {
            //if ($list->userid != $USER->id){
            //  unset($table2->data);
            //}

            $table2->data[] = new html_table_row(array(new html_table_cell($peertext), new html_table_cell(voiceshadow_set_rait($list->id, 6))));
            $table2->data[] = new html_table_row(array(new html_table_cell(get_string("cell4::teacher", "voiceshadow")), new html_table_cell(voiceshadow_set_rait($list->id, 7))));

            $cell2 = new html_table_cell(html_writer::table($table2) . $chtml . $addcommentlink);
            $row = new html_table_row(array($cell1, $cell2));
            $table->data[] = $row;


            //$row = new html_table_row(array($cell2));
            //$table->data[] = $row;

            //$cell1f = new html_table_cell($cell1);
            //$cell2f = new html_table_cell(new html_table_cell(html_writer::table($table2) . $chtml . $addcommentlink));
            //$table->data[] = new html_table_row(array($cell1f, $cell2f));
        } else {
            //if ($list->userid == $USER->id)
            $cell2 = new html_table_cell(html_writer::table($table2) . $chtml . $addcommentlink);
            //else
            //  $cell2 = new html_table_cell($chtml . $addcommentlink);

            //3-cell
            $cell3 = new html_table_cell(voiceshadow_set_rait($list->id, 6));

            //4-cell
            $cell4 = new html_table_cell(voiceshadow_set_rait($list->id, 7));

            $cell1->sortdata = fullname($userdata);

            $cells = array($cell1, $cell2, $cell3, $cell4);

            $row = new html_table_row($cells);

            $table->data[] = $row;
        }
    }
    //}

    if ($voiceshadow->grademethodt == "rubrics") {
        echo html_writer::start_tag('div');
        echo html_writer::link(new moodle_url('/mod/voiceshadow/submissions.php', array("id" => $id)), get_string("rubrics", "voiceshadow"));
        echo html_writer::end_tag('div');
    }

    $totalpages = count($table->data);

    $table->data = voiceshadow_sort_table_data($table->data, $titlesarray, $orderby, $sort, $page, $perpage);

    $alinkpadding = new moodle_url("/mod/voiceshadow/view.php", array("id" => $id, "sort" => $sort, "orderby" => $orderby));

    echo $OUTPUT->render(new paging_bar($totalpages, $page, $perpage, $alinkpadding));

    if ($table)
        echo html_writer::table($table);

    echo $OUTPUT->render(new paging_bar($totalpages, $page, $perpage, $alinkpadding));

    echo html_writer::script('
 $(document).ready(function() {
  $(".voiceshadow_rate_box").change(function() {
    var value = $(this).val();
    var data  = $(this).attr("data-url");

    var e = $(this).parent();
    e.html(\'<img src="img/ajax-loader.gif" />\');

    $.get("ajax.php", {id: ' . $id . ', act: "setrating", data: data, value: value}, function(data) {
      e.html(data);
    });
  });
 });
    ');

}


if ($a == "add") {
    class voiceshadow_comment_form extends moodleform
    {
        function definition()
        {
            global $CFG, $USER, $DB, $course, $fileid, $id, $act, $commentid, $voiceshadow;

            $audioVars = array();
            $time = time();
            $filename = str_replace(" ", "_", $USER->username) . "_" . date("Ymd_Hi", $time);

            $mform =& $this->_form;

            $mform->disable_form_change_checker();


            //-----Speech to text plugin----------------//

            //if ($voiceshadow->speechtotext == 1 && voiceshadow_get_browser() == 'chrome')
                //$mform->addElement('html', '<div id="foo"> <div class="p-header k" style="padding: 0px 0px 4px; margin: 0px;"> <table cellpadding="0" class="cf Ht"><tbody><tr id=":2jc"><td><div id=":2k1" class="Hp"><div class="aYF" style="width:152px">Use speech to text&lrm;</div></div></td><td class="Hm"><img class="Hl" id=":256" src="img/cleardot.gif"><img class="Hq" id=":257" src="img/cleardot.gif"></td></tr></tbody></table> </div><div class="p-content"> <div style="width: 100%;background-color: #fff;"> <div style="margin:0;width:280px;"> <div style="float:left;width: 120px;margin: 10px 20px 0 0;"><!--<button type="button" style="width: 134px;" id="p-start-record">Start transcribing</button>--></div><div style="float:left;width: 120px;font-size: 60%;padding: 3px;" class="" id="p-rec-notice">Click "Start transcribing" button when you are ready.</div><div style="clear:both;"></div></div><textarea id="speechtext" style="width: 250px;height: 180px;margin: 0 0 0 8px;"></textarea> <div style="margin:10px 0 0 10px;width:260px;"> <div style="float:left;width: 120px;margin: 0 20px 0 0;"><!--<button type="button" style="width: 120px;" id="p-speech-text">Speak it!</button>--></div><div style="float:left;width: 120px;"><!--<button type="button" style="width: 120px;" id="p-clear-text">Clear text</button>--></div><div style="clear:both;"></div></div></div></div></div>');

            //--------------Checking Embed code---------//
            if (!empty($voiceshadow->embedvideo)) {
                $mform->addElement('header', 'Embed', get_string('embedcode', 'voiceshadow'));
                $mform->addElement('static', 'description', '', $voiceshadow->embedvideo);
            }
            //------------------------------------------//

            //--------------Uploadd MP3 ----------------//
            //if (!voiceshadow_is_ios()) {
            $filepickeroptions = array();
            $filepickeroptions['maxbytes'] = get_max_upload_file_size($voiceshadow->maxbytes);
            $mform->addElement('header', 'mp3upload', get_string('mp3upload', 'voiceshadow'));
            $mform->addElement('filepicker', 'submitfile', get_string('uploadmp3', 'voiceshadow'), null, $filepickeroptions);
            //}

            //-------------- Listen to recorded audio ----------------//
            $mform->addElement('header', 'listentorecordedaudio', get_string('listentorecordedaudio', 'voiceshadow'));

            for ($i = 1; $i <= 5; $i++) {
                $name = "var{$i}";
                $nametext = "var{$i}text";
                if (!empty($voiceshadow->{$name})) {
                    if ($item = $DB->get_record("files", array("id" => $voiceshadow->{$name}))) {

                        $link = new moodle_url("/mod/voiceshadow/file.php?file=" . $voiceshadow->{$name});

                        $audioVars[$i] = $voiceshadow->{$name};

                        if (!isset($linkhtml5mp3))
                            $linkhtml5mp3 = $link;

                        if ($i == 1)
                            $checked = 'checked="checked"';
                        else
                            $checked = '';

                        $o = '<div style="margin:10px 0">';

                        //if (voiceshadow_is_ios() || voiceshadow_get_browser() == 'chrome' || voiceshadow_get_browser() == 'android') {
                        $o .= '<div style="float:left;"><audio src="' . $link . '" controls="controls" id="listen-player-' . $i . '"><a href="' . $link . '">audio</a></audio></div>';

                        $style_listen_select = "";

                        if ($voiceshadow->showspeedbox == 1 || $voiceshadow->showspeedbox == 3) { } else {
                            $style_listen_select = "display:none";
                        }

                        $levels = array("1" => get_string('speedchange', 'voiceshadow'), "0.7" => "70%", "0.8" => "80%", "0.9" => "90%", "1" => get_string('normal', 'voiceshadow'));

                        $o .= html_writer::start_tag('div', array('style' => 'float:left'));
                        $o .= html_writer::select($levels, '', '', true, array("id" => "listen-select-{$i}", "style" => $style_listen_select));
                        $o .= html_writer::end_tag('div');

                        if ($voiceshadow->showspeedbox == 2 || $voiceshadow->showspeedbox == 3) {
                            $o .= html_writer::start_tag('div', array('style' => 'float:left'));
                            $o .= html_writer::empty_tag('input', array('type' => 'text', 'id' => 'listen-select-own-' . $i, 'value' => 100, 'style' => 'width:45px;margin-left:20px;'));
                            $o .= html_writer::empty_tag('input', array('type' => 'button', 'data-url' => $i, 'class' => 'listen-select-own-set', 'value' => get_string('speedchange', 'voiceshadow')));
                            $o .= html_writer::end_tag('div');
                        }

                        if ($voiceshadow->showspeedbox >= 1) {
                            $o .= '
<div style="clear:both"></div>
<script>
    var speed' . $i . ' = 1;
    function initAudio' . $i . '(){
        var audioListener' . $i . ' = document.getElementById("listen-player-' . $i . '");
        var speedlist' . $i . ' = document.getElementById("listen-select-' . $i . '");
        speedlist' . $i . '.addEventListener("change",changeSpeed' . $i . ');
        function changeSpeed' . $i . '(event){
            audioListener' . $i . '.playbackRate = event.target.value;
            speed' . $i . ' = event.target.value;
            //console.log (event.target.value);
            $(\'input[name=speed]\').val(Math.round(speed' . $i . '*100));
        }
        function changeSpeedF' . $i . '(v){
            audioListener' . $i . '.playbackRate = v;
            //console.log (v);
            speed' . $i . ' = v;
            $(\'input[name=speed]\').val(Math.round(speed' . $i . '*100));
        }
        $(\'.listen-select-own-set\').click(function() {
            var i = $(this).attr(\'data-url\');
            var val = $("#listen-select-own-"+i).val();
            val = val.replace(/%/g,\'\');
            if (val > 2) {
              val = val / 100;
            }
            changeSpeedF' . $i . '(val);
        });
	}
    $(\'#listen-player-' . $i . '\').on(\'playing\', function() {
       var audioListener' . $i . ' = document.getElementById("listen-player-' . $i . '");
       audioListener' . $i . '.playbackRate = speed' . $i . ';
    });
	window.addEventListener("load", initAudio' . $i . ');
</script>';
                        }

                        //} else {
                        //    $o .= html_writer::script('var fn = function() {var att = { data:"' . (new moodle_url("/mod/voiceshadow/js/mp3player.swf")) . '", width:"90", height:"15" };var par = { flashvars:"src=' . $link . '" };var id = "audios_' . $voiceshadow->{$name} . '";var myObject = swfobject.createSWF(att, par, id);};swfobject.addDomLoadEvent(fn);');
                        //    $o .= '<div><div id="audios_' . $voiceshadow->{$name} . '"><a href="' . $link . '">audio</a></div></div>';
                        //}

                        $o .= '</div>';

                        $mform->addElement('static', 'description', '', $o);
                    }
                }
            }
            //-------------- END -------------------------------------//


            //-------------- Record ----------------//
            if ($voiceshadow->recorder == 0) {
                if (voiceshadow_is_ios()) {
                    $recorderType = "ios";
                } else if (voiceshadow_get_browser() == 'chrome' || voiceshadow_get_browser() == 'android') {
                    $recorderType = "html5";
                } else {
                    $recorderType = "flash";
                }
            } else if ($voiceshadow->recorder == 1) {
                $recorderType = "html5";
            } else if ($voiceshadow->recorder == 2) {
                $recorderType = "flash";
            }

            $mediadata = "";

            if ($CFG->voiceshadow_stt_core == "google") {

                /*
                 * Google recording
                 */

                if ($recorderType == "ios") { // || voiceshadow_get_browser() == 'android'
                    $mediadata .= html_writer::start_tag("h3", array("style" => "padding: 0 20px;"));

                    if ($voiceshadow->shadowingmode == 2) {
                        $mediadata .= html_writer::start_tag("a", array("href" => 'voiceshadow://?link=' . $CFG->wwwroot . '&id=' . $id . '&uid=' . $USER->id . '&time=' . $time . '&fid=0&var=1&audioBtn=0&mod=voiceshadow', "id" => "id_recoring_link",
                            "onclick" => 'formsubmit(this.href)'));
                    } else {
                        $mediadata .= html_writer::start_tag("a", array("href" => 'voiceshadow://?link=' . $CFG->wwwroot . '&id=' . $id . '&uid=' . $USER->id . '&time=' . $time . '&fid=' . $audioVars[1] . '&var=1&audioBtn=1&sstBtn=1&type=voiceshadow&mod=voiceshadow', "id" => "id_recoring_link",  //
                            "onclick" => 'formsubmit(this.href)'));
                    }


                    $mediadata .= get_string('recordvoice', 'voiceshadow');
                    $mediadata .= html_writer::end_tag('a');
                    $mediadata .= html_writer::end_tag('h3');

                    $mediadata .= html_writer::start_tag("div", array("style" => "font-size: 21px;line-height: 40px;color: #333;"));
                    $mediadata .= "Recordings";
                    $mediadata .= html_writer::end_tag('div');

                    //$mediadata .= '<div id="recordappfile_debug" controls></div>';

                    $mediadata .= html_writer::start_tag("ul", array("id" => "recordingslist", "style" => "display:none; list-style-type: none;"));
                    $mediadata .= '<li><audio id="recordappfile_aac" controls></audio></li>';
                    $mediadata .= html_writer::end_tag('ul');

                    $mediadata .= html_writer::script('
setInterval(function(){
    $.get( "ajax-apprecord.php", { id: ' . $id . ', uid: ' . $USER->id . ' }, function(json){
        var j = JSON.parse(json);
        var t = +new Date();

        //$(\'#recordappfile_debug\').html(t+": "+json);

        if (j.status == "success") {
            $(\'#recordappfile_aac\').html("adding...");
            $(\'#recordingslist\').show();
            $(\'#recordappfile_aac\').append(\'<source src="' . $CFG->wwwroot . '/mod/voiceshadow/file.php?file=\'+j.fileid+\'" type="audio/aac" /> <input type="hidden" name="speechtext" value="\'+j.text+\'" />\');
            $("#id_submitfile").val(j.itemid);

            $.get( "ajax-apprecord.php", { a: "delete", id: ' . $id . ', uid: ' . $USER->id . ' });
        }
    } );
}, 1000);
                ');

                } else if ($recorderType == "html5") {

                    $additionalCodeSpeechToTextBox = "";

                    if ($voiceshadow->speechtotext == 1 && voiceshadow_get_browser() == 'chrome')
                        $additionalCodeSpeechToTextBox = '<textarea id="speechtext" style="width: 650px;height: 40px;margin: 0 0 0 8px;" readonly></textarea>';

                    $mediadata .= '

  <!--<div style="font-size: 21px;line-height: 40px;color: #333;">Record</div>-->
  
  <div>
   <svg width="48" height="48" viewBox="0 0 500 500" id="voiceShadowMicSvg" style="float: left">
    <path d="M242.25,306c43.35,0,76.5-33.15,76.5-76.5v-153c0-43.35-33.15-76.5-76.5-76.5c-43.35,0-76.5,33.15-76.5,76.5v153 C165.75,272.85,198.9,306,242.25,306z M377.4,229.5c0,76.5-63.75,130.05-135.15,130.05c-71.4,0-135.15-53.55-135.15-130.05H63.75 c0,86.7,68.85,158.1,153,170.85v84.15h51v-84.15c84.15-12.75,153-84.149,153-170.85H377.4L377.4,229.5z"/>
    </svg>
    
    <ul style="float: left; list-style-type:none;font-size: 34px;color: #ccc;" onclick="$(\'#voiceshadow_recordingCounter_OnOf\').toggle();$(\'.voiceshadow_counter_numbers\').toggle();">
    <li style="display:none;" id="voiceshadow_recordingCounter_OnOf">Recording counter disabled</li>
    <li style="float: left" class="voiceshadow_counter_numbers" id="voiceShadow_timer_Min">00</li>
    <li style="float: left" class="voiceshadow_counter_numbers">:</li>
    <li style="float: left" class="voiceshadow_counter_numbers" id="voiceShadow_timer_Sec">00</li>
    <li style="float: left" class="voiceshadow_counter_numbers">:</li>
    <li style="float: left" class="voiceshadow_counter_numbers" id="voiceShadow_timer_milSec">00</li>
</ul>
<div style="clear: both"></div>

</div>

  <!--<img src="img/spiffygif_30x30.gif" style="display:none;" id="html5-mp3-loader"/>-->
  <button onclick="startRecording(this);" id="btn_rec" disabled style="margin-left: 60px;"  class="button-xl">record</button>
  <button onclick="stopRecording(this);" id="btn_stop" disabled  class="button-xl">stop</button>

  <div style="margin: 20px 0;">' . $additionalCodeSpeechToTextBox . '</div>

  <div style="font-size: 21px;line-height: 40px;color: #333;">Recordings</div>
  <ul id="recordingslist" style="list-style-type: none;"></ul>

  <div style="font-size: 21px;line-height: 40px;color: #333;display:none;">Log</div>
  <pre id="log" style="display:none"></pre>

  <script>
  
  recognition.lang = "' . $CFG->voiceshadow_speechtotextlang . '";
  
  var timerCount = 0;
  var timerCountMilSec = 0;
  
  var btnRecordSvg;
  var btnRecordSec;
  var btnRecordMilSec;

  $(".selectaudiomodel").click(function(){
    $("#audioshadowmp3").attr("src", $(this).parent().find("audio").attr("src"));
    __log($(this).parent().find("audio").attr("src"));
  });

  function __log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || \'\');
  }

  var audio_context;
  var recorder;

  function startUserMedia(stream) {
    var input = audio_context.createMediaStreamSource(stream);
    __log(\'Media stream created.\' );
    __log("input sample rate " +input.context.sampleRate);

    //input.connect(audio_context.destination);
    //__log(\'Input connected to audio context destination.\');

    recorder = new Recorder(input, {
                  numChannels: 1,
                  sampleRate: 48000,
                });
    __log(\'Recorder initialised.\');
  }

  function startRecording(button) {
      $("#voiceShadow_timer_Min").html("00");
      $("#voiceShadow_timer_Sec").html("00");
      $("#voiceShadow_timer_milSec").html("00");
      
      timerCount = 0;
      timerCountMilSec = 0;
      
      btnRecordSvg = setInterval(function () {
                if ($("svg#voiceShadowMicSvg").attr("fill") == "red") {
                    $("svg#voiceShadowMicSvg").attr("fill", "black");
                } else {
                    $("svg#voiceShadowMicSvg").attr("fill", "red");    
                }
      }, 500);
      
      
      btnRecordMilSec = setInterval(function () {
          timerCountMilSec = timerCountMilSec + 5;
          
          if (timerCountMilSec < 10) {
              var print = "0" + timerCountMilSec; 
          } else {
              var print = timerCountMilSec;
          }
          
          $("#voiceShadow_timer_milSec").html(print);
          
          if (timerCountMilSec >= 59.95) {
              timerCountMilSec = 0;
          }
          
      }, 50);
      
      
      btnRecordSec = setInterval(function () {
          timerCount = timerCount + 1;
                
          var min = $("#voiceShadow_timer_Min").html();
          var sec = $("#voiceShadow_timer_Sec").html();
                
          if (sec >= 59) {
              sec = "00";
              min = parseInt(min, 10) + 1;
              
              if (min <= 9){
                 min = "0" + min;
              }
          } else {
              sec = parseInt(sec, 10) + 1;
              if (sec <= 9) {
                  sec = "0" + sec;
              }
          } 
          
                
          $("#voiceShadow_timer_Sec").html(sec);
          $("#voiceShadow_timer_Min").html(min);
                
      }, 1000);
            
    recorder.startRecording();
    button.disabled = true;
    button.nextElementSibling.disabled = false;
    __log(\'Recording...\');
  }

  function stopRecording(button) {
      clearTimeout(btnRecordSec);
      clearTimeout(btnRecordSvg);
      clearTimeout(btnRecordMilSec);
      $("svg#voiceShadowMicSvg").attr("fill", "black");
      
      console.log(timerCount);
      
    recorder.finishRecording();
    button.disabled = true;
    button.previousElementSibling.disabled = false;
    __log(\'Stopped recording.\');
  }

  window.onload = function init() {
    // navigator.getUserMedia shim
    navigator.getUserMedia =
      navigator.getUserMedia ||
      navigator.webkitGetUserMedia ||
      navigator.mozGetUserMedia ||
      navigator.msGetUserMedia;
    
    // URL shim
    window.URL = window.URL || window.webkitURL;
    
    // audio context + .createScriptProcessor shim
    var audioContext = new AudioContext;
    if (audioContext.createScriptProcessor == null)
      audioContext.createScriptProcessor = audioContext.createJavaScriptNode;
    
    var testTone = (function() {
      var osc = audioContext.createOscillator(),
          lfo = audioContext.createOscillator(),
          ampMod = audioContext.createGain(),
          output = audioContext.createGain();
      lfo.type = \'square\';
      lfo.frequency.value = 2;
      osc.connect(ampMod);
      lfo.connect(ampMod.gain);
      output.gain.value = 0.5;
      ampMod.connect(output);
      osc.start();
      lfo.start();
      return output;
    })();
    
    

    
    var testToneLevel = audioContext.createGain(),
        microphone = undefined,     // obtained by user click
        microphoneLevel = audioContext.createGain(),
        mixer = audioContext.createGain();
    
    testTone.connect(testToneLevel);
    testToneLevel.gain.value = 0;
    //testToneLevel.connect(mixer);
    microphoneLevel.gain.value = 0.5;
    microphoneLevel.connect(mixer);
    //mixer.connect(audioContext.destination);

      if (microphone == null)
        navigator.getUserMedia({ audio: true },
          function(stream) {
            microphone = audioContext.createMediaStreamSource(stream);
            microphone.connect(microphoneLevel);
          },
          function(error) {
          console.log("Could not get audio input.");
            audioRecorder.onError(audioRecorder, "Could not get audio input.");
          });
    
    
        recorder = new WebAudioRecorder(mixer, {
          workerDir: "js/"
        });
        
        recorder.setEncoding("mp3");
        
          recorder.setOptions({
        timeLimit: 300,
        mp3: { bitRate: 64 }
      });
    
    recorder.onComplete = function(recorder, blob) {
      window.LatestBlob = blob;
      
      var time = new Date(),
      url = URL.createObjectURL(blob),
      html = "<p recording=\'" + url + "\'>" +
             "<audio controls src=\'" + url + "\'></audio> " +
             "</p>";
      
      $("#recordingslist").html(html);
                    
      //saveRecording(blob, recorder.encoding);
      uploadAudio(blob);
      

    };
    
  };
  
  	
	function uploadAudio(mp3Data){
		var reader = new FileReader();
		reader.onload = function(event){
			var fd = new FormData();
			var mp3Name = encodeURIComponent(\'audio_recording_\' + new Date().getTime() + \'.mp3\');
			console.log("mp3name = " + mp3Name);
			fd.append(\'name\', mp3Name);
			fd.append(\'p\', $(\'#audioshadowmp3\').attr("data-url"));
			fd.append(\'audio\', event.target.result);
			$.ajax({
				type: \'POST\',
				url: \'uploadmp3.php\',
				data: fd,
				processData: false,
				contentType: false
			}).done(function(data) {
				//console.log(data);
				obj = JSON.parse(data);
				$("#id_submitfile").val(obj.id);
				
				log.innerHTML += "\n" + data;
			});
		};      
		reader.readAsDataURL(mp3Data);
	}

  function jInit(){
      audio = $("#audioshadowmp3");
      addEventHandlers();
  }

  function addEventHandlers(){
      $("#btn_rec").click(startAudio);
      $("#btn_stop").click(stopAudio);
  }

  function loadAudio(){
      audio.bind("load",function(){
        __log(\'MP3 Audio Loaded succesfully\');
        $(\'#btn_rec\').removeAttr( "disabled" );
      });
      audio.trigger(\'load\');
      //startAudio()
  }

  function startAudio(){
      __log(\'MP3 Audio Play\');
      audio.trigger(\'play\');
  }

  function pauseAudio(){
      __log(\'MP3 Audio Pause\');
      audio.trigger(\'pause\');
  }

  function stopAudio(){
      pauseAudio();
      audio.prop("currentTime",0);
  }

  function forwardAudio(){
      pauseAudio();
      audio.prop("currentTime",audio.prop("currentTime")+5);
      startAudio();
  }

  function backAudio(){
      pauseAudio();
      audio.prop("currentTime",audio.prop("currentTime")-5);
      startAudio();
  }

  function volumeUp(){
      var volume = audio.prop("volume")+0.2;
      if(volume >1){
        volume = 1;
      }
      audio.prop("volume",volume);
  }

  function volumeDown(){
      var volume = audio.prop("volume")-0.2;
      if(volume <0){
        volume = 0;
      }
      audio.prop("volume",volume);
  }

  function toggleMuteAudio(){
      audio.prop("muted",!audio.prop("muted"));
  }

  $( document ).ready(function() {
     jInit();
     loadAudio();

     //$("#id_Recording").find(".fitemtitle").append(\'<img src="img/spiffygif_30x30.gif" style="display:none;" id="html5-mp3-loader"/>\');
  });
</script>';

                    if (isset($linkhtml5mp3))
                        $mediadata .= ' <audio src="' . $linkhtml5mp3 . '" id="audioshadowmp3" autobuffer="autobuffer" data-url="' . urlencode(json_encode(array("id" => $id, "userid" => $USER->id))) . '"></audio>
                  ';
                    else
                        $mediadata .= ' <audio src="" id="audioshadowmp3" autobuffer="autobuffer" data-url="' . urlencode(json_encode(array("id" => $id, "userid" => $USER->id))) . '"></audio>
                  ';
                } else {
                    $filename = str_replace(" ", "_", $USER->username) . "_" . date("Ymd_Hi", $time);

                    $speechcode = "";

                    $mediadata = html_writer::script('var flashvars={};flashvars.gain=35;flashvars.rate=44;flashvars.call="callbackjs";flashvars.name = "' . $filename . '";flashvars.p = "' . urlencode(json_encode(array("id" => $id, "userid" => $USER->id))) . '";flashvars.url = "' . urlencode(new moodle_url("/mod/voiceshadow/uploadmp3.php")) . '";' . $speechcode . 'swfobject.embedSWF("' . (new moodle_url("/mod/voiceshadow/js/recorder.swf")) . '", "mp3_flash_recorder", "220", "200", "9.0.0", "expressInstall.swf", flashvars);');
                    $mediadata .= '<div id="mp3_flash_recorder"></div><div id="mp3_flash_records" style="margin:20px 0;"></div>';


                    /*
                    * Safari fix
                    */
                    if (voiceshadow_get_browser() == 'safari')
                        $preplayer = '<embed xmlns="http://www.w3.org/1999/xhtml" align="" allowFullScreen="false" flashvars="src=' . $CFG->wwwroot . '\'+obj.url+\'" height="15" width="90" pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="' . (new moodle_url("/mod/voiceshadow/js/mp3player.swf")) . '" type="application/x-shockwave-flash" />';
                    else
                        $preplayer = '<audio controls id="peviewaudio" oncanplay="this.volume=1"><source src="' . $CFG->wwwroot . '\'+obj.url+\'" preload="auto" type="audio/mpeg"></audio>';


                    echo html_writer::script('
function chooserecord(e){
console.log("1");
  $(".choosingrecord").html(\'<img src="' . (new moodle_url("/mod/voiceshadow/img/right-arrow-gray.png")) . '" style="margin-top: 6px;"/>\');
  $(e).html(\'<img src="' . (new moodle_url("/mod/voiceshadow/img/right-arrow.png")) . '" style="margin-top: 6px;"/>\');
  $("#id_submitfile").val($(e).attr("data-url"));
  $("#id_speechtext").val($(e).attr("data-text"));
}
function callbackjs(e){
  /*
  * Speech to text box stop
  */
  if ($(".p-content").length > 0) {
    recognition.stop();
    window.recordmark = 0;
    var stext = $("#speechtext").val();
    $("#speechtext").val("");
  } else
    var stext = "";


  $("#id_speechtext").val(""+stext+"");
  $(".choosingrecord").html(\'<img src="' . (new moodle_url("/mod/voiceshadow/img/right-arrow-gray.png")) . '" style="margin-top: 6px;"/>\');
  obj = JSON.parse(e.data);

  console.log("2");

  $("#mp3_flash_records").prepend(\'<div class="recordings"><div class="choosingrecord" style="float:left;cursor: pointer;" data-url="\'+obj.id+\'" data-text="\'+stext+\'" onclick="chooserecord(this)"><img src="' . (new moodle_url("/mod/voiceshadow/img/right-arrow.png")) . '" style="margin-top: 6px;"/></div><div style="float:left;"><div>' . $preplayer . '</div><div style="margin-bottom: 10px;width: 275px;padding: 10px;border: 1px dashed #666;background-color: #eeefff;">\'+stext+\'</div></div><div style="clear:both;"></div></div>\');
  $("#id_submitfile").val(obj.id);

  if($("#mp3_flash_records > div").size() >= ' . ($voiceshadow->allowmultiple + 1) . ') {
    $("#mp3_flash_records > div").last().remove();
  }

}
');

                }

            } else if($CFG->voiceshadow_stt_core == "amazon") {
                /*
                 * Amazon recording
                 */


                $additionalCodeSpeechToTextBox = "";

                if ($voiceshadow->speechtotext == 1)
                    $additionalCodeSpeechToTextBox = '<textarea id="speechtext" style="width: 650px;height: 80px;margin: 0 0 0 8px;" readonly></textarea>';


                $mediadata .= html_writer::start_tag('div');
                $mediadata .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => "amazon_language", 'value' => $CFG->voiceshadow_speechtotextlang));
                $mediadata .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => "amazon_region", 'value' => $CFG->voiceshadow_amazon_region));
                $mediadata .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => "amazon_accessid", 'value' => $CFG->voiceshadow_amazon_accessid));
                $mediadata .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => "amazon_secretkey", 'value' => $CFG->voiceshadow_amazon_secretkey));
                $mediadata .= html_writer::end_tag('div');


                $mediadata .= '
  
  <div>
   <svg width="48" height="48" viewBox="0 0 500 500" id="voiceShadowMicSvg" style="float: left">
    <path d="M242.25,306c43.35,0,76.5-33.15,76.5-76.5v-153c0-43.35-33.15-76.5-76.5-76.5c-43.35,0-76.5,33.15-76.5,76.5v153 C165.75,272.85,198.9,306,242.25,306z M377.4,229.5c0,76.5-63.75,130.05-135.15,130.05c-71.4,0-135.15-53.55-135.15-130.05H63.75 c0,86.7,68.85,158.1,153,170.85v84.15h51v-84.15c84.15-12.75,153-84.149,153-170.85H377.4L377.4,229.5z"/>
    </svg>
    
    <ul style="float: left; list-style-type:none;font-size: 34px;color: #ccc;" onclick="$(\'#voiceshadow_recordingCounter_OnOf\').toggle();$(\'.voiceshadow_counter_numbers\').toggle();">
    <li style="display:none;" id="voiceshadow_recordingCounter_OnOf">Recording counter disabled</li>
    <li style="float: left" class="voiceshadow_counter_numbers" id="voiceShadow_timer_Min">00</li>
    <li style="float: left" class="voiceshadow_counter_numbers">:</li>
    <li style="float: left" class="voiceshadow_counter_numbers" id="voiceShadow_timer_Sec">00</li>
    <li style="float: left" class="voiceshadow_counter_numbers">:</li>
    <li style="float: left" class="voiceshadow_counter_numbers" id="voiceShadow_timer_milSec">00</li>
</ul>
<div style="clear: both"></div>

</div>

  <!--<img src="img/spiffygif_30x30.gif" style="display:none;" id="html5-mp3-loader"/>-->
  <button id="start-button" disabled style="margin-left: 60px;" class="button-xl" title="Start Transcription">     
    <i class="fa fa-microphone"></i> Start
  </button>
  <button id="stop-button" class="button-xl" disabled>
    <i class="fa fa-stop-circle"></i> Stop
  </button>

  <div style="margin: 20px 0;">' . $additionalCodeSpeechToTextBox . '</div>

  <div style="font-size: 21px;line-height: 40px;color: #333;">Recordings</div>
  <ul id="mp3_file" style="list-style-type: none;"></ul>

  <div style="font-size: 21px;line-height: 40px;color: #333;display:none;">Log</div>
  <pre id="log" style="display:none"></pre>
';

                if (isset($linkhtml5mp3))
                    $mediadata .= ' <audio src="' . $linkhtml5mp3 . '" id="audioshadowmp3" autobuffer="autobuffer" data-url="' . urlencode(json_encode(array("id" => $id, "userid" => $USER->id))) . '"></audio>
                  ';
                else
                    $mediadata .= ' <audio src="" id="audioshadowmp3" autobuffer="autobuffer" data-url="' . urlencode(json_encode(array("id" => $id, "userid" => $USER->id))) . '"></audio>
                  ';



                $mediadata .= html_writer::script(null, new moodle_url('/mod/voiceshadow/js/amazon/lame.js'));
                $mediadata .= html_writer::script(null, new moodle_url('/mod/voiceshadow/js/amazon/main.js'));

            }

            $mform->addElement('header', 'Recording', get_string('recordvoice', 'voiceshadow'));


//Audio listen-record

            for ($i = 1; $i <= 5; $i++) {
                $name = "var{$i}";
                $nametext = "var{$i}text";


                /*
                 * Hide select box if only one recording exist
                 */
                if (empty($voiceshadow->var2)) {
                    $hideFirstCheckboxMark = "display: none;";
                } else {
                    $hideFirstCheckboxMark = "";
                }

                if (!empty($voiceshadow->{$name})) {
                    if ($item = $DB->get_record("files", array("id" => $voiceshadow->{$name}))) {

                        $link = new moodle_url("/mod/voiceshadow/file.php?file=" . $voiceshadow->{$name});

                        if (!isset($linkhtml5mp3))
                            $linkhtml5mp3 = $link;

                        if ($i == 1)
                            $checked = 'checked="checked"';
                        else
                            $checked = '';

                        if ($voiceshadow->shadowingmode == 2) {
                            $o = '<div style="margin:10px 0;'.$hideFirstCheckboxMark.'">
                      <input type="radio" name="selectaudiomodel" value="' . $i . '" class="selectaudiomodel" id="id_selectaudiomodel_' . $i . '" style="float: left;margin: 0 20px 0 0;" ' . $checked . ' data-url="voiceshadow://?link=' . $CFG->wwwroot . '&id=' . $id . '&uid=' . $USER->id . '&fid=' . $audioVars[$i] . '&time=' . $time . '&var=' . $i . '&audioBtn=0&mod=voiceshadow" />
                      ';
                        } else {
                            $o = '<div style="margin:10px 0;'.$hideFirstCheckboxMark.'">
                      <input type="radio" name="selectaudiomodel" value="' . $i . '" class="selectaudiomodel" id="id_selectaudiomodel_' . $i . '" style="float: left;margin: 0 20px 0 0;" ' . $checked . ' data-url="voiceshadow://?link=' . $CFG->wwwroot . '&id=' . $id . '&uid=' . $USER->id . '&fid=' . $audioVars[$i] . '&time=' . $time . '&var=' . $i . '&audioBtn=1&type=voiceshadow&mod=voiceshadow" />
                      ';
                        }

                        if (voiceshadow_is_ios() || voiceshadow_get_browser() == 'chrome' || voiceshadow_get_browser() == 'android') {
                            $o .= '<div style="float:left;"><audio src="' . $link . '" id="audio_' . $voiceshadow->{$name} . '" controls="controls"><a href="' . $link . '">audio</a></audio></div><label for="id_selectaudiomodel_' . $i . '" style="float: left;margin-left: 20px;font-size: 15px;">' . $voiceshadow->{$nametext} . '</label><div style="clear:both;"></div>';
                        } else {
                            $o .= html_writer::script('var fn = function() {var att = { data:"' . (new moodle_url("/mod/voiceshadow/js/mp3player.swf")) . '", width:"90", height:"15" };var par = { flashvars:"src=' . $link . '" };var id = "audio_' . $voiceshadow->{$name} . '";var myObject = swfobject.createSWF(att, par, id);};swfobject.addDomLoadEvent(fn);');
                            $o .= '<div style="float:left;"><div id="audio_' . $voiceshadow->{$name} . '"><a href="' . $link . '">audio</a></div></div><label for="id_selectaudiomodel_' . $i . '" style="float: left;margin-left: 20px;font-size: 15px;">' . $voiceshadow->{$nametext} . '</label><div style="clear:both;"></div>';
                        }

                        if (!empty($voiceshadow->{"var" . $i . "transcript"}) && $voiceshadow->showscore == 1)
                            $o .= '<div id="transcript_' . $i . '">' . $voiceshadow->{"var" . $i . "transcript"} . '</div>';


                        $o .= '</div>';

                        $mform->addElement('static', 'description', '', $o);
                    }
                }
            }
//----
            if (!empty($fileid) && empty($act)) {
                $data = $DB->get_record("voiceshadow_files", array("id" => $fileid, "userid" => $USER->id));
            }

            $mform->addelEment('hidden', 'filename', $filename);
            $mform->addelEment('hidden', 'iphonelink', '');
            $mform->addelEment('hidden', 'speed', 100);
            $mform->addElement('static', 'description', '', $mediadata);

            if (!empty($fileid) && empty($act)) {
                $mform->setDefault("filename", $data->filename);
                $mform->addelEment('hidden', 'fileid', $fileid);
            }

            if (!empty($act)) {
                $mform->addelEment('hidden', 'act', $act);
                $mform->addelEment('hidden', 'fileid', $fileid);
            } else
                $mform->addelEment('hidden', 'act', 'newinstance');

            if ($voiceshadow->speechtotext == 1)
                $mform->addelEment('hidden', 'speechtext', 'null', array('id' => 'id_speechtext'));
            //-------------- Record -------END------//


            $mform->addElement('header', 'addcomment', get_string('addcomment', 'voiceshadow'));

            if (!empty($fileid) && empty($act)) {
                $mform->addElement('editor', 'summary', '')->setValue(array('text' => $data->summary));
            } else {
                if (!empty($act) && !empty($commentid)) {
                    $data = $DB->get_record("voiceshadow_comments", array("id" => $commentid, "userid" => $USER->id));
                    $mform->addElement('editor', 'summary', '')->setValue(array('text' => $data->summary));
                    $mform->addelEment('hidden', 'commentid', $commentid);
                } else
                    $mform->addElement('editor', 'summary', '');
            }

            $mform->addElement('html', '<script language="JavaScript">
            $(document).ready(function() {
              $(".selectaudiomodel").click(function(){
                $("#id_recoring_link").attr("href", $(this).attr("data-url"));
              });
            });
            </script>');


            $mform->setType('speed', PARAM_INT);
            $mform->setType('fileid', PARAM_TEXT);
            $mform->setType('filename', PARAM_TEXT);
            $mform->setType('iphonelink', PARAM_TEXT);
            $mform->setType('act', PARAM_TEXT);
            $mform->setType('speechtext', PARAM_TEXT);

            $this->add_action_buttons(false, $submitlabel = get_string("saverecording", "voiceshadow"));
        }
    }

    $mform = new voiceshadow_comment_form('view.php?a=' . $a . '&id=' . $id);

    $mform->display();
}

/// Finish the page
echo $OUTPUT->footer();
