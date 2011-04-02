<?php
/**
 * Initialize Core Library and register all available components.
 * 
 * @category  foundry-core
 * @package   Foundry\Core
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
 
namespace Foundry\Core;

set_include_path(get_include_path() .
                 PATH_SEPARATOR . __DIR__ );

// Load common functions for debugging
require_once("Functions/common.php");

// Load functions for registering and loading modules.
require_once("Core/Core.php");

// Utilities
Core::provides('\Foundry\Core\Utilities\Renderer', 'Utilities/Renderer.php');

// Core functionality (pre-load)
Core::provides('\Foundry\Core\Exceptions', 'Core/Exceptions.php',    true);
Core::provides('\Foundry\Core\Service',    'Core/Service.php',       true);
Core::provides('\Foundry\Core\Model',      'Core/Model.php',         true);


// Additional modules
Core::provides('\Foundry\Core\Access\Access',     'Access/Access.php');
Core::provides('\Foundry\Core\Auth\Auth',         'Auth/Auth.php');
Core::provides('\Foundry\Core\Config\Config',     'Config/Config.php');
Core::provides('\Foundry\Core\Database\Database', 'Database/Database.php');
Core::provides('\Foundry\Core\Email\Email',       'Email/Email.php');
Core::provides('\Foundry\Core\Logging\Log',       'Log/Log.php');

?>