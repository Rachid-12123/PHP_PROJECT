
<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE images SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $description, $id]);

    if ($_FILES['image']['name']) {
        $image = $_FILES['image'];
        $imagePath = 'assets/images/' . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);

        $stmt = $pdo->prepare("UPDATE images SET image_path = ? WHERE id = ?");
        $stmt->execute([$imagePath, $id]);
    }

    header('Location: index.php');
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'image</title>
</head>
<body>
    <h1>Modifier l'image</h1>
    <form action="edit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $image['id'] ?>">

        <label for="title">Titre</label>
        <input type="text" name="title" value="<?= $image['title'] ?>" required><br>

        <label for="description">Description</label>
        <textarea name="description" required><?= $image['description'] ?></textarea><br>

        <label for="image">Image</label>
        <input type="file" name="image"><br>

        <input type="submit" value="Mettre à jour l'image">
    </form>
    <a href="index.php">Retour à la liste</a>
</body>
</html>
