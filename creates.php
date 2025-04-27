<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    if ($image['error'] == 0) {
        $imageName = uniqid() . '_' . $image['name'];
        move_uploaded_file($image['tmp_name'], 'uploads/' . $imageName);

        $stmt = $pdo->prepare('INSERT INTO images (title, description, image) VALUES (?, ?, ?)');
        $stmt->execute([$title, $description, $imageName]);

        header('Location: index.php');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Image</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">Learning Gallery</div>
    <nav>
        <a href="index.php">Accueil</a>
    </nav>
</header>

<section class="form-section">
    <h1>Ajouter une nouvelle image</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Titre" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="file" name="image" required>
        <button type="submit">Ajouter</button>
    </form>
</section>

</body>
</html>
