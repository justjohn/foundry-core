<?php
require_once("lib/Access/AccessServiceTest.php");
require_once("Access/Service/InMemoryAccessService.php");

class InMemoryAccessServiceTest extends AccessServiceTest {

    protected static $in_memory_options = array("cache"=>false);

    public function  __construct() {
        $access_service = new InMemoryAccessService(self::$in_memory_options);
        parent::__construct($access_service);
    }
}
?>
