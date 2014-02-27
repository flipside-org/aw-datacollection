<?= $this->js_settings->get_output_script() ?>
<script src="<?= base_url('assets/js/jquery.js'); ?>"></script>

<?php if (isset($enketo_action)) : ?>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic-ext,cyrillic,greek-ext,greek,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
  <link type="text/css" href="<?= base_url('assets/libs/enketo-core/build/css/formhub.css'); ?>" media="all" rel="stylesheet" />

  <script type="text/javascript" src="<?= base_url('assets/js/connection.js'); ?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/respondentQueue.js'); ?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/submissionQueue.js'); ?>"></script>
  
  <?php $enketo_file = isset($enketo_action) && $enketo_action == 'testrun' ? 'enketo_testrun.js' : 'enketo_collection.js'; ?>
  <script type="text/javascript" data-main="<?= base_url('assets/js/' . $enketo_file); ?>" src="<?= base_url('assets/libs/enketo-core/lib/require.js'); ?>"></script>
<?php endif; ?>