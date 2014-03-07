<div class="row">

  <?= $messages ?>

  <span class="label"><strong>Status:</strong> <?= $survey->status ?></span>
  <h1><?= $survey->title ?></h1>
  <h2>Add respondents</h2>

  <div>
    <?= nl2br_except_pre($survey->introduction) ?>
  </div>


  <?php // @todo add correct permission here ?>
  <?php if (1) :?>
    <a href="<?= $survey->get_url_respondents() ?>" class="button tiny secondary">View respondents</a>
  <?php endif; ?>

</div>
