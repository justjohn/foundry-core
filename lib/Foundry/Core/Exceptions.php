<?php
/**
 * Exceptions used in Foundry Core components.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Exceptions;

/**
 * A general CoreException class that provides a base for all Foundry Core
 *  exceptions.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
abstract class CoreException extends \Exception
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code
    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown

    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }
}


/**
 * An exception class for missing required options.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class ServiceValidationException extends CoreException {}
/**
 * An exception class for service connection errors.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class ServiceConnectionException extends CoreException {}
/**
 * An exception class for loading service class errors.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class ServiceLoadException extends CoreException {}
/**
 * An exception for calling methods that don't exist in data models.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class MethodDoesNotExistException extends CoreException {}
/**
 * An exception for creating model classes that don't exist.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class ClassDoesNotExistException extends CoreException {}
/**
 * An exception for calling fields that don't exist in data models.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class FieldDoesNotExistException extends CoreException {}
/**
 * An exception for classes unable to load model classes.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
class ModelDoesNotExistException extends CoreException {}
?>
