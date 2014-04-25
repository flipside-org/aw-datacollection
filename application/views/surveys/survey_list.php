<main id="site-body">
  <section class="row">

    <header id="page-head">
      <div class="inner">
        <div class="heading">
          <h1 class="hd-xl">Surveys</h1>
        </div>

        <nav id="secondary" role="navigation">
          <ul class="bttn-toolbar">
            <?php if (has_permission('create survey')) : ?>
            <li>
              <a href="<?= base_url('survey/add'); ?>" class="bttn bttn-primary bttn-medium">Add new</a>
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
              <li><a href="<?= base_url('surveys'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === FALSE ? 'current' : ''; ?>">All</a></li>
              <?php if (has_permission('view survey list any')) : ?>
              <?php
                // Draft surveys are reserved to those who have
                // 'view survey list any' permission.
                // It's not included in the default list because users with 
                // permission to 'view survey list assigned' can only view open,
                // closed, or canceled surveys.
              ?>
              <li><a href="<?= base_url('surveys/draft'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'draft' ? 'current' : ''; ?>">Draft</a></li>
              <?php endif; ?>
              <li><a href="<?= base_url('surveys/open'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'open' ? 'current' : ''; ?>">Open</a></li>
              <li><a href="<?= base_url('surveys/closed'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'closed' ? 'current' : ''; ?>">Closed</a></li>
              <li><a href="<?= base_url('surveys/canceled'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'canceled' ? 'current' : ''; ?>">Canceled</a></li>
            </ul>
            
          </header>
          
          <?php if (empty($surveys)): ?>
            <div class="contained-empty">
              <h1>Nothing to show</h1>
              <p>There are no surveys with the selected filter.</p>
            </div>
          <?php else : ?>
          <div class="contained-body">
            <table>
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Title</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($surveys as $survey_entity):?>
                <tr>
                  <td><strong class="status <?= $survey_entity->get_status_html_class(); ?>"><?= $survey_entity->get_status_label(); ?></strong></td>
                  <td><a href="<?= $survey_entity->get_url_view() ?>" class="go-link"><strong class="highlight"><?= $survey_entity->title ?></strong></a></td>
                  <td><?= date('d M, Y', $survey_entity->updated->sec) ?> <small><?= $survey_entity->created == $survey_entity->updated ? 'Created' : 'Updated'; ?></small></td>
                  <td>
                    
                    <?php if (has_permission('edit any survey') || has_permission('delete any survey')) : ?>
                    <ul class="bttn-toolbar">
                      <li>
                        <a href="#" class="bttn bttn-primary bttn-small bttn-dropdown" data-dropdown="action-bttn">Edit</a>
                        <ul class="action-dropdown for-bttn-small">
                          <?php if (has_permission('edit any survey')) : ?>
                          <li><?= anchor($survey_entity->get_url_edit(), 'Modify'); ?></li>
                          <?php endif; ?>
      
                          <?php if (has_permission('delete any survey')) : ?>
                          <?php $class = 'danger'; ?>
                          <?php $class .= !$survey_entity->status_allows('delete any survey') ? ' disabled': ''; ?>
                          <li><?= anchor_csrf($survey_entity->get_url_delete(), 'Delete', array('class' => $class, 'data-confirm-action' => 'Are you sure you want to delete: <em>' . $survey_entity->title . '</em>?')) ?></li>
                          <?php endif; ?>
                        </ul>
                      </li>
                    </ul>
                    <?php endif; ?>
                    
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </section>
      </div>
    </div>

  </section>
</main>