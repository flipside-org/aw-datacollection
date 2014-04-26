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
            <li>
              <a href="#" class="bttn bttn-success bttn-medium" data-trigger-submit="user_submit">Save</a>
            </li>
          </ul>
        </nav>
        
      </div>
    </header>

    <div class="content">
      
      <?= form_open(); ?>
      <div class="columns small-6">
        <fieldset class="contained">
          <div class="form-control">
          <?= form_label('Name <small>Required</small>', 'user_name'); ?>
          <?= form_input('user_name', set_value('user_name', property_if_not_null($user, 'name')), 'id="user_name"'); ?>
          <?= form_error('user_name'); ?>
          </div>
          
          <?php if ($action == 'add'): ?>
            <div class="form-control">
            <?= form_label('Username <small>Required</small>', 'user_username'); ?>
            <?= form_input('user_username', set_value('user_username'), 'id="user_username"'); ?>
            <?= form_error('user_username'); ?>
            </div>
            
            <div class="form-control">
            <?= form_label('Email <small>Required</small>', 'user_email'); ?>
            <?= form_input(array('type' => 'email', 'name' => 'user_email'), set_value('user_email'), 'id="user_email"'); ?>
            <?= form_error('user_email'); ?>
            </div>
          
          <?php endif; ?>
          
          <?php if ($action == 'edit_own'): ?>
            <div class="form-control">
            <?= form_label('Current password', 'user_password'); ?>
            <?= form_password('user_password', '', 'id="user_password"'); ?>
            <p class="help-text">Only type your current password if you're going to change it.</p>
            <?= form_error('user_password'); ?>
            </div>
          <?php endif; ?>
          
          <div class="form-control">
            <?php if ($action == 'edit_own' || $action == 'edit_other'): ?>
              <?= form_label('New Password', 'user_new_password'); ?>
            <?php else: ?>
              <?= form_label('Password <small>Required</small>', 'user_new_password'); ?>
            <?php endif; ?>
            <?= form_password('user_new_password', '', 'id="user_new_password"'); ?>
            <p class="help-text">If you would like to change the password type a new one. Otherwise leave this blank.</p>
            <p class="help-text">Hint: The password should be at least eight characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! &quot; ? $ % &circ; &amp; ).</p>
            <?= form_error('user_new_password'); ?>
          </div>
            
          
          <?php if ($action == 'edit_own' || $action == 'edit_other'): ?>
            <div class="form-control">
            <?= form_label('New Password Confirmation', 'user_new_password_confirm'); ?>
            <?= form_password('user_new_password_confirm', '', 'id="user_new_password_confirm"'); ?>
            <p class="help-text">Type the new password again.</p>
            <?= form_error('user_new_password_confirm'); ?>
            </div>
          <?php endif; ?>
          
        </fieldset>
      </div>
      
      <?php if ($action == 'edit_other' || $action == 'add'): ?>
      <div class="columns small-6">
        <fieldset class="contained">
          
          <?php if ($action == 'edit_other' || $action == 'add'): ?>
            <div class="form-control">
            <?= form_label('Roles', 'user_roles'); ?>
            <?php foreach ($this->config->item('roles') as $key => $role_name): ?>
              <?php $checked_role = isset($user) ? $user->has_role($key) : FALSE; ?>
              <label class="inline-label">
              <?= form_checkbox('user_roles[]', $key, set_value('user_roles', $checked_role)); ?> <?= $role_name; ?>
              </label>
            <?php endforeach; ?>
            <?= form_error('user_roles'); ?>
            </div>
            
            <div class="form-control">
            <?= form_label('Status', 'user_status'); ?>
            <?= form_dropdown('user_status', User_entity::$statuses, property_if_not_null($user, 'status'), 'id="user_status"'); ?>
            <?= form_error('user_status'); ?>
            </div>
          <?php endif; ?>
          
        </fieldset>
        
        <?php if ($action == 'add'): ?>
        <fieldset class="contained">
          <div class="form-control">
            <label class="inline-label">
            <?= form_checkbox('user_notify', 'notify'); ?> Notify user about account creation.
            </label>
            <p class="help-text">If checked an email will be sent to the user with the login data.</p>
          </div>
        </fieldset>
        <?php endif; ?>
      </div>
      <?php endif; ?>
        
        
        
        
        
        
      <?= form_button(array(
        'type' => 'submit',
        'name' => 'user_submit',
        'id' => 'user_submit',
        'value' => 'user_submit',
        'class' => 'hide',
        'content' => 'Save user'));
      ?>
      <?= form_close(); ?>

    </div>
  </section>
</main>