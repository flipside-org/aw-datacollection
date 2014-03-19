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
        
        <?php if (has_permission('view survey list')) : ?>
        <li class="divider"></li>
        <li class="has-dropdown">
          <a href="<?= base_url('surveys'); ?>">Surveys</a>
          <ul class="dropdown">
            <li><a href="<?= base_url('survey/add'); ?>">Add</a></li>            
          </ul>
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
            <li><a href="<?= base_url('fixtures/switch_user/1?current=' . current_url()) ?>">Admin (1)</a></li>
            <li><a href="<?= base_url('fixtures/switch_user/4?current=' . current_url()) ?>">Moderator (4)</a></li>
            <li><a href="<?= base_url('fixtures/switch_user/3?current=' . current_url()) ?>">CC Agent (3)</a></li>
            <li><a href="<?= base_url('fixtures/switch_user/2?current=' . current_url()) ?>">Regular (2)</a></li>
            <li><a href="<?= base_url('fixtures/switch_user/5?current=' . current_url()) ?>">Blocked (5)</a></li>
            <li><a href="<?= base_url('fixtures/switch_user/6?current=' . current_url()) ?>">Deleted (6)</a></li>
            <li><a href="<?= base_url('fixtures/switch_user/7?current=' . current_url()) ?>">All Roles (7)</a></li>
          </ul>
        </li>
        <li class="divider"></li>
        
      <?php if (is_logged()) : ?>
      	<li class="name"><a href="<?= base_url('user') ?>">Hello <?= current_user()->name ?></a></li>
      	<li class="has-form">
      	  <a href="<?= base_url('logout') ?>" class="button alert">logout</a>
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