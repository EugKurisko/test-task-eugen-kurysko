<?php

namespace classes;

class Incident
{
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM incidents ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM incidents WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        if (empty($data['title'])) {
            throw new \Exception('Title must not be empty');
        }
        if (!isset($data['severity']) || $data['severity'] < 1 || $data['severity'] > 5) {
            throw new \Exception('Severity must be between 1 and 5');
        }
        $stmt = $this->pdo->prepare("INSERT INTO incidents (title, description, severity) VALUES (?, ?, ?)");
        $stmt->execute([$data['title'], $data['description'], $data['severity']]);
        return $this->getById($this->pdo->lastInsertId());
    }

    public function update($id, $data) {
        $incident = $this->getById($id);
        if (!$incident) {
            throw new \Exception("Incident ${id} not found", 404);
        }
        if (empty($data['title'])) {
            throw new \Exception('Title must not be empty');
        }
        $title = $data['title'] ?? $incident['title'];
        $description = $data['description'] ?? $incident['description'];
        if (!isset($data['severity']) || $data['severity'] < 1 || $data['severity'] > 5) {
            throw new \Exception('Severity must be between 1 and 5');
        }
        $severity = (isset($data['severity']) && $data['severity'] >= 1 && $data['severity'] <= 5)
            ? $data['severity']
            : $incident['severity'];

        $stmt = $this->pdo->prepare("UPDATE incidents SET title=?, description=?, severity=? WHERE id=?");
        $stmt->execute([$title, $description, $severity, $id]);

        return $this->getById($id);
    }

    public function delete($id) {
        $incident = $this->getById($id);
        if (!$incident) {
            throw new \Exception("Incident ${id} not found", 404);
        }
        $stmt = $this->pdo->prepare("DELETE FROM incidents WHERE id=?");
        return $stmt->execute([$id]);
    }
}