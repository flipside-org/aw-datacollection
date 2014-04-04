<header id="site-head">
    <h1 id="site-title" class="hd-l"><a href="<?= base_url(); ?>" title="Go home">Airwolf</a></h1>

    <nav id="primary" role="navigation">
      <ul id="nav-links">
        <li>
          <?php $current = isset($active_menu) && $active_menu == 'dashboard' ? 'current' : ''; ?>
          <a href="<?= base_url(); ?>" class="dashboard <?= $current; ?>"><span class="visually-hidden">Dashboard</span></a>
        </li>
        
        <?php if (has_permission('view survey list any') || has_permission('view survey list assigned')) : ?>
        <li>
          <?php $current = isset($active_menu) && $active_menu == 'surveys' ? 'current' : ''; ?>
          <a href="<?= base_url('surveys'); ?>" class="surveys <?= $current; ?>" data-dropdown="action-bttn-primary"><span class="visually-hidden">Surveys</span></a>
          <ul class="action-dropdown-primary">
            <li><a href="<?= base_url('surveys'); ?>">View all Surveys</a></li>
            
            <?php if (has_permission('create survey')) : ?>
            <li><a href="<?= base_url('survey/add'); ?>">Add new</a></li>
            <?php endif; ?>
            
          </ul>
        </li>
        <?php endif; ?>
        
        <?php if (has_permission('view user list')) : ?>
        <li>
          <?php $current = isset($active_menu) && $active_menu == 'users' ? 'current' : ''; ?>
          <a href="<?= base_url('users'); ?>" class="users <?= $current; ?>" data-dropdown="action-bttn-primary"><span class="visually-hidden">Users</span></a>
          <ul class="action-dropdown-primary">
            <li><a href="<?= base_url('users'); ?>">View all Users</a></li>
            
            <?php if (has_permission('create account')) : ?>
            <li><a href="<?= base_url('user/add'); ?>">Add new</a></li>
            <?php endif; ?>
            
          </ul>
        </li>
        <?php endif; ?>
        
        <!-- Dev option to switch between users -->
        <li>
          <a href="#" class="user-switch-dev" data-dropdown="action-bttn-primary"><span class="visually-hidden">Users</span></a>
          <ul class="action-dropdown-primary">
            <?php foreach ($this->user_model->get_all() as $user): ?>
            <li>
              <a href="<?= base_url('fixtures/switch_user/' . $user->uid . '?current=' . current_url()) ?>"><?= $user->name; ?> [<?= $user->uid; ?>] (<?= implode(',', $user->roles); ?>)</a>
            </li>
            <?php endforeach; ?>
          </ul>
        </li>
        <!-- //END Dev option to switch between users -->
        
        <?php if (is_logged()) : ?>
        <li>
          <a href="#" class="account" data-dropdown="action-bttn-primary"><span class="visually-hidden">Account</span></a>
          <ul class="action-dropdown-primary">
            <li class="logged-user">Signed in as <strong><?= current_user()->name ?></strong></li>
            <li><a href="<?= current_user()->get_url_edit() ?>">Edit profile</a></li>
            <li><a href="<?= base_url('logout') ?>">Sign out</a></li>
          </ul>
        </li>
        <?php else: ?>
        <?php endif; ?>
      
      </ul>
    </nav>

    <div id="connection-status">
      <em class="beacon"><span class="visually-hidden">Online</span></em>
    </div>
  </header>