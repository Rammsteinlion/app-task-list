<?php

namespace App\Routes;

use App\Config\ResponseHttp;
use App\DB\Database;

require_once __DIR__ . '/../../public/config.php';

class Router extends Database
{
    private $controller;
    private $method;
    private $data;
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->connect();

        if ($this->pdo === null) {
            echo 'No se pudo conectar a la base de datos.';
            exit;
        }

        $this->validateContentType();
        $this->matchRoute();
    }


    private function validateContentType()
    {
        header('Content-Type: application/json; charset=UTF-8');
        
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            ResponseHttp::sendJsonResponse(['error' => 'Invalid Content-Type, expected application/json'], 400);
            exit;
        }
    }

    public function matchRoute()
    {
        $url = explode('/', URL);

        $this->controller = isset($url[1]) ? ucfirst($url[1]) . 'Controller' : '';
        $this->method = isset($url[2]) ? $url[2] : '';
        $this->data = json_decode(file_get_contents('php://input'), true);

        $controllerPath = __DIR__ . '/../Controllers/' . $this->controller . '.php';

        if (!file_exists($controllerPath)) {
            ResponseHttp::sendJsonResponse(['error' => 'Controller not found'], 404);
            exit;
        }

        // Incluir el archivo del controlador
        require_once($controllerPath);

        $controllerClass = "App\\Controllers\\" . $this->controller;

        if (!method_exists($controllerClass, $this->method)) {
            ResponseHttp::sendJsonResponse(['error' => 'Method not found'], 404);
            exit;
        }
    }


    public function run()
    {
        // Instanciar el controlador y llamar al método
        $controllerClass = "App\\Controllers\\" . $this->controller;  // Namespace completo
        $controllerInstance = new $controllerClass($this->pdo);  // Pasar la conexión PDO al constructor
        $method = $this->method;

        // Llamar al método
        call_user_func([$controllerInstance, $method], $this->data);
    }


}
