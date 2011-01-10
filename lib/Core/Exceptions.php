<?php
// Based on code from http://us2.php.net/exceptions by ask@nilpo.com

interface IException
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formated string of trace

    /* Overrideable methods inherited from Exception class */
    public function __toString();                 // formated string for display
    public function __construct($message = null, $code = 0);
}

abstract class CoreException extends Exception implements IException
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
 */
class ServiceValidationException extends CoreException {}
/**
 * An exception class for service connection errors.
 */
class ServiceConnectionException extends CoreException {}
/**
 * An exception class for loading service class errors.
 */
class ServiceLoadException extends CoreException {}
/**
 * An exception for calling methods that don't exist in data models.
 */
class MethodDoesNotExistException extends CoreException {}
/**
 * An exception for calling fields that don't exist in data models.
 */
class FieldDoesNotExistException extends CoreException {}
?>