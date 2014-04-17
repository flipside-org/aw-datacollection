<main id="site-body">
  <section class="row">
    <header id="page-head">
      <div class="inner">
        
        <div class="heading">
          <h1 class="hd-xl">Users</h1>
        </div>
        
        <nav id="secondary" role="navigation">
          <ul class="bttn-toolbar">
            <?php if (has_permission('create account')) : ?>
            <li>
              <a href="<?= base_url('user/add'); ?>" class="bttn bttn-primary bttn-medium">Add new</a>
            </li>
            <?php endif; ?>
          </ul>
        </nav>
      
      </div>
    </header>
    
    <div class="content">      
      <div class="columns small-12">
        <section class="contained">
          <header class="contained-head">
            
            <ul class="bttn-group bttn-center filters">
              <?php $active_filter = $this->uri->segment(2); ?>
              <li><a href="<?= base_url('users'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === FALSE ? 'current' : ''; ?>">All</a></li>
              <li><a href="<?= base_url('users/active'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'active' ? 'current' : ''; ?>">Active</a></li>
              <li><a href="<?= base_url('users/blocked'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'blocked' ? 'current' : ''; ?>">Blocked</a></li>
            </ul>
            
          </header>
            
          <div class="contained-body">
            <table>
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Name</th>
                  <th>Roles</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $user_entity):?>
                <tr>
                  <td><strong class="status <?= $user_entity->get_status_html_class(); ?>"><?= $user_entity->get_status_label(); ?></strong></td>
                  <td><strong class="highlight"><?= $user_entity->name; ?></strong></td>
                  <td><?= implode(', ', $user_entity->get_roles_label()); ?></td>
                  <td>
                    
                    <?php if (has_permission('edit any account') || has_permission('delete any account')) : ?>
                    <ul class="bttn-toolbar">
                      <li>
                        <a href="#" class="bttn bttn-primary bttn-small bttn-dropdown" data-dropdown="action-bttn">Edit</a>
                        <ul class="action-dropdown for-bttn-small">
                          <?php if (has_permission('edit any account')) : ?>
                          <li><?= anchor($user_entity->get_url_edit(), 'Modify'); ?></li>
                          <?php endif; ?>
      
                          <?php if (has_permission('delete any account')) : ?>
                          <li><?= anchor_csrf($user_entity->get_url_delete(), 'Delete', array('class' => 'danger')); ?></li>
                          <?php endif; ?>
                        </ul>
                      </li>
                    </ul>
                    <?php endif; ?>
                    
                  </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($users)): ?>
                  <tr>
                  	<td colspan="4">
                      <div class="contained-empty">
                        <h1>Nothing to show</h1>
                        <p>There are no users with the selected filter.</p>
                      </div>
                  	</td>
                  </tr>
                <?php endif; ?>
                
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
      
  </section>
</main>