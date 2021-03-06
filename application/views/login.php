<header id="site-head">
  <div class="inner">
    <h1 id="site-title" class="hd-xl"><a href="<?= base_url(); ?>" title="Go home">Airwolf</a></h1>
  </div>
</header>

<main id="site-body">
  <div class="row">
    <div class="login-box">
    
    <section class="contained">
      <header class="contained-head">
        <h1 class="hd-s">Login</h1>
      </header>
      
      <div class="contained-body">
        
        <?= form_open('', array('id' => 'login-form')); ?>
          <div class="form-control">
          <?= form_label('Username', 'signin_username'); ?>
          <?= form_input('signin_username', '', 'id="signin_username"'); ?>
          <?= form_error('signin_username'); ?>
          </div>
          
          <div class="form-control">
          <?= form_label('Password', 'signin_password'); ?>
          <?= form_password('signin_password', '', 'id="signin_username"'); ?>
          <?= form_error('signin_password'); ?>
          </div>
          <?= anchor('user/recover', 'Recover password'); ?>
          <?= form_button(array(
            'type' => 'submit',
            'name' => 'signin_submit',
            'id' => 'signin_submit',
            'value' => 'signin_submit',
            'class' => 'bttn bttn-success bttn-medium',
            'content' => 'Login'));
          ?>
        
        <?= form_close(); ?>
      </div>
      
      <?php if (ENVIRONMENT == 'demo') : ?>
      <div id="demo-users">
        <dl>
        	<dt>User: agent</dt>
        	<dd><span class="highlight">Pass:</span> agent</dd>
        </dl>
        <dl>
        	<dt>User: moderator</dt>
        	<dd><span class="highlight">Pass:</span> moderator</dd>
        </dl>
        More information can be found on the <a href="https://github.com/flipside-org/aw-datacollection/" title="View project on github.">project's github page.</a>
      </div>
      <?php endif; ?>
      
    </section>
    </div>
  </div>
</main>