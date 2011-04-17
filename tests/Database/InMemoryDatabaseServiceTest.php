<?php
namespace Foundry\Core\Database;

use Foundry\Core\Core;

Core::configure('\Foundry\Core\Database\Database', array(
    "admin_group" => "svn_administrators",
    "service" => 'Foundry\Core\Database\Service\InMemory',
    "service_config" => array( )
));

Core::requires('\Foundry\Core\Database\Database');

require_once("DatabaseServiceTest.php");

class InMemoryDatabaseServiceTest extends DatabaseServiceTest
{
    public function  __construct() {
        $db_service = Core::get('\Foundry\Core\Database\Database');
        parent::__construct($db_service);
    }
}

?>
