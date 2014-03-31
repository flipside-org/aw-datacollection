<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Respondent entity.
 *
 * @todo update the documentation below
 * The respondent entity serves as base to manage respondents.
 * This works in close proximity with Respondent_model although
 * this doesn't depend on it.
 *
 * Adding new fields to a respondent:
 *   - Handle constructor data in the constructor function.
 *     Data comes in directly from a mongo query.
 *   - All object's PUBLIC fields will be saved to mongodb. That's how you
 *     define which fields are saved. If you need an accessible field, set it as
 *     protected and use Getters and Setters.
 *   - Add new fileds to fixtures (Only during dev)
 *
 *
 * IMPORTANT: Only use public field for fields that need to be saved to mongodb
 */
class Respondent_entity extends Entity {

  /********************************
   ********************************
   * Start of respondent fields.
   * The next variables hold actual respondent info that will
   * go in the database.
   * Every field should be of public access.
   */

  /**
   * End of respondent fields.
   *******************************/

  /********************************
   ********************************
   * Start of methods to manage settings.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   */


  /**
   * End of setting methods.
   *******************************/

  /********************************
   ********************************
   * Start of respondent's public methods.
   */


  /**
   * End of public methods.
   *******************************/

  /********************************
   ********************************
   * Start of private and protected methods.
   */

  /**
   * End of private and protected methods.
   *******************************/
}

/* End of file respondent_entity.php */
/* Location: ./application/entities/respondent_entity.php */
