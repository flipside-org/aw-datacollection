<div class="row">
<?= validation_errors(); ?>
<?= form_open(); ?>

  <?= form_label('New Password', 'user_new_password'); ?>
  <?= form_password('user_new_password'); ?>
  <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
  <?= form_password('user_new_password_confirm'); ?>
  
  <?= form_submit('user_submit', 'Save'); ?>

<?= form_close(); ?>
</div>