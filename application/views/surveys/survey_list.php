<div class="row">
  
  <table width='100%' class="survey_list">
    <thead>
      <tr>
        <th width='60%'>Title</th>
        <th width='10%'>Status</th>
        <th width='30%'>Actions</th>
      </tr>
    </thead>
    
    <tbody>
    <?php foreach ($surveys as $survey_entity):?>
      <tr>
        <td><a href="<?= $survey_entity->get_url_view() ?>"><?= $survey_entity->title ?></a></td>
        <td><?= $survey_entity->status; ?></td>
        <td>
          <ul class="button-group">
            <li><a href="<?= $survey_entity->get_url_edit() ?>" class="button tiny">Edit</a></li>
            <li>
              <?php 
              print form_open('survey/delete');
              print form_hidden('survey_sid', $survey_entity->sid);
              print form_submit(array(
                'name' => 'survey_delete',
                'value' => 'Delete',
                'class' => 'button tiny'
              ));
              print form_close();
            ?>
            </li>
          </ul>
        </td>
      </tr>
    <?php endforeach; ?>
    
    </tbody>
  </table>
</div>