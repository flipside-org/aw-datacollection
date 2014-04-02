<main id="site-body">
  <section class="row">
    <header id="page-head">
      <div class="inner">

        <div class="heading">
          <h1 class="hd-xl <?= $survey->get_status_html_class('indicator-'); ?>"><?= $survey->title ?></h1>
        </div>

        <nav id="secondary" role="navigation">
          <ul class="links">
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
    
    <h2>Confirm respondents</h2>
  
    <?php if (sizeof($respondents_numbers)) : ?>
      <ul>
      <?php foreach ($respondents_numbers as $respondent_number) : ?>
        <li><?= $respondent_number; ?></li>
      <?php endforeach; ?>
      </ul>
  
      <?= form_submit('survey_respondents_submit', 'Confirm respondents'); ?>
    <?php endif ?>
  
  
    <?= form_close(); ?>



    </div>
  </section>
</main>