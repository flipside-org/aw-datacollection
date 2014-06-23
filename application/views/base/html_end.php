    </div>
  <?php if (ENVIRONMENT == 'demo') : ?>
  <p class="ribbon">
    <a title="Airwolf on Github" href="https://github.com/flipside-org/aw-datacollection/">View on GitHub</a>
  </p>
  <?php endif; ?>
  <?php $this->load->view('components/confirm_box') ?>
  <?php $this->load->view('base/footer_scripts') ?>
  </body>
</html>
