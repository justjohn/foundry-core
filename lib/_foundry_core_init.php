<?php
namespace foundry\core;

// Load common functions for debugging
require_once("Functions/common.php");

// Load functions for registering and loading modules.
require_once("Core/Core.php");

// Utilities
Core::provides('\foundry\core\utilities\Renderer', 'Utilities/Renderer.php');

// Core functionality (pre-load)
Core::provides('\foundry\core\Exceptions', 'Core/Exceptions.php',    true);
Core::provides('\foundry\core\Service',    'Core/Service.php',       true);
Core::provides('\foundry\core\Model',      'Core/Model.php',         true);


// Additional modules
Core::provides('\foundry\core\access\Access',     'Access/Access.php');
Core::provides('\foundry\core\auth\Auth',         'Auth/Auth.php');
Core::provides('\foundry\core\config\Config',     'Config/Config.php');
Core::provides('\foundry\core\database\Database', 'Database/Database.php');
Core::provides('\foundry\core\email\Email',       'Email/Email.php');
Core::provides('\foundry\core\logging\Log',       'Log/Log.php');

?>