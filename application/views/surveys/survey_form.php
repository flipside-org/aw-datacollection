<div class="row">
<?= validation_errors(); ?>
<?= form_open_multipart(); ?>

  <?= form_label('Survey Title', 'survey_title'); ?>
  <?= form_input('survey_title', set_value('survey_title', property_if_not_null($survey, 'title'))); ?>
  
  <?= form_label('Status', 'survey_status'); ?>
  <?= form_dropdown('survey_status',
        Survey_entity::$statuses,
        set_value('survey_status', property_if_not_null($survey, 'status', array()))); ?>
        
  <?= form_label('Survey Introduction', 'survey_introduction'); ?>
  <?= form_textarea('survey_introduction', set_value('survey_introduction', property_if_not_null($survey, 'introduction'))); ?>
        
  <?= form_upload('survey_file'); ?>
  
  <?= form_submit('survey_submit', 'Submit Survey'); ?>

<?= form_close(); ?>
</div>