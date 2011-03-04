<?php
namespace foundry\core\auth;
use \foundry\core\Core as Core;

set_include_path(get_include_path()
        . PATH_SEPARATOR . "../lib/");
require_once("_foundry_core_init.php");

require_once("lib/Auth/AuthServiceTest.php");

Core::configure('\foundry\core\auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemoryAuthService',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\foundry\core\auth\Auth');

require_once("lib/Auth/AuthServiceTest.php");
require_once("Auth/Service/InMemoryAuthService.php");

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
