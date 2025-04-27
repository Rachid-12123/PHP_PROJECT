<?php
require_once 'Database.php';

class ImageManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllImages() {
        $query = "SELECT * FROM images";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addImage($title, $category, $embed_code) {
        $stmt = $this->db->prepare("INSERT INTO images (title, category, embed_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $category, $embed_code);
        return $stmt->execute();
    }

    public function updateImage($id, $title, $category, $embed_code) {
        $stmt = $this->db->prepare("UPDATE images SET title = ?, category = ?, embed_code = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $category, $embed_code, $id);
        return $stmt->execute();
    }

    public function deleteImage($id) {
        $stmt = $this->db->prepare("DELETE FROM images WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getImageById($id) {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$imageManager = new ImageManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    if (isset($_POST['title'], $_POST['category'], $_POST['embed_code'])) {
                        $imageManager->addImage(
                            $_POST['title'],
                            $_POST['category'],
                            $_POST['embed_code']
                        );
                        $message = "Image ajoutée avec succès";
                    }
                    break;
                case 'update':
                    if (isset($_POST['id'], $_POST['title'], $_POST['category'], $_POST['embed_code'])) {
                        $imageManager->updateImage(
                            $_POST['id'],
                            $_POST['title'],
                            $_POST['category'],
                            $_POST['embed_code']
                        );
                        $message = "Image mise à jour avec succès";
                    }
                    break;
                case 'delete':
                    if (isset($_POST['id'])) {
                        $imageManager->deleteImage($_POST['id']);
                        $message = "Image supprimée avec succès";
                    }
                    break;
            }
            // Redirect to prevent form resubmission
            header("Location: Image.php?success=" . urlencode($message));
            exit();
        }
    } catch (Exception $e) {
        header("Location: Image.php?error=" . urlencode("Erreur: " . $e->getMessage()));
        exit();
    }
}

// Get all images for display
$images = $imageManager->getAllImages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <!-- site metas -->
    <title>KIDS PLACE - Gestion des images éducatives</title>
    <meta name="keywords" content="siteweb, éducation, images, apprentissage">
    <meta name="description" content="Gestion des contenus image éducatifs">
    <meta name="author" content="">
    <!-- bootstrap css -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- fevicon -->
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <!-- Tweaks for older IEs-->
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .image-section {
            padding: 90px 0;
            background: #fff;
        }
        .image-titlepage h2 {
            color: #2b2b2b;
            padding-bottom: 15px;
        }
        .image-titlepage p {
            color: #2b2b2b;
            font-size: 17px;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        .image-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .image-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .image-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            background: #f0f0f0;
        }
        .image-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .image-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #f0390f;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .image-content {
            padding: 20px;
        }
        .image-content h3 {
            color: #2b2b2b;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .image-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-image {
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-edit {
            background: #3e0bce;
            color: white;
        }
        .btn-delete {
            background: #f0390f;
            color: white;
        }
        .btn-add {
            background: #f0390f;
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 16px;
            margin-bottom: 30px;
            display: inline-block;
        }
        .btn-add:hover {
            background: #3e0bce;
            color: white;
        }
        /* Modal styles to match theme */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 50%;
            max-width: 600px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-title {
            color: #2b2b2b;
            font-size: 24px;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #2b2b2b;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        /* Status Messages */
        .status-message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }
        /* Header styles */
        .header {
            background: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            font-size: 24px;
            color: #2b2b2b;
            margin: 0;
            font-weight: bold;
        }
    </style>
</head>
<body class="main-layout">
    <!-- loader  -->
    <div class="loader_bg">
         <div class="loader"><img src="images/loading.gif" alt="#"/></div>
      </div>
      <!-- end loader -->
      
      <!-- header -->
      <div class="header">
         <div class="container-fluid">
            <div class="row d_flex">
               <div class=" col-md-2 col-sm-3 col logo_section">
                  <div class="full">
                     <div class="center-desk">
                        <div class="logo">
                           <a href="index.php"><h1 style="font-size: 24px;color: #2b2b2b;margin: 0;">KIDS PLACE</h1></a>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-md-8 col-sm-12">
                  <nav class="navigation navbar navbar-expand-md navbar-dark ">
                     <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon"></span>
                     </button>
                    
                  </nav>
               </div>
               
            </div>
         </div>
      </div>
      <!-- end header inner -->

    <!-- image section -->
    <div class="image-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="image-titlepage text_align_center">
                        <h2>Galerie d'Images Éducatives</h2>
                        <p>Gérez et organisez du contenu visuel d'apprentissage</p>
                    </div>
                    
                    <!-- Status Messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="status-message success-message">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="status-message error-message">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <button class="btn-add" id="addImage">
                        <i class="fa fa-plus"></i> Ajouter une image
                    </button>
                    
                    <div class="image-grid" id="imageContainer">
                        <?php foreach ($images as $image): ?>
                            <div class="image-card" data-id="<?= $image['id'] ?>">
                                <div class="image-container">
                                    <?= htmlspecialchars_decode($image['embed_code']) ?>
                                    <div class="image-badge"><?= htmlspecialchars($image['category']) ?></div>
                                </div>
                                <div class="image-content">
                                    <h3><?= htmlspecialchars($image['title']) ?></h3>
                                    <div class="image-actions">
                                        <button class="btn-image btn-edit" onclick="openModal(<?= $image['id'] ?>)">
                                            <i class="fa fa-edit"></i> Modifier
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $image['id'] ?>">
                                            <button type="submit" class="btn-image btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                                                <i class="fa fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end image section -->

    <!-- CRUD Modal -->
    <div class="modal" id="imageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Ajouter une nouvelle image</h3>
                <span class="close">&times;</span>
            </div>
            <form id="imageForm" method="POST">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="id" id="imageId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Titre de l'image</label>
                        <input type="text" class="form-control" id="imageTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catégorie</label>
                        <select class="form-control" id="imageCategory" name="category" required>
                            <option>Art</option>
                            <option>Science</option>
                            <option>Histoire</option>
                            <option>Géographie</option>
                            <option>Biologie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code d'intégration Imgur</label>
                        <textarea class="form-control" id="imageEmbedCode" name="embed_code" required placeholder="Collez le code embed d'Imgur ici"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-image btn-delete modal-close">Annuler</button>
                    <button type="submit" class="btn-image btn-edit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Javascript files-->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.0.0.min.js"></script>
    <!-- sidebar -->
    <script src="js/custom.js"></script>
    <script>
        document.getElementById('addImage').addEventListener('click', () => {
            document.getElementById('formAction').value = 'add';
            document.getElementById('modalTitle').textContent = 'Ajouter une nouvelle image';
            document.getElementById('imageForm').reset();
            document.getElementById('imageModal').style.display = 'block';
        });

        document.querySelectorAll('.close, .modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('imageModal').style.display = 'none';
            });
        });

        function openModal(id) {
            // In a real application, you would fetch the image data via AJAX
            // For this example, we'll just show the modal with the ID
            document.getElementById('formAction').value = 'update';
            document.getElementById('imageId').value = id;
            document.getElementById('modalTitle').textContent = 'Modifier l\'image';
            document.getElementById('imageModal').style.display = 'block';
        }

        // Close modal when clicking outside
        document.getElementById('imageModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('imageModal')) {
                document.getElementById('imageModal').style.display = 'none';
            }
        });
    </script>
</body>
</html>