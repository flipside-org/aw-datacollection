<ul>
	<li><a href="survey/add">Add survey</a></li>
</ul>

<?php foreach ($surveys as $survey_entity):?>
  
  <h1>
    <a href="<?= $survey_entity->get_url_view() ?>"><?= $survey_entity->title ?></a>
    <small>
      <a href="<?= $survey_entity->get_url_edit() ?>">edit</a>
      <?php 
        print form_open('survey/delete');
        print form_hidden('survey_sid', $survey_entity->sid);
        print form_submit('survey_delete', 'Delete');
        print form_close();
      ?>
    </small>
  </h1>
  
<?php endforeach; ?>
