<?php
// Some permission checking that are going to be used in different places.
$show_actions_enketo_data_collection = FALSE;
if (has_permission('enketo collect data any')) {
  $show_actions_enketo_data_collection = TRUE;
}
else if (has_permission('enketo collect data assigned') && $survey->is_assigned_agent(current_user()->uid)){
  $show_actions_enketo_data_collection = TRUE;
}

$show_actions_enketo_testrun = FALSE;
if (has_permission('enketo testrun any')) {
  $show_actions_enketo_testrun = TRUE;
}
else if (has_permission('enketo testrun assigned') && $survey->is_assigned_agent(current_user()->uid)){
  $show_actions_enketo_testrun = TRUE;
}
?>

<main id="site-body">
  <section class="row">
    <header id="page-head">
        
      <div class="heading">
        <h1 class="hd-xl <?= $survey->get_status_html_class('indicator-'); ?>"><?= $survey->title ?></h1>
      </div>
      
      <nav id="secondary" role="navigation">
        <ul class="links">
          <li class="sector-switcher">
            <a class="bttn-sector bttn-dropdown" href="" data-dropdown="action-bttn"><strong>Summary</strong></a>
            <ul class="action-dropdown">
              <?php if (has_permission('manage respondents any survey')) :?>
              <li><a href="<?= $survey->get_url_respondents() ?>">Respondents</a></li>
              <?php endif; ?>
              
              <?php if ($show_actions_enketo_data_collection) :?>
              <li><a href="<?= $survey->get_url_call_activity() ?>" class="<?= !$survey->has_xml() ? 'disabled' : ''; ?>">Call activity</a></li>
              <?php endif; ?>
            </ul>
          </li>
          
          <?php if (has_permission('download survey files')) : ?>
          <li>
            <a href="" class="bttn bttn-primary bttn-medium bttn-dropdown" data-dropdown="action-bttn">Export</a>
            <ul class="action-dropdown">
              <li><a href="<?= $survey->get_url_file('xls'); ?>" class="<?= !$survey->has_xls() ? 'disabled' : ''; ?>">Definition file (XLS)</a></li>
              <li><a href="<?= $survey->get_url_file('xml'); ?>" class="<?= !$survey->has_xml() ? 'disabled' : ''; ?>">Definition file (XML)</a></li>
            </ul>
          </li>
          <?php endif; ?>
          
          <?php if (has_permission('edit any survey') || has_permission('delete any survey')) : ?>
          <li>
            <a href="" class="bttn bttn-primary bttn-medium bttn-dropdown" data-dropdown="action-bttn">Edit</a>
            <ul class="action-dropdown">
              <?php if (has_permission('edit any survey')) : ?>
              <li><a href="<?= $survey->get_url_edit(); ?>">Modify</a></li>
              <?php endif; ?>
              
              <?php if (has_permission('delete any survey')) : ?>
              <li><?= anchor_csrf($survey->get_url_delete(), 'Delete', array('class' => 'danger')); ?></li>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>
          
          <li>
            <a href="" class="bttn bttn-success bttn-medium bttn-dropdown" data-dropdown="action-bttn">Run</a>
            <ul class="action-dropdown">
              <?php if ($show_actions_enketo_testrun) :?>
              <li><a href="<?= $survey->get_url_survey_enketo('testrun') ?>" class="<?= !$survey->has_xml() ? 'disabled' : ''; ?>">Testrun</a></li>
              <?php endif; ?>
              
              <?php if ($show_actions_enketo_data_collection) :?>
              <li><a href="<?= $survey->get_url_survey_enketo('collection') ?>" class="<?= !$survey->has_xml() ? 'disabled' : ''; ?>">Collect Data</a></li>
              <?php endif; ?>
            </ul>
          </li>
          
        </ul>
      </nav>
      
    </header>
      


    <!-- TO FORMAT AND PUT IN PROPER PLACE!!!!!! -->

  <div>
    <h1>Introduction</h1>
    <?= nl2br_except_pre($survey->introduction) ?>
  </div>
  
<div>

  <?php if (has_permission('assign agents')) : ?>
    <h2>Manage Agents</h2>
    <?= form_open($survey->get_url_manage_agents(), array('id' => 'assign-agents')); ?>
    <select data-placeholder="Assign Call Center Agents" class="chosen-select" style="width:350px;" multiple>
      <option value=""></option>
      <?php foreach ($agents as $agent) : ?>
        <option value="<?= $agent['user']->uid ?>" <?= implode(' ', $agent['properties']) ?>><?= $agent['user']->name ?></option>
      <?php endforeach; ?>
    </select>
    <?= form_close(); ?>
  <?php endif; ?>
  
</div>

<!-- // TO FORMAT AND PUT IN PROPER PLACE!!!!!! -->





  </section>
</main>