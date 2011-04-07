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
require_once("Foundry/Core/Functions/common.php");

// Load functions for registering and loading modules.
require_once("Foundry/Core/Core.php");

// Utilities
Core::provides('\Foundry\Core\Utilities\Renderer', 'Foundry/Core/Utilities/Renderer.php');

// Core functionality (pre-load)
Core::provides('\Foundry\Core\Exceptions', 'Foundry/Core/Exceptions.php',    true);
Core::provides('\Foundry\Core\Service',    'Foundry/Core/Service.php',       true);
Core::provides('\Foundry\Core\Model',      'Foundry/Core/Model.php',         true);

// Additional modules
Core::provides('\Foundry\Core\Access\Access',     'Foundry/Core/Access/Access.php');
Core::provides('\Foundry\Core\Auth\Auth',         'Foundry/Core/Auth/Auth.php');
Core::provides('\Foundry\Core\Config\Config',     'Foundry/Core/Config/Config.php');
Core::provides('\Foundry\Core\Database\Database', 'Foundry/Core/Database/Database.php');
Core::provides('\Foundry\Core\Email\Email',       'Foundry/Core/Email/Email.php');
Core::provides('\Foundry\Core\Logging\Log',       'Foundry/Core/Log/Log.php');

?>