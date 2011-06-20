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
            'service' => 'Mongo',
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
    // This loads and caches a database object for later use in the framework.
    $database = Core::requires('\foundry\core\database\Database');
    
    // snip...

    // You can also get the same loaded module later on with
    $database = Core::get('\foundry\core\database\Database');
    ?>

Note the configuration is seperate from usage and doesn't require the module to be loaded. This means you can configure all your modules in a single file and include it everywhere in your site but only load the modules when they're needed. Also, once a module is loaded it's cached in the framework so it only needs to be loaded once per instantiation.

Modules can also require other modules, so if you build a project management module that requires a database and authentication, you can put the necessary Core::requires('...') statements at the top of your module and when you use the module elsewhere you only need to use a single requires statement. 

Usage
-----

Include 'INSTALL_PATH/lib/_foundry_core_init.php' and consult the documentation for detailed use of the provided modules and module framework.

License
-------

The foundry core framework is licensed under the new BSD License. See the LICENSE file for details.

