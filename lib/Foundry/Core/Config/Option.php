<?php
/**
 * Configuration Options.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Config
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Config;

use \Foundry\Core\Model;

/**
 * Configuration Options.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Config
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Option extends \Foundry\Core\BaseModel {

    private $fields = array("name"=>Model::STR, "value"=>Model::STR, "id"=>Model::INT);
    private $key_field = "id";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>