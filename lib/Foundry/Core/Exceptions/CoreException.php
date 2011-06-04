<?php
/**
 * Exceptions used in Foundry Core components.
 * 
 * @category  Foundry-Core
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
 * @category  Foundry-Core
 * @package   Foundry\Core\Exceptions
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
abstract class CoreException extends \Exception
{
    protected $message = 'Unknown exception';
    protected $code    = 0;
    protected $file;
    protected $line;

    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return "<pre>" .
                  get_class($this) . ": {$this->message}\n{$this->file}({$this->line})\n\n"
                                   . "{$this->getTraceAsString()}"
             . "</pre>";
    }
}
?>
