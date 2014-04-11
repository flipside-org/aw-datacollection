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
      <div class="inner">
        
        <div class="heading">
          <h1 class="hd-xl <?= $survey->get_status_html_class('indicator-'); ?>"><?= $survey->title ?></h1>
        </div>
        
        <nav id="secondary" role="navigation">
          <ul class="bttn-toolbar">
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
        
      </div>
    </header>

    <div class="content">
      
      <div class="columns small-6">
        <section class="contained">
          <header class="contained-head">
            <h1 class="hd-s"><b>For</b> <?= $survey->client; ?></h1>
            <?php if ($survey->description): ?>
              <p><?= nl2br_except_pre($survey->description); ?></p>
            <?php endif; ?>
              <p class="time">
                <?= $survey->created->sec == $survey->updated->sec ? 'Created' : 'Updated'; ?> on <?= date('d M, Y', $survey->updated->sec); ?>
              </p>
          </header>
          <div class="contained-body">
            
            
            
            <article class="widget">
            	<header class="widget-head">
            		<h1 class="hd-s">Progress</h1>
            	</header>
            	<div class="widget-body">
            	  
            	  <ul class="progress-bar">
                  <li class="primary" style="width: 60%">&nbsp;</li>
                </ul>
                <ul class="progress-bar-legend">
                  <li class="primary">Completed: <strong>560</strong> (33.3%)</li>
                  <li class="summary">Goal: <strong>250</strong></li>
                </ul>
            	  
            	</div>
            </article>
            
            
            
            <article class="widget">
            	<header class="widget-head">
            		<h1 class="hd-s">Respondents</h1>
            	</header>
            	<div class="widget-body">
            	  <?php if ($call_tasks_status_bar['total'] != 0):
            	   
                 $per_success = $call_tasks_status_bar['success'] / $call_tasks_status_bar['total'] * 100;
                 $per_failed = $call_tasks_status_bar['failed'] / $call_tasks_status_bar['total'] * 100;
                 $per_pending = $call_tasks_status_bar['pending'] / $call_tasks_status_bar['total'] * 100;
                 $per_remaining = $call_tasks_status_bar['remaining'] / $call_tasks_status_bar['total'] * 100;
            	  ?>
            	  
            	  <ul class="progress-bar">
                  <li class="success" style="width: <?= $per_success; ?>%">&nbsp;</li>
                  <li class="danger" style="width: <?= $per_failed; ?>%">&nbsp;</li>
                  <li class="warning" style="width: <?= $per_pending; ?>%">&nbsp;</li>
                </ul>
                
                <ul class="progress-bar-legend">
                  <li class="success">Success: <strong><?= $call_tasks_status_bar['success']; ?></strong> (<?= round($per_success, 2); ?>%)</li>
                  <li class="danger">Failed: <strong><?= $call_tasks_status_bar['failed']; ?></strong> (<?= round($per_failed, 2); ?>%)</li>
                  <li class="warning">Pending: <strong><?= $call_tasks_status_bar['pending']; ?></strong> (<?= round($per_pending, 2); ?>%)</li>
                  <li class="default">Remaining: <strong><?= $call_tasks_status_bar['remaining']; ?></strong> (<?= round($per_remaining, 2); ?>%)</li>
                  <li class="summary">Total: <strong><?= $call_tasks_status_bar['total']; ?></strong></li>
                </ul>
                
                <?php else: ?>
                  No respondents!
                <?php endif; ?>
            	  
            	</div>
            </article>
            
            
            
            <article class="widget">
            	<header class="widget-head">
            		<h1 class="hd-s">Placed Calls</h1>
            	</header>
            	<div class="widget-body"></div>
            </article>
            
            
            
            
            
          </div>
        </section>
      </div>
      
      <div class="columns small-6">        
        <section class="contained">
          <h1 class="visually-hidden">Settings</h1>
          <div class="contained-body">
            
            <article class="widget widget-bfc">
              <header class="widget-head">
                <h1 class="hd-s">Status</h1>
              </header>
              <div class="widget-body">
                <ul class="bttn-toolbar">
                  <li>
                    <a href="#" class="bttn bttn-default-light bttn-small bttn-dropdown status-open" data-dropdown="action-bttn">Draft</a>
                    <ul class="action-dropdown for-bttn-small">
                      <li><a href="#" class="status-open" data-confirm-action="Are you sure?" data-confirm-title="Title set by data attribute">Status 1</a></li>
                      <li><a href="#" class="status-canceled" data-confirm-action="Are you sure?">Status 2</a></li>
                    </ul>
                  </li>
                </ul>
              </div>
            </article>
            
            
            
            
            
            <?php if (has_permission('assign agents')) : ?>
            <article class="widget widget-bfc">
              <header class="widget-head">
                <h1 class="hd-s">Agents</h1>
              </header>
              <div class="widget-body">
                <?= form_open($survey->get_url_manage_agents(), array('id' => 'assign-agents')); ?>
                <select data-placeholder="Assign Call Center Agents" class="chosen-select" multiple>
                  <option value=""></option>
                  <?php foreach ($agents as $agent) : ?>
                    <option value="<?= $agent['user']->uid ?>" <?= implode(' ', $agent['properties']) ?>><?= $agent['user']->name ?></option>
                  <?php endforeach; ?>
                </select>
                <?= form_close(); ?>
              </div>
            </article>
            <?php endif; ?>
            
            
            
            
            
            <article class="widget widget-bfc">
              <header class="widget-head">
                <h1 class="hd-s">Definition file</h1>
              </header>
              <div class="widget-body">
                <p>Active <a href="#" class="survey-warnings-expand" data-expand="survey-warnings">View warnings <small>(11)</small></a></p>
                  <div id="survey-warnings">
                    <ul>
                    	<li>This is one of the warnings occurred when uploading the xls.</li>
                    	<li>And yet another warnig.</li>
                    	<li>Enough isn't it.</li>
                    </ul>
                  </div>
              </div>
            </article>
            
            
          </div>
        </section>
        
        <section class="contained">
          <header class="contained-head">
            <h1 class="hd-s">Call tasks</h1>
          </header>
          <div class="contained-body">
            <?php if (!empty($call_tasks_table)) : ?>
            <table class="fancy-cb-group">
              <thead>
                <tr>
                  <th>Agent</th>
                  <th>Success</th>
                  <th>Failed</th>
                  <th>Pending</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($call_tasks_table as $agent) : ?>
                <tr>
                	<td><strong class="highlight"><?= $agent['name']; ?></strong></td>
                	<td><?= $agent['success']; ?></td>
                	<td><?= $agent['failed']; ?></td>
                	<td><?= $agent['pending']; ?></td>
                	<td><strong><?= $agent['sum']; ?></strong></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php else: ?>
              No respondents!
            <?php endif; ?>
          </div>
        </section>
        
        <section class="contained">
          <h1 class="hd-s">Welcome text</h1>
          <p><?= nl2br_except_pre($survey->introduction) ?></p>
        </section>
      </div>
      
    </div>

    <!-- TO FORMAT AND PUT IN PROPER PLACE!!!!!! -->

  
<div>

  
  
</div>

<!-- // TO FORMAT AND PUT IN PROPER PLACE!!!!!! -->




    </div>
  </section>
</main>