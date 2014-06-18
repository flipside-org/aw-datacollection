<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['default']['dsn'] = 'mongodb://localhost:27017/aw_datacollection';
$config['default']['persist']  = TRUE;
$config['default']['persist_key']	 = 'ci_persist';
$config['default']['replica_set']  = FALSE;
$config['default']['query_safety'] = 'w';
