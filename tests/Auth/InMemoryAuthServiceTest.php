<?php
namespace Foundry\Core\Auth;
use \Foundry\Core\Core;

Core::configure('\Foundry\Core\Auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'Foundry\Core\Auth\Service\InMemory',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\Foundry\Core\Auth\Auth');

require_once("AuthServiceTest.php");

/**
 * Description of InMemoryAuthService
 *
 * @author john
 */
class InMemoryAuthServiceTest extends AuthServiceTest
{
    public function  __construct() {
        $auth_service = Core::get('\Foundry\Core\Auth\Auth');
        parent::__construct($auth_service);
    }
}
?>
