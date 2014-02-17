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
        <li class="divider"></li>
        <li class="has-dropdown">
          <a href="<?= base_url('surveys'); ?>">Surveys</a>
          <ul class="dropdown">
            <li><a href="<?= base_url('survey/add'); ?>">Add</a></li>            
          </ul>
        </li>
        <li class="divider"></li>
        <li class="has-dropdown">
          <a href="#">Main Item 6</a>
          <ul class="dropdown">
            <li><a href="#">Dropdown Option</a></li>
            <li><a href="#">Dropdown Option</a></li>
            <li><label>Section Name</label></li>
            <li class="has-dropdown">
              <a href="#" class="">Has Dropdown, Level 1</a>
              <ul class="dropdown">
                <li><a href="#">Dropdown Options</a></li>
                <li><a href="#">Dropdown Options</a></li>
                
              </ul>
            </li>
          </ul>
        </li>
      </ul>
      <?php if (is_logged()) : ?>
      <ul class="right">
      	<li class="name"><a href="user">Hello <?= current_user()->name ?></a></li>
      	<li class="has-form">
      	  <a href="<?= base_url('logout') ?>" class="button alert">logout</a>
      	</li>
      </ul>
      <?php endif; ?>
    </section>
  </nav>
 
  <!-- End Header and Nav -->