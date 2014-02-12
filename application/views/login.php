<div class="row"> 
<?= validation_errors(); ?>
<?= form_open(); ?>

  <?= form_label('Username', 'signin_username'); ?>
  <?= form_input('signin_username'); ?>

  <?= form_label('Password', 'signin_password'); ?>
  <?= form_password('signin_password'); ?>
  
  <?= form_submit('signin_submit', 'Login'); ?>

<?= form_close(); ?>
</div>