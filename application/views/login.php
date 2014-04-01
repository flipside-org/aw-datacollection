<header id="site-head" class="horizontal">
  <h1 id="site-title"><a href="<?= base_url(); ?>" title="Go home"><span>Airwolf</span></a></h1>
</header>

<main id="site-body">
  <div class="row">
    <div class="login-box">
    
    <section class="contained">
      <header class="contained-head">
        <h1 class="hd-s">Login</h1>
      </header>
      
      <div class="contained-body">
        <?= form_error('signin_username', '<div class="error" data-ref="signin_username">', '</div>'); ?>
        <?= form_error('signin_password', '<div class="error" data-ref="signin_password">', '</div>'); ?>
        
        <?= form_open('', array('id' => 'login-form')); ?>
        
          <?= form_label('Username', 'signin_username'); ?>
          <?= form_input('signin_username'); ?>
        
          <?= form_label('Password', 'signin_password'); ?>
          <?= form_password('signin_password'); ?>
          <?= anchor('user/recover', 'Recover password'); ?>
          <?= form_submit('signin_submit', 'Login'); ?>
        
        <?= form_close(); ?>
      </div>
      
    </section>
    
    </div>
  </div>
</main>