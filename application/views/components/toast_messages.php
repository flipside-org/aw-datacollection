<?php
/**
 * Toast messages file.
 * This file is being included in footer_scripts.php
 * 
 * Shows toasts for the various 'levels':
 * - notice
 * - success
 * - warning
 * - error
 * 
 * Success toasts are the only ones not sticky due to its nature.
 */
?>
<?php $messages = Status_msg::get(); ?>
<?php if ($messages) : ?>
  <script>
  <?php foreach($messages as $index => $msg): ?>
  $().toastmessage('showToast', {
    sticky   : <?= $msg['sticky'] ? 'true' : 'false'; ?>,
    text     : "<?= $msg['msg']; ?>",
    type     : '<?= $msg['level']; ?>',
    inEffectDuration : 100,
    position : 'bottom-right',
  });
  <?php endforeach; ?>
  </script>
<?php endif; ?>