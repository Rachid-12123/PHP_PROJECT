<?php
require 'config.php';

$stmt = $pdo->query('SELECT * FROM images ORDER BY created_at DESC');
$images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Galerie d'apprentissage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">Photos d'apprentissage</div>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="create.php" class="btn">Ajouter une Image</a>
    </nav>
</header>

<section class="gallery">
    <?php foreach ($images as $image): ?>
    <div class="card">
        <img src="uploads/<?= htmlspecialchars($image['image']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
        <h2><?= htmlspecialchars($image['title']) ?></h2>
        <p><?= htmlspecialchars($image['description']) ?></p>
        <div class="actions">
            <a href="update.php?id=<?= $image['id'] ?>">Modifier</a>
            <a href="delete.php?id=<?= $image['id'] ?>">Supprimer</a>
        </div>
    </div>
    <?php endforeach; ?>
</section>

</body>
</html>
