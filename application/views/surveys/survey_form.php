<main id="site-body">
  <section class="row">
    <header id="page-head">
      <div class="inner">
        
        <div class="heading">
          <?php if ($survey) : ?>
          <h1 class="hd-xl <?= $survey->get_status_html_class('indicator-'); ?>"><?= $survey->title ?></h1>
          <?php else : ?>
          <h1 class="hd-xl indicator-status-draft">Surveys</h1>
          <?php endif; ?>
        </div>
        
        <nav id="secondary" role="navigation">
          <ul class="links">
            <li class="sector-switcher">
              <strong class="bttn-sector">
              <?php if ($survey) : ?>
                Edit
              <?php else : ?>
                New
              <?php endif; ?>
              </strong>
            </li>
            <li>
              <?php if ($survey) : ?>
              <a href="<?= $survey->get_url_view(); ?>" class="bttn bttn-default bttn-medium">Cancel</a>
              <?php else : ?>
              <a href="<?= base_url('surveys'); ?>" class="bttn bttn-default bttn-medium">Cancel</a>
              <?php endif; ?>
            </li>
          </ul>
        </nav>
        
      </div>
    </header>
    
    <div class="content">
    
    <?= validation_errors(); ?>
    <?= form_open_multipart(); ?>
    
      <?= form_label('Survey Title', 'survey_title'); ?>
      <?= form_input('survey_title', set_value('survey_title', property_if_not_null($survey, 'title'))); ?>
      
      <?= form_label('Status', 'survey_status'); ?>
      <?= form_dropdown('survey_status',
            Survey_entity::$statuses,
            set_value('survey_status', property_if_not_null($survey, 'status', array()))); ?>
            
      <?= form_label('Survey Introduction', 'survey_introduction'); ?>
      <?= form_textarea('survey_introduction', set_value('survey_introduction', property_if_not_null($survey, 'introduction'))); ?>
            
      <?= form_upload('survey_file'); ?>
      
      <?= form_submit('survey_submit', 'Submit Survey'); ?>
    
    <?= form_close(); ?>


    </div>
  </section>
</main>