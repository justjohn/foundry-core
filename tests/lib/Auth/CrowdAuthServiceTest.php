<?php
require_once("lib/Auth/AuthServiceTest.php");
require_once("Auth/Service/CrowdAuthService.php");

/**
 * Description of CrowdAuthServiceTest
 *
 * @author john
 */
class CrowdAuthServiceTest extends AuthServiceTest
{
    protected static $crowd_options = array("app_name"=>"hubtest",
                                     "app_credential"=>"v13sda0%T",
                                     "service_url"=>"http://crowd.moqul.net/services/SecurityServer?wsdl");

    public function  __construct() {
        $auth_service = new CrowdAuthService(self::$crowd_options);
        parent::__construct($auth_service);
    }
}
?>
