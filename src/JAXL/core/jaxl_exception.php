<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2012, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Abhinav Singh nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

/**
 *
 * @author abhinavsingh
 */
class JAXLException extends Exception
{

    public function __construct($message = null, $code = null, $file = null, $line = null)
    {
        JAXLLogger::notice("got jaxl exception construct with $message, $code, $file, $line");
        if ($code === null) {
            parent::__construct($message);
        } else {
            parent::__construct($message, $code);
        }

        if ($file !== null) {
            $this->file = $file;
        }

        if ($line !== null) {
            $this->line = $line;
        }
    }

    public static function addHandlers()
    {
        set_error_handler(array('JAXLException', 'error_handler'));
        set_exception_handler(array('JAXLException', 'exception_handler'));
        register_shutdown_function(array('JAXLException', 'shutdown_handler'));
    }

    // added null to vars, in PHP 8.0 it generate the error:
    // Too few arguments to function JAXLException::error_handler(), 4 passed and exactly 5 expected
    public static function error_handler($errno, $error, $file, $line, $vars = null)
    {
        JAXLLogger::debug("error handler called with $errno, $error, $file, $line");
        if ($errno === 0 || ($errno & error_reporting()) === 0) {
            return;
        }

        throw new JAXLException($error, $errno, $file, $line);
    }

    public static function exception_handler($e)
    {
        // in PHP 8 json_encode doesn't work with object(ArgumentCountError) move to print_r
        JAXLLogger::debug("exception handler catched ".print_r($e, true));

        // TODO: Pretty print backtrace
        //print_r(debug_backtrace());
    }

    public static function shutdown_handler()
    {
        try {
            JAXLLogger::debug("got shutdown handler");
            if (null !== ($error = error_get_last())) {
                throw new JAXLException($error['message'], $error['type'], $error['file'], $error['line']);
            }
        } catch (Exception $e) {
            JAXLLogger::debug("shutdown handler catched with exception ".json_encode($e));
        }
    }
}
