<?php
namespace foundry\core\access;
use \foundry\core\Core as Core;

require_once("AccessServiceTest.php");

Core::configure('\foundry\core\access\Access', array(
    "service" => 'InMemoryAccessService',
    "service_config" => array(
        "cache"=>false
    )
));

Core::configure('\foundry\core\auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'InMemoryAuthService',
    "service_config" => array(
        "cache"=>false
    )
));

Core::requires('\foundry\core\auth\Auth');
Core::requires('\foundry\core\access\Access');

/**
 * Test class for Access.
 * Generated by PHPUnit on 2010-10-22 at 17:41:03.
 */
class AccessTest extends \PHPUnit_Framework_TestCase {
    private $access_manager;
    private $auth_manager;
    /**
     * Sets up the test.
     */
    protected function setUp() {
        $this->auth_manager = Core::get('\foundry\core\auth\Auth');
        $this->access_manager = Core::get('\foundry\core\access\Access');

        $this->addAuthTestData();
        $this->addAccessTestData();
    }

    private $group1_name = "test";
    private $group1_desc = "something...";

    private $group2_name = "group";
    private $group2_desc = "test group";

    private $admin_group_name = "admin";
    private $admin_group_desc = "admin group";

    private function addAuthTestData() {
        $group1 = new \foundry\core\auth\Group();
        $group1->setName($this->group1_name);
        $group1->setDescription($this->group1_desc);
        $this->auth_manager->addGroup($group1);

        $group2 = new \foundry\core\auth\Group();
        $group2->setName($this->group2_name);
        $group2->setDescription($this->group2_desc);
        $this->auth_manager->addGroup($group2);

        $group3 = new \foundry\core\auth\Group();
        $group3->setName($this->admin_group_name);
        $group3->setDescription($this->admin_group_desc);
        $this->auth_manager->addGroup($group3);
    }


    private $role1;
    private $role2;

    public function __construct() {
        $this->role1 = new Role("test-users", "some data", array($this->group1_name));
        $this->role2 = new Role("admin_ish", "admin role", array($this->admin_group_name));
    }

    private function addAccessTestData() {
        $this->access_manager->addRole($this->role1);
        $this->access_manager->addRole($this->role2);
    }

    /**
     * @todo Implement testGetRole().
     */
    public function testGetRole() {
        $role = $this->access_manager->getRole($this->role1->getKey());
        $this->assertEquals($role, $this->role1);
        $role = $this->access_manager->getRole($this->role2->getKey());
        $this->assertEquals($role, $this->role2);
    }

    /**
     * @todo Implement testAddRole().
     */
    public function testAddRole() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRemoveRole().
     */
    public function testRemoveRole() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testHasRole().
     */
    public function testHasRole() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}

?>
