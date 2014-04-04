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
              <a class="bttn-sector bttn-dropdown" href="#" data-dropdown="action-bttn"><strong>Respondents</strong></a>
              <ul class="action-dropdown">
                <li><a href="<?= $survey->get_url_view() ?>">Summary</a></li>
              </ul>
            </li>

            <?php if (has_permission('manage respondents any survey')) : ?>
            <li>
              <a href="#" class="bttn bttn-primary bttn-medium bttn-dropdown"  data-dropdown="action-bttn">Add new</a>
              <ul class="action-dropdown">
                <li><a href="<?= $survey->get_url_respondents_add('file'); ?>">Upload file</a></li>
                <li><a href="<?= $survey->get_url_respondents_add('direct'); ?>">Direct input</a></li>
              </ul>
            </li>
            <?php endif; ?>

          </ul>
        </nav>

      </div>
    </header>
    <div class="content">

    <table class="cb-group">
      <tr>
        <td>
          <label class="label-check cb-master-label" for="respondents-check-all">
            <input name="respondents-check-all" value="1" type="checkbox" class="cb-master"/>
          </label>
        </td>
        <td>Number</td>
        <td>Actions</td>
      </tr>
      <?php foreach ($respondents as $resp) : ?>
        <tr>
          <td>

            <label class="label-check cb-slave-label" for="respondents-check">
              <input name="respondents-check[]" value="<?= $resp->ctid; ?>" type="checkbox" class="cb-slave"/>
            </label>

          </td>
          <td><?= $resp->number; ?></td>
          <td></td>
        </tr>
      <?php endforeach; ?>
    </table>
    
    <?= $this->pagination->create_links(); ?>



    </div>
  </section>
</main>