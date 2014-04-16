<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email messages
|--------------------------------------------------------------------------
|
| The emails sent to the users should be added in this file.
| 
|
|
|
*/

// Available placeholders:
// {{username}}
// {{password}}
// {{name}}
$config['message_account_created'] = array(
  'subject' => 'Airwolf - Account created',
  'message' => "
    Hello {{name}},
    
    An account has been created for you with the following details:
    Username: {{username}}
    Password: {{password}}
  "
);

// Available placeholders:
// {{username}}
// {{name}}
// {{reset_link}}
$config['message_pwd_recover'] = array(
  'subject' => 'Airwolf - Password recover',
  'message' => "
    Hello {{name}},
    
    A password reset has been requested for this account. Use the following link to choose a new password:
    {{reset_link}}
  "
);
