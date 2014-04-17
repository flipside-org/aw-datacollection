<main id="site-body">
  <section class="row">
    <header id="page-head">
      <div class="inner">
        
        <div class="heading">
          <h1 class="hd-xl <?= $survey->get_status_html_class('indicator-'); ?>"><?= $survey->title ?></h1>
        </div>
        
        <nav id="secondary" role="navigation">
          <ul class="bttn-toolbar">
            <li class="sector-switcher">
              <a class="bttn-sector bttn-dropdown" href="" data-dropdown="action-bttn"><strong>Call activity</strong></a>
              <ul class="action-dropdown">
                <li><a href="<?= $survey->get_url_view() ?>">Summary</a></li>
                
                <?php if (has_permission('manage respondents any survey')) :?>
                <li><a href="<?= $survey->get_url_respondents() ?>">Respondents</a></li>
                <?php endif; ?>
              </ul>
            </li>
            
          </ul>
        </nav>
        
      </div>
    </header>


    <div class="content">
      <div class="columns small-12">
        
        <section class="contained">
          <header class="contained-head">

            <ul class="bttn-group bttn-center filters">
              <?php $active_filter = $this->uri->segment(4); ?>
              <li><a href="<?= $survey->get_url_call_activity(); ?>" class="bttn bttn-default bttn-small <?= $active_filter === FALSE ? 'current' : ''; ?>">All</a></li>
              <li><a href="<?= $survey->get_url_call_activity('completed'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'completed' ? 'current' : ''; ?>">Completed</a></li>
              <li><a href="<?= $survey->get_url_call_activity('pending'); ?>" class="bttn bttn-default bttn-small <?= $active_filter === 'pending' ? 'current' : ''; ?>">Pending</a></li>
            </ul>
            
          </header>

          <?php if (empty($call_tasks)): ?>
            <div class="contained-empty">
              <h1>Nothing to show</h1>
              <p>There is no activity with the selected filter.</p>
            </div>
          <?php else : ?>
          <div class="contained-body">
            <table class="table-nested-4-col">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Number</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              
              <?php foreach ($call_tasks as $call_task_entity):?>
              <tbody>
                <tr>
                  <td><strong class="status"><?= $call_task_entity->is_resolved() ? 'Completed' : 'Pending'; ?></strong></td>
                  <td><a href="#" class="expand-link" data-expand="ct-<?= $call_task_entity->ctid; ?>"><strong class="highlight"><?= $call_task_entity->number ?></strong></a></td>
                  <?php $last_call = end($call_task_entity->activity); ?>
                  <td><?= date('d M, Y', $last_call->created->sec) ?> <small>Last call placed</small></td>
                  <?php $disabled = $call_task_entity->is_resolved() ? 'disabled' : ''; ?>
                  <td>
                    <ul class="bttn-toolbar">
                      <li>
                        <a href="<?= $call_task_entity->get_url_single_data_collection() ?>" class="bttn bttn-success bttn-small <?= $disabled; ?>">Collect data</a>
                      </li>
                    </ul>
                </tr>
                <tr>
                  <td colspan="4">
                    <div id="ct-<?= $call_task_entity->ctid; ?>" class="expandable">
                      <table>
                        <?php foreach ($call_task_entity->activity as $call_task_activity_item):?>
                        <tr>
                          <td></td>
                          <td>
                            <p><strong><?= $call_task_activity_item->get_label(); ?></strong></p>
                            <p><em><?= $call_task_activity_item->message; ?></em></p>
                          </td>
                          <td><?= date('d M, Y \a\t H:i', $call_task_activity_item->created->sec) ?></td>
                          <td></td>
                        </tr>
                        <?php endforeach; ?>
                      </table>
                    </div>                    
                  </td>
                </tr>
              </tbody>
              <?php endforeach; ?>
            </table>
          </div>
          <?php endif; ?>
        </section>
      </div>
    </div>
    
  </section>
</main>