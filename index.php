<?php

use classes\Database;
use classes\Incident;
use classes\Response;

require "config.php";
require "classes/Database.php";
require "classes/Incident.php";
require "classes/Response.php";

$db = (new Database())->pdo;
$incident = new Incident($db);

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));

if ($uri[0] !== "incidents") {
    Response::json(["error" => "Not Found"], 404);
}

$id = $uri[1] ?? null;

switch ($method) {
    case "GET":
        if ($id) {
            $data = $incident->getById($id);
            $data ? Response::json($data) : Response::json(["error" => "Not found"], 404);
        } else {
            Response::json($incident->getAll());
        }
        break;

    case "POST":
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);
        try {
            $newIncident = $incident->create($data);
            Response::json($newIncident, 201);
        } catch (Exception $e) {
            Response::json(["error" => $e->getMessage()], $e->getCode());
        }
        break;

    case "PUT":
        if (!$id) Response::json(["error" => "ID required"], 400);
        $data = json_decode(file_get_contents("php://input"), true);
        try {
            $updated = $incident->update($id, $data);
            Response::json($updated);
        } catch (Exception $e) {
            Response::json(["error" => $e->getMessage()], $e->getCode());
        }
        break;

    case "DELETE":
        if (!$id) {
            Response::json(["error" => "ID required"], 400);
        }
        try {
            $deleted = $incident->delete($id);
            Response::json(["message" => "Deleted"]);
        } catch (Exception $e) {
            Response::json(["error" => $e->getMessage()], $e->getCode());
        }
        break;
    default:
        Response::json(["error" => "Method not allowed"], 405);
}