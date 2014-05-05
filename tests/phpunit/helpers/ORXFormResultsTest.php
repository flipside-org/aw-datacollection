<?php

class or_xform_results_helper_test extends PHPUnit_Framework_TestCase {

  private static $CI;
  
  const FILE_PATH = 'tests/test_resources/surveys_and_answers/';

  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    
    self::$CI->load->helper('or_xform_results');
  }
  
  public function test_flat_xform_survey_no_lang() {
    
    $expected_xform_flat = array(
      '/survey_no_lang/user_name' => array(
        'machine_name' => "user_name",
        'type' => "string",
        'label' => "What's your name?",
      ),

      '/survey_no_lang/a_integer' => array(
        'machine_name' => "a_integer",
        'type' => "int",
        'label' => "Pick an integer:",
      ),

      '/survey_no_lang/a_decimal' => array(
        'machine_name' => "a_decimal",
        'type' => "decimal",
        'label' => "Pick a decimal:",
      ),

      '/survey_no_lang/calculate' => array(
        'machine_name' => "calculate",
        'type' => "string",
        'label' => "calculate",
        'system' => "1",
       ),

      '/survey_no_lang/required_text' => array(
        'machine_name' => "required_text",
        'type' => "string",
        'label' => "Required question",
       ),

      '/survey_no_lang/skip_example' => array(
        'machine_name' => "skip_example",
        'type' => "select1",
        'label' => "Skip the next question?",
        'items' => array(
          'yes' => "Yes",
          'no' => "No",
        ),
      ),

      '/survey_no_lang/skipable_question' => array(
        'machine_name' => "skipable_question",
        'type' => "string",
        'label' => "Then enter something.",
       ),

      '/survey_no_lang/repeat_test/repeating_question' => array(
        'machine_name' => "repeating_question",
        'type' => "string",
        'label' => "This is a repeating question.",
       ),

      '/survey_no_lang/group_test/select_multiple' => array(
        'machine_name' => "select_multiple",
        'type' => "select",
        'label' => "Select multiple",
        'items' => array(
          'yes' => "Yes",
          'no' => "No",
         ),
      ),

      '/survey_no_lang/group_test/group_within_note/select_multiple_within' => array(
        'machine_name' => "select_multiple_within",
        'type' => "select",
        'label' => "Select multiple within a group",
        'items' => array(
          'banana' => "Banana",
          'pear' => "Pear",
          'kiwi' => "Kiwi",
          'apple' => "Apple",
          'passion_fruit' => "Passion Fruit",
          'tomato' => "Tomato",
         ),
       ),
    );
    $survey_no_lang = new OR_xform_results(or_xform_results_helper_test::FILE_PATH . 'survey_no_lang/survey_no_lang.xml');
    $this->assertEquals($expected_xform_flat, $survey_no_lang->get_flatten());
  }
  
  public function test_parsing_survey_no_lang() {
    $survey_no_lang = new OR_xform_results(or_xform_results_helper_test::FILE_PATH . 'survey_no_lang/survey_no_lang.xml');
    
    $expected_result1 = array(
      array (
        'label' => "What's your name?",
        'machine_label' => "user_name",
        'value' => "Anonymous",
        'machine_value' => "Anonymous",
      ),
  
      array (
        'label' => "Pick an integer:",
        'machine_label' => "a_integer",
        'value' => "10",
        'machine_value' => "10",
      ),
  
      array (
        'label' => "Pick a decimal:",
        'machine_label' => "a_decimal",
        'value' => "1.5",
        'machine_value' => "1.5",
      ),
  
      array (
        'label' => "calculate",
        'machine_label' => "calculate",
        'value' => "11.5",
        'machine_value' => "11.5",
      ),
  
      array (
        'label' => "Required question",
        'machine_label' => "required_text",
        'value' => "This is required",
        'machine_value' => "This is required",
      ),
  
      array (
        'label' => "Skip the next question?",
        'machine_label' => "skip_example",
        'value' => "No",
        'machine_value' => "no",
      ),
  
      array (
        'label' => "Then enter something.",
        'machine_label' => "skipable_question",
        'value' => "Something",
        'machine_value' => "Something",
      ),
  
      array (
        'label' => "This is a repeating question.",
        'machine_label' => "repeating_question",
        'value' => "Repeat 1",
        'machine_value' => "Repeat 1",
      ),
  
      array (
        'label' => "This is a repeating question.",
        'machine_label' => "repeating_question",
        'value' => "Repeat 2",
        'machine_value' => "Repeat 2",
      ),
  
      array (
        'label' => "Select multiple",
        'machine_label' => "select_multiple",
        'value' => "No",
        'machine_value' => "no",
      ),
  
      array (
        'label' => "Select multiple within a group",
        'machine_label' => "select_multiple_within",
        "value" => array (
          "Banana",
          "Pear",
          "Kiwi",
          "Apple",
        ),
        'machine_value' => array (
          "banana",
          "pear",
          "kiwi",
          "apple",
        ),
      )
    
    );
    
    $result1 = $survey_no_lang->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_no_lang/survey_no_lang_1.xml');
    $this->assertEquals($expected_result1, $result1);
    
    $expected_result2 = array(
      array (
        'label' => "What's your name?",
        'machine_label' => "user_name",
        'value' => "John Doe",
        'machine_value' => "John Doe",
      ),
  
      array (
        'label' => "Pick an integer:",
        'machine_label' => "a_integer",
        'value' => "1",
        'machine_value' => "1",
      ),
  
      array (
        'label' => "Pick a decimal:",
        'machine_label' => "a_decimal",
        'value' => "0.555",
        'machine_value' => "0.555",
      ),
  
      array (
        'label' => "calculate",
        'machine_label' => "calculate",
        'value' => "1.5550000000000002",
        'machine_value' => "1.5550000000000002",
      ),
  
      array (
        'label' => "Required question",
        'machine_label' => "required_text",
        'value' => "Required",
        'machine_value' => "Required",
      ),
  
      array (
        'label' => "Skip the next question?",
        'machine_label' => "skip_example",
        'value' => "Yes",
        'machine_value' => "yes",
      ),
  
      array (
        'label' => "Then enter something.",
        'machine_label' => "skipable_question",
        'value' => "",
        'machine_value' => "",
      ),
  
      array (
        'label' => "This is a repeating question.",
        'machine_label' => "repeating_question",
        'value' => "",
        'machine_value' => "",
      ),
  
      array (
        'label' => "Select multiple",
        'machine_label' => "select_multiple",
        'value' => array(
          'Yes',
          'No'
        ),
        'machine_value' => array(
          'yes',
          'no'
        ),
      ),
  
      array (
        'label' => "Select multiple within a group",
        'machine_label' => "select_multiple_within",
        "value" => array (
          "Banana",
          "Pear",
          "Kiwi",
          "Apple",
          "Passion Fruit",
          "Tomato",
        ),
        'machine_value' => array (
          "banana",
          "pear",
          "kiwi",
          "apple",
          "passion_fruit",
          "tomato",
        ),
      )
    
    );
    
    $result2 = $survey_no_lang->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_no_lang/survey_no_lang_2.xml');
    $this->assertEquals($expected_result2, $result2);
  }
  
  public function test_flat_xform_survey_en_pt() {
    
    $expected_xform_flat = array(
      '/survey_en_pt/user_name' => array(
        'machine_name' => "user_name",
        'type' => "string",
        'label' => array(
          'Portugues' => "Qual é o teu nome?",
          'English' => "What's your name?"
        )
      ),

      '/survey_en_pt/a_integer' => array(
        'machine_name' => "a_integer",
        'type' => "int",
        'label' => array(
          'Portugues' => "Escolhe um número inteiro:",
          'English' => "Pick an integer:",
        )
      ),

      '/survey_en_pt/a_decimal' => array(
        'machine_name' => "a_decimal",
        'type' => "decimal",
        'label' => array(
          'Portugues' => "Escolhe um número decimal:",
          'English' => "Pick a decimal:"
        )
      ),

      '/survey_en_pt/calculate' => array(
        'machine_name' => "calculate",
        'type' => "string",
        'label' => "calculate",
        'system' => "1",
       ),

      '/survey_en_pt/required_text' => array(
        'machine_name' => "required_text",
        'type' => "string",
        'label' => array(
          'Portugues' => "Questão obrigatória:",
          'English' => "Required question"
        )
       ),

      '/survey_en_pt/skip_example' => array(
        'machine_name' => "skip_example",
        'type' => "select1",
        'label' => array(
          'Portugues' => "Saltar a próxima questão?",
          'English' => "Skip the next question?"
        ),
        'items' => array(
          'yes' => array(
            'Portugues' => "Sim",
            'English' => "Yes"
          ),
          'no' => array(
            'Portugues' => "Não",
            'English' => "No"
          )
        )
      ),

      '/survey_en_pt/skipable_question' => array(
        'machine_name' => "skipable_question",
        'type' => "string",
        'label' => array(
          'Portugues' => "Então escreve alguma coisa.",
          'English' => "Then enter something."
        )
       ),

      '/survey_en_pt/repeat_test/repeating_question' => array(
        'machine_name' => "repeating_question",
        'type' => "string",
        'label' => array(
          'Portugues' => "Esta é uma questão repetível.",
          'English' => "This is a repeating question."
        )
       ),

      '/survey_en_pt/group_test/select_multiple' => array(
        'machine_name' => "select_multiple",
        'type' => "select",
        'label' => array(
          'Portugues' => "Escolha múltipla",
          'English' => "Select multiple"
        ),
        'items' => array(
          'yes' => array(
            'Portugues' => "Sim",
            'English' => "Yes"
          ),
          'no' => array(
            'Portugues' => "Não",
            'English' => "No"
          )
        )
      ),

      '/survey_en_pt/group_test/group_within_note/select_multiple_within' => array(
        'machine_name' => "select_multiple_within",
        'type' => "select",
        'label' => array(
          'Portugues' => "Escolha múltipla dentro de um grupo.",
          'English' => "Select multiple within a group."
        ),
        'items' => array(
          'banana' => array(
            'Portugues' => "Banana",
            'English' => "Banana"
          ),
          'pear' => array(
            'Portugues' => "Pera",
            'English' => "Pear"
          ),
          'kiwi' => array(
            'Portugues' => "Kiwi",
            'English' => "Kiwi"
          ),
          'apple' => array(
            'Portugues' => "Maçã",
            'English' => "Apple"
          ),
          'passion_fruit' => array(
            'Portugues' => "Maracujá",
            'English' => "Passion Fruit"
          ),
          'tomato' => array(
            'Portugues' => "Tomate",
            'English' => "Tomato"
          )
       )
     )
    );
    
    $survey_no_lang = new OR_xform_results(or_xform_results_helper_test::FILE_PATH . 'survey_en_pt/survey_en_pt.xml');
    $this->assertEquals($expected_xform_flat, $survey_no_lang->get_flatten());
  }

  public function test_language_survey_en_pt() {
    $survey_en_pt = new OR_xform_results(or_xform_results_helper_test::FILE_PATH . 'survey_en_pt/survey_en_pt.xml');
    
    // We know that the parsing works.
    // Now we just need to test the language variant.
    
    // By default is English.
    $result = $survey_en_pt->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_en_pt/survey_en_pt_1.xml');
    $this->assertEquals("What's your name?", $result[0]['label']);
    
    // Set to invalid language
    $survey_en_pt->set_language('invalid');
    $result = $survey_en_pt->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_en_pt/survey_en_pt_1.xml');
    $this->assertEquals("What's your name?", $result[0]['label']);
    
    // Set to Portuguese language
    $survey_en_pt->set_language('Portugues');
    $result = $survey_en_pt->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_en_pt/survey_en_pt_1.xml');
    $this->assertEquals("Qual é o teu nome?", $result[0]['label']);
  }

  public function test_language_survey_pt_it() {
    $survey_pt_it = new OR_xform_results(or_xform_results_helper_test::FILE_PATH . 'survey_pt_it/survey_pt_it.xml');
    
    // We know that the parsing works.
    // Now we just need to test the language variant.
    
    // By default is English.
    // However in this file there's no English so the default should be
    // Portuguese because it comes first.
    $result = $survey_pt_it->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_pt_it/survey_pt_it_1.xml');
    $this->assertEquals("Qual é o teu nome?", $result[0]['label']);
    
    // Set to Italian language
    $survey_pt_it->set_language('Italiano');
    $result = $survey_pt_it->parse_result_file(or_xform_results_helper_test::FILE_PATH . 'survey_pt_it/survey_pt_it_1.xml');
    $this->assertEquals("Qual'è il tuo nome?", $result[0]['label']);
  }
}

?>