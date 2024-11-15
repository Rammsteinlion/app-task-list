<?php

namespace App\Config;

date_default_timezone_set('America/Bogota');


class ErrorLog {

    public static function activateErrorLog()
    {
        // Reportar todos los errores
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE); // Asegúrate de que esto esté en TRUE
        ini_set('log_errors', TRUE);
        ini_set('error_log', dirname(__DIR__) . '/Logs/php-error.log');

        // // Establecer un manejador de errores personalizado
        // set_error_handler(function($errno, $errstr, $errfile, $errline) {
        //     echo json_encode([
        //         'error' => 'Error',
        //         'message' => $errstr,
        //         'file' => $errfile,
        //         'line' => $errline
        //     ]);
        //     exit;
        // });

        // // Establecer un manejador de excepciones
        // set_exception_handler(function($exception) {
        //     echo json_encode([
        //         'error' => 'Exception',
        //         'message' => $exception->getMessage(),
        //         'file' => $exception->getFile(),
        //         'line' => $exception->getLine()
        //     ]);
        //     exit;
        // });
    }
}
