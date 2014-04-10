<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extend of built in pagination to add custom methods.
 * 
 * @see CI_Pagination
 */
class MY_Pagination extends CI_Pagination {

  /**
   * Returns the information about the items being viewed
   * in the format x-y of total
   * 
   * @return string
   *  x-y of total
   */
  public function get_page_info() {
    // Set the base page index for starting page number
    if ($this->use_page_numbers && $this->cur_page != 0) {
      $beginning = ($this->cur_page - 1) * $this->per_page + 1;
    }
    else {
      $beginning = $this->cur_page * $this->per_page + 1;
    }
    
    $end = $beginning + $this->per_page - 1;
    
    if ($end > $this->total_rows) {
      $end = $this->total_rows;
    }
    
    if ($end == 0) {
      $beginning = 0;
    }
    
    return $beginning . '-' . $end . ' of ' . $this->total_rows;
  }
}

// ------------------------------------------------------------------------

/* End of file MY_Pagination.php */
/* Location: ./application/libraries/MY_Pagination.php */