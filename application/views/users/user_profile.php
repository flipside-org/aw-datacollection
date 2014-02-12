<div class="row">
  
  <h1><?= $user->name ?></h1>
  
  <a href="<?= base_url('user/' . $user->uid . '/edit'); ?>">Edit profile</a>
  
  <?php krumo($user); ?>

</div>