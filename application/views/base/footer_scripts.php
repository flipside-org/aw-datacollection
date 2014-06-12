<script src="<?= base_url('assets/scripts/website.min.js'); ?>"></script>

<!-- Toast messages -->
<?php $this->load->view('components/toast_messages'); ?>
<!-- End Toast messages -->

<?php if (isset($enketo_action)) : ?>
  <script type="text/javascript" src="<?= base_url('assets/scripts/enketo_base.min.js'); ?>"></script>
  <?php
    switch ($enketo_action) {
      case 'testrun':
        $enketo_file = 'enketo_testrun.min.js';
        break;
      case 'data_collection':
        $enketo_file = 'enketo_collection.min.js';
        break;
      case 'data_collection_single':
        $enketo_file = 'enketo_collection_single.min.js';
        break;
    }
  ?>
  <script type="text/javascript" data-main="<?= base_url('assets/scripts/' . $enketo_file); ?>" src="<?= base_url('assets/libs/enketo-core/lib/require.js'); ?>"></script>
<?php endif; ?>

<?php if (ENVIRONMENT == 'demo' && defined('RESET_SECONDS_LEFT')) : ?>
  <style>
    #reset-timer-counter {
      position: fixed;
      z-index: 999;
      top: 4px;
      left: 50%;
      margin-left: -70px;
      background-color: #DA454A;
      color: #FFFFFF;
      width: 140px;
      text-align: center;
      border-radius: 4px;
      padding: 8px 0;
    }
    #reset-timer-counter .time {
      font-weight: bold;
    }
    #reset-timer-counter .label {
      font-size: 14px;
    }
  </style>
  <script type="text/javascript">
    var seconds_left = <?= RESET_SECONDS_LEFT ?>;
    
    function display_reset_timer(seconds_left) {
      if (seconds_left < 0) {
        seconds_left = 0;
        window.location.reload();
      }
    
      var remainder = seconds_left;
      var h = Math.floor(seconds_left / 3600);
      remainder -= 3600 * h;
      var i = Math.floor(remainder / 60);
      remainder -= 60 * i;
      var s = remainder;
      
      h = h < 10 ? '0' + h : h;
      i = i < 10 ? '0' + i : i;
      s = s < 10 ? '0' + s : s;
      $('#reset-timer-counter .time').text(h + ':' + i + ':' + s);
    }
    
    // Interval to update.
    setInterval(function() { display_reset_timer(--seconds_left); }, 1000);
    
    // First run.
    $('body').append('<div id="reset-timer-counter"><span class="label">reset in</span> <span class="time"></span></div>');
    display_reset_timer(seconds_left);
    
  </script>
<?php endif; ?>