<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Email extends CI_Email {
  
  protected $log_emails = FALSE;
  protected $block_emails = FALSE;
  
  /**
   * Spool mail to the mail server
   *
   * @access  protected
   * @return  bool
   */
  protected function _spool_email() {
    $this->_unwrap_specials();
    
    if (!$this->block_emails) {

      switch ($this->_get_protocol())
      {
        case 'mail' :
  
            if ( ! $this->_send_with_mail())
            {
              $this->_set_error_message('lang:email_send_failure_phpmail');
              return FALSE;
            }
        break;
        case 'sendmail' :
  
            if ( ! $this->_send_with_sendmail())
            {
              $this->_set_error_message('lang:email_send_failure_sendmail');
              return FALSE;
            }
        break;
        case 'smtp' :
  
            if ( ! $this->_send_with_smtp())
            {
              $this->_set_error_message('lang:email_send_failure_smtp');
              return FALSE;
            }
        break;
  
      }
    }

    $this->_set_error_message('lang:email_sent', $this->_get_protocol());
    return TRUE;
  }
  
  /**
   * Send Email
   *
   * @access  public
   * @return  bool
   */
  public function send() {
    $result = parent::send();
    
    if ($result && $this->log_emails) {
      $this->_log_email_to_file();
    }
    return $result;
  }
  
  protected function _log_email_to_file() {
    if ( !is_dir(APPPATH.'logs/') || !is_really_writable(APPPATH.'logs/')) {
      return FALSE;
    }
    
    $filepath = APPPATH.'logs/mail-log-' . date('Y-m-d') . '.php';
    $log_data = '';
    $separator = "\n" . str_repeat('=', 50) . "\n";
    if ( !file_exists($filepath)) {
      $log_data .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
    }
    
    if (count($this->_debug_msg) > 0) {
      foreach ($this->_debug_msg as $val) {
        $log_data .= $val;
      }
    }   
     
    $log_data .= $this->_header_str . "\n" . $this->_subject . "\n" . $this->_finalbody;       
        
    $log_data .= $separator;
    file_put_contents($filepath, $log_data, FILE_APPEND);
  }
}

// ------------------------------------------------------------------------

/* End of file MY_Email.php */
/* Location: ./application/libraries/MY_Email.php */