<h1><?= $survey->title ?></h1>

<div>Status: <?= $survey->status ?></div>

<?php if ($survey->files['xls'] !== NULL) : ?>
  <div>xls: <a href="<?= base_url(sprintf('survey/%d/files/xls', $survey->sid)); ?>">xls</a></div>
<?php endif; ?>
