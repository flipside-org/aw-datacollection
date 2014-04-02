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
              <a class="bttn-sector bttn-dropdown" href="" data-dropdown="action-bttn"><strong>Respondents</strong></a>
              <ul class="action-dropdown">
                <li><a href="<?= $survey->get_url_view() ?>">Summary</a></li>
              </ul>
            </li>

            <?php if (has_permission('manage respondents any survey')) : ?>
            <li>
              <a href="<?= $survey->get_url_respondents_add(); ?>" class="bttn bttn-primary bttn-medium">Add new</a>
            </li>
            <?php endif; ?>

          </ul>
        </nav>

      </div>
    </header>

    <div class="content">



    <?= validation_errors(); ?>
    <?= form_open_multipart(); ?>
    
    <h2>Add respondents</h2>
  
    <?= form_label('Respondents Text', 'survey_respondents_text'); ?>
    <?= form_textarea('survey_respondents_text', set_value('survey_respondents_text')); ?>
  
    <?= form_upload('survey_respondents_file'); ?>
  
    <?= form_submit('survey_respondents_submit', 'Add respondents'); ?>
  
  <?= form_close(); ?>



    </div>
  </section>
</main>