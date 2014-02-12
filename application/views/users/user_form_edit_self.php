<div class="row">
<?= validation_errors(); ?>
<?= form_open(); ?>

  <?= form_label('Name', 'user_name'); ?>
  <?= form_input('user_name', set_value('user_name', $user->name)); ?>
  
  <?= form_label('New Password', 'user_new_password'); ?>
  <?= form_password('user_new_password'); ?>
  <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
  <?= form_password('user_new_password_confirm'); ?>
  
  <hr>
  <?= form_label('Password', 'user_password'); ?>
  <?= form_password('user_password'); ?>
  The password is always needed when changing the profile.<br />
  <?= form_submit('user_submit', 'Save'); ?>

<?= form_close(); ?>
</div>