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
              <?php if ($survey) : ?>
              <a href="<?= $survey->get_url_view(); ?>" class="bttn bttn-default bttn-medium bttn-icon-cancel">Cancel</a>
              <?php else : ?>
              <a href="<?= base_url('surveys'); ?>" class="bttn bttn-default bttn-medium bttn-icon-cancel">Cancel</a>
              <?php endif; ?>
            </li>
            <li>
              <a href="#" class="bttn bttn-success bttn-medium bttn-icon-save" data-trigger-submit="survey_submit">Save</a>
            </li>
          </ul>
        </nav>
        
      </div>
    </header>
    
    <div class="content">
      
      <?= form_open_multipart(); ?>
      <?php
        $disabled_metadata = $survey != NULL && !$survey->status_allows('edit any survey metadata') ? ' disabled' : ''; ;
      ?>
      <div class="columns small-6">
        <fieldset class="contained">
          <div class="form-control">
            <?= form_label('Title <small>Required</small>', 'survey_title'); ?>
            <?= form_input('survey_title', set_value('survey_title', property_if_not_null($survey, 'title')), 'id="survey_title"' . $disabled_metadata); ?>
            <p class="help-text">A descriptive title for this survey.</p>
            <?= form_error('survey_title'); ?>
          </div>
          
          <div class="form-control">
            <?= form_label('Client <small>Required</small>', 'survey_client'); ?>
            <?= form_input('survey_client', set_value('survey_client', property_if_not_null($survey, 'client')), 'id="survey_client"' . $disabled_metadata); ?>
            <?= form_error('survey_client'); ?>
          </div>
          
          <div class="form-control">
            <?= form_label('Goal', 'survey_goal'); ?>
            <?= form_input('survey_goal', set_value('survey_goal', property_if_not_null($survey, 'goal')), 'id="survey_goal"' . $disabled_metadata); ?>
            <p class="help-text">Minimum amount of respondents for the survey to be considered done.</p>
            <?= form_error('survey_goal'); ?>
          </div>
          
          <div class="form-control">
            <?= form_label('Description', 'survey_description'); ?>
            <?= form_textarea('survey_description', set_value('survey_description', property_if_not_null($survey, 'description')), 'id="survey_description"' . $disabled_metadata); ?>
            <p class="help-text">A brief introduction that will help other users understand this survey's purpose.</p>
            <?= form_error('survey_description'); ?>
          </div>
          
          <div class="form-control">
            <label class="inline-label">
            <?= form_checkbox(array(
              'name' => 'survey_anonymize',
              'value' => 'anonymize',
              'checked' => set_checkbox('survey_anonymize', 'anonymize', property_if_not_null($survey, 'anonymize'))
            )); ?> Anonymize survey results.
            </label>
            <p class="help-text">If checked the survey results will be anonymized when the data is exported.</p>
          </div>
          
        </fieldset>
      </div>
      
      <div class="columns small-6">
        <fieldset class="contained">
          <div class="form-control">
            <?php
              $disabled_def_file = $survey != NULL && !$survey->status_allows('edit any survey def file') ? ' disabled' : ''; ;
            ?>
            <?= form_label('Definition file', 'survey_file'); ?>
            <?= form_upload('survey_file', '', 'id="survey_file"' . $disabled_def_file); ?>
            <?= form_error('survey_file'); ?>
          </div>
        </fieldset>
        <fieldset class="contained">
          <div class="form-control">
            <?= form_label('Survey introduction', 'survey_introduction'); ?>
            <?= form_textarea('survey_introduction', set_value('survey_introduction', property_if_not_null($survey, 'introduction')), 'id="survey_introduction"' . $disabled_metadata); ?>
            <p class="help-text">This introduction will be read by the agents to the respondents before proceeding with the actual survey questions.</p>
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