<?php
/**
 * Atlassian Crowd Authentication Service Implementation
 *
 * This file contains the logic required to authenticate against an Atlassian
 * Crowd SOAP endpoint.
 *
 * This module contains code based on the Services_Atlassian_Crowd PEAR package
 * written by Luca Corbo (http://pear.php.net/packages/Services_Atlassian_Crowd)
 * licensed under the http://www.apache.org/licenses/LICENSE-2.0 Apache License
 *
 * @package Auth
 * @author John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke, 2008 Infinite Campus Inc., 2008 Luca Corbo
 * @link      http://pear.php.net/packages/Services_Atlassian_Crowd
 * @link      http://www.atlassian.com/software/crowd
 * @link      http://confluence.atlassian.com/display/CROWD/SOAP+API
 * @link      http://confluence.atlassian.com/display/CROWDEXT/Integrate+Crowd+with+PHP
 */

/**
 * Crowd Authentication Service
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 * @link      http://pear.php.net/packages/Services_Atlassian_Crowd
 * @link      http://www.atlassian.com/software/crowd
 * @link      http://confluence.atlassian.com/display/CROWD/SOAP+API
 * @link      http://confluence.atlassian.com/display/CROWDEXT/Integrate+Crowd+with+PHP
 */
class CrowdAuthService implements AuthService, AuthServiceSSO {

    /**
     * The Crowd SOAP client
     *
     * @var object
     */
    protected $crowd_client;

    /**
     * Array contains the configuration parameters
     *
     * @var array
     */
    protected $crowd_config;

    /**
     * The Crowd application token
     *
     * @var string
     */
    protected $crowd_app_token;

    private $auth_token;
    private $sso_user;

    /**
     * The required configuration options to instantiate a CrowdAuthService.
     *
     * Required options are:
     *
     * - string  app_name:  The username which the application will use when it
     *                      authenticates against the Crowd framework as a client.
     *
     * - string  app_credential: The password which the application will use when it
     *                           authenticates against the Crowd framework
     *                           as a client.
     *
     * - string  service_url: The SOAP WSDL URL for Crowd
     *
     */
    public static $required_options = array("app_name",
                                            "app_credential",
                                            "service_url");

    /**
     * Connect to a Crowd SOAP endpoint.
     *
     * @param array Options for connecting to the Crowd SOAP endpoint.
     * @throws ServiceValidationException All required options are not present.
     */
    public function __construct($options) {
        Service::validate($options, self::$required_options);
        try {
            $access_exception = "Unable to connect to the Crowd SOAP endpoint. ".
                                "Check configuration and ensure Crowd is running and accessable.";
            $this->crowd_config = $options;

            // Create the Crowd SOAP client
            $this->crowd_client = new SoapClient($options['service_url']);
            $credential = array('credential' => $options['app_credential']);

            $name       = $options['app_name'];
            $param      = array('in0' => array('credential' => $credential,
                                               'name'       => $name));

            $resp = $this->crowd_client->authenticateApplication($param);
            $this->crowd_app_token = $resp->out->token;

            if (empty($this->crowd_app_token)) {
                throw new ServiceConnectionException($access_exception);
            }

        } catch (SoapFault $fault) {
            // Unable to connect.
            throw new ServiceConnectionException($access_exception);
        }
    }

    /**
     * Authenticate a user.
     * @param string $user
     * @param string $password
     * @return boolean
     */
    public function authenticate($username, $password) {
        try {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $remote_address = $_SERVER['REMOTE_ADDR'];

            // Build the parameter used to authenticate the principal
            $param = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                          'token' => $this->crowd_app_token),
                           'in1' => array('application' => $this->crowd_config['app_name'],
                                          'credential'  => array('credential' => $password),
                                          'name'        => $username,
                                          'validationFactors' => array(array('name'  => 'User-Agent',
                                                                             'value' => $user_agent),
                                                                       array('name'  => 'remote_address',
                                                                             'value' => $remote_address))));

            // Attempt to authenticate the user (principal) via Crowd.
            $resp = $this->crowd_client->authenticatePrincipal($param);

            // Get the principal's token
            $this->auth_token = $resp->out;

            // Set SSO cookie
            $result = $this->__call('getCookieInfo');
            $domain = $result->domain;
            $secure = $result->secure;

            setcookie("crowd_token_key", $this->auth_token, "7200", "/", $domain, $secure);
            return true;
        } catch (SoapFault $fault) {
            //print($exception->getMessage());
            // Unable to connect.
            return false;
        }
    }

    /**
     * Change a user password.
     * @param string $user
     * @param string $password
     */
    public function changePassword($username, $password) {
        try {
            $result = $this->__call('updatePrincipalCredential', array($username, $password));
            return true;
        } catch (CrowdServiceException $exception) {
        }
        return false;

    }

    /**
     * Check to see if a group exists.
     * @param string $groupname The group name to check.
     * @return boolean
     */
    public function groupExists($groupname) {
        return ($this->getGroup($groupname) instanceOf Group);
    }

    /**
     * Check to see if a user exists.
     * @param string $username The username to check.
     * @return boolean
     */
    public function userExists($username) {
        return ($this->getUser($username) instanceOf User);
    }

    /**
     * Returns an array of all the users or arrays keyed by username.
     * @return array|boolean
     */
    public function getUsers() {
        try {
            $result = $this->__call('searchPrincipals', array());
            $user_names = $result->SOAPPrincipal;
            $users = array();
            if (is_array($user_names) && !empty($user_names)) {
                foreach($user_names as $user_info) {
                    $username = $user_info->name;
                    $attribute_objs = $user_info->attributes->SOAPAttribute;
                    $attributes = array();
                    if (count($attribute_objs) > 0) {
                        foreach ($attribute_objs as $attribute) {
                            $attributes[$attribute->name] = $attribute->values->string;
                        }
                    }

                    $user = new User();
                    $user->setUsername($username);
                    $user->setEmail($attributes["mail"]);
                    $user->setDisplayName($attributes["displayName"]);
                    $user->setFirstName($attributes["givenName"]);
                    $user->setSurname($attributes["sn"]);
                    
                    $users[$user->getUsername()] = $user;
                }
            }
            return($users);
        } catch (CrowdServiceException $exception) {
        }
        return false;

    }

    /**
     * Get a user's information as an array.
     * @param string $username
     * @return array
     */
    public function getUser($username) {
        if ($username == '') {
            return false;
        }
        try {
            // get user
            $result = $this->__call('findPrincipalWithAttributesByName', array($username));
            $attribute_objs = $result->attributes->SOAPAttribute;
            $attributes = array();
            if (count($attribute_objs) > 0) {
                foreach ($attribute_objs as $attribute) {
                    $attributes[$attribute->name] = $attribute->values->string;
                }
            }
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($attributes["mail"]);
            $user->setFirstName($attributes["givenName"]);
            $user->setSurname($attributes["sn"]);
            $user->setDisplayName($attributes["displayName"]);
            return($user);
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Update a user.
     * @param User $user The attributes of the user to update.
     * @return boolean true on sucess, false on failure.
     */
    public function updateUser($user) {
        try {
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

            $user_array = array($username, 'mail', $email);
            $result = $this->__call('updatePrincipalAttribute', $user_array);

            $user_array = array($username, 'givenName', $firstname);
            $result = $this->__call('updatePrincipalAttribute', $user_array);

            $user_array = array($username, 'sn', $surname);
            $result = $this->__call('updatePrincipalAttribute', $user_array);

            $user_array = array($username, 'displayName', $displayname);
            //$result = $this->__call('updatePrincipalAttribute', $user_array);

            return true;
        } catch (CrowdServiceException $exception) {
            print_a($exception);
        }
        return false;

    }

    private function soap_attribute($name, $value) {
        return array('name'  => $name,
                     'values' => array($value));

    }

    /**
     * Add a user.
     * @param User $user The details of the user to add.
     * @param string $password The user's password.
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password) {
        try {
            $user_array = array(
                'name' => $user->getUsername(),
                'active' => true,
                'attributes' => array(
                    $this->soap_attribute('mail', $user->getEmail()),
                    $this->soap_attribute('givenName', $user->getFirstName()),
                    $this->soap_attribute('sn', $user->getSurname()),
                    $this->soap_attribute('displayName', $user->getDisplayName())
                )
            );
            $result = $this->__call('addPrincipal', array($user_array, $password));

            return true;
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Delete a user.
     * @param string $username The username to delete.
     * @return boolean true on success, false on failure
     */
    public function deleteUser($username) {
        try {
            $result = $this->__call('removePrincipal', array($username));

            return true;
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Returns an array of all the groups keyed by group name.
     * @return array|boolean
     */
    public function getGroupNames() {
        try {
            // get user
            $result = $this->__call('searchGroups', array());
            $group_names = $result->SOAPGroup;
            $groups = array();
            if (is_array($group_names) && !empty($group_names)) {
                foreach($group_names as $group_info) {
                    $groupname = $group_info->name;
                    $groups[$groupname] = $groupname;
                }
            }
            return($groups);
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Returns an array of all the groups keyed by group name.
     * @return array|boolean
     */
    public function getGroups() {
        try {
            // get user
            $result = $this->__call('searchGroups', array());
            $group_names = $result->SOAPGroup;
            $groups = array();
            if (is_array($group_names) && !empty($group_names)) {
                foreach($group_names as $group_info) {
                    $groupname = $group_info->name;
                    $groups[$groupname] = $this->getGroup($groupname);;
                }
            }
            return($groups);
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Get a group's members.
     * @param array $groupname
     * @return array
     */
    public function getGroup($groupname) {
        if ($groupname == '') {
            return false;
        }
        try {
            // get user
            $result = $this->__call('findGroupWithAttributesByName', array($groupname));
            $description = $result->description;
            $members = $result->members;
            $users = array();
            if (empty($members->string)) {
                // Do nothing, no members
            } else if (is_string($members->string)) {
                $users[$members->string] = $members->string;

            } else if (is_array($members->string)) {
                foreach ($members->string as $user) {
                    $users[$user] = $user;
                }
            }
            $group = new Group();
            $group->setName($groupname);
            $group->setDescription($description);
            $group->setUsers($users);
            return($group);
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Add a group.
     * @param Group $group The group to add.
     * @return boolean true on sucess, false on failure.
     */
    public function addGroup($group) {
        try {
            $group_array = array(
                "name" => $group->getName(),
                "members" => array($group->getUsers()),
                "description" => $group->getDescription()
            );
            $result = $this->__call('addGroup', array($group_array));

            return true;
        } catch (CrowdServiceException $exception) {
            //print_a($exception->getMessage());
        }
        return false;
    }

    /**
     * Delete a group.
     * @param string $groupname The name of the group to delete.
     * @return boolean true on success, false on failure.
     */
    public function deleteGroup($groupname) {
        try {
            $result = $this->__call('removeGroup', array($groupname));

            return true;
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Get an array of groups the given user is a member of.
     * @param string $username
     * @return array
     */
    public function getUserGroups($username) {
        if ($username == '') {
            return false;
        }
        try {
            // get user
            $result = $this->__call('findGroupMemberships', array($username));
            $group_names = $result->string;
            $groups = array();
            if (!empty($group_names)) {
                if (is_string($group_names)) {
                    $groups[$group_names] = $group_names;
                } else if (is_array($group_names)) {
                    foreach($group_names as $groupname) {
                        $groups[$groupname] = $groupname;
                    }
                }
            }
            return($groups);
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    
    /**
     * Add a user to a group.
     * @param string $username The username to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    public function addUserToGroup($username, $groupname) {
        try {
            $result = $this->__call('addPrincipalToGroup', array($username, $groupname));
            return true;
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }

    /**
     * Remove a user from a group.
     * @param string $username The username to remove from the group.
     * @param string $groupname The name of the group to remove the user from.
     */
    public function removeUserFromGroup($username, $groupname) {
        try {
            $result = $this->__call('removePrincipalFromGroup', array($username, $groupname));
            return true;
        } catch (CrowdServiceException $exception) {
        }
        return false;
    }
    
    /**
     * Add a subgroup to a group.
     * @param string $subgroupname The name of the subgroup to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    //public function addSubgroupToGroup($subgroupname, $groupname);

    /**
     * Remove a subgroup from a group.
     * @param string $subgroupname The name of the subgroup to remove from the group.
     * @param string $groupname The name of the group to remove the subgroup from.
     */
    //public function removeSubgroupFromGroup($subgroupname, $groupname);
    
    
    /**
     * Check for a single sign on token.
     * @return boolean True if logged into a SSO, false if not.
     */
    public function checkSSO() {
        if (isset($_COOKIE['crowd_token_key'])) {
            $crowd_key = $_COOKIE['crowd_token_key'];
            try {
                $result = $this->__call('findPrincipalByToken', array($crowd_key));
                if ($result !== false) {
                    $this->sso_user = $result->name;
                    return $this->sso_user;
                }
            } catch (CrowdServiceException $exception) {
            }
        }
        return false;
    }

    /**
     * Logout of the SSO system.
     */
    public function logoutSSO() {
        if (isset($_COOKIE['crowd_token_key'])) {
            $crowd_key = $_COOKIE['crowd_token_key'];
            try {
                $result = $this->__call('invalidatePrincipalToken', array($crowd_key));
                return $result;
            } catch (CrowdServiceException $exception) {
                return false;
            }
        }
    }

    /**
     *  Calls a remote method
     *
     * @param string $method The remote method to call
     * @param mixed  $args   The parameters to use with remote method
     *
     * @return object | true
     * @throws CrowdServiceException if there is an error communicating
     *                                            with the Crowd security server.
     *
     * @method    object isValidPrincipalToken(array($princ_token, $user_agent, $remote_address))
     *                    Determines if the principal's current token is still valid in Crowd.
     * @method    boolean invalidatePrincipalToken(string $princ_token)
     *                    Invalidates a token for for this principal for all application clients in Crowd.
     * @method    object findPrincipalByToken(string $princ_token)
     *                   Finds a principal by token.
     * @method    object findGroupMemberships(string $princ_name)
     *                   Finds all of the groups the specified principal is in.
     *
     */

    public function __call($method, $args='')
    {
        if (!is_array($args)) {
            $args[0] = $args;
        }

        //Supported methods of Crowd's API
        switch ($method) {
            case 'findAllRoleNames':
            case 'findAllGroupNames':
            case 'findAllPrincipalNames':
            case 'getCookieInfo':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token));
                break;
            case 'findGroupMemberships':
            case 'findPrincipalByToken':
            case 'invalidatePrincipalToken':
            case 'findPrincipalWithAttributesByName':
            case 'findGroupWithAttributesByName':
            case 'searchPrincipals':
            case 'searchGroups':
            case 'removePrincipal':
            case 'addGroup':
            case 'removeGroup':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0]);
                break;
            case 'addPrincipal':
            case 'updatePrincipalCredential':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0],
                                'in2' => array("credential" => $args[1],
                                               "encryptedCredential" => false));
                break;
            case 'isValidPrincipalToken':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0],
                                'in2' => array(array('name'  => 'User-Agent',
                                                     'value' => $args[1]),
                                               array('name'  => 'remote_address',
                                                     'value' => $args[2])));
                break;
            case 'updatePrincipalAttribute':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0],
                                'in2' => $this->soap_attribute($args[1], $args[2]));
                break;
            case 'addPrincipalToGroup':
            case 'removePrincipalFromGroup':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0],
                                'in2' => $args[1] );

                break;
            default:
                throw new CrowdServiceException(
                    'Method (' . $method . ') is not implemented'
                );
            break;
        }

        try {
            $resp = $this->crowd_client->$method($params);
            if (isset($resp->out)) {
                return $resp->out;
            } else {
                return true;
            }
        } catch (SoapFault $fault) {
            //print_a($params);
            //print_a($fault->getMessage());
            throw new CrowdServiceException($fault->getMessage(),
                                            $fault->getCode());
        }
    }
}


/**
 * Crowd Service Exception Class
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */
class CrowdServiceException extends CoreException {}

?>