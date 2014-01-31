<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en" >
  <head>
    <?php $this->load->view('base/meta') ?>
    <title>Foundation 5</title>
    
    <?php // TODO: Move scripts to a more appropriate view! ?>
    <?= $this->js_settings->get_output_script() ?>
    <?php if (isset($using_enketo)) : ?>
      <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic-ext,cyrillic,greek-ext,greek,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
      <link type="text/css" href="<?= base_url('assets/libs/enketo-core/build/css/formhub.css'); ?>" media="all" rel="stylesheet" />
  
      <script type="text/javascript" data-main="<?= base_url('assets/libs/enketo-core/app.js'); ?>" src="<?= base_url('assets/libs/enketo-core/lib/require.js'); ?>"></script>
    <?php endif; ?>
    
    <?php $this->load->view('base/head_styles') ?>
    
  </head>
  <body>