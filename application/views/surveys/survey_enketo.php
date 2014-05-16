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
              <strong class="bttn-sector">Collect data</strong>
              <em class="respondent-number">00351232381004</em>
            </li>
            
            <li>
              <a href="#" class="bttn bttn-danger bttn-medium bttn-icon-halt">Halt</a>
            </li>
            
            <li>
              <a href="#" class="bttn bttn-primary bttn-medium bttn-icon-proceed">Proceed</a>
            </li>
            
          </ul>
        </nav>
        
      </div>
    </header>

    <div class="content">
      
      <!-- START debug data -->
      <div class="debug" id="debug_data">
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
      
      
      <div class="columns small-6">
        <section class="contained">
          <header class="contained-head">
            <h1 class="hd-s">Call activity</h1>
          </header>
          <div class="contained-body">
            <table class="fancy-cb-group">
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
          </div>
        </section>
      </div>
      
      <div class="columns small-6">
        <section class="contained">
          <h1 class="hd-s">Survey introduction</h1>
          <?php if ($survey->introduction) : ?>
            <p><?= nl2br_except_pre($survey->introduction) ?></p> 
          <?php else: ?>
            <p>There's no introduction text.</p>
          <?php endif; ?>
        </section>
      </div>
      
      <div>
        
        <?php $visibility = $enketo_action == 'data_collection' ? 'hide' : ''; ?>
        <div id="aw-enketo-wrapper" class="columns small-12">
          <div class="main enketo-container">
            <article class="paper">
              <header class="form-header clearfix">
                <span class="form-language-selector"><span>Choose Language</span></span>
              </header>
              
              <div id="enketo-form"><!-- Enketo form location --></div>
              
              <!-- <?php if ($enketo_action == 'testrun'): ?>
                <button id="validate-form" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Validate</button>
              <?php else : ?>
                <button id="submit-form" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Submit</button>
              <?php endif; ?> -->
              
              <div class="enketo-power">Powered by <a href="http://enketo.org" title="enketo.org website"><img src="https://enketo.org/images/enketo_bare_100x37.png" alt="enketo logo" /></a>
              </div>
            </article>
          </div>        
      </div>
      
    </div>

  </section>
</main>












<div class="modal-wrapper">
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



















<?php if ($enketo_action == 'data_collection' || $enketo_action == 'data_collection_single'): ?>
<div class="row">
  <div class="small-12 columns">
    <h2>Number: <small id="respondent_number">Waiting for number...</small></h2>
    
    <div class="call-actions hide" style="margin-top: 20px">
      <button id="proceed-collection" class="button success tiny">Proceed</button>
      <button id="halt-collection" class="button alert tiny" style="border-radius: 0">Halt</button>
    </div>
    
  </div>
</div>

<div class="row">
  <div class="small-12 columns">
    
    <div class="call-status hide">
      <form>
        <fieldset>
          <legend>Call Status feedback</legend>
          <?php
            $labels = Call_task_status::$labels;
            unset($labels[Call_task_status::SUCCESSFUL]);
          ?>
          
          <select name="call_task_status_code">
            <option value="--">-- Select Status --</option>
          <?php foreach ($labels as $key => $value) : ?>
            <option value="<?= $key; ?>"><?= $value; ?></option>
          <?php endforeach; ?>
          </select>
          
          <textarea name="call_task_status_msg">not used</textarea>
          <button id="call-task-status-submit" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Submit</button>
          <button id="call-task-status-cancel" class="btn btn-danger btn-large" ><i class="glyphicon glyphicon-ok"></i> Cancel</button>
        </fieldset>
      </form>
    </div>
    
  </div>
</div>
<?php endif; ?>

