<?php

/*
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PHPOnCouch;



use PHPOnCouch\Exceptions\CouchException;
use stdClass;
use InvalidArgumentException;
use Exception;

/**
 * Special class to handle administration tasks
 * - create administrators
 * - create users
 * - create roles
 * - assign roles to users
 *
 *
 *
 */
class CouchAdmin {

    /**
     * @var reference to our CouchDB client
     */
    private $client = null;

    /**
     * @var the name of the CouchDB server "users" database
     */
    private $usersdb = "_users";
    private $node;

    /**
     * constructor
     *
     * @param CouchClient $client the couchClient instance
     * @param array $options array. For now the only options is "users_database"(to override the defaults "_users") or
     *  node which is the node that will be used for the configuration.
     *  It it's not specified, the first node will be used.
     */
    public function __construct(CouchClient $client, $options = []) {
        $this->client = $client;
        if (is_array($options)) {
            if (isset($options["users_database"]))
                $this->usersdb = $options["users_database"];
            if (isset($options['node']))
                $this->node = $options['node'];
        }
        if (empty($this->node))
            $this->node = $client->getMemberShip()->cluster_nodes[0];
    }

    /**
     * Set the name of the users database (_users by default)
     *
     * @param string $name CouchDB users database name (_users is the default)
     */
    public function setUsersDatabase($name) {
        $this->usersdb = $name;
    }

    /**
     * get the name of the users database this class will use
     *
     *
     * @return string users database name
     */
    public function getUsersDatabase() {
        return $this->usersdb;
    }

    private function _buildUrl($parts) {
        $back = $parts["scheme"] . "://";
        if (!empty($parts["user"])) {
            $back .= $parts["user"];
            if (!empty($parts["pass"])) {
                $back .= ":" . $parts["pass"];
            }
            $back .= "@";
        }
        $back .= $parts["host"];
        if (!empty($parts["port"])) {
            $back .= ":" . $parts["port"];
        }
        $back .= "/";
        if (!empty($parts["path"])) {
            $back .= $parts["path"];
        }
        return $back;
    }

    /**
     * Creates a new CouchDB server administrator
     *
     * @param string $login administrator login
     * @param string $password administrator password
     * @param array $roles add additionnal roles to the new admin
     * @return stdClass CouchDB server response
     * @throws InvalidArgumentException|Exception|CouchException
     */
    public function createAdmin($login, $password, $roles = []) {
        $login = urlencode($login);
        $data = (string) $password;
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        if (strlen($data) < 1) {
            throw new InvalidArgumentException("Password can't be empty");
        }
        $url = '/_node/' . urlencode($this->node) . '/_config/admins/' . urlencode($login);
        try {
            $raw = $this->client->query(
                    "PUT", $url, [], json_encode($data)
            );
        } catch (Exception $e) {
            throw $e;
        }
        $resp = Couch::parseRawResponse($raw);
        if ($resp['status_code'] != 200) {
            throw new CouchException($raw);
        }

        $dsn = $this->client->dsnPart();
        $dsn["user"] = $login;
        $dsn["pass"] = $password;
        $client = new CouchClient($this->_buildUrl($dsn), $this->usersdb, $this->client->options());
        $user = new stdClass();
        $user->name = $login;
        $user->type = "user";
        $user->roles = $roles;
        $user->_id = "org.couchdb.user:" . $login;
        return $client->storeDoc($user);
    }

    /**
     * Permanently removes a CouchDB Server administrator
     *
     *
     * @param string $login administrator login
     * @return stdClass CouchDB server response
     * @throws InvalidArgumentException|CouchException
     */
    public function deleteAdmin($login) {
        $encodedLogin = urlencode($login);
        if (strlen($encodedLogin) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }

        try {
            $client = new CouchClient($this->client->dsn(), $this->usersdb);
            $doc = $client->getDoc("org.couchdb.user:" . $encodedLogin);
            $client->deleteDoc($doc);
        } catch (CouchException $e) {
            if ($e->getCode() !== 404)
                throw $e;
        }

        $url = '/_node/' . urlencode($this->node) . '/_config/admins/' . urlencode($encodedLogin);
        $raw = $this->client->query(
                "DELETE", $url
        );
        $resp = Couch::parseRawResponse($raw);
        if ($resp['status_code'] != 200) {
            throw new CouchException($raw);
        }
        return $resp["body"];
    }

    /**
     * create a user
     *
     * @param string $login user login
     * @param string $password user password
     * @param array $roles add additionnal roles to the new user
     * @return stdClass CouchDB user creation response (the same as a document storage response)
     * @throws InvalidArgumentException
     */
    public function createUser($login, $password, $roles = []) {
        $pwdStr = (string) $password;
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        if (strlen($pwdStr) < 1) {
            throw new InvalidArgumentException("Password can't be empty");
        }
        $user = new stdClass();
        $user->salt = sha1(microtime() . mt_rand(1000000, 9999999), false);
        $user->password_sha = sha1($pwdStr . $user->salt, false);
        $user->name = $login;
        $user->type = "user";
        $user->roles = $roles;
        $user->_id = "org.couchdb.user:" . $login;
        $client = new CouchClient($this->client->dsn(), $this->usersdb, $this->client->options());
        return $client->storeDoc($user);
    }

    /**
     * Permanently removes a CouchDB User
     *
     *
     * @param string $login user login
     * @return stdClass CouchDB server response
     * @throws InvalidArgumentException
     */
    public function deleteUser($login) {
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        $client = new CouchClient($this->client->dsn(), $this->usersdb);
        $doc = $client->getDoc("org.couchdb.user:" . $login);
        return $client->deleteDoc($doc);
    }

    /**
     * returns the document of a user
     *
     * @param string $login login of the user to fetch
     * @return stdClass CouchDB document
     * @throws InvalidArgumentException
     */
    public function getUser($login) {
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        $client = new CouchClient($this->client->dsn(), $this->usersdb, $this->client->options());
        return $client->getDoc("org.couchdb.user:" . $login);
    }

    /**
     * returns all users
     *
     * @param boolean $includeDocs if set to true, users documents will also be included
     * @return array users array : each row is a stdObject with "id", "rev" and optionally "doc" properties
     */
    public function getAllUsers($includeDocs = false) {
        $client = new CouchClient($this->client->dsn(), $this->usersdb, $this->client->options());
        if ($includeDocs) {
            $client->include_docs(true);
        }
        return $client->startkey("org.couchdb.user:")->endkey("org.couchdb.user?")->getAllDocs()->rows;
    }

    /**
     * Add a role to a user document
     *
     * @param string|stdClass $user the user login (as a string) or the user document ( fetched by getUser() method )
     * @param string $role the role to add in the list of roles the user belongs to
     * @return boolean true if the user $user now belongs to the role $role
     * @throws InvalidArgumentException
     */
    public function addRoleToUser($user, $role) {
        if (is_string($user)) {
            $user = $this->getUser($user);
        } elseif (!property_exists($user, "_id") || !property_exists($user, "roles")) {
            throw new InvalidArgumentException("user parameter should be the login or a user document");
        }
        if (!in_array($role, $user->roles)) {
            $user->roles[] = $role;
            $client = clone($this->client);
            $client->useDatabase($this->usersdb);
            $client->storeDoc($user);
        }
        return true;
    }

    /**
     * Remove a role from a user document
     *
     * @param string|stdClass $user the user login (as a string) or the user document ( fetched by getUser() method )
     * @param string $role the role to remove from the list of roles the user belongs to
     * @return boolean true if the user $user don't belong to the role $role anymore
     * @throws InvalidArgumentException
     */
    public function removeRoleFromUser($user, $role) {
        if (is_string($user)) {
            $user = $this->getUser($user);
        } elseif (!property_exists($user, "_id") || !property_exists($user, "roles")) {
            throw new InvalidArgumentException("user parameter should be the login or a user document");
        }
        if (in_array($role, $user->roles)) {
            $user->roles = $this->_removeFromArray($role, $user->roles);
            $client = clone($this->client);
            $client->useDatabase($this->usersdb);
            $client->storeDoc($user);
        }
        return true;
    }

    /**
     * returns the security object of a database
     *
     * @link http://wiki.apache.org/couchdb/Security_Features_Overview
     * @return stdClass security object of the database
     * @throws CouchException
     */
    public function getSecurity() {
        $dbname = $this->client->getDatabaseName();
        $raw = $this->client->query(
                "GET", "/" . $dbname . "/_security"
        );
        $resp = Couch::parseRawResponse($raw);
        if ($resp['status_code'] != 200) {
            throw new CouchException($raw);
        }
        if (!property_exists($resp['body'], "admins")) {
            $resp["body"]->admins = new stdClass();
            $resp["body"]->admins->names = [];
            $resp["body"]->admins->roles = [];
        }
        if (!property_exists($resp['body'], "members")) {
            $resp["body"]->members = new stdClass();
            $resp["body"]->members->names = [];
            $resp["body"]->members->roles = [];
        }
        return $resp['body'];
    }

    /**
     * set the security object of a database
     *
     * @link http://wiki.apache.org/couchdb/Security_Features_Overview
     * @param stdClass $security the security object to apply to the database
     * @return stdClass CouchDB server response ( { "ok": true } )
     * @throws InvalidArgumentException|CouchException
     */
    public function setSecurity($security) {
        if (!is_object($security)) {
            throw new InvalidArgumentException("Security should be an object");
        }
        $dbname = $this->client->getDatabaseName();
        $raw = $this->client->query(
                "PUT", "/" . $dbname . "/_security", [], json_encode($security)
        );
        $resp = Couch::parseRawResponse($raw);
        if ($resp['status_code'] == 200) {
            return $resp['body'];
        }
        throw new CouchException($raw);
    }

    /**
     * add a user to the list of members for the current database
     *
     * @param string $login user login
     * @return boolean true if the user has successfuly been added
     * @throws InvalidArgumentException
     */
    public function addDatabaseMemberUser($login) {
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        $sec = $this->getSecurity();
        if (in_array($login, $sec->members->names)) {
            return true;
        }
        array_push($sec->members->names, $login);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

    /**
     * add a user to the list of admins for the current database
     *
     * @param string $login user login
     * @return boolean true if the user has successfuly been added
     * @throws InvalidArgumentException
     */
    public function addDatabaseAdminUser($login) {
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        $sec = $this->getSecurity();
        if (in_array($login, $sec->admins->names)) {
            return true;
        }
        array_push($sec->admins->names, $login);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

    /**
     * get the list of admins for the current database
     *
     * @return array database admins logins
     */
    public function getDatabaseAdminUsers() {
        $sec = $this->getSecurity();
        return $sec->admins->names;
    }

    /**
     * get the list of members for the current database
     *
     * @return array database members logins
     */
    public function getDatabaseMemberUsers() {
        $sec = $this->getSecurity();
        return $sec->members->names;
    }

    /**
     * remove a user from the list of members for the current database
     *
     * @param string $login user login
     * @return boolean true if the user has successfuly been removed
     * @throws InvalidArgumentException
     */
    public function removeDatabaseMemberUser($login) {
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        $sec = $this->getSecurity();
        if (!in_array($login, $sec->members->names)) {
            return true;
        }
        $sec->members->names = $this->_removeFromArray($login, $sec->members->names);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

    /**
     * remove a user from the list of admins for the current database
     *
     * @param string $login user login
     * @return boolean true if the user has successfuly been removed
     * @throws InvalidArgumentException
     */
    public function removeDatabaseAdminUser($login) {
        if (strlen($login) < 1) {
            throw new InvalidArgumentException("Login can't be empty");
        }
        $sec = $this->getSecurity();
        if (!in_array($login, $sec->admins->names)) {
            return true;
        }
        $sec->admins->names = $this->_removeFromArray($login, $sec->admins->names);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

/// roles

    /**
     * add a role to the list of members for the current database
     *
     * @param string $role role name
     * @return boolean true if the role has successfuly been added
     * @throws InvalidArgumentException
     */
    public function addDatabaseMemberRole($role) {
        if (strlen($role) < 1) {
            throw new InvalidArgumentException("Role can't be empty");
        }
        $sec = $this->getSecurity();
        if (in_array($role, $sec->members->roles)) {
            return true;
        }
        array_push($sec->members->roles, $role);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

    /**
     * add a role to the list of admins for the current database
     *
     * @param string $role role name
     * @return boolean true if the role has successfuly been added
     * @throws InvalidArgumentException
     */
    public function addDatabaseAdminRole($role) {
        if (strlen($role) < 1) {
            throw new InvalidArgumentException("Role can't be empty");
        }
        $sec = $this->getSecurity();
        if (in_array($role, $sec->admins->roles)) {
            return true;
        }
        array_push($sec->admins->roles, $role);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

    /**
     * get the list of admin roles for the current database
     *
     * @return array database admins roles
     */
    public function getDatabaseAdminRoles() {
        $sec = $this->getSecurity();
        return $sec->admins->roles;
    }

    /**
     * get the list of member roles for the current database
     *
     * @return array database members roles
     */
    public function getDatabaseMemberRoles() {
        $sec = $this->getSecurity();
        return $sec->members->roles;
    }

    /**
     * remove a role from the list of members for the current database
     *
     * @param string $role role name
     * @return boolean true if the role has successfuly been removed
     * @throws InvalidArgumentException
     */
    public function removeDatabaseMemberRole($role) {
        if (strlen($role) < 1) {
            throw new InvalidArgumentException("Role can't be empty");
        }
        $sec = $this->getSecurity();
        if (!in_array($role, $sec->members->roles)) {
            return true;
        }
        $sec->members->roles = $this->_removeFromArray($role, $sec->members->roles);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

    /**
     * remove a role from the list of admins for the current database
     *
     * @param string $role role name
     * @return boolean true if the role has successfuly been removed
     * @throws InvalidArgumentException|CouchException
     */
    public function removeDatabaseAdminRole($role) {
        if (strlen($role) < 1) {
            throw new InvalidArgumentException("Role can't be empty");
        }
        $sec = $this->getSecurity();
        if (!in_array($role, $sec->admins->roles)) {
            return true;
        }
        $sec->admins->roles = $this->_removeFromArray($role, $sec->admins->roles);
        $back = $this->setSecurity($sec);
        if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
            return true;
        }
        return false;
    }

/// /roles

    private function _removeFromArray($needle, $haystack) {
        $back = [];
        foreach ($haystack as $one) {
            if ($one != $needle) {
                $back[] = $one;
            }
        }
        return $back;
    }

}
