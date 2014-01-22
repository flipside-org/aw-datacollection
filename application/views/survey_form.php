<?php
// This form is shared between add and edit.
function property_if_not_null($obj, $prop, $default = '') {
  return $obj !== NULL ? $obj->{$prop} : $default;
}
?>

<?= validation_errors(); ?>
<?= form_open_multipart(); ?>
<?php
 // Only need sid if editing.
  if (isset($survey->sid)) {
    print form_hidden('survey_sid', $survey->sid);
  }
?>

  <?= form_label('Survey Title', 'survey_title'); ?><br />  
  <?= form_input('survey_title', set_value('survey_title', property_if_not_null($survey, 'title'))); ?>  <br />
  
  <?= form_label('Status', 'survey_status'); ?><br />
  <?= form_dropdown('survey_status',
        Survey_entity::$allowed_status,
        set_value('survey_status', property_if_not_null($survey, 'status', array()))); ?>  <br />
        
  <?= form_label('Status', 'survey_status'); ?><br />
  <?= form_upload('survey_file'); ?><br />
  
  <?= form_submit('survey_submit', 'Submit Survey'); ?>

<?= form_close(); ?>
