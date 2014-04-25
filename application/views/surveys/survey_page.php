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
            
            <?php if (has_permission('download any survey files')) : ?>
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
                <?php $class = 'danger'; ?>
                <?php $class .= !$survey->status_allows('delete any survey') ? ' disabled': ''; ?>
                <li><?= anchor_csrf($survey->get_url_delete(), 'Delete', array('class' => $class, 'data-confirm-action' => 'Are you sure you want to delete the survey: ' . $survey->title)); ?></li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>
            
            <li>
              <a href="" class="bttn bttn-success bttn-medium bttn-dropdown" data-dropdown="action-bttn">Run</a>
              <ul class="action-dropdown">
                <?php if ($show_actions_enketo_testrun) :?>
                <?php $disbled = !$survey->has_xml() || !$survey->status_allows('enketo collect data') ? 'disabled' : ''; ?>
                <li><a href="<?= $survey->get_url_survey_enketo('testrun') ?>" class="<?= $disbled; ?>">Testrun</a></li>
                <?php endif; ?>
                
                <?php if ($show_actions_enketo_data_collection) :?>
                <?php $disbled = !$survey->has_xml() || !$survey->status_allows('enketo testrun') ? 'disabled' : ''; ?>
                <li><a href="<?= $survey->get_url_survey_enketo('collection') ?>" class="<?= $disbled; ?>">Collect Data</a></li>
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
            
            
            
            <?php if ($survey->goal):
              $per_success = $call_tasks_status_bar['success'] / $survey->goal * 100;
            ?>
            <article class="widget">
              <header class="widget-head">
                <h1 class="hd-s">Progress towards goal</h1>
              </header>
              <div class="widget-body">
                
                <div class="progress-bar">
                  <ul>
                    <li class="success" style="width: <?= $per_success; ?>%">&nbsp;</li>
                  </ul>
                </div>
                <ul class="progress-bar-legend">
                  <li class="success">Success: <strong><?= $call_tasks_status_bar['success']; ?></strong> (<?= $per_success; ?>%)</li>
                  <li class="summary-alt">Goal: <strong><?= $survey->goal; ?></strong></li>
                </ul>
                
              </div>
            </article>
            <?php endif; ?>
            
            
            <?php if (has_permission('view survey stats - respondents progress')) : ?>
            <article class="widget">
              <header class="widget-head">
                <h1 class="hd-s">Respondents</h1>
              </header>
              <?php if ($call_tasks_status_bar['total']) :
                $per_success = $call_tasks_status_bar['success'] / $call_tasks_status_bar['total'] * 100;
                $per_failed = $call_tasks_status_bar['failed'] / $call_tasks_status_bar['total'] * 100;
                $per_pending = $call_tasks_status_bar['pending'] / $call_tasks_status_bar['total'] * 100;
                $per_remaining = $call_tasks_status_bar['remaining'] / $call_tasks_status_bar['total'] * 100;
              ?>
              <div class="widget-body">                 
                
                <div class="progress-bar">
                  <ul>
                    <li class="success" style="width: <?= $per_success; ?>%">&nbsp;</li>
                    <li class="danger" style="width: <?= $per_failed; ?>%">&nbsp;</li>
                    <li class="warning" style="width: <?= $per_pending; ?>%">&nbsp;</li>
                  </ul>
                </div>
                <ul class="progress-bar-legend">
                  <li class="success">Success: <strong><?= $call_tasks_status_bar['success']; ?></strong> (<?= round($per_success, 2); ?>%)</li>
                  <li class="danger">Failed: <strong><?= $call_tasks_status_bar['failed']; ?></strong> (<?= round($per_failed, 2); ?>%)</li>
                  <li class="warning">Pending: <strong><?= $call_tasks_status_bar['pending']; ?></strong> (<?= round($per_pending, 2); ?>%)</li>
                  <li class="default">Remaining: <strong><?= $call_tasks_status_bar['remaining']; ?></strong> (<?= round($per_remaining, 2); ?>%)</li>
                  <li class="summary-alt">Total: <strong><?= $call_tasks_status_bar['total']; ?></strong></li>
                </ul>
                
              </div>
              <?php else : ?>
              <div class="widget-empty">
                <p>There are no respondents yet.</p>
              </div>
              <?php endif; ?>
            </article>
            <?php endif; ?>
            
            
            <?php if (has_permission('view survey stats - calls placed')) : ?>
            <article class="widget">
              <header class="widget-head">
                <h1 class="hd-s">Placed Calls</h1>
              </header>
              
              <?php if ($call_tasks_placed_calls['sum']) : ?>
              <div class="widget-body">
                                
                <figure class="stats-placed-calls">
                  <div class="chart" id="placed-calls-chart" style="height: 230px;"></div>
                  <script type="text/javascript">
                    $(function() {
                      Morris.Donut({
                        element: 'placed-calls-chart',
                        data: <?= json_encode(array_values($call_tasks_placed_calls['data'])); ?>,
                        colors: ['#047998', '#2A8DA8', '#4FA1B7', '#75B5C6', '#9AC9D6', '#C0DDE5', '#E5F1F4']
                      });
                    });
                  </script>
                  <figcaption class="chart-legend">
                    <ul class="progress-bar-legend">
                      <?php foreach ($call_tasks_placed_calls['data'] as $placed_call_status) : ?>
                        <?php $percent = $placed_call_status['value'] / $call_tasks_placed_calls['sum'] * 100; ?>
                        <li><?= $placed_call_status['label']; ?>: <strong><?= $placed_call_status['value']; ?></strong> (<?= round($percent, 2); ?>%)</li>
                      <?php endforeach ?>
                        <li class="summary">Total: <strong><?= $call_tasks_placed_calls['sum']; ?></strong></li>
                    </ul>
                  </figcaption>
                </figure>
              </div>
              
              <?php else : ?>
              <div class="widget-empty">
                <p>No calls placed yet.</p>
              </div>
              <?php endif ?>
              
            </article>
            <?php endif; ?>
            
            
          </div>
        </section>
      </div>
      
      <div class="columns small-6">
        <?php if (has_permission('change status any survey') || has_permission('manage agents') || has_permission('download any survey files')) : ?>
        <section class="contained">
          <h1 class="visually-hidden">Settings</h1>
          <div class="contained-body">
            
            <?php if (has_permission('change status any survey')) : ?>
            <article class="widget widget-bfc">
              <header class="widget-head">
                <h1 class="hd-s">Status</h1>
              </header>
              <div class="widget-body">
                <ul class="bttn-toolbar">
                  <li>
                    <?php $available_statuses = $survey->allowed_status_change();?>
                    <a href="#" class="bttn bttn-default-light bttn-small bttn-dropdown <?= $survey->get_status_html_class() ?> <?= empty($available_statuses) ? 'disabled' : '' ?>" data-dropdown="action-bttn"><?= $survey->get_status_label() ?></a>
                    <ul class="action-dropdown for-bttn-small">
                      <?php foreach($available_statuses as $status_code) : ?>
                      <li><?=
                        anchor_csrf($survey->get_url_change_status($status_code), Survey_entity::status_label($status_code), array(
                          'class' => Survey_entity::status_html_class($status_code),
                          'data-confirm-action' => "Status changes are irreversible. Are you sure you want to change the status to <em>" . Survey_entity::status_label($status_code) . "</em>?",
                          'data-confirm-title' => "Change survey status"
                        ));
                      ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </li>
                </ul>
              </div>
            </article>
            <?php endif; ?>
            
            <?php if (has_permission('manage agents')) : ?>
            <article class="widget widget-bfc">
              <header class="widget-head">
                <h1 class="hd-s">Agents</h1>
              </header>
              <div class="widget-body">
                <?= form_open($survey->get_url_manage_agents(), array('id' => 'assign-agents')); ?>
                <?php $disabled = !$survey->status_allows('manage agents') ? 'disabled' : ''; ?>
                <select data-placeholder="Assign Call Center Agents" class="chosen-select" multiple <?= $disabled; ?>>
                  <option value=""></option>
                  <?php foreach ($agents as $agent) : ?>
                    <option value="<?= $agent['user']->uid ?>" <?= implode(' ', $agent['properties']) ?>><?= $agent['user']->name ?></option>
                  <?php endforeach; ?>
                </select>
                <?= form_close(); ?>
              </div>
            </article>
            <?php endif; ?>
            
            <?php if (has_permission('download any survey files')) : ?>
            <article class="widget widget-bfc">
              <header class="widget-head">
                <h1 class="hd-s">Definition file</h1>
              </header>
              <div class="widget-body">
                <p>
                  <?= $survey->has_xml()? 'Active' : 'Not present';?> 
                  <?php $warnings = $survey->files['last_conversion']['warnings']; ?>
                  <?php if ($warnings): ?>
                  <a href="#" class="survey-warnings-expand" data-expand="survey-warnings">View warnings <small>(<?= count($warnings); ?>)</small></a>
                  <?php endif; ?>
                </p>
                  <?php if ($warnings): ?>
                  <div id="survey-warnings">
                    <ul>
                      <?php foreach ($warnings as $warn) :?>
                      <li><?= $warn ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                  <?php endif; ?>
              </div>
            </article>
            <?php endif; ?>
            
          </div>
        </section>
        <?php endif; ?>
        
        <?php if (has_permission('view survey stats - call tasks full table')) : ?>
          <section class="contained">
            <header class="contained-head">
              <h1 class="hd-s">Agents' summary</h1>
            </header>
            <?php if (!empty($call_tasks_table)) : ?>
            <div class="contained-body">
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
            </div>
            <?php else : ?>
            <div class="widget-empty">
              <p>No assigned agents yet.<br/><em>After assigning agents refresh the page.</em></p>
            </div>
            <?php endif ?>
            
          </section>
        <?php
          // Without permission only sees the table if there's data for the current user.
         elseif (isset($call_tasks_table[current_user()->uid])) :
           $agent = $call_tasks_table[current_user()->uid];
        ?>
        <section class="contained">
          <header class="contained-head">
            <h1 class="hd-s">Your summary</h1>
          </header>
          <div class="contained-body">
            <table class="fancy-cb-group">
              <thead>
                <tr>
                  <th>Success</th>
                  <th>Failed</th>
                  <th>Pending</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?= $agent['success']; ?></td>
                  <td><?= $agent['failed']; ?></td>
                  <td><?= $agent['pending']; ?></td>
                  <td><strong><?= $agent['sum']; ?></strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
        <?php endif; ?>
        
        
        <?php if ($survey->introduction) : ?>
        <section class="contained">
          <h1 class="hd-s">Survey introduction</h1>
          <p><?= nl2br_except_pre($survey->introduction) ?></p> 
        </section>
        
        <?php elseif (has_permission('edit any survey')): ?>
        <section class="contained">
          <h1 class="hd-s">Survey introduction</h1>
          <div class="widget-empty">
            <p>There's no introduction text.<br/>You can add it by editing the survey <em>(Edit > Modify)</em></p>
          </div>
        </section>
        <?php endif; ?>
        
      </div>
      
    </div>

  </section>
</main>