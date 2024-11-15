<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Config\ResponseHttp;
use PDO;
class TaskController{

    private $taskModel;

    public function __construct(PDO $pdo)
    {
        $this->taskModel = new TaskModel($pdo);
    }

    public function createTask($params)
    {
        ResponseHttp::validateParams($params);
        $response = $this->taskModel->create($params);
        ResponseHttp::sendJsonResponse($response, 201);
    }

    public function updateTask($params)
    {
        ResponseHttp::validateParams($params);
        $response = $this->taskModel->update($params);
        ResponseHttp::sendJsonResponse($response, 201);
    }

    public function deleteTask($params)
    {
    
        ResponseHttp::validateParams($params);

        // Llamar al método del modelo para eliminar la tarea
        $response = $this->taskModel->delete($params);

        // Devolver respuesta
        ResponseHttp::sendJsonResponse($response, 200);
    }
}
