<?php
namespace foundry\core\auth;
use \foundry\core\Core as Core;

Core::configure('\foundry\core\auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemoryAuthService',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\foundry\core\auth\Auth');

require_once("AuthServiceTest.php");
require_once("Auth/Service/InMemory.php");

/**
 * Description of InMemoryAuthService
 *
 * @author john
 */
class InMemoryAuthServiceTest extends AuthServiceTest
{
    public function  __construct() {
        $auth_service = Core::get('\foundry\core\auth\Auth');
        parent::__construct($auth_service);
    }
}
?>
