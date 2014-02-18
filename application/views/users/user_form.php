<div class="row">
<?= validation_errors(); ?>
<?= form_open(); ?>

  <?= form_label('Name', 'user_name'); ?>
  <?= form_input('user_name', set_value('user_name', property_if_not_null($user, 'name'))); ?>
  
  <?php if ($action == 'add'): ?>
    <?= form_label('Username', 'user_username'); ?>
    <?= form_input('user_username', set_value('user_username')); ?>
    
    <?= form_label('Email', 'user_email'); ?>
    <?= form_input('user_email', set_value('user_email')); ?>
  
    <?= form_label('Password', 'user_new_password'); ?>
    <?= form_password('user_new_password'); ?>
  <?php endif; ?>
  
  <?php if ($action == 'edit_own' || $action == 'edit_other'): ?>
    <?= form_label('New Password', 'user_new_password'); ?>
    <?= form_password('user_new_password'); ?>
    <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
    <?= form_password('user_new_password_confirm'); ?>
  <?php endif; ?>
  
  <?php if ($action == 'edit_other' || $action == 'add'): ?>
    <?= form_label('Roles', 'user_roles'); ?>
    <?php foreach ($this->config->item('roles') as $key => $role_name): ?>
      <div>
      <?= form_checkbox('user_roles[]', $key, in_array($key, property_if_not_null($user, 'roles', array()))); ?>
      <?= $role_name; ?>
      </div>
    <?php endforeach; ?>
    
    <?= form_label('Status', 'user_status'); ?>
    <?= form_dropdown('user_status', User_entity::$statuses, property_if_not_null($user, 'status')); ?>
  <?php endif; ?>
  
  
  <?php if ($action == 'edit_own' || $action == 'edit_other'): ?>
    <hr>
    <?= form_label('Password', 'user_password'); ?>
    <?= form_password('user_password'); ?>
    The password is always needed when changing profiles.<br />
  <?php endif; ?>
  
  <?= form_submit('user_submit', 'Save'); ?>

<?= form_close(); ?>
</div>