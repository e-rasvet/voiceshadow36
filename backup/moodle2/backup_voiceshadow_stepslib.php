<?php

/**
 * Define the complete voiceshadow structure for backup, with file and id annotations
 */     
class backup_voiceshadow_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $voiceshadow = new backup_nested_element('voiceshadow', array('id'), array(
            'name', 'intro', 'introformat', 'timeopen', 'timeclose', 'teacher', 'embedvideo', 'recordtype', 'fileid',
            'grade', 'gradet', 'showscore', 'voiceshadowtype', 'resubmit', 'preventlate', 'speechtotext',
            'emailteachers', 'allowmultiple', 'var1', 'var2', 'var3', 'var4', 'var5', 
            'var1text', 'var2text', 'var3text', 'var4text', 'var5text',
            'var1transcript', 'var2transcript', 'var3transcript', 'var4transcript', 'var5transcript',
            'maxbytes', 'grademethod', 'grademethodt', 'timedue', 'timeavailable', 'timemodified'));
 
        $files = new backup_nested_element('files');
        $vfile = new backup_nested_element('vfile', array('id'), array(
            'userid', 'summary', 'speechtext', 'itemoldid', 'itemid', 'itemimgid', 'filename', 'var', 'time'));
        
        $ratings = new backup_nested_element('ratings');
        $rating = new backup_nested_element('rating', array('id'), array(
            'fileid', 'userid', 'rating', 'ratingrhythm', 'ratingclear', 'ratingintonation', 'ratingspeed', 'ratingreproduction', 'summary', 'time'));
        
        $comments = new backup_nested_element('comments');
        $comment = new backup_nested_element('comment', array('id'), array(
            'fileid', 'userid', 'summary', 'itemoldid', 'itemid', 'itemimgid', 'filename', 'time'));
        
        
        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', array('id'), array(
            'userid', 'timing', 'timecreated', 'timemodified', 'data1', 'data2', 'grade',
            'submissioncomment', 'format', 'teacher', 'timemarked', 'gradebefore', 'gradeafter',
            'gradebeforeteacher', 'gradebeforeself', 'gradebeforepeer', 'gradeafterteacher', 'gradeafterself',
            'gradeafterpeer', 'mailed'));

        $grade_items = new backup_nested_element('grade_items');
        $grade_item = new backup_nested_element('grade_item', array('id'), array(
            'submission', 'type', 'userid', 'usedbypeermarking'));
        
        $grades = new backup_nested_element('grades');
        $vgrade = new backup_nested_element('vgrade', array('id'), array(
            'gradeitem', 'timemarked', 'grade', 'submissioncomment', 'mailed'));
        
        $likes = new backup_nested_element('likes');
        $like = new backup_nested_element('like', array('id'), array(
            'fileid', 'userid', 'time'));
        
        // Build the tree
        $voiceshadow->add_child($files);
        $files->add_child($vfile);
        $voiceshadow->add_child($ratings);
        $ratings->add_child($rating);
        $voiceshadow->add_child($comments);
        $comments->add_child($comment);
        $voiceshadow->add_child($submissions);
        $submissions->add_child($submission);
        $voiceshadow->add_child($grade_items);
        $grade_items->add_child($grade_item);
        $voiceshadow->add_child($grades);
        $grades->add_child($vgrade);
        $voiceshadow->add_child($likes);
        $likes->add_child($like);
        
        // Define sources
        $voiceshadow->set_source_table('voiceshadow', array('id' => backup::VAR_ACTIVITYID)); //, 'course' => backup::VAR_COURSEID
        
        $vfile->set_source_table('voiceshadow_files', array('instance' => backup::VAR_PARENTID));
        $comment->set_source_table('voiceshadow_comments', array('instance' => backup::VAR_PARENTID));
        $like->set_source_table('voiceshadow_likes', array('instance' => backup::VAR_PARENTID));
        $submission->set_source_table('voiceshadow_submissions', array('voiceshadow' => backup::VAR_PARENTID));
        $grade_item->set_source_table('voiceshadow_grade_items', array('voiceshadow' => backup::VAR_PARENTID));
        $vgrade->set_source_table('voiceshadow_grades', array('voiceshadow' => backup::VAR_PARENTID));
        
        
        // Define id annotations
        
        $voiceshadow->annotate_ids('user', 'teacher');
        $voiceshadow->annotate_ids('file', 'fileid');
        $voiceshadow->annotate_ids('file', 'var1');
        $voiceshadow->annotate_ids('file', 'var2');
        $voiceshadow->annotate_ids('file', 'var3');
        $voiceshadow->annotate_ids('file', 'var4');
        $voiceshadow->annotate_ids('file', 'var5');
        
        $vfile->annotate_ids('user', 'userid');
        $vfile->annotate_ids('file', 'itemoldid');
        $vfile->annotate_ids('file', 'itemid');
        $vfile->annotate_ids('file', 'itemimgid');
        
        $rating->annotate_ids('vfiles', 'fileid');
        $rating->annotate_ids('user', 'userid');
        
        $comment->annotate_ids('user', 'userid');
        $comment->annotate_ids('vfiles', 'fileid');
        $comment->annotate_ids('file', 'itemoldid');
        $comment->annotate_ids('file', 'itemid');
        $comment->annotate_ids('file', 'itemimgid');
        
        $submission->annotate_ids('user', 'userid');
        
        $grade_item->annotate_ids('submission', 'submission');
        $grade_item->annotate_ids('user', 'userid');
        
        $vgrade->annotate_ids('vgrade', 'gradeitem');
        
        $like->annotate_ids('user', 'userid');
        $like->annotate_ids('vfiles', 'fileid');
        
        
        $voiceshadow->annotate_files('mod_voiceshadow', 'private', null);
        $voiceshadow->annotate_files('user', 'public', null);
        
        // Return the root element (voiceshadow), wrapped into standard activity structure
        
        return $this->prepare_activity_structure($voiceshadow);
    }
}