<?php
/**
 * Authentication Service Single Sign-On Support Interface
 *
 * @package   foundry\core\auth
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 */
namespace foundry\core\auth;


/**
 * Authentication Service Single Sign-On Support Interface
 *
 * @package   foundry\core\auth
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
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
