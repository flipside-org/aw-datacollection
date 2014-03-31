<div class="row">

  <?= $messages ?>

  <span class="label"><strong>Status:</strong> <?= $survey->status ?></span>
  <h1><?= $survey->title ?></h1>
  <h2>Respondents list</h2>

  <?php // @todo add correct permission here ?>
  <?php if (1) :?>
  <a href="<?= $survey->get_url_respondents_add() ?>" class="button tiny secondary">Add respondents</a>
  <?php endif; ?>
  
  <table>
    <tr>
      <td>Number</td>
      <td>Actions</td>
    </tr>
    <?php foreach ($respondents as $resp) : ?>
      <tr>
        <td><?= $resp->number; ?></td>
        <td></td>
      </tr>
    <?php endforeach; ?>
  </table>
  
  <?= $this->pagination->create_links(); ?>
</div>
