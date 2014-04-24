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
        <h1 class="hd-s">Recover password</h1>
      </header>
      
      <div class="contained-body">
        <?= form_open(); ?>
        
          <div class="form-control">
          <?= form_label('Email', 'user_email'); ?>
          <?= form_input('user_email', set_value('user_email'), 'id="user_email"'); ?>
          <?= form_error('user_email'); ?>
        </div>
        <?= form_button(array(
          'type' => 'submit',
          'name' => 'user_submit',
          'id' => 'user_submit',
          'value' => 'user_submit',
          'class' => 'bttn bttn-success bttn-medium',
          'content' => 'Recover password'));
        ?>
        <?= form_close(); ?>
      </div>
      
    </section>
    
    </div>
  </div>
</main>