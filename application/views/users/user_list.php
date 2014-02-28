<div class="row">
  
  <table width='100%' class="survey_list">
    <thead>
      <tr>
        <th width='20%'>Name</th>
        <th width='25%'>Roles</th>
        <th width='5%'>Status</th>
        <th width='35%'>Actions</th>
      </tr>
    </thead>
    
    <tbody>
    <?php foreach ($users as $user_entity):?>
      <tr>
        <td><?= $user_entity->name ?></a></td>
        <td><?= implode(', ', $user_entity->roles); ?></td>
        <td><?= $user_entity->status; ?></td>
        <td>
          <ul class="button-group">
            <?php if (has_permission('edit any account')) :?>
            <li><a href="<?= $user_entity->get_url_edit() ?>" class="button tiny">Edit</a></li>
            <?php endif; ?>
          </ul>
        </td>
      </tr>
    <?php endforeach; ?>
    
    </tbody>
  </table>
</div>