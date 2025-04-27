<?php
require_once 'Database.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_assoc());
?>