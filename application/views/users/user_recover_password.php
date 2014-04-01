<header id="site-head" class="horizontal">
  <h1 id="site-title"><a href="<?= base_url(); ?>" title="Go home"><span>Airwolf</span></a></h1>
</header>

<main id="site-body">
  <div class="row">
    <div class="login-box">
    
    <section class="contained">
      <header class="contained-head">
        <h1 class="hd-s">Recover password</h1>
      </header>
      
      <div class="contained-body">
        <?= validation_errors(); ?>
        <?= form_open(); ?>
        
          <?= form_label('Email', 'user_email'); ?>
          <?= form_input('user_email', set_value('user_email')); ?>
          
          <?= form_submit('user_submit', 'Save'); ?>
        
        <?= form_close(); ?>
      </div>
      
    </section>
    
    </div>
  </div>
</main>