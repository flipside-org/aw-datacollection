<main id="site-body">
  <section class="row">
    
    <header id="page-head">
      <div class="heading">
        <h1 class="hd-xl">Users</h1>
      </div>
      
      <nav id="secondary" role="navigation">
        <ul class="links">
          <?php if (has_permission('create account')) : ?>
          <li>
            <a href="<?= base_url('user/add'); ?>" class="bttn bttn-primary bttn-medium">Add new</a>
          </li>
          <?php endif; ?>
        </ul>
      </nav>
    </header>
    
    <div class="content">      
      <div class="columns small-12">
        <section class="contained">
          <header class="contained-head">
            
            <ul class="bttn-group bttn-center">
              <li><a href="" class="bttn bttn-default bttn-small current">All</a></li>
              <li><a href="" class="bttn bttn-default bttn-small">Active</a></li>
              <li><a href="" class="bttn bttn-default bttn-small">Blocked</a></li>
            </ul>
            
          </header>
            
          <div class="contained-body">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Roles</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $user_entity):?>
                <tr>
                  <td><?= $user_entity->name; ?></td>
                  <td><?= implode(', ', $user_entity->roles); ?></td>
                  <td><?= $user_entity->status; ?></td>
                  <td>
                    <?php if (has_permission('edit any account')) :?>
                    <a href="<?= $user_entity->get_url_edit() ?>" class="bttn bttn-primary bttn-small">Edit</a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
      
  </section>
</main>