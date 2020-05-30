<?php

class ExceptionHandling {
    private static $uncaught_exception = null;
    private static $fatal_error = null;

    public static function shutdown_callback() {
        if (defined('GRACEFUL_SHUTDOWN')) {
            // everything went fine
            ob_end_flush();
            return;
        }

    }


    // turn errors into exceptions
    public static function exception_error_handler($errno, $errstr, $errfile, $errline) {
        // hhvm can report fatals as errors. We can not convert them to exceptions, however, because they will
        // not trigger the exception handler and the stack trace is wrong, anyway. We need to save the errfile and
        // errline here because they are not available in the shutdown function.
        // for the errno, see https://github.com/facebook/hhvm/blob/master/hphp/runtime/base/runtime-error.h#L57
        if ($errno & (1 << 24)) {
	    echo "fatal: $errno in $errfile on line $errline: $errstr";
            self::$fatal_error = array(
                    'message' => $errstr,
                    'type' => $errno,
                    'file' => $errfile,
                    'line' => $errline
            );
        }
	
	if (error_reporting() === 0) {
            return false; // code used @ to suppress errors
        }
        return false;
    }

    public static function exception_handler($exception) {
        self::$uncaught_exception = $exception;
    }

    /**
     * @return Exception|null the exception that caused the app to crash, or null if there was none
     */
    public static function get_uncaught_exception() {
        return self::$uncaught_exception;
    }

    /**
     * Gets the last error that has occurred, be it fatal or non-fatal
     * @return array with the fields message, type, file and line
     */
    public static function get_last_error() {
        if (self::$fatal_error) {
            return self::$fatal_error;
        }
        return error_get_last();
    }
}
register_shutdown_function(array('ExceptionHandling', 'shutdown_callback'));
set_error_handler(array('ExceptionHandling', "exception_error_handler"));
set_exception_handler(array('ExceptionHandling', 'exception_handler'));
