
<div class="row">
  <?= validation_errors(); ?>
  <?= form_open_multipart(); ?>
  <?= $messages ?>

  <span class="label"><strong>Status:</strong> <?= $survey->status ?></span>
  <h1><?= $survey->title ?></h1>
  <h2>Add respondents</h2>

  <div>
    <?= nl2br_except_pre($survey->introduction) ?>
  </div>

  <?= form_label('Respondents Text', 'survey_respondents_text'); ?>
  <?= form_textarea('survey_respondents_text', set_value('survey_respondents_text')); ?>

  <?= form_upload('survey_respondents_file'); ?>

  <?= form_submit('survey_respondents_submit', 'Add respondents'); ?>

  <?php // @todo add correct permission here ?>
  <?php if (1) :?>
    <a href="<?= $survey->get_url_respondents() ?>" class="button tiny secondary">View respondents</a>
  <?php endif; ?>

<?= form_close(); ?>
</div>


