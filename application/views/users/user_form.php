<main id="site-body">
  <section class="row">
    <header id="page-head">
      <div class="inner">
        
        <div class="heading">
          <?php if ($user) : ?>
          <h1 class="hd-xl"><?= $user->name ?></h1>
          <?php else : ?>
          <h1 class="hd-xl">Users</h1>
          <?php endif; ?>
        </div>
        
        <nav id="secondary" role="navigation">
          <ul class="bttn-toolbar">
            <li class="sector-switcher">
              <strong class="bttn-sector">
              <?php if ($user) : ?>
                Edit
              <?php else : ?>
                New
              <?php endif; ?>
              </strong>
            </li>
            <li>
              <?php if(has_permission('view user list')) : ?>
              <a href="<?= base_url('users'); ?>" class="bttn bttn-default bttn-medium">Cancel</a>
              <?php else : ?>
              <a href="<?= base_url(); ?>" class="bttn bttn-default bttn-medium">Cancel</a>
              <?php endif; ?>
            </li>
          </ul>
        </nav>
        
      </div>
    </header>

    <div class="content">

      <?= validation_errors(); ?>
      <?= form_open(); ?>
      
        <?= form_label('Name', 'user_name'); ?>
        <?= form_input('user_name', set_value('user_name', property_if_not_null($user, 'name'))); ?>
        
        <?php if ($action == 'add'): ?>
          <?= form_label('Username', 'user_username'); ?>
          <?= form_input('user_username', set_value('user_username')); ?>
          
          <?= form_label('Email', 'user_email'); ?>
          <?= form_input('user_email', set_value('user_email')); ?>
        
          <?= form_label('Password', 'user_new_password'); ?>
          <?= form_password('user_new_password'); ?>
        <?php endif; ?>
        
        <?php if ($action == 'edit_own' || $action == 'edit_other'): ?>
          <?= form_label('New Password', 'user_new_password'); ?>
          <?= form_password('user_new_password'); ?>
          <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
          <?= form_password('user_new_password_confirm'); ?>
        <?php endif; ?>
        
        <?php if ($action == 'edit_other' || $action == 'add'): ?>
          <?= form_label('Roles', 'user_roles'); ?>
          <?php foreach ($this->config->item('roles') as $key => $role_name): ?>
            <?php
              $checked_role = isset($user) ? $user->has_role($key) : FALSE;
            ?>
            <div>
            <?= form_checkbox('user_roles[]', $key, set_value('user_roles', $checked_role)); ?>
            <?= $role_name; ?>
            </div>
          <?php endforeach; ?>
          
          <?= form_label('Status', 'user_status'); ?>
          <?= form_dropdown('user_status', User_entity::$statuses, property_if_not_null($user, 'status')); ?>
        <?php endif; ?>
        
        <?php if ($action == 'add'): ?>
          <div>
            <?= form_checkbox('user_notify', 'notify'); ?>
            Notify user about account creation.
          </div>
        <?php endif; ?>
        
        <?php if ($action == 'edit_own' || $action == 'edit_other'): ?>
          <hr>
          <?= form_label('Password', 'user_password'); ?>
          <?= form_password('user_password'); ?>
          The password is always needed when changing profiles.<br />
        <?php endif; ?>
        
        <?= form_submit('user_submit', 'Save'); ?>
      
      <?= form_close(); ?>

    </div>
  </section>
</main>