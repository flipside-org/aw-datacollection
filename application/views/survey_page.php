<?php if ($messages) : ?>
<div>
  <?php
    foreach($messages as $key => $messages_level):
    if (empty($messages_level)) continue;
  ?>
    
    <strong><?= $key ?></strong>
    <ul>
      <?php foreach($messages_level as $msg): ?>
        <li><?= $msg ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>
</div>
<?php endif; ?>


<h1><?= $survey->title ?></h1>

<div>Status: <?= $survey->status ?></div>

<?php if ($survey->files['xls'] !== NULL) : ?>
  <div>xls: <a href="<?= base_url(sprintf('survey/%d/files/xls', $survey->sid)); ?>">xls</a></div>
<?php endif; ?>
<?php if ($survey->files['xml'] !== NULL) : ?>
  <div>xml: <a href="<?= base_url(sprintf('survey/%d/files/xml', $survey->sid)); ?>">xml</a></div>
<?php endif; ?>
