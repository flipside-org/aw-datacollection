<main id="site-body">
  <section class="row">
    
    <header id="page-head">
      <div class="heading">
        <h1 class="hd-xl">Surveys</h1>
      </div>
      
      <nav id="secondary" role="navigation">
        <ul class="links">
          <?php if (has_permission('create survey')) : ?>
          <li>
            <a href="<?= base_url('survey/add'); ?>" class="bttn bttn-primary bttn-medium">Add new</a>
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
                  <td><strong class="<?= $survey_entity->get_status_html_class(); ?>"><?= $survey_entity->get_status_label(); ?></strong></td>
                  <td><a href="<?= $survey_entity->get_url_view() ?>"><?= $survey_entity->title ?></a></td>
                  <td><?= date('d-m-Y', 0)?></td>
                  <td>
                    <?php if (has_permission('edit any survey')) : ?>
                    <?= anchor($survey_entity->get_url_edit(), 'Edit', array('class' => 'bttn bttn-small bttn-primary')); ?>
                    <?php endif; ?>
              
                    <?php if (has_permission('delete any survey')) : ?>
                    <?= anchor_csrf($survey_entity->get_url_delete(), 'Delete', array('class' => 'bttn bttn-small bttn-danger')); ?>
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