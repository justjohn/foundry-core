<?php
namespace foundry\core\config;

\foundry\core\Core::requires('\foundry\core\logging\Log');
\foundry\core\Core::requires('\foundry\core\database\Database');

use \foundry\core\logging\Log as Log;

/**
 * Configuration Manager
 * 
 * This file contains the configuration infrastructure for the application.
 *
 * @package   Config
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

// Register data model with the class loader.
\foundry\core\Core::register_class("foundry\core\config\Option", "Config/model/Option.php");

/**
 * Configuration Manager
 *
 * Load configuration options from a database.
 * @package   Config
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */
class Config {
    /**
     * Database access.
     * @var Database
     */
    var $database;

    /**
     * Setup the configuration manager.
     * @param Database $database
     */
    public function __construct(\foundry\core\database\Database $database) {
       $this->database = $database;
    }

    /**
     * Get a configuration option or options.
     * @param String $name The name of the option to load, or blank to get all the options.
     * @return boolean|string|array false if a name is given and the option doesn't exist,
     *                              the value of the config option if $name is set. An array
     *                              of all configuraiton options if $name is blank.
     */
    public function getConfig($name='') {
        if ($name != '') {
            $config = $this->database->load_object('\foundry\core\config\Option', "config_options", array("name"=>$name));
            if ($config !== false) {
                return $config->getValue();
            } else {
                return false;
            }
        }
        $config_objs = $this->database->load_objects('\foundry\core\config\Option', "config_options", "name");
        $config = array();
        if ($config_objs === false) {
            die("unable to load configuration from database");
        } else {
            if (count($config_objs) > 0) {
                foreach ($config_objs as $name=>$config_obj) {
                   $config[$name] = $config_obj->getValue();
                }
            }
        }
        return $config;
    }

    /**
     * Set a configuration option.
     * @param String $name The key of the configuration option to set.
     * @param String $value The value of the configuration option.
     */
    public function setConfig($name, $value) {
        Log::info("ConfigManager::setConfig", "setConfig('$name', '$value')");
        $config = new Option();
        $config->setName(trim($name));
        $config->setValue(trim($value));
        $cur_config = $this->getConfig($name);
        if ($cur_config === false) {
            $this->database->write_object($config, "config_options");
        } else {
            $this->database->update_object($config, "config_options", array("name"=>$name), array("value"));
        }
    }
}
?>
