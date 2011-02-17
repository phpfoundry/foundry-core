<?php
namespace foundry\core\access;
use \foundry\core\Core as Core;

set_include_path(get_include_path()
        . PATH_SEPARATOR . "../lib/");
require_once("_foundry_core_init.php");

require_once("lib/Access/AccessServiceTest.php");

Core::configure('\foundry\core\access\Access', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemoryAccessService',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\foundry\core\access\Access');

require_once("lib/Access/AccessServiceTest.php");
require_once("Access/Service/InMemoryAccessService.php");

class InMemoryAccessServiceTest extends AccessServiceTest {
    public function  __construct() {
        $access_service = Core::get('\foundry\core\access\Access');
        parent::__construct($access_service);
    }
}
?>
