      <!-- Header and Nav -->
  <nav class="top-bar" data-topbar>
    <ul class="title-area">
      <!-- Title Area -->
      <li class="name">
        <h1>
          <a href="<?= base_url(); ?>">
            Airwolf
          </a>
        </h1>
      </li>
      <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
    </ul>

    <section class="top-bar-section">
      <!-- Right Nav Section -->
      <ul class="left">
        
        <?php if (has_permission('view survey list any') || has_permission('view survey list assigned')) : ?>
        <li class="divider"></li>
        <li class="has-dropdown">
          <a href="<?= base_url('surveys'); ?>">Surveys</a>
          <?php if (has_permission('create survey')) : ?>
          <ul class="dropdown">
            <li><a href="<?= base_url('survey/add'); ?>">Add</a></li>
          </ul>
          <?php endif; ?>
        </li>
        <?php endif; ?>

        <?php if (has_permission('view user list')) : ?>
        <li class="divider"></li>
        <li class="has-dropdown">
          <a href="<?= base_url('users'); ?>">Users</a>
          <ul class="dropdown">
            <li><a href="<?= base_url('user/add'); ?>">Add</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
      
      <ul class="right">        
        <li class="divider"></li>
        <li class="has-dropdown">
          <a href="#">Switch user</a>
          <ul class="dropdown">
            <?php foreach ($this->user_model->get_all() as $user): ?>
            <li>
              <a href="<?= base_url('fixtures/switch_user/' . $user->uid . '?current=' . current_url()) ?>"><?= $user->name; ?> [<?= $user->uid; ?>] (<?= implode(',', $user->roles); ?>)</a>
            </li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="divider"></li>
        
      <?php if (is_logged()) : ?>
      	<li class="name"><a href="<?= base_url('user') ?>">Hello <?= current_user()->name ?></a></li>
      	<li class="has-form">
      	  <a href="<?= base_url('logout') ?>" id="logout-button" class="button alert">logout</a>
      	</li>
      </ul>
      <?php else: ?>
        <li class="has-form">
          <a href="<?= base_url('login') ?>" class="button">login</a>
        </li>
      <?php endif; ?>
    </section>
  </nav>

  <!-- End Header and Nav -->