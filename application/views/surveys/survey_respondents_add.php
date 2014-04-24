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
              <strong class="bttn-sector">Add Respondents</strong>
            </li>
            <li>
              <a href="#" class="bttn bttn-success bttn-medium" data-trigger-submit="respondents_add_submit">Import</a>
            </li>
            <li>
              <a href="<?= $survey->get_url_respondents(); ?>" class="bttn bttn-default bttn-medium">Cancel</a>
            </li>
          </ul>
        </nav>

      </div>
    </header>

    <div class="content">
      <?= form_open_multipart(); ?>
      <div class="columns small-12">
        <fieldset class="contained">
          <div class="form-control">
          <?php if ($import_type == 'direct') : ?>
            <?= form_label('Direct import', 'survey_respondents_text'); ?>
            <?= form_textarea('survey_respondents_text', implode("\n", $invalid_respondents), 'id="survey_respondents_text"'); ?>
            <p class="help-text">Insert on number per line</p>
            <?= form_error('survey_respondents_text'); ?>
            
          <?php else: ?>
            <?= form_label('File import', 'survey_respondents_file'); ?>
            <?= form_upload('survey_respondents_file', '', 'id="survey_respondents_file"'); ?>
            <?= form_error('survey_respondents_file'); ?>
            
          <?php endif; ?>
          </div>
        </fieldset>
      </div>
      
      <?= form_button(array(
        'type' => 'submit',
        'name' => 'respondents_add_submit',
        'id' => 'respondents_add_submit',
        'value' => 'respondents_add_submit',
        'class' => 'hide',
        'content' => 'Import respondents'));
      ?>
      
      <?= form_close(); ?>  
  
    </div>
  </section>
</main>