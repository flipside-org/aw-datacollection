  
<div id="debug_data">
  <span class="connection-status label alert">Offline</span>
  <a href="#" class="button tiny success allow-submission">Allow one submission</a>
  <h2 id="totals"></h2>
  <h3>Queue Resp</h3>
  <div id="queue_resp"></div>
  <h3>Queue Submit</h3>
  <div id="queue_submit"></div>
</div>
  
 
<div class="main">
  <article class="paper">
    <h1 id="respondent_number"></h1>
    <header class="form-header clearfix">
      <span class="form-language-selector"><span>Choose Language</span></span>
    </header>
    <!-- this is where the form will go -->
    
    <?php if (isset($enketo_testrun) && $enketo_testrun): ?>
      <button id="validate-form" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Validate</button>
    <?php else : ?>
      <button id="submit-form" class="btn btn-primary btn-large" ><i class="glyphicon glyphicon-ok"></i> Submit</button>
    <?php endif; ?>
    
    <div class="enketo-power">Powered by <a href="http://enketo.org" title="enketo.org website"><img src="https://enketo.org/images/enketo_bare_100x37.png" alt="enketo logo" /></a>
    </div>
  </article>
</div>