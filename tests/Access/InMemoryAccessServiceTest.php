<?php
namespace foundry\core\access;
use \foundry\core\Core as Core;

Core::configure('\foundry\core\access\Access', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemoryAccessService',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\foundry\core\access\Access');

require_once("AccessServiceTest.php");
require_once("Access/Service/InMemoryAccessService.php");

class InMemoryAccessServiceTest extends AccessServiceTest {
    public function  __construct() {
        $access_service = Core::get('\foundry\core\access\Access');
        parent::__construct($access_service);
    }
}
?>
