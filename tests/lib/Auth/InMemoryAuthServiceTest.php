<?php
require_once("lib/Auth/AuthServiceTest.php");
require_once("Auth/Service/InMemoryAuthService.php");

/**
 * Description of InMemoryAuthService
 *
 * @author john
 */
class InMemoryAuthServiceTest extends AuthServiceTest
{
    protected static $options = array("cache"=>false);

    public function  __construct() {
        $auth_service = new InMemoryAuthService(self::$options);
        parent::__construct($auth_service);
    }
}
?>
