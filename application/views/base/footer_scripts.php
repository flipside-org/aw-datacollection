<script src="<?= base_url('assets/scripts/foundation.min.js'); ?>"></script>
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