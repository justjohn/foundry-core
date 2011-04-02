<?php
namespace Foundry\Core\Access;
use \Foundry\Core\Core as Core;

Core::configure('\Foundry\Core\Access\Access', array(
    "service" => 'InMemory',
    "service_config" => array(
        "cache"=>false
    )
));

Core::configure('\Foundry\Core\Auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemory',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\Foundry\Core\Access\Access');

require_once("AccessServiceTest.php");

class InMemoryAccessServiceTest extends AccessServiceTest {
    public function  __construct() {
        $access_service = Core::get('\Foundry\Core\Access\Access');
        parent::__construct($access_service);
    }
}
?>
