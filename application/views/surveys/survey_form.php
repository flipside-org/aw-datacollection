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
          <ul class="bttn-toolbar">
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
              <a href="#" class="bttn bttn-success bttn-medium" data-trigger-submit="survey_submit">Save</a>
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
      
      <?= form_open_multipart(); ?>
      <div class="columns small-6">
        <fieldset class="contained">
          <div class="form-control">
            <?= form_label('Title <small>Required</small>', 'survey_title'); ?>
            <?= form_input('survey_title', set_value('survey_title', property_if_not_null($survey, 'title')), 'id="survey_title"'); ?>
            <?= form_error('survey_title'); ?>
          </div>
          
          <div class="form-control">
            <?= form_label('Client <small>Required</small>', 'survey_client'); ?>
            <?= form_input('survey_client', set_value('survey_client', property_if_not_null($survey, 'client')), 'id="survey_client"'); ?>
            <?= form_error('survey_client'); ?>
          </div>
          
          <div class="form-control">
            <?= form_label('Goal', 'survey_goal'); ?>
            <?= form_input('survey_goal', set_value('survey_goal', property_if_not_null($survey, 'goal')), 'id="survey_goal"'); ?>
            <p class="help-text">Minimum amount of respondents for the survey to be considered done.</p>
            <?= form_error('survey_goal'); ?>
          </div>
          
          <div class="form-control">
            <?= form_label('Description', 'survey_description'); ?>
            <?= form_textarea('survey_description', set_value('survey_description', property_if_not_null($survey, 'description')), 'id="survey_description"'); ?>
            <?= form_error('survey_description'); ?>
          </div>
        </fieldset>
      </div>
      
      <div class="columns small-6">
        <fieldset class="contained">
          <div class="form-control">
            <?= form_label('Definition file', 'survey_file'); ?>
            <?= form_upload('survey_file', '', 'id="survey_file"'); ?>
            <?= form_error('survey_file'); ?>
          </div>
        </fieldset>
        <fieldset class="contained">
          <div class="form-control">
            <?= form_label('Introductory text', 'survey_introduction'); ?>
            <?= form_textarea('survey_introduction', set_value('survey_introduction', property_if_not_null($survey, 'introduction')), 'id="survey_introduction"'); ?>
            <?= form_error('survey_introduction'); ?>
          </div>
        </fieldset>
      </div>
      
      <?= form_button(array(
        'type' => 'submit',
        'name' => 'survey_submit',
        'id' => 'survey_submit',
        'value' => 'survey_submit',
        'class' => 'hide',
        'content' => 'Submit Survey'));
      ?>
      
      <?= form_close(); ?>

    </div>
  </section>
</main>