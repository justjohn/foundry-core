<?php
require_once("common.php");
require_once("Access/Access.php");
require_once("Access/AccessService.php");
require_once("Auth/Auth.php");

class AccessTest {
    private $access_manager;
    public function __construct() {
        $auth_class = "InMemoryAuthService";
        $auth_config = array("cache" => false);
        $admin_group = "admin_group";
        $url_manager = new URL($url_base, true, "test");
        $auth_manager = new Auth($auth_class,
                                 $auth_config,
                                 $admin_group,
                                 $url_manager,
                                 $database,
                                 "12tr*sfda-F");

        $access_class = "InMemoryAccessService";
        $access_config = array("cache" => false);
        $this->access_manager = new Access($auth_manager, $access_class, $access_config);
    }
}
?>
