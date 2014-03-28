
<div class="row">
  <?= validation_errors(); ?>
  <?= form_open_multipart(); ?>
  <?= $messages ?>

  <span class="label"><strong>Status:</strong> <?= $survey->status ?></span>
  <h1><?= $survey->title ?></h1>
  <h2>Confirm respondents</h2>

  <?php if (sizeof($respondents_numbers)) : ?>
    <ul>
    <?php foreach ($respondents_numbers as $respondent_number) : ?>
      <li><?= $respondent_number; ?></li>
    <?php endforeach; ?>
    </ul>

    <?= form_submit('survey_respondents_submit', 'Confirm respondents'); ?>
  <?php endif ?>


  <?= form_close(); ?>
</div>


