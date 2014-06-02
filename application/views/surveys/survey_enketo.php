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
              <?php if ($enketo_action == 'data_collection' || $enketo_action == 'data_collection_single'): ?>
              <strong class="bttn-sector">Collect data</strong>
              <em class="respondent-number">Loading number...</em>
              <?php else: ?>
              <strong class="bttn-sector">Testrun</strong>
              <?php endif; ?>
            </li>
            <?php if ($enketo_action == 'data_collection' || $enketo_action == 'data_collection_single'): ?>
            <li><a href="#" class="bttn bttn-danger bttn-medium bttn-icon-halt revealed disabled" id="enketo-halt">Halt</a></li>
            <li><a href="#" class="bttn bttn-primary bttn-medium bttn-icon-proceed step1" id="enketo-proceed">Proceed</a></li>
            <li><a href="#" class="bttn bttn-success bttn-medium bttn-icon-save step2" id="enketo-save">Save</a></li>
            <?php else: ?>
            <li><a href="<?= $survey->get_url_view(); ?>" class="bttn bttn-default bttn-medium bttn-icon-cancel">Cancel</a></li>
            <li><a href="#" class="bttn bttn-success bttn-medium bttn-icon-save" id="enketo-validate">Validate</a></li>
            <?php endif; ?>
          </ul>
        </nav>
        
      </div>
    </header>

    <div class="content">
      
      <!-- START debug data -->
      <div class="debug" id="debug-data" style="display:none">
        <div class="columns small-6">
          <section class="contained">
            <h1 class="hd-s">Queue respondents</h1>
            <p><em>Respondents received from the server since page loading:</em></p>
            <div class="queue-resp"></div>
          </section>
        </div>
        <div class="columns small-6">
          <section class="contained">
            <h1 class="hd-s">Queue submit</h1>
            <a href="#" class="bttn bttn-success bttn-small allow-submission">Allow one submission</a>
            <p><em>Respondents with data ready to be submitted:</em></p>
            <div class="queue-submit"></div>
          </section>
        </div>
        <hr />
      </div>
      <!-- END debug data -->
      
      <?php if ($enketo_action == 'data_collection' || $enketo_action == 'data_collection_single'): ?>
      <!-- START metadata -->
      <div class="step1">
        
        <div class="columns small-6">
          <section class="contained" id="call-activity">
            <!-- Set through javascript -->
            <!-- <header class="contained-head">
              <h1 class="hd-s">Call activity</h1>
            </header>
            <div class="contained-body">
              <table>
                <thead>
                  <tr>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <strong>No reply</strong>
                      <p><em>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum viverra ornare orci malesuada.</em></p>
                    </td>
                    <td>18 Mar, 2014 at 14:00</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="widget-empty">
              <p>There is no call activity for this respondent.</p>
            </div> -->
          </section>
        </div>
        
        <div class="columns small-6">
          <section class="contained" id="survey-introduction">
            <h1 class="hd-s">Survey introduction</h1>
            <?php if ($survey->introduction) : ?>
              <p><?= nl2br_except_pre($survey->introduction) ?></p> 
            <?php else: ?>
              <p>There's no introduction text.</p>
            <?php endif; ?>
          </section>
        </div>
      
      </div>
      <!-- END metadata -->
      <?php endif; ?>
      
      <!-- START enketo form (step2) -->
      <div class="step2">
        <div id="aw-enketo-wrapper" class="columns small-12">
          <div class="main enketo-container">
            <article class="paper">
              <header class="form-header clearfix">
                <span class="form-language-selector"><span>Choose Language</span></span>
              </header>
              <div id="enketo-form"><!-- Enketo form location --></div>
              <div class="enketo-power">Powered by <a href="http://enketo.org" title="enketo.org website"><img src="https://enketo.org/images/enketo_bare_100x37.png" alt="enketo logo" /></a>
              </div>
            </article>
          </div>        
      </div>
    </div>
    <!-- END enketo form (step2) -->
    
    <?php if ($enketo_action == 'data_collection' || $enketo_action == 'data_collection_single'): ?>
    <!-- START modal -->
    <div class="modal-wrapper" id="modal">
      <section class="confirm-box">
        <?php form_open(); ?>
        <header class="confirm-box-head">
          <h1 class="hd-s confirm-title">Halt</h1>
          <a href="#" class="confirm-close confirm-icon-close"><span class="visually-hidden">Close</span></a>
        </header>
        <fieldset class="confirm-box-body">
          <div class="form-control">
            <?php
              $labels = Call_task_status::$labels;
              // Remove success status.
              unset($labels[Call_task_status::SUCCESSFUL]);
              // Add a "Select status" option.
              $labels = array('--' => '-- Select status --') + $labels;
            ?>
            <?= form_label('Reason <small>Required</small>', 'call_task_status_code'); ?>
            <?= form_dropdown('call_task_status_code', $labels) ?>
            <small class="error">The reason is required.</small>
          </div>
          
          <div class="form-control">
            <?= form_label('Additional notes', 'call_task_status_msg'); ?>
            <?= form_textarea('call_task_status_msg'); ?>
            <p class="help-text">You can provide additional notes if needed.</p>
          </div>
        </fieldset>
        <footer class="confirm-box-foot">
          <ul class="bttn-toolbar">
            <li><button id="call-task-status-submit" class="bttn bttn-medium bttn-default confirm-cancel">Cancel</button></li>
            <li><button id="call-task-status-cancel" class="bttn bttn-medium bttn-success confirm-accept">Confirm</button></li>
          </ul>
        </footer>
        <?php form_close() ?>
      </section>
    </div>
    <!-- END modal -->
    <?php endif; ?>

  </section>
</main>