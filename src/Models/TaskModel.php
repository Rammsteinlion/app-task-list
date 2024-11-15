<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Config\ResponseHttp;

class TaskModel
{

    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($params)
    {
        $user_id = 1;

        // Verificar si el usuario existe
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $userExists = $stmt->fetchColumn() > 0;

        if (!$userExists) {
            ResponseHttp::sendJsonResponse(['error' => 'El user_id no existe en la tabla users'], 404);
            exit;
        }

        // Asignar valores predeterminados
        $params['user_id'] = $user_id;
        $params['status'] = 'pending';
        $params['created_at'] = date('Y-m-d H:i:s');
        $params['updated_at'] = date('Y-m-d H:i:s');

        // Consulta SQL para insertar la tarea
        $sql = "INSERT INTO tasks (user_id, title, description, status, created_at, updated_at)
                VALUES (:user_id, :title, :description, :status, :created_at, :updated_at)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            ResponseHttp::sendJsonResponse(['error' => 'Error al ejecutar la consulta: ' . $e->getMessage()], 500);
            exit;
        }

        return ['message' => 'Tarea creada con éxito'];
    }

    public function update($params)
    {
        if (isset($params['id'])) {

            $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id");
            $stmt->execute(['id' => $params['id']]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task) {
                ResponseHttp::sendJsonResponse(['error' => 'La tarea no existe'], 404);
                exit;
            }

            // Iniciar la construcción de la consulta
            $fieldsToUpdate = [];
            $params['updated_at'] = date('Y-m-d H:i:s');
            $queryParams = $params;

            // Obtener las columnas de la tabla "tasks"
            $columns = array_keys($task);

            foreach ($columns as $column) {
                if (isset($params[$column]) && $params[$column] !== $task[$column]) {
                    $fieldsToUpdate[] = "$column = :$column";
                    $queryParams[$column] = $params[$column];
                }
            }

            // Construir la consulta SQL
        $sql = "UPDATE tasks SET " . implode(', ', $fieldsToUpdate) . " WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($queryParams);
            return ['message' => 'Tarea actualizada con éxito'];
        } catch (PDOException $e) {
            ResponseHttp::sendJsonResponse(['error' => 'Error al actualizar la tarea: ' . $e->getMessage()], 500);
            exit;
        }
        
        } else {
            ResponseHttp::sendJsonResponse(['error' => 'El ID de la tarea es requerido'], 400);
            exit;
        }
    }

    public function delete($params)
    {
        if (isset($params['id'])) {
            $id = $params['id'];

            // Verificar si la tarea existe
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->fetchColumn() == 0) {
                ResponseHttp::sendJsonResponse(['error' => 'La tarea no existe'], 404);
                exit;
            }

            // Eliminar tarea
            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->bindParam(':id', $id);

            try {
                $stmt->execute();
                return ['message' => 'Tarea eliminada con éxito'];
            } catch (PDOException $e) {
                ResponseHttp::sendJsonResponse(['error' => 'Error al eliminar la tarea: ' . $e->getMessage()], 500);
                exit;
            }
        } else {
            ResponseHttp::sendJsonResponse(['error' => 'El ID de la tarea es requerido'], 400);
            exit;
        }
    }
}
