<?= $this->js_settings->get_output_script() ?>

<?php if (isset($using_enketo)) : ?>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic-ext,cyrillic,greek-ext,greek,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
  <link type="text/css" href="<?= base_url('assets/libs/enketo-core/build/css/formhub.css'); ?>" media="all" rel="stylesheet" />

  <script type="text/javascript" data-main="<?= base_url('assets/js/app.js'); ?>" src="<?= base_url('assets/libs/enketo-core/lib/require.js'); ?>"></script>
<?php endif; ?>