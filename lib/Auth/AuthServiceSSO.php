<?php
namespace foundry\core\auth;

/**
 * Authentication Service Single Sign-On Support Interface
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

/**
 * Authentication Service Single Sign-On Support Interface
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
*/
interface AuthServiceSSO
{
    /**
     * Check for a single sign on token.
     * @return boolean True if logged into a SSO, false if not.
     */
    public function checkSSO();

    /**
     * Logout of the SSO system.
     */
    public function logoutSSO();
}
?>
