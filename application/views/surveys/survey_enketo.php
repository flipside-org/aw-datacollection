<!-- START debug data -->
<div id="debug_data" class="">
  <div class="row">
    <div class="small-12 columns">
      <h2>Debug</h2>
      <span class="connection-status label alert">Offline</span>
      <a href="#" class="button tiny success allow-submission">Allow one submission</a>
    </div>
  </div>
  <div class="row">
    <div class="small-6 columns">
      <h4>Queue Respondents</h4>
      <p>Respondents received from the server since page loading:</p>
      <div class="queue-resp"></div>
    </div>
    <div class="small-6 columns">
      <h4>Queue Submit</h4>
      <p>Respondents with data ready to be submitted:</p>
      <div class="queue-submit"></div>
    </div>
  </div>
<hr />
</div>
<!-- END debug data -->



<?php if ($enketo_action == 'data_collection' || $enketo_action == 'data_collection_single'): ?>
<div class="row">
  <div class="small-12 columns">
    <h2>Number: <small id="respondent_number">Waiting for number...</small></h2>
    
    <div class="intro-text"><?= nl2br_except_pre($survey->introduction) ?></div>
    
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

<?php $vidibility = $enketo_action == 'data_collection' ? 'hide' : ''; ?>
<div class="main enketo-container <?= $vidibility ?>">
  <article class="paper">
    <header class="form-header clearfix">
      <span class="form-language-selector"><span>Choose Language</span></span>
    </header>
    <!-- this is where the form will go -->
    
    <?php if ($enketo_action == 'testrun'): ?>
      <button id="validate-form" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Validate</button>
    <?php else : ?>
      <button id="submit-form" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Submit</button>
    <?php endif; ?>
    
    <div class="enketo-power">Powered by <a href="http://enketo.org" title="enketo.org website"><img src="https://enketo.org/images/enketo_bare_100x37.png" alt="enketo logo" /></a>
    </div>
  </article>
</div>