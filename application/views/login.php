<div class="row">

<?= form_error('signin_username', '<div class="error" data-ref="signin_username">', '</div>'); ?>
<?= form_error('signin_password', '<div class="error" data-ref="signin_password">', '</div>'); ?>

<?= form_open('', array('id' => 'login-form')); ?>

  <?= form_label('Username', 'signin_username'); ?>
  <?= form_input('signin_username'); ?>

  <?= form_label('Password', 'signin_password'); ?>
  <?= form_password('signin_password'); ?>
  <?= anchor('user/recover', 'Recover password'); ?>
  <?= form_submit('signin_submit', 'Login'); ?>

<?= form_close(); ?>
</div>
