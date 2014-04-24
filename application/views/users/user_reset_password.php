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
        <h1 class="hd-s">Reset password</h1>
      </header>
      
      <div class="contained-body">
      <?= form_open(); ?>
        <div class="form-control">
          <?= form_label('New Password', 'user_new_password'); ?>
          <?= form_password('user_new_password', '', 'id="user_new_password"'); ?>
          <?= form_error('user_new_password'); ?>
        </div>
        
        <div class="form-control">
          <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
          <?= form_password('user_new_password_confirm', '', 'id="user_new_password_confirm"'); ?>
          <?= form_error('user_new_password_confirm'); ?>
        </div>
        <?= form_button(array(
          'type' => 'submit',
          'name' => 'user_submit',
          'id' => 'user_submit',
          'value' => 'user_submit',
          'class' => 'bttn bttn-success bttn-medium',
          'content' => 'Reset password'));
        ?>
      <?= form_close(); ?>
      </div>
    </section>
    
    </div>
  </div>
</main>