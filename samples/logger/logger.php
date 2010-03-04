<?php

/**
 *  MongoLogger class.
 *
 *  This function save PHP errors in MongoDB 
 *  database.
 *
 *  
 */
class MongoLogger extends ActiveMongo
{
    public $type;
    public $file;
    public $line;
    public $code;
    public $error;

    /**
     *  This method initialize the MongoLogger
     *  class.
     */
    final public static function init()
    {
        $class = get_called_class();
        set_exception_handler(array($class, "exception_logger"));
        set_error_handler(array($class, "error_handler"));
    }

    /**
     *  Save the PHP Error in MongoDB
     */
    final public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $class = get_called_class();
        if (isset($errcontext['GLOBALS'])) {
            unset($errcontext['GLOBALS']);
        }
        $log = new $class;
        $log->type    = "ERROR";
        $log->code    = $errno;
        $log->error   = $errstr;
        $log->file    = $errfile;
        $log->line    = $errline;
        $log->context = $errcontext;

        /* save it fast */
        $log->save(false);

        return false;
    }

    /** 
     *  Save the Exception in MongoDB
     */
    final public static function exception_logger($exception)
    {
        $class = get_called_class();
        $log = new $class;
        $log->type      = "EXCEPTION";
        $log->exception = get_class($exception); 
        $log->error     = $exception->getMessage();
        $log->line      = $exception->getLine();
        $log->file      = $exception->getFile();
        $log->code      = $exception->getCode();
        $log->trace     = $exception->getTrace();
        $log->save(false);
    }

    /**
     *  Setup the indexes
     */
    function setup()
    {
        $collection = $this->_getCollection();
        $collection->ensureIndex(array("type" => 1), array("background" => true));
    }
}

