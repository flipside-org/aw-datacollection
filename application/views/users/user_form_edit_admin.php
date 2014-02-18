<div class="row">
<?= validation_errors(); ?>
<?= form_open(); ?>

  <?= form_label('Name', 'user_name'); ?>
  <?= form_input('user_name', set_value('user_name', $user->name)); ?>
  
  <?= form_label('New Password', 'user_new_password'); ?>
  <?= form_password('user_new_password'); ?>
  <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
  <?= form_password('user_new_password_confirm'); ?>
  
  <?= form_label('Roles', 'user_roles'); ?>
  <?php foreach ($this->config->item('roles') as $key => $role_name): ?>
    <div>
    <?= form_checkbox('user_roles[]', $key, in_array($key, $user->roles)); ?>
    <?= $role_name; ?>
    </div>
  <?php endforeach; ?>
  
  <?= form_label('Status', 'user_status'); ?>
  <?= form_dropdown('user_status', User_entity::$statuses, $user->status); ?>
  
  
  <hr>
  <?= form_label('Password', 'user_password'); ?>
  <?= form_password('user_password'); ?>
  The password is always needed when changing profiles.<br />
  <?= form_submit('user_submit', 'Save'); ?>

<?= form_close(); ?>
</div>

<?php krumo($user); ?>