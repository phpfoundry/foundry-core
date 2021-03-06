<?php
/**
 * Configuration Manager
 * 
 * This file contains the configuration infrastructure for the application.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Config
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

namespace Foundry\Core\Config;

use Foundry\Core\Core;
use Foundry\Core\Logging\Log;

Core::requires('\Foundry\Core\Logging\Log');
Core::requires('\Foundry\Core\Database\Database');

/**
 * Configuration Manager
 *
 * Load configuration options from a database.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Config
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Config {
    /**
     * Thec configuration table.
     * @var string
     */
    private $config_table = "config_options";
    /**
     * Database access.
     * @var Database
     */
    private $database;

    /**
     * Setup the configuration manager.
     * @param Database $database
     */
    public function __construct() {
       $this->database = Core::get('\Foundry\Core\Database\Database');
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
            $config = $this->database->load_object('\Foundry\Core\Config\Option', $this->config_table, array("name"=>$name));
            if ($config !== false) {
                return $config->getValue();
            } else {
                return false;
            }
        }
        $config_objs = $this->database->load_objects('\Foundry\Core\Config\Option', $this->config_table, "name");
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
            $this->database->write_object($config, $this->config_table);
        } else {
            $this->database->update_object($config, $this->config_table, array("name"=>$name), array("value"));
        }
    }
}

?>