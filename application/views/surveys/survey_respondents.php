<main id="site-body">
  <section class="row">
    <header id="page-head">
        
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
      
    </header>
    
    
    
    
    <table>
      <tr>
        <td>Number</td>
        <td>Actions</td>
      </tr>
      <?php foreach ($respondents as $resp) : ?>
        <tr>
          <td><?= $resp->number; ?></td>
          <td></td>
        </tr>
      <?php endforeach; ?>
    </table>
    
    <?= $this->pagination->create_links(); ?>
    
    
    
    

  </section>
</main>