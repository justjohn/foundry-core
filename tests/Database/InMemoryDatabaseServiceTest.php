<?php
namespace foundry\core\database;
use \foundry\core\Core;

Core::configure('\foundry\core\database\Database', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemory',
    "service_config" => array( )
));

Core::requires('\foundry\core\database\Database');

require_once("DatabaseServiceTest.php");

class InMemoryDatabaseServiceTest extends DatabaseServiceTest
{
    public function  __construct() {
        $db_service = Core::get('\foundry\core\database\Database');
        parent::__construct($db_service);
    }
}

?>
