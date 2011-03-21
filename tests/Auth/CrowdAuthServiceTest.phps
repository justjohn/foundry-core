<?php
namespace foundry\core\auth;
use \foundry\core\Core as Core;

require_once("AuthServiceTest.php");
require_once("Auth/Service/CrowdAuthService.php");

/**
 * Description of CrowdAuthServiceTest
 *
 * @author john
 */
/* class CrowdAuthServiceTest extends AuthServiceTest
{
    protected static $crowd_options = array("app_name"=>"coretest",
                                     "app_credential"=>"ois9S093*f",
                                     "service_url"=>"http://crowd.phpfoundry.com/services/SecurityServer?wsdl");

    public function  __construct() {
        $auth_service = new CrowdAuthService(self::$crowd_options);
        parent::__construct($auth_service);
    }
} */
?>
