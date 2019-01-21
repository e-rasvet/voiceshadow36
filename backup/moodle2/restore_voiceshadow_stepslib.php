<?php

/**
 * Structure step to restore one voiceshadow activity
 */
class restore_voiceshadow_activity_structure_step extends restore_activity_structure_step {
 
    protected function define_structure() {
 
        $paths = array();

        $paths[] = new restore_path_element('voiceshadow', '/activity/voiceshadow');
        $paths[] = new restore_path_element('voiceshadow_files', '/activity/voiceshadow/files/vfile');
        $paths[] = new restore_path_element('voiceshadow_comments', '/activity/voiceshadow/comments/comment');
        $paths[] = new restore_path_element('voiceshadow_likes', '/activity/voiceshadow/likes/like');
        $paths[] = new restore_path_element('voiceshadow_submissions', '/activity/voiceshadow/submissions/submission');
        $paths[] = new restore_path_element('voiceshadow_grade_items', '/activity/voiceshadow/grade_items/grade_item');
        $paths[] = new restore_path_element('voiceshadow_grades', '/activity/voiceshadow/grades/vgrade');
 
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }
 
    protected function process_voiceshadow($data) {
        global $DB;
  
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        
        $data->teacher = $this->get_mappingid('user', $data->teacher);
        $data->fileid = $this->get_mappingid('file', $data->fileid);
        $data->var1 = $this->get_mappingid('file', $data->var1);
        $data->var2 = $this->get_mappingid('file', $data->var2);
        $data->var3 = $this->get_mappingid('file', $data->var3);
        $data->var4 = $this->get_mappingid('file', $data->var4);
        $data->var5 = $this->get_mappingid('file', $data->var5);

        // Insert the attendance record.
        $newitemid = $DB->insert_record('voiceshadow', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }
    
    protected function process_voiceshadow_files($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;

        $data->instance = $this->get_new_parentid('voiceshadow');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->itemoldid = $this->get_mappingid('file', $data->itemoldid);
        $data->itemid = $this->get_mappingid('file', $data->itemid);
        $data->itemimgid = $this->get_mappingid('file', $data->itemimgid);

        $newitemid = $DB->insert_record('voiceshadow_files', $data);
        $this->set_mapping('voiceshadow_file', $oldid, $newitemid, true);
    }
    
    protected function process_voiceshadow_comments($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;

        $data->instance = $this->get_new_parentid('voiceshadow');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->itemoldid = $this->get_mappingid('file', $data->itemoldid);
        $data->itemid = $this->get_mappingid('file', $data->itemid);
        $data->itemimgid = $this->get_mappingid('file', $data->itemimgid);
        $data->fileid = $this->get_mappingid('voiceshadow_file', $data->fileid);

        $newitemid = $DB->insert_record('voiceshadow_comments', $data);
        $this->set_mapping('voiceshadow_comment', $oldid, $newitemid, true);
    }
    
    protected function process_voiceshadow_likes($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->instance = $this->get_new_parentid('voiceshadow');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->fileid = $this->get_mappingid('voiceshadow_file', $data->fileid);

        $newitemid = $DB->insert_record('voiceshadow_likes', $data);
        $this->set_mapping('voiceshadow_like', $oldid, $newitemid, true);
    }
    
    protected function process_voiceshadow_submissions($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->voiceshadow = $this->get_new_parentid('voiceshadow');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('voiceshadow_submissions', $data);
        $this->set_mapping('voiceshadow_submission', $oldid, $newitemid, true);
    }
    
    protected function voiceshadow_grade_items($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->instance = $this->get_new_parentid('voiceshadow');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->submission = $this->get_mappingid('voiceshadow_submission', $data->submission);

        $newitemid = $DB->insert_record('voiceshadow_grade_items', $data);
        $this->set_mapping('voiceshadow_grade_item', $oldid, $newitemid, true);
    }
    
    protected function voiceshadow_grades($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->instance = $this->get_new_parentid('voiceshadow');
        $data->gradeitem = $this->get_mappingid('voiceshadow_grade_item', $data->gradeitem);

        $newitemid = $DB->insert_record('voiceshadow_grades', $data);
        $this->set_mapping('voiceshadow_grade', $oldid, $newitemid, true);
    }
    
    protected function after_execute() {
        // Add voiceshadow related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_voiceshadow', 'private', null);
        $this->add_related_files('user', 'public', null);
    }
}