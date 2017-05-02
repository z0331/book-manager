<?php

use PHPOnCouch\CouchClient;
use PHPOnCouch\CouchAdmin;
use PHPOnCouch\Exceptions;

require_once join(DIRECTORY_SEPARATOR, [__DIR__, '_config', 'config.php']);

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-11-01 at 15:39:08.
 * 
 */
class CouchAdminTest extends PHPUnit_Framework_TestCase {

    private $host = 'localhost';
    private $port = '5984';
    private $admin = array("login" => "adm", "password" => "sometest");

    /**
     *
     * @var PHPOnCouch\CouchClient
     */
    private $client;

    /**
     *
     * @var PHPOnCouch\CouchClient
     */
    private $aclient;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $config = \config::getInstance();
        $url = $config->getUrl($this->host, $this->port, null);
        $aUrl = $config->getUrl($this->host, $this->port, $config->getFirstAdmin());
        $this->client = new CouchClient($url, 'couchclienttest');
        $this->aclient = new CouchClient($aUrl, 'couchclienttest');
        try {
            $this->aclient->deleteDatabase();
        } catch (Exception $e) {
            
        }
        $this->aclient->createDatabase();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->client = null;
        $this->aclient = null;
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getUsersDatabase
     */
    public function testGetUsersDatabase() {
        $adm = new CouchAdmin($this->aclient, array("users_database" => "test"));
        $this->assertEquals("test", $adm->getUsersDatabase());
        $adm = new CouchAdmin($this->aclient);
        $this->assertEquals("_users", $adm->getUsersDatabase());
        $adm->setUsersDatabase("test");
        $this->assertEquals("test", $adm->getUsersDatabase());
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::setUsersDatabase
     * @depends testGetUsersDatabase
     */
    public function testSetUsersDatabase() {
        $adm = new CouchAdmin($this->aclient);
        $adm->setUsersDatabase("testDB");
        $this->assertEquals('testDB', $adm->getUsersDatabase());
        $adm->setUsersDatabase(null);
        $this->assertEquals(null, $adm->getUsersDatabase());
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::createAdmin
     */
    public function testCreateAdmin() {
        $adm = new CouchAdmin($this->aclient);
        $adm->createAdmin($this->admin["login"], $this->admin["password"]);

        $this->expectException(Exceptions\CouchException::class);
        $this->expectExceptionCode('412');
//		$this->setExpectedException('PHPOnCouch\Exceptions\CouchException', '', 412);
        $this->aclient->createDatabase();
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::createAdmin
     * @depends testCreateAdmin
     */
    public function testAdminRights() {
        $this->aclient->deleteDatabase();

        $ok = $this->aclient->createDatabase();
        $this->assertInternalType("object", $ok);
        $this->assertObjectHasAttribute("ok", $ok);
        $this->assertEquals($ok->ok, true);
        $ok = $this->aclient->deleteDatabase();
        $this->assertInternalType("object", $ok);
        $this->assertObjectHasAttribute("ok", $ok);
        $this->assertEquals($ok->ok, true);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::deleteAdmin
     */
    public function testDeleteAdmin() {
        $adm = new CouchAdmin($this->aclient);
        $adm->createAdmin("secondAdmin", "password");
        $adm->deleteAdmin("secondAdmin");
        $adm->createAdmin("secondAdmin", "password");
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::createUser
     */
    public function testCreateUser() {
        $adm = new CouchAdmin($this->aclient);
        $ok = $adm->createUser("joe", "dalton");
        $this->assertInternalType("object", $ok);
        $this->assertObjectHasAttribute("ok", $ok);
        $this->assertEquals($ok->ok, true);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getUser
     * @depends testCreateUser
     */
    public function testGetUser() {
        $adm = new CouchAdmin($this->aclient);
        $ok = $adm->getUser("joe");
        $this->assertInternalType("object", $ok);
        $this->assertObjectHasAttribute("_id", $ok);
        $this->assertObjectHasAttribute("name", $ok);
        $this->assertEquals('joe', $ok->name);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::deleteUser
     * @depends testGetUser
     */
    public function testDeleteUser() {
        $adm = new CouchAdmin($this->aclient);
        $ok = $adm->deleteUser("joe");
        $this->assertInternalType("object", $ok);
        $this->assertObjectHasAttribute("ok", $ok);
        $this->assertEquals($ok->ok, true);
        $ok = $adm->getAllUsers(true);
        $this->assertInternalType("array", $ok);
        $this->assertEquals(count($ok), 2);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getAllUsers
     */
    public function testGetAllUsers() {
        $adm = new CouchAdmin($this->aclient);
        $ok = $adm->getAllUsers(true);
        $this->assertInternalType("array", $ok);
        $this->assertEquals(count($ok), 2);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::addRoleToUser
     * @todo   Implement testAddRoleToUser().
     */
    public function testAddRoleToUser() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::removeRoleFromUser
     * @todo   Implement testRemoveRoleFromUser().
     */
    public function testRemoveRoleFromUser() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getSecurity
     */
    public function testGetSecurity() {
        $adm = new CouchAdmin($this->aclient);
        $security = $adm->getSecurity();
        $this->assertObjectHasAttribute("admins", $security);
        $this->assertObjectHasAttribute("members", $security);
        $this->assertObjectHasAttribute("names", $security->admins);
        $this->assertObjectHasAttribute("roles", $security->admins);
        $this->assertObjectHasAttribute("names", $security->members);
        $this->assertObjectHasAttribute("roles", $security->members);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::setSecurity
     */
    public function testSetSecurity() {
        $adm = new CouchAdmin($this->aclient);
        $security = $adm->getSecurity();
        $security->admins->names[] = "joe";
        $security->members->names[] = "jack";
        $ok = $adm->setSecurity($security);
        $this->assertInternalType("object", $ok);
        $this->assertObjectHasAttribute("ok", $ok);
        $this->assertEquals($ok->ok, true);

        $security = $adm->getSecurity();
        $this->assertEquals(count($security->members->names), 1);
        $this->assertEquals(reset($security->members->names), "jack");
        $this->assertEquals(count($security->admins->names), 1);
        $this->assertEquals(reset($security->admins->names), "joe");
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::addDatabaseMemberUser
     */
    public function testAddDatabaseMemberUser() {
        $adm = new CouchAdmin($this->aclient);
        $ok = $adm->removeDatabaseMemberUser("jack");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->members->names), 0);
        $ok = $adm->addDatabaseMemberUser("jack");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->members->names), 1);
        $this->assertEquals(reset($security->members->names), "jack");
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::addDatabaseAdminUser
     */
    public function testAddDatabaseAdminUser() {
        $adm = new CouchAdmin($this->aclient);
        $ok = $adm->removeDatabaseAdminUser("joe");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->admins->names), 0);
        $ok = $adm->addDatabaseAdminUser("joe");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->admins->names), 1);
        $this->assertEquals(reset($security->admins->names), "joe");
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getDatabaseAdminUsers
     * @depends testAddDatabaseAdminUser
     */
    public function testGetDatabaseAdminUsers() {
        $adm = new CouchAdmin($this->aclient);
        $users = $adm->getDatabaseAdminUsers();
        $this->assertInternalType("array", $users);
        $this->assertEquals(0, count($users));

        $ok = $adm->addDatabaseAdminUser("joe");
        $users = $adm->getDatabaseAdminUsers();
        $this->assertInternalType("array", $users);
        $this->assertEquals(1, count($users));
        $this->assertEquals("joe", reset($users));
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getDatabaseMemberUsers
     * @depends testAddDatabaseMemberUser
     */
    public function testGetDatabaseMemberUsers() {
        $adm = new CouchAdmin($this->aclient);
        $users = $adm->getDatabaseMemberUsers();
        $this->assertInternalType("array", $users);
        $this->assertEquals(0, count($users));

        $adm->addDatabaseMemberUser("jack");
        $users = $adm->getDatabaseMemberUsers();
        $this->assertInternalType("array", $users);
        $this->assertEquals(1, count($users));
        $this->assertEquals("jack", reset($users));
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::removeDatabaseMemberUser
     * @todo   Implement testRemoveDatabaseMemberUser().
     */
    public function testRemoveDatabaseMemberUser() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::removeDatabaseAdminUser
     * @todo   Implement testRemoveDatabaseAdminUser().
     */
    public function testRemoveDatabaseAdminUser() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::addDatabaseMemberRole
     */
    public function testAddDatabaseMemberRole() {
        $adm = new CouchAdmin($this->aclient);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->members->roles), 0);
        $ok = $adm->addDatabaseMemberRole("cowboy");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->members->roles), 1);
        $this->assertEquals(reset($security->members->roles), "cowboy");
        $ok = $adm->removeDatabaseMemberRole("cowboy");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->members->roles), 0);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::addDatabaseAdminRole
     */
    public function testAddDatabaseAdminRole() {
        $adm = new CouchAdmin($this->aclient);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->admins->roles), 0);
        $ok = $adm->addDatabaseAdminRole("cowboy");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->admins->roles), 1);
        $this->assertEquals(reset($security->admins->roles), "cowboy");
        $ok = $adm->removeDatabaseAdminRole("cowboy");
        $this->assertInternalType("boolean", $ok);
        $this->assertEquals($ok, true);
        $security = $adm->getSecurity();
        $this->assertEquals(count($security->admins->roles), 0);
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getDatabaseAdminRoles
     */
    public function testGetDatabaseAdminRoles() {
        $adm = new CouchAdmin($this->aclient);
        $users = $adm->getDatabaseAdminRoles();
        $this->assertInternalType("array", $users);
        $this->assertEquals(0, count($users));
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::getDatabaseMemberRoles
     * @todo   Implement testGetDatabaseMemberRoles().
     */
    public function testGetDatabaseMemberRoles() {
        $adm = new CouchAdmin($this->aclient);
        $users = $adm->getDatabaseMemberRoles();
        $this->assertInternalType("array", $users);
        $this->assertEquals(0, count($users));
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::removeDatabaseMemberRole
     * @todo   Implement testRemoveDatabaseMemberRole().
     */
    public function testRemoveDatabaseMemberRole() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PHPOnCouch\CouchAdmin::removeDatabaseAdminRole
     * @todo   Implement testRemoveDatabaseAdminRole().
     */
    public function testRemoveDatabaseAdminRole() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
