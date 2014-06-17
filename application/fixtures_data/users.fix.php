<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File users.fix.php
 * Fixtures for all users.
 */

  ////////////////////////////////////////////////
  // User 1
  $user = new User_entity(array(
    // uid will be 1 (auto increment)
    'email' => 'admin@localhost.dev',
    'name' => 'Admin',
    'username' => 'admin',
    'author' => 0,
  ));
  $user->set_password(hash_password('admin'))
    ->set_status(User_entity::STATUS_ACTIVE)
    ->set_roles(array(ROLE_ADMINISTRATOR));
    
  $this->user_model->save($user);

  ////////////////////////////////////////////////
  // User 2
  $user = new User_entity(array(
    // uid will be 2 (auto increment)
    'email' => 'regular@localhost.dev',
    'name' => 'Regular user',
    'username' => 'regular',
    'author' => 1,
  ));
  $user->set_password(hash_password('regular'))
    ->set_status(User_entity::STATUS_ACTIVE);
    
  $this->user_model->save($user);
  
  ////////////////////////////////////////////////
  // User 3
  $user = new User_entity(array(
    // uid will be 3 (auto increment)
    'email' => 'agent@localhost.dev',
    'name' => 'The Agent',
    'username' => 'agent',
    'author' => 1,
  ));
  
  $user->set_password(hash_password('agent'))
    ->set_status(User_entity::STATUS_ACTIVE)
    ->set_roles(array(ROLE_CC_AGENT));
    
  $this->user_model->save($user);
  
  ////////////////////////////////////////////////
  // User 4
  $user = new User_entity(array(
    // uid will be 4 (auto increment)
    'email' => 'moderator@localhost.dev',
    'name' => 'The Moderator',
    'username' => 'moderator',
    'author' => 1,
  ));
  
  $user->set_password(hash_password('moderator'))
    ->set_status(User_entity::STATUS_ACTIVE)
    ->set_roles(array(ROLE_MODERATOR));
    
  $this->user_model->save($user);
  
  ////////////////////////////////////////////////
  // User 5
  $user = new User_entity(array(
    // uid will be 5 (auto increment)
    'email' => 'blocked@localhost.dev',
    'name' => 'The Blocked Agent',
    'username' => 'blocked',
    'author' => 1,
  ));
  
  $user->set_password(hash_password('blocked'))
    ->set_status(User_entity::STATUS_BLOCKED)
    ->set_roles(array(ROLE_CC_AGENT));
    
  $this->user_model->save($user);
  
  ////////////////////////////////////////////////
  // User 6
  $user = new User_entity(array(
    // uid will be 6 (auto increment)
    'email' => 'deleted@localhost.dev',
    'name' => 'The Deleted',
    'username' => 'deleted',
    'author' => 1,
  ));
  
  $user->set_password(hash_password('deleted'))
    ->set_status(User_entity::STATUS_DELETED);
    
  $this->user_model->save($user);
  
  ////////////////////////////////////////////////
  // User 7
  $user = new User_entity(array(
    // uid will be 7 (auto increment)
    'email' => 'all_roles@localhost.dev',
    'name' => 'The All Roles',
    'username' => 'all_roles',
    'author' => 1,
  ));
  
  $user->set_password(hash_password('all_roles'))
    ->set_status(User_entity::STATUS_ACTIVE)
    ->set_roles(array(ROLE_ADMINISTRATOR, ROLE_MODERATOR, ROLE_CC_AGENT));
    
  $this->user_model->save($user);