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
              <li><a href="" class="bttn bttn-default bttn-small current">All</a></li>
              <li><a href="" class="bttn bttn-default bttn-small">Draft</a></li>
              <li><a href="" class="bttn bttn-default bttn-small">Open</a></li>
              <li><a href="" class="bttn bttn-default bttn-small">Closed</a></li>
              <li><a href="" class="bttn bttn-default bttn-small">Canceled</a></li>
            </ul>
            
          </header>

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
                  <td><?= date('d M, Y', 0) ?> <small>Modified</small></td>
                  <td>
                    <ul class="bttn-toolbar">
                      <li>
                        <a href="#" class="bttn bttn-primary bttn-small bttn-dropdown" data-dropdown="action-bttn">Edit</a>
                        <ul class="action-dropdown for-bttn-small">
                          <li>
                          <?php if (has_permission('edit any survey')) : ?>
                          <li><?= anchor($survey_entity->get_url_edit(), 'Modify'); ?></li>
                          <?php endif; ?>
      
                          <?php if (has_permission('delete any survey')) : ?>
                          <li><?= anchor_csrf($survey_entity->get_url_delete(), 'Delete', array('class' => 'danger')); ?></li>
                          <?php endif; ?>
                        </ul>
                      </li>
                    </ul>
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