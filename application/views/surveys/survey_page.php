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
  
  <h2>Operators</h2>
  
  <select data-placeholder="Assign Call Center Agents" class="chosen-select" style="width:350px;" multiple>
    <option value=""></option>
    <?php foreach ($agents as $agent) : ?>
      <option value="<?= $agent['user']->uid ?>" <?= implode(' ', $agent['properties']) ?>><?= $agent['user']->name ?></option>
    <?php endforeach; ?>
  </select>
  
</div>



