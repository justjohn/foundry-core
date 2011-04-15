<?php
/**
 * Authentication Service Single Sign-On Support Interface
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Auth;


/**
 * Authentication Service Single Sign-On Support Interface
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
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
