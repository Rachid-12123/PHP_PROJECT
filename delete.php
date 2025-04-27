<?php
require 'config.php';

$id = $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM images WHERE id = ?');
$stmt->execute([$id]);
$image = $stmt->fetch();

if ($image) {
    unlink('uploads/' . $image['image']);
    $pdo->prepare('DELETE FROM images WHERE id = ?')->execute([$id]);
}

header('Location: index.php');
?>
