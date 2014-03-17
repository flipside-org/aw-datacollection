<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('xls2xform')) {
  /**
   * xls2xform
   * Converts xls form into xml using pyxform library.
   * 
   * @param string $source
   *   The source
   * @param string $destination
   * 
   * @return array
   *   Result of conversion.
   */
  function xls2xform($source, $destination) {
    $pyxform_lib = get_instance()->config->item('aw_pyxform_lib');
    
    $program = $pyxform_lib . 'pyxform/xls2xform.py';
    $result = shell_exec("python $program $source $destination --json");
    return json_decode($result);
  }
}

// ------------------------------------------------------------------------

/* End of file pyxform_helper.php */
/* Location: ./application/helpers/pyxform_helper.php */