<?php
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
register_class("Option", "Config/model/Option.php");

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
    public function __construct($database) {
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
            $config = $this->database->load_object("Option", "config_options", array("name"=>$name));
            if ($config !== false) {
                return $config->getValue();
            } else {
                return false;
            }
        }
        $config_objs = $this->database->load_objects("Option", "config_options", "name");
        $config = array();
        foreach ($config_objs as $name=>$config_obj) {
            $config[$name] = $config_obj->getValue();
        }
        return $config;
    }

    /**
     * Set a configuration option.
     * @param String $name The key of the configuration option to set.
     * @param String $value The value of the configuration option.
     */
    public function setConfig($name, $value) {
        LogManager::info("ConfigManager::setConfig", "setConfig('$name', '$value')");
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