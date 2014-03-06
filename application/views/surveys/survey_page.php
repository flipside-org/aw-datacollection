<div class="row">

  <?= $messages ?>

  <span class="label"><strong>Status:</strong> <?= $survey->status ?></span>
  <h1><?= $survey->title ?></h1>

  <div>
    <?= nl2br_except_pre($survey->introduction) ?>
  </div>

  <?php if (has_permission('download survey files')) : ?>
    <?php if ($survey->files['xls'] !== NULL) : ?>
      <div>xls: <a href="<?= base_url(sprintf('survey/%d/files/xls', $survey->sid)); ?>">xls</a></div>
    <?php endif; ?>

    <?php if ($survey->files['xml'] !== NULL) : ?>
      <div>xml: <a href="<?= base_url(sprintf('survey/%d/files/xml', $survey->sid)); ?>">xml</a></div>
    <?php endif; ?>
  <?php endif; ?>

  <?php // @todo add correct permission here ?>
  <?php if (1) :?>
  <a href="<?= $survey->get_url_respondents() ?>" class="button tiny secondary">Manage respondents</a>
  <?php endif; ?>

</div>
