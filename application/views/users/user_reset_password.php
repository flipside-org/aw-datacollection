<header id="site-head">
  <div class="inner">
    <h1 id="site-title"><a href="<?= base_url(); ?>" title="Go home"><span>Airwolf</span></a></h1>
  </div>
</header>

<main id="site-body">
  <div class="row">
    <div class="login-box">
    
    <section class="contained">
      <header class="contained-head">
        <h1 class="hd-s">Reset password</h1>
      </header>
      
      <div class="contained-body">
      <?= validation_errors(); ?>
      <?= form_open(); ?>
      
        <?= form_label('New Password', 'user_new_password'); ?>
        <?= form_password('user_new_password'); ?>
        <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
        <?= form_password('user_new_password_confirm'); ?>
        
        <?= form_submit('user_submit', 'Save'); ?>
      
      <?= form_close(); ?>
      </div>
      
    </section>
    
    </div>
  </div>
</main>