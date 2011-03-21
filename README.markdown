Foundry Core
============

Overview
--------

The Foundry Core libraries are an on-demand framework that provides infrastructure for module loading and dependency management. With the core framework modules you only need to know the name of the module to load and it's configuration parameters.

For example, you can configure and load a Mongo database connection with the following snippets:

configuration.php
    <?php
    // Provide configuration for the database
    Core::configure('\foundry\core\database\Database',
        array(
            'service' => 'MongoDatabaseService',
            'service_options' => array(
                'host'      => 'localhost',
                'db'        => 'data'
            )
        )
    );
    ?>

index.php
    <?php
    require_once('configuration.php');
    
    // Require the database library
    Core::requires('\foundry\core\database\Database');
    
    // Get the loaded module
    $database = Core::get('\foundry\core\database\Database');
    ?>

Note the configuration is seperate from usage and doesn't require the module to be loaded. This means you can configure all your modules in a single file and include it everywhere in you site but only load the modules when they're needed. Also, once a module is loaded it's cached in the framework so it only needs to be loaded once per instiation.

Modules can also require other modules, so if you build a project management module that requires a database and authentication, you can put the necessary Core::requires('...') statements at the top of your module and when you use the module elsewhere you only need to use a single requires statement. 

Usage
-----

Include '<install path>/lib/_foundry_core_init.php' and consult the documentation for detailed use of the provided modules and module framework.

License
-------

The foundry core framework is licenced under the new BSD License. See the LICENSE file for details.
