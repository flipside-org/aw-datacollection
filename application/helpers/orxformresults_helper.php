<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ORXFormResults
 */
class ORXFormResults {
  
  private $preferred_language = 'English';
  
  
  protected $xform = NULL;
  protected $languages = array();
  
  /**
   * The xform after being flatten.
   * Will be an array keyed with the question's path.
   * EG:
   * /survey_7_xls/q0_3
   *   machine_name   - The question's machine name.
   *   system         - Whether is a system question. Like start_date
   *   type           - Type of the question. Like int, select1
   *   label          - The label. Will be an array if there are multiple languages
   *     lang         - Key is the language, value is the translation.
   *   items          - If is a select question, an array with the items
   *     key          - Item key. Will be an array if there are multiple languages
   *      lang
   */
  protected $flat_xform = array();
  
  function __construct($xform_file) {
    // Load the survey definition file.
    $this->xform = simplexml_load_file($xform_file);
    // Namespaces are needed in order to do queries.
    // Returns all the namespaces used by the elements
    $ns = $this->xform->getNamespaces(TRUE);
    
    // When querying we need to provide the namespace (Ex: ns:tag).
    // The default namespace has no prefix and that's a problem because
    // we can't use it to query. This is solved by registering the default
    // namespace giving it a prefix.
    $this->xform->registerXPathNamespace('d', $ns['']);
    
    // Extract all the languages available in the file.
    $translation_nodes = $this->xform->xpath('//d:itext/d:translation');
    foreach ($translation_nodes as $node) {
      $this->languages[] = (string)$node['lang'];
    }
    
    // Get all questions by querying the bind tag.
    // The questions always have a @type attribute and are not @readonly.
    $questions = $this->xform->xpath('//d:model/d:bind[not(@readonly) and @type]');
    // Create a list with all the questions keyed by the path
    // storing the label and, if they exist, the options.
    // Note: All the values need to be converted to string, otherwise
    // they are stores as xml object.
    $list = array();
    foreach($questions as $k => $v){
      $path = (string)$v['nodeset'];
      $type = (string)$v['type'];
      
      $path_pieces = explode('/', $path);
      $machine_name = end($path_pieces);
      
      // Store the question's machine name.
     $list[$path]['machine_name'] = $machine_name;
      // Store the question's type
     $list[$path]['type'] = $type;
     
     // Search the question to get the label and value.
     // It will return an array of results.
      $question = $this->xform->xpath(sprintf('h:body//*[@ref="%s"]', $path));
      if (!$question) {
        // The question was not found. Probably a system value 
        // like the start date. Set the label as the machine name.
        $list[$path]['label'] = $machine_name;
        $list[$path]['system'] = TRUE;
      }
      else {
        // Only need the first result.
        $question = $question[0];
        $list[$path]['label'] = $this->_label_translation($question->label);
        
        // Search for items. (EG: Options for checkboxes)
        if (isset($question->item)) {
          foreach($question->item as $item) {            
            $list[$path]['items'][(string)$item->value] = $this->_label_translation($item->label);
          }
        }
      }  
    }
    $this->flat_xform = $list;
  }

  public function set_language($language) {
    if (in_array($language, $this->languages)) {
      $this->preferred_language = $language;
    }
  }

  public function parse_result_file($result_file_path) {
    // Load the result file.
    $result_file_sxe = simplexml_load_file($result_file_path);
    return $this->_normalize_result_item($result_file_sxe);
  }

  protected function _normalize_result_item($item, $path = NULL) {
    // Normalised data.
    $normalised = array();
    
    // Initialise current path for the first time.
    // The current path is need when drilling down.
    // it is used to match the questions.
    if ($path === NULL) {
      $path = '/' . $item->getName();
    }
    
    foreach($item as $key => $item_data) {
      // Update current_path
      $current_path = $path . '/' . (string)$key;
      
      if ($item_data->count()) {
        // The node has children. Start recursion.
        $normalised = array_merge($normalised, $this->_normalize_result_item($item_data, $current_path));
      }
      else {
        // No children.        
        // Does the question exist?
        if (isset($this->flat_xform[$current_path])) {
          $question = $this->flat_xform[$current_path];
          
          // Store results for this question.
          $norm = array();
          // Store label
          $norm['label'] = $this->_get_question_label_translation($question);
          $norm['machine_label'] = $question['machine_name'];
          
          // Check if question was answered.
          if ($item_data) {
            
            // Check if is a question with items.
            if (isset($question['items'])) {
              $answer_key = trim((string)$item_data);
              
              // Check if the key exists in the items array.
              if (isset($question['items'][$answer_key])) {
                // Translation.
                $norm['value'] = $this->_get_question_item_translation($question, $answer_key);
                $norm['machine_value'] = $answer_key;
              }
              else {
                // The value was not found in the item array.
                // This may mean that its a multiple answer question.
                // Split by space and search each term.
                $answer_pieces = explode(' ', $answer_key);
                $norm['value'] = array();
                foreach ($answer_pieces as $value) {
                  if (isset($question['items'][$value])) {
                    $norm['value'][] = $this->_get_question_item_translation($question, $value);
                    $norm['machine_value'] = $value;
                  }
                }
              }
              
            }
            else {
              // It's an open ended question.
              // Store value.
              $norm['value'] = (string)$item_data;
              $norm['machine_value'] = (string)$item_data;
            }
          }
          else {
            // The question was not answered
            $norm['value'] = NULL;
            $norm['machine_value'] = NULL;
          }
          
          $normalised[] = $norm;
        }
        // else skip
      }
    }
    // Return results.
    return $normalised;
  }

  protected function _get_question_label_translation($question) {
    return ($this->is_translated() && !$question['system']) ? $question['label'][$this->preferred_language] : $question['label'];
  }

  protected function _get_question_item_translation($question, $item_key) {
    return $this->is_translated() ? $question['items'][$item_key][$this->preferred_language] : $question['items'][$item_key];
  }

  /**
   * Checks if the xform is multi-language.
   * @return boolean
   *   Whether there are multiple languages.
   */
  public function is_translated() {
    return count($this->languages) > 0;
  }
  
  /**
   * Gets the translation for the given label.
   * It assumes that the label has a @ref attribute with the translation path.
   * If the file has no translations the label value is returned.
   * @param SimpleXMLElement
   *   The label element.
   * @return mixed
   *  Array keyed by language with the translated value if there
   *  are translations, the label value otherwise.
   */
  protected function _label_translation($label) {
    if (!$this->is_translated()) {
      return (string)$label;
    }
    
    // The @ref attribute will look like:
    // jr:itext('/survey_7_xls/section1/q11_other:label')
    $ref = (string)$label['ref'];
    
    // Extract path: /survey_7_xls/section1/q11_other:label
    if (!preg_match("/jr:itext\\(\'(.*)\'\\)/", $ref, $matches)) {
      return NULL;
    }
    // Translation path will be the first captured result.
    $translation_path = $matches[1];
    
    $translations = array();
    foreach ($this->languages as $lang) {
      $translation_value = $this->xform->xpath(sprintf('//d:itext/d:translation[@lang="%s"]//*[@id="%s"]/d:value', $lang, $translation_path));
      $translations[$lang] = empty($translation_value) ? NULL : (string)$translation_value[0];
    }
    
    return $translations;
  }
  
  /**
   * Returns the xform after being flatten.
   * @return array
   */
  public function get_result() {
    return $this->flat_xform;
  }
}

// ------------------------------------------------------------------------

/* End of file ORXFormResults_helper.php */
/* Location: ./application/helpers/ORXFormResults_helper.php */
