<?php
namespace foundry\core\auth;

/**
 * LDAP Authentication Service Implementation
 *
 * This file contains the logic required to authenticate against an LDAP or
 * ActiveDirectory endpoint.
 *
 * @package Auth
 * @author John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

/**
 * LDAP Authentication Service
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 * @license   TBD
 */
class LDAPAuthService implements AuthService, AuthServiceSubgroups {
    /**
     * The LDAP connection
     * @var LDAP link identifier 
     */
    private $ldap_conn = false;

    /**
     * The LDAP attributes.
     * @var LDAPAttributes
     */
    private $ldap_attributes;
    
    /**
     * The required options for this authentication service.
     * @var array
     */
    public static $required_options = array("connectionString",
                                            "baseDN",
                                            "managerDN",
                                            "managerPassword",
                                            "userDN",
                                            "usernameAttr",
                                            "userFirstNameAttr",
                                            "userSurnameAttr",
                                            "userDisplayNameAttr",
                                            "userEmailAttr",
                                            "userGroupAttr",
                                            "userPasswordAttr",
                                            "userObjectClass",
                                            "groupDN",
                                            "groupObjectClass",
                                            "groupNameAttr",
                                            "groupDescAttr",
                                            "groupMemberAttr",
                                            "roleDN",
                                            "roleObjectClass",
                                            "roleNameAttr",
                                            "roleDescAttr",
                                            "roleMemberAttr");

    /**
     * A set of defaults for connecting to an LDAP directory.
     * @var array
     */
    public static $option_defaults = array(
            "connectionString"      => "ldap://",
            "baseDN"                => "dc=example,dc=com",
            "managerDN"             => "cn=admin",
            "managerPassword"       => "",
            "userDN"                => "ou=people",
            "usernameAttr"          => "cn",
            "userFirstNameAttr"     => "givenName",
            "userSurnameAttr"       => "sn",
            "userDisplayNameAttr"   => "displayName",
            "userEmailAttr"         => "mail",
            "userGroupAttr"         => "memberOf",
            "userPasswordAttr"      => "userPassword",
            "userObjectClass"       => "inetOrgPerson",
            "groupDN"               => "ou=groups",
            "groupObjectClass"      => "groupOfUniqueNames",
            "groupNameAttr"         => "cn",
            "groupDescAttr"         => "description",
            "groupMemberAttr"       => "uniqueMember",
            "roleDN"                => "ou=roles",
            "roleObjectClass"       => "groupOfUniqueNames",
            "roleNameAttr"          => "cn",
            "roleDescAttr"          => "description",
            "roleMemberAttr"        => "uniqueMember");


    /**
     * Bind to the LDAP directory.
     * @return boolean
     */
    private function bind() {
        ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $result = ldap_bind($this->ldap_conn,
                             $this->ldap_attributes->managerDN . "," . $this->ldap_attributes->baseDN,
                             $this->ldap_attributes->managerPassword);
        if (!$result) {
            throw new \foundry\core\exceptions\ServiceConnectionException("Unable to bind to the LDAP directory.");
        }
        return $result;
    }

    /**
     * Connect and bind to the LDAP directory.
     * @param array|string An array of LDAP options or the filename for an ini file.
     * @throws ServiceValidationException All required options are not present.
     * @throws ServiceConnectionException All required options are not present.
     */
    public function __construct($options) {
        \foundry\core\Service::validate($options, self::$required_options);
        $ldap_attr = new LDAPAttributes($options);
        $this->ldap_attributes = $ldap_attr;
        $ldap_conn = ldap_connect($ldap_attr->connectionString);
        if ($ldap_conn) {
            $this->ldap_conn = $ldap_conn;
            $ldap_bind = $this->bind();
            if ($ldap_bind) {
                // LDAP bind complete
                return;
            }
        }
        throw new \foundry\core\exceptions\ServiceConnectionException("Unable to connect and bind to the LDAP directory.");
    }

    /**
     * Add a user to the LDAP directory.
     * @param User $user The details of the user to add.
     * @param string $password The user's password.
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password) {
        $username = $user->getUsername();
        $email = $user->getEmail();
        $firstname = $user->getFirstName();
        $surname = $user->getSurname();
        $displayname = $user->getDisplayName();

        // Surname is a required attribute, if blank default to username
        if ($surname == '') {
            $surname = $username;
        }
        // Display name defaults to First Last
        if ($displayname == "") {
            $displayname = $firstname;
            if ($firstname != '' && $surname != '') {
                $displayname .= " ";
            }
            $displayname .= $surname;
        }
        
        $attributes = $this->ldap_attributes;
        
        $ldap_attr = array();
        $ldap_attr["objectclass"][0] = "top";
        $ldap_attr["objectclass"][1] = "person";
        $ldap_attr["objectclass"][2] = "organizationalPerson";
        $ldap_attr["objectclass"][3] = $attributes->userObjectClass;
        $ldap_attr[$attributes->usernameAttr] = $username;
        $ldap_attr[$attributes->userSurnameAttr] = $surname;
        $ldap_attr[$attributes->userDisplayNameAttr] = $displayname;
        if ($email != '') $ldap_attr[$attributes->userEmailAttr] = $email;
        if ($firstname != '') $ldap_attr[$attributes->userFirstNameAttr] = $firstname;
        
        // base64 encode the binary hash of the MD5 hashed password
        $ldap_attr[$attributes->userPasswordAttr] = '{MD5}' . base64_encode(pack('H*',md5($password)));

        $userdn = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;
        $result = @ldap_add($this->ldap_conn, $userdn, $ldap_attr);
        return $result;
    }

    /**
     * Update a user.
     * @param User $user The attributes of the user to update.
     * @return boolean true on sucess, false on failure.
     */
    public function updateUser($user) {
        $username = $user->getUsername();
        $email = $user->getEmail();
        $firstname = $user->getFirstName();
        $surname = $user->getSurname();
        $displayname = $user->getDisplayName();

        // Surname is a required attribute, if blank default to username
        if ($surname == '') {
            $surname = $username;
        }
        // Display name defaults to First Last
        if (trim($displayname) == "") {
            $displayname = $firstname;
            if ($firstname != '' && $surname != '') {
                $displayname .= " ";
            }
            $displayname .= $surname;
        }

        $attributes = $this->ldap_attributes;
        $userdn = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;

        $user_info = array();
        $user_info[$attributes->userEmailAttr] = $email;
        $user_info[$attributes->userFirstNameAttr] = $firstname;
        $user_info[$attributes->userSurnameAttr] = $surname;
        $user_info[$attributes->userDisplayNameAttr] = $displayname;
        return ldap_mod_replace($this->ldap_conn, $userdn, $user_info);

    }

    /**
     * Add a group to the LDAP Directory.
     * @param Group $group The group to add.
     * @return boolean true on sucess, false on failure.
     */
    public function addGroup($group) {

        $attributes = $this->ldap_attributes;

        $name = $group->getName();
        $description = $group->getDescription();
        $users = $group->getUsers();

        $ldap_attr = array();
        $ldap_attr["objectclass"][0] = "top";
        $ldap_attr["objectclass"][1] = $attributes->groupObjectClass;
        $ldap_attr[$attributes->groupNameAttr] = $name;
        $ldap_attr[$attributes->groupDescAttr] = $description;
        $ldap_attr[$attributes->groupMemberAttr] = '';

        /* if (count($users) > 0) {
            $ldap_attr[$attributes->groupMemberAttr] = array();
            foreach ($users as $user) {
                $userdn = $attributes->usernameAttr . "=" . $user . "," . $attributes->userDN . "," . $attributes->baseDN;
                $ldap_attr[$attributes->groupMemberAttr][] = $userdn;
            }
        } */

        $groupdn = $attributes->groupNameAttr . "=" . $name . "," . $attributes->groupDN . "," . $attributes->baseDN;
        $result = @ldap_add($this->ldap_conn, $groupdn, $ldap_attr);
        return $result;

    }

    /**
     * Delete a user from the LDAP directory.
     * @param string $username The username to delete.
     * @return boolean true on success, false on failure
     */
    public function deleteUser($username) {
        $attributes = $this->ldap_attributes;
        $userdn = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;
        $result = @ldap_delete($this->ldap_conn, $userdn);
        return $result;
    }

    /**
     * Delete a group from the LDAP directory.
     * @param string $groupname The name of the group to delete.
     * @return boolean true on success, false on failure.
     */
    public function deleteGroup($groupname) {
        $attributes = $this->ldap_attributes;
        $groupdn = $attributes->groupNameAttr . "=" . $groupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        $result = @ldap_delete($this->ldap_conn, $groupdn);
        return $result;
    }
    /**
     * Authenticate a user.
     * @param string $user The username to authenticate.
     * @param string $password The user password.
     * @return boolean true on success, false on failure.
     */
    public function authenticate($user, $password) {
        if (empty($user) || empty($password)) {
            return false;
        }
        $auth = false;
        $attributes = $this->ldap_attributes;
        $userdn = $attributes->usernameAttr . "=" . $user . "," . $attributes->userDN . "," . $attributes->baseDN;
        $user_exists = ldap_search($this->ldap_conn,
                $attributes->userDN . "," . $attributes->baseDN,
                $attributes->usernameAttr . "=" . $user);
        if ($user_exists !== false) {
            $auth = @ldap_bind($this->ldap_conn,
                    $userdn,
                    $password);
        }

        $this->bind();
        return $auth === true;
    }

    /**
     * Change a user password.
     * @param string $username
     * @param string $password
     * @return boolean True on success, false on failure.
     */
    function  changePassword($username, $password) {
        if (empty($username) || empty($password)) {
            return false;
        }
        $attributes = $this->ldap_attributes;
        $userdn = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;

        $user_info = array();
        $user_info[$attributes->userPasswordAttr] = $password;

        return @ldap_mod_replace($this->ldap_conn, $userdn, $user_info);
    }

    /**
     * Get a Group.
     * @param string $groupname The name of the group.
     * @return Group|boolean false A Group object or false if a group with the given group name doesn't exist.
     */
    public function getGroup($groupname) {
        $groups = array();
        $attributes = $this->ldap_attributes;
        $search = "(" . $attributes->groupNameAttr . "=" . $groupname . ")";
        $entry = $attributes->groupDN . "," . $attributes->baseDN;
        $group_results = ldap_search($this->ldap_conn, $entry, $search);
        if ($group_results !== false) {
            $entries = ldap_get_entries($this->ldap_conn, $group_results);
            if ($entries["count"] > 0) {
                foreach($entries as $entry) {
                    if (is_array($entry)) {
                        $name = isset($entry[$attributes->groupNameAttr])?$entry[$attributes->groupNameAttr][0]:"Unnamed Group";
                        $description = isset($entry[$attributes->groupDescAttr])?$entry[$attributes->groupDescAttr][0]:"";
                        // Get Users
                        $users = array();
                        $subgroups = array();
                        $users_list = $entry[$attributes->groupMemberAttr];
                        for ($i=0;$i<$users_list["count"];$i++) {
                            $user = $users_list[$i];
                            if ($user != "") {
                                if (strpos($user, $attributes->userDN.",".$attributes->baseDN) !== false) {
                                    $user = str_replace(",".$attributes->userDN.",".$attributes->baseDN, "", $user);
                                    $user = str_replace($attributes->groupNameAttr."=", "", $user);
                                    if ($this->userExists($user)) {
                                        $users[$user] = $user;
                                    }
                                }
                                if (strpos($user, $attributes->groupDN.",".$attributes->baseDN) !== false) {
                                    $user = str_replace(",".$attributes->groupDN.",".$attributes->baseDN, "", $user);
                                    $user = str_replace($attributes->groupNameAttr."=", "", $user);
                                    $subgroups[$user] = $user;
                                }
                            }
                        }
                        if ($name == $groupname) {
                            $group = new Group();
                            $group->setName($name);
                            $group->setDescription($description);
                            $group->setSubgroups($subgroups);
                            $group->setUsers($users);
                            return $group;
                        }
                    }
                }
            }
        }
        return false;
    }

    
    /**
     * Returns an array of Group names keyed by group name.
     * @return array an group names (strings) keyed by group names.
     */
    public function getGroupNames() {
        $groups = $this->getGroups();
        if (!empty($groups)) {
            foreach ($groups as $groupname=>$group) {
                $groups[$groupname] = $groupname;
            }
        }
        return $groups;
    }

    /**
     * Returns an array of Groups keyed by group name.
     * @return array an array of Group objects.
     */
    public function getGroups() {
        $groups = array();
        $attributes = $this->ldap_attributes;
        $search = "(objectclass=" . $attributes->groupObjectClass . ")";
        $entry = $attributes->groupDN . "," . $attributes->baseDN;
        $group_results = ldap_search($this->ldap_conn, $entry, $search);

        if ($group_results !== false) {
            $entries = ldap_get_entries($this->ldap_conn, $group_results);
            if ($entries["count"] > 0) {
                foreach($entries as $entry) {
                    if (is_array($entry)) {
                        $name = isset($entry[$attributes->groupNameAttr])?$entry[$attributes->groupNameAttr][0]:"Unnamed Group";
                        $description = isset($entry[$attributes->groupDescAttr])?$entry[$attributes->groupDescAttr][0]:"";
                        // Get Users
                        $users = array();
                        $subgroups = array();
                        $users_list = $entry[$attributes->groupMemberAttr];
                        for ($i=0;$i<$users_list["count"];$i++) {
                            $user = $users_list[$i];
                            if ($user != "") {
                                if (strpos($user, $attributes->userDN.",".$attributes->baseDN) !== false) {
                                    $user = str_replace(",".$attributes->userDN.",".$attributes->baseDN, "", $user);
                                    $user = str_replace($attributes->groupNameAttr."=", "", $user);
                                    if ($this->userExists($user)) {
                                        $users[$user] = $user;
                                    }
                                }
                                if (strpos($user, $attributes->groupDN.",".$attributes->baseDN) !== false) {
                                    $user = str_replace(",".$attributes->groupDN.",".$attributes->baseDN, "", $user);
                                    $user = str_replace($attributes->groupNameAttr."=", "", $user);                                    
                                    $subgroups[$user] = $user;
                                }
                            }
                        }
                        $group = new Group();
                        $group->setName($name);
                        $group->setDescription($description);
                        $group->setUsers($users);
                        $group->setSubgroups($subgroups);
                        $groups[$name] = $group;
                    }
                }
            }
        }
        return $groups;
    }
    
    /**
     * Returns an array of Users keyed by username.
     * @return array an array of Users.
     */
    public function getUsers() {
        $users = array();
        $attributes = $this->ldap_attributes;
        $search = "(objectclass=" . $attributes->userObjectClass . ")";
        $dn = $attributes->userDN . "," . $attributes->baseDN;
        $results = ldap_search($this->ldap_conn, $dn, $search);
        if ($results !== false) {
            $entries = ldap_get_entries($this->ldap_conn, $results);
            if ($entries["count"] > 0) {
                foreach($entries as $entry) {
                    if (is_array($entry)) {
                        $username = $entry[$attributes->usernameAttr][0];
                        $email = $entry[$attributes->userEmailAttr][0];
                        $firstname = $entry[$attributes->userFirstNameAttr][0];
                        $surname = $entry[$attributes->userSurnameAttr][0];
                        $displayname = $entry[$attributes->userDisplayNameAttr][0];
                        $user = new User();
                        $user->setUsername($username);
                        $user->setEmail($email);
                        $user->setFirstName($firstname);
                        $user->setSurname($surname);
                        $user->setDisplayName($displayname);
                        $users[$username] = $user;
                    }
                }
            }
        }
        return $users;
    }

    /**
     * Get a user by username.
     * @param string $username The username to get.
     * @return boolean|User false if the user doesn't exist, a User object otherwise.
     */
    public function getUser($username) {
        $attributes = $this->ldap_attributes;
        $search = "(" . $attributes->usernameAttr . "=" . $username . ")";
        $dn = $attributes->userDN . "," . $attributes->baseDN;
        $results = ldap_search($this->ldap_conn, $dn, $search);
        if ($results !== false) {
            $entries = ldap_get_entries($this->ldap_conn, $results);
            $entry = isset($entries[0])?$entries[0]:false;
            if (is_array($entry)) {
                $username = $entry[$attributes->usernameAttr][0];
                $email = $entry[$attributes->userEmailAttr][0];
                $firstname = $entry[$attributes->userFirstNameAttr][0];
                $surname = $entry[$attributes->userSurnameAttr][0];
                $displayname = $entry[$attributes->userDisplayNameAttr][0];
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setFirstName($firstname);
                $user->setSurname($surname);
                $user->setDisplayName($displayname);
                return $user;
            }
        }
        return false;
    }
    
    /**
     * Check to see if a user exists in the LDAP directory.
     * @param string $username The username to check.
     */
    public function userExists($username) {
        $attributes = $this->ldap_attributes;
        $dn = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;
        $search = "(" . $attributes->usernameAttr . "=" . $username . ")";
        $result = @ldap_search($this->ldap_conn, $dn, $search);
        if ($result === false) return false;
        $info = @ldap_get_entries($this->ldap_conn, $result);
        if ($info === false) {
            return false;
        } else {
            return $info["count"] > 0;
        }
    }

    /**
     * Check to see if a group exists.
     * @param string $groupname The group name to check.
     * @return boolean
     */
    public function groupExists($groupname) {
        $attributes = $this->ldap_attributes;
        $dn = $attributes->groupNameAttr . "=" . $groupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        $search = "(" . $attributes->groupNameAttr . "=" . $groupname . ")";
        $result = @ldap_search($this->ldap_conn, $dn, $search);
        if ($result === false) return false;
        $info = @ldap_get_entries($this->ldap_conn, $result);
        if ($info === false) {
            return false;
        } else {
            return $info["count"] > 0;
        }
    }

    /**
     * Get an array of groups the given user is a member of.
     * @param string $user
     * @return array
     */
    public function getUserGroups($user) {
        $attributes = $this->ldap_attributes;
        $dn = $attributes->groupDN . "," . $attributes->baseDN;
        $search = $attributes->groupMemberAttr . "=" . $attributes->usernameAttr . "="
                . $user . "," . $attributes->userDN . "," . $attributes->baseDN;
        $results = ldap_search($this->ldap_conn, $dn, $search);
        $groups = array();
        if ($results !== false) {
            $entries = ldap_get_entries($this->ldap_conn, $results);
            if ($entries["count"] > 0) {
                $ug = 1;
                foreach($entries as $entry) {
                    if (is_array($entry)) {
                        $name = isset($entry[$attributes->groupNameAttr])?$entry[$attributes->groupNameAttr][0]:"Unnamed Group ".$ug++;
                        $description = isset($entry[$attributes->groupDescAttr])?$entry[$attributes->groupDescAttr][0]:"";
                        $groups[$name] = $name;
                    }
                }
            }
        }
        return $groups;
    }

    /**
     * Add a user to a group.
     * @param string $username The username to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    public function addUserToGroup($username, $groupname) {
        // Check if user exists
        if (!$this->userExists($username)) return false;
        
        $attributes = $this->ldap_attributes;
        $user  = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;
        $group = $attributes->groupNameAttr . "=" . $groupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        
        $group_info = array();
        $group_info[$attributes->groupMemberAttr] = $user;
        
        return @ldap_mod_add($this->ldap_conn, $group, $group_info);
    }
    /**
     * Add a subgroup to a group.
     * @param string $subgroupname The name of the subgroup to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    public function addSubgroupToGroup($subgroupname, $groupname) {
        // Check if group exists
        if (!$this->groupExists($subgroupname)) return false;

        $attributes = $this->ldap_attributes;
        $subgroup = $attributes->groupNameAttr . "=" . $subgroupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        $group    = $attributes->groupNameAttr . "=" . $groupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        
        $group_info = array();
        $group_info[$attributes->groupMemberAttr] = $subgroup;
        
        return @ldap_mod_add($this->ldap_conn, $group, $group_info);
    }
    /**
     * Remove a user from a group.
     * @param string $username The username to remove from the group.
     * @param string $groupname The name of the group to remove the user from.
     */
    public function removeUserFromGroup($username, $groupname) {
        $attributes = $this->ldap_attributes;
        $user  = $attributes->usernameAttr . "=" . $username . "," . $attributes->userDN . "," . $attributes->baseDN;
        $group = $attributes->groupNameAttr . "=" . $groupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        
        $group_info = array();
        $group_info[$attributes->groupMemberAttr] = $user;
        
        return @ldap_mod_del($this->ldap_conn, $group, $group_info);
    }
    /**
     * Remove a subgroup from a group.
     * @param string $subgroupname The name of the subgroup to remove from the group.
     * @param string $groupname The name of the group to remove the subgroup from.
     */
    public function removeSubgroupFromGroup($subgroupname, $groupname) {
        $attributes = $this->ldap_attributes;
        $subgroup = $attributes->groupNameAttr . "=" . $subgroupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        $group    = $attributes->groupNameAttr . "=" . $groupname . "," . $attributes->groupDN . "," . $attributes->baseDN;
        
        $group_info = array();
        $group_info[$attributes->groupMemberAttr] = $subgroup;
        
        return @ldap_mod_del($this->ldap_conn, $group, $group_info);
    }
}

/**
 * LDAP Attribute Holder
 *
 * This class stores LDAP attributes and can also load them from an ini file if
 * one is provided. It converts all of the attributes to lowercase except for
 * managerMassword.
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 * @license   TBD
 */

class LDAPAttributes {
    /**
     * The LDAP connection string.
     * @var string
     */
    public $connectionString = "";
    /**
     * The LDAP directory base DN to use (dc=example,dc=com)
     * @var string
     */
    public $baseDN = "";
    /**
     * The administrative user's DN (cn=admin,dc=example,dc=com)
     * @var string
     */
    public $managerDN = "";
    /**
     * The administrative user's password.
     * @var string
     */
    public $managerPassword = "";
    /**
     * The user DN (ou=people)
     * @var string
     */
    public $userDN = "";
    /**
     * The user object username attribute (cn)
     * @var string
     */
    public $usernameAttr = "";
    /**
     * The user object first name attribute (firstName)
     * @var string
     */
    public $userFirstNameAttr = "";
    /**
     * The user object surname attribute (sn)
     * @var string
     */
    public $userSurnameAttr = "";
    /**
     * The user object display name attribute (displayName)
     * @var string
     */
    public $userDisplayNameAttr = "";
    /**
     * The user object email attribute (mail)
     * @var string
     */
    public $userEmailAttr = "";
    /**
     * The user object password attribute (password)
     * @var string
     */
    public $userPasswordAttr = "";
    /**
     * The user object type (inetOrgPerson)
     * @var string
     */
    public $userObjectClass = "";
    /**
     * The group DN (ou=groups)
     * @var string
     */
    public $groupDN = "";
    /**
     * The group object name attribure (cn)
     * @var string
     */
    public $groupNameAttr = "";
    /**
     * The group object description attribute (description)
     * @var string
     */
    public $groupDescAttr = "";
    /**
     * The group object member attribute (uniqueMember)
     * @var string
     */
    public $groupMemberAttr = "";
    /**
     * The group object class (groupOfUniqueNames)
     * @var string
     */
    public $groupObjectClass = "";

    /**
     * Load LDAP attributes from an array or filename.
     * @param array|string $options If options is an array, load the options from the array. If it's a string, treat it as a filename.
     */
    public function __construct($options) {
        // It's not an array, check if it's a file
        if (gettype($options) == "string") {
            if (is_file($options)) {
                $options = parse_ini_file($options);
            } else {
                return false;
            }
        }

        $this->connectionString = strtolower($options['connectionString']);
        $this->baseDN = strtolower($options['baseDN']);
        $this->managerDN = strtolower($options['managerDN']);
        $this->managerPassword = $options['managerPassword'];

        $this->userDN = strtolower($options['userDN']);
        $this->usernameAttr = strtolower($options['usernameAttr']);
        $this->userFirstNameAttr = strtolower($options['userFirstNameAttr']);
        $this->userSurnameAttr = strtolower($options['userSurnameAttr']);
        $this->userDisplayNameAttr = strtolower($options['userDisplayNameAttr']);
        $this->userEmailAttr = strtolower($options['userEmailAttr']);
        $this->userPasswordAttr = strtolower($options['userPasswordAttr']);
        $this->userObjectClass = strtolower($options['userObjectClass']);

        $this->groupDN = strtolower($options['groupDN']);
        $this->groupNameAttr = strtolower($options['groupNameAttr']);
        $this->groupDescAttr = strtolower($options['groupDescAttr']);
        $this->groupMemberAttr = strtolower($options['groupMemberAttr']);
        $this->groupObjectClass = strtolower($options['groupObjectClass']);
    }
}
?>
