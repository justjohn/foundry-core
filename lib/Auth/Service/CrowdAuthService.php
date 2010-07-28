<?php
/**
 * Crowd Authentication Service Implementation
 *
 * This file contains the logic required to authenticate against an Atlassian
 * Crowd endpoint.
 *
 * This module contains code based on the Services_Atlassian_Crowd PEAR package
 * written by Luca Corbo (http://pear.php.net/packages/Services_Atlassian_Crowd)
 *
 * @package Auth
 * @author John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

/**
 * Crowd Authentication Service
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */
class CrowdAuthService implements AuthService {
    private $app_token;
    private $crowd;
    private $auth_token;
    private $sso_user;
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
            $this->crowd = new Services_Atlassian_Crowd($options);
            $this->app_token = $this->crowd->authenticateApplication();

        } catch (CrowdServiceException $exception) {
            // Unable to connect.
            throw new ServiceConnectionException("Unable to connect to the Crowd SOAP endpoint.");
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
            // authenticate
            $this->auth_token = $this->crowd->authenticatePrincipal($username, $password, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);

            // Set SSO cookie
            $result = $this->crowd->__call('getCookieInfo');
            $domain = $result->domain;
            $secure = $result->secure;

            setcookie("crowd_token_key", $this->auth_token, "7200", "/", $domain, $secure);
            return true;
        } catch (CrowdServiceException $exception) {
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
            $result = $this->crowd->__call('updatePrincipalCredential', array($username, $password));
            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
        }
        return false;

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
            $result = $this->crowd->__call('searchPrincipals', array());
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

                    // Cache user object
                    $users[$user->getUsername()] = $user;
                }
            }
            return($users);
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('findPrincipalWithAttributesByName', array($username));
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
            $user->setDisplayName($attributes["displayName"]);
            $user->setFirstName($attributes["givenName"]);
            $user->setSurname($attributes["sn"]);
            return($user);
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
        }
        return false;
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
                'attributes' => array(
                    'mail' => $user->getEmail(),
                    'displayName' => $user->getDisplayName(),
                    'givenName' => $user->getFirstName(),
                    'sn' => $user->getSurname()
                )
            );
            $result = $this->crowd->__call('addPrincipal', array($user_array));

            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('removePrincipal', array($username));

            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('searchGroups', array());
            $group_names = $result->SOAPGroup;
            $groups = array();
            if (is_array($group_names) && !empty($group_names)) {
                foreach($group_names as $group_info) {
                    $groupname = $group_info->name;
                    $group = new Group();
                    $group->setName($groupname);
                    $group->setDescription($group_info->description);
                    $members = $group_info->members;
                    if (isset($members->string)) {
                        $members = $members->string;
                        if (is_array($members)) {
                            $group->setUsers($members);
                        } else {
                            $group->setUsers(array($members));
                        }
                    }
                    $groups[$groupname] = $group;
                }
            }
            return($groups);
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('findGroupWithAttributesByName', array($groupname));
            $attribute_objs = $result->attributes->SOAPAttribute;
            $attributes = array();
            if (count($attribute_objs) > 0) {
                foreach ($attribute_objs as $attribute) {
                    $attributes[$attribute->name] = $attribute->values->string;
                }
            }
            $group = new Group();
            $group->setName($groupname);
            $group->setDescription($attributes["description"]);
            $group->setUsers($attributes["users"]);
            return($group);
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
                "description" => $group->getDescription(),
                "members" => array($group->getUsers())
            );
            $result = $this->crowd->__call('addGroup', array($group_array));

            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('removeGroup', array($groupname));

            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
        }
        return false;
    }

    /**
     * Get an array of groups the given user is a member of.
     * @param string $user
     * @return array
     */
    public function getUserGroups($user) {
        if ($user == '') {
            return false;
        }
        try {
            // get user
            $result = $this->crowd->__call('findGroupMemberships', array($user));
            $group_names = $result->string;
            $groups = array();
            if (is_array($group_names) && !empty($group_names)) {
                foreach($group_names as $groupname) {
                    //$group = $this->getGroup($groupname);
                    $groups[$groupname] = $group;
                }
            }
            return($groups);
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('addPrincipalToGroup', array($username, $groupname));
            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
            $result = $this->crowd->__call('removePrincipalFromGroup', array($username, $groupname));
            return true;
        } catch (CrowdServiceException $exception) {
            print($exception->getMessage()."\n");
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
                $result = $this->crowd->__call('findPrincipalByToken', array($crowd_key));
                if ($result !== false) {
                    $this->sso_user = $result->name;
                    return $this->sso_user;
                }
            } catch (CrowdServiceException $exception) {
                //print($exception->getMessage()."\n");
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
                $result = $this->crowd->__call('invalidatePrincipalToken', array($crowd_key));
                return $result;
            } catch (CrowdServiceException $exception) {
                //print($exception->getMessage()."\n");
                return false;
            }
        }
    }
}


/**
 * Include the modified Services_Atlassian_Crowd class.
 */

/**
 * This is a modified version of the Services_Atlassian_Crowd PEAR package
 * updated to support additional operations on the Crowd API.
 *
 * ----------------------------------------------------------------------------
 *
 * Services_Atlassian_Crowd is a package to use Atlassian Crowd from PHP
 *
 * Crowd is a web-based single sign-on (SSO) tool
 * that simplifies application provisioning and identity management.
 *
 * This package is derived from the PHP Client Library for Atlassian Crowd
 * class written by Infinite Campus, Inc.
 *
 * PHP version 5
 *
 * Copyright (C) 2008 Infinite Campus Inc., 2008 Luca Corbo
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Services
 * @package   Services_Atlassian_Crowd
 * @author    Infinite Campus, Inc.
 * @author    Luca Corbo <lucor@php.net>
 * @copyright 2008 Infinite Campus Inc., 2008 Luca Corbo
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @link      http://pear.php.net/packages/Services_Atlassian_Crowd
 * @link      http://www.atlassian.com/software/crowd
 * @link      http://confluence.atlassian.com/display/CROWD/SOAP+API
 * @link      http://confluence.atlassian.com/display/CROWDEXT/Integrate+Crowd+with+PHP
 */


/**
 * Class to use Crowd API from PHP
 *
 * @package   Auth
 * @author    Infinite Campus, Inc.
 * @author    Luca Corbo <lucor@php.net>
 * @copyright 2008 Infinite Campus Inc., 2008 Luca Corbo
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @link      http://pear.php.net/packages/Services_Atlassian_Crowd
 * @link      http://www.atlassian.com/software/crowd
 * @link      http://confluence.atlassian.com/display/CROWD/SOAP+API
 * @link      http://confluence.atlassian.com/display/CROWDEXT/Integrate+Crowd+with+PHP
 */
class Services_Atlassian_Crowd
{

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

    /**
     * The options required in configuration
     *
     * @see __construct
     * @var array
     */
    private $_crowd_required_options = array('app_name', 'app_credential', 'service_url');

    /**
     * Create an application client using the passed in configuration parameters.
     *
     * Available options are:
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
     * @param array $options optional. An array of options used to connect to Crowd.
     *
     * @throws CrowdServiceException if there is an error communicating
     *                                            with the Crowd security server.
     */
    public function __construct($options)
    {
        //Check for required parameters
        foreach ($this->_crowd_required_options as $option) {
            if (!array_key_exists($option, $options)) {
                $exception_message = $option . ' is required!';
                throw new CrowdServiceException($exception_message);
            }
        }

        $this->crowd_config = $options;

        // Create the Crowd SOAP client
        try {
            $this->crowd_client = new SoapClient($this->crowd_config['service_url']);
        } catch (SoapFault $fault) {
            $exception_message = 'Unable to connect to Crowd. Verify the service_url ' .
                                 'property is defined and Crowd is running.';
            throw new CrowdServiceException($exception_message . "\n" .
                                                         $fault->getMessage(),
                                                         $fault);
        }
    }

    /**
     * Authenticates an application client to the Crowd security server.
     *
     * @return string the application token
     * @throws CrowdServiceException if there is an error communicating
     *                                            with the Crowd security server.
     */
    public function authenticateApplication()
    {
        $credential = array('credential' => $this->crowd_config['app_credential']);
        $name       = $this->crowd_config['app_name'];
        $param      = array('in0' => array('credential' => $credential,
                                           'name'       => $name));

        $exception_message = 'Unable to login to Crowd. Verify the app_name and' .
                             'app_credential properties are defined and valid.';
        try {
            $resp = $this->crowd_client->authenticateApplication($param);

            $this->crowd_app_token = $resp->out->token;

            if (empty($this->crowd_app_token)) {
                throw new CrowdServiceException($exception_message . "\n" .
                                                             $fault->getMessage());
            }
        } catch (SoapFault $fault) {
            throw new CrowdServiceException($exception_message . "\n" .
                                                         $fault->getMessage(), $fault);
        }

        return $this->crowd_app_token;
    }

    /**
     * Authenticates a principal to the Crowd security server
     * for the application client.
     *
     * @param string $name           The username to authenticate
     * @param string $credential     The password of the user to authenticate
     * @param string $user_agent     The user agent
     * @param string $remote_address The remote address
     *
     * @return string the principal token
     * @throws CrowdServiceException if there is an error communicating
     *                                            with the Crowd security server.
     */
    public function authenticatePrincipal($name, $credential, $user_agent, $remote_address)
    {

        // Build the parameter used to authenticate the principal
        $param = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                      'token' => $this->crowd_app_token),
                       'in1' => array('application' => $this->crowd_config['app_name'],
                                      'credential'  => array('credential' => $credential),
                                      'name'        => $name,
                                      'validationFactors' => array(array('name'  => 'User-Agent',
                                                                         'value' => $user_agent),
                                                                   array('name'  => 'remote_address',
                                                                         'value' => $remote_address))));

        // Attempt to authenticate the user (principal) via Crowd.
        try {
            $resp = $this->crowd_client->authenticatePrincipal($param);
        } catch (SoapFault $fault) {
            $message = $fault->getMessage();
            $code = $fault->getCode();
            throw new CrowdServiceException($message, $code);

        }

        // Get the principal's token
        return $resp->out;
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
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0]);
                break;
            case 'updatePrincipalCredential':
                $params = array('in0' => array('name'  => $this->crowd_config['app_name'],
                                               'token' => $this->crowd_app_token),
                                'in1' => $args[0],
                                'in2' => array("credential" => $args[1],
                                               "encryptedCredential" => "false") );
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