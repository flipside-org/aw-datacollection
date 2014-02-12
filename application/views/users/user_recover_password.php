<div class="row">
<?= validation_errors(); ?>
<?= form_open(); ?>

  <?= form_label('Email', 'user_email'); ?>
  <?= form_input('user_email', set_value('user_email')); ?>
  
  <?= form_submit('user_submit', 'Save'); ?>

<?= form_close(); ?>
</div>