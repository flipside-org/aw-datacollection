<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en" >
  <head>
    <?php $this->load->view('base/head_meta') ?>
    <title>Airwolf</title>
    
    <?php $this->load->view('base/head_scripts') ?>
    
    <?php $this->load->view('base/head_styles') ?>
  </head>
  <body class="<?= is_logged() ? 'is-logged' : 'not-logged'; ?>">
    <div id="site-canvas">