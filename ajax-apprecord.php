<?php
/**
 * Created by PhpStorm.
 * User: Свет
 * Date: 18.02.2017
 * Time: 13:43
 */

  require_once '../../config.php';
  require_once 'lib.php';


  $a                       = optional_param('a', NULL, PARAM_TEXT);
  $id                      = optional_param('id', 0, PARAM_INT);
  $inst                    = optional_param('inst', 0, PARAM_INT);
  $uid                     = optional_param('uid', 0, PARAM_INT);

  if ($inst > 0) {
      $data = $DB->get_records("voiceshadow_appfiles", array("instance"=>$inst, "sourcefileid"=>$id, "userid"=>$uid), "id DESC");
  } else {
      $data = $DB->get_records("voiceshadow_appfiles", array("instance"=>$id, "userid"=>$uid), "id DESC");
  }

  if ($data){
      $data = current($data);
      //$DB->delete_records("voiceshadow_appfiles", array("id"=>$data->id));

      if ($file = $DB->get_record("files", array("id"=>$data->fileid))){
          $data->status = 'success';
          $data->itemid = $file->itemid;
          
          if ($a == "delete") {
            echo json_encode(array('status'=>'noitem'));
            $DB->delete_records("voiceshadow_appfiles", array("id"=>$data->id));
          } else {
            echo json_encode($data);
          }
      }
  } else {
      echo json_encode(array('status'=>'noitem'));
  }