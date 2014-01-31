<?php if ($messages) : ?>
  <div class="messages">
    <?php foreach($messages as $level => $messages_level): ?>
      <?php if (empty($messages_level)) continue; ?>

      <div class="alert-box <?= $level ?>">
        <ul>
          <?php foreach($messages_level as $msg): ?>
            <li><?= $msg ?></li>
          <?php endforeach; ?>
        </ul>
      </div>        
      
    <?php endforeach; ?>
  </div>
<?php endif; ?>