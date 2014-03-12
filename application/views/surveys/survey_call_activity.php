<div class="row">
  
  <h1>Completed</h1>
  <table width='100%' class="call_task_resolved_list">
    <thead>
      <tr>
        <th width='10%'>Number</th>
        <th width='55%'>Message</th>
        <th width='35%'>Actions</th>
      </tr>
    </thead>
    
    <tbody>
    <?php foreach ($call_tasks_resolved as $call_task_entity):?>
      <tr>
      	<td><?= $call_task_entity->number ?></td>
      	<td>Last status here</td>
      	<td>no actions</td>
      </tr>
    <?php endforeach; ?>
    
    </tbody>
  </table>
  
  <h1>To do</h1>
  <table width='100%' class="call_task_unresolved_list">
    <thead>
      <tr>
        <th width='10%'>Number</th>
        <th width='55%'>Message</th>
        <th width='35%'>Actions</th>
      </tr>
    </thead>
    
    <tbody>
    <?php foreach ($call_tasks_unresolved as $call_task_entity):?>
      <tr>
      	<td><?= $call_task_entity->number ?></td>
      	<td></td>
      	<td><a href="<?= $call_task_entity->get_url_single_data_collection() ?>" class="button tiny secondary">Collect data</a></td>
      </tr>
    <?php endforeach; ?>
    
    </tbody>
  </table>
</div>