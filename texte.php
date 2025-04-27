<?php
require_once 'Database.php';

class TexteManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllTextes() {
        $query = "SELECT * FROM textes";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addTexte($title, $category, $content, $interactive_content) {
        $stmt = $this->db->prepare("INSERT INTO textes (title, category, content, interactive_content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $category, $content, $interactive_content);
        return $stmt->execute();
    }

    public function updateTexte($id, $title, $category, $content, $interactive_content) {
        $stmt = $this->db->prepare("UPDATE textes SET title = ?, category = ?, content = ?, interactive_content = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $category, $content, $interactive_content, $id);
        return $stmt->execute();
    }

    public function deleteTexte($id) {
        $stmt = $this->db->prepare("DELETE FROM textes WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getTexteById($id) {
        $stmt = $this->db->prepare("SELECT * FROM textes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$texteManager = new TexteManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['title'], $_POST['category'], $_POST['content'])) {
                    $texteManager->addTexte(
                        $_POST['title'],
                        $_POST['category'],
                        $_POST['content'],
                        $_POST['interactive_content'] ?? ''
                    );
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['title'], $_POST['category'], $_POST['content'])) {
                    $texteManager->updateTexte(
                        $_POST['id'],
                        $_POST['title'],
                        $_POST['category'],
                        $_POST['content'],
                        $_POST['interactive_content'] ?? ''
                    );
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    $texteManager->deleteTexte($_POST['id']);
                }
                break;
        }
        // Redirect to prevent form resubmission
        header("Location: Texte.php");
        exit();
    }
}

// Get all textes for display
$textes = $texteManager->getAllTextes();
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
      <title>KIDS PLACE - Gestion des Textes</title>
      <meta name="keywords" content="siteweb, éducation, enfants, apprentissage">
      <meta name="description" content="Gestion des contenus textuels éducatifs pour enfants">
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
         .texte-section {
            padding: 90px 0;
            background: #fff;
         }
         .texte-titlepage h2 {
            color: #2b2b2b;
            padding-bottom: 15px;
         }
         .texte-titlepage p {
            color: #2b2b2b;
            font-size: 17px;
         }
         .texte-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
         }
         .texte-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
         }
         .texte-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
         }
         .texte-header {
            background: #3e0bce;
            color: white;
            padding: 20px;
            position: relative;
         }
         .texte-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #f0390f;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
         }
         .texte-content {
            padding: 20px;
         }
         .texte-content h3 {
            color: #2b2b2b;
            font-size: 20px;
            margin-bottom: 10px;
         }
         .texte-body {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 15px;
         }
         .texte-interactive {
            background: #FFF9C4;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            display: none;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
         }
         .texte-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
         }
         .btn-texte {
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
         .btn-interactive {
            background: #f8961e;
            color: white;
            width: 100%;
         }
         .btn-add {
            background: #f0390f;
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 16px;
            margin-bottom: 30px;
            display: inline-block;
            border: none;
            cursor: pointer;
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
         textarea.form-control {
            min-height: 120px;
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
                           <a href="index.html"><h1 style="font-size: 24px;color: #2b2b2b;margin: 0;">KIDS PLACE</h1></a>
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

      <!-- texte section -->
      <div class="texte-section">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="texte-titlepage text_align_center">
                     <h2>Bibliothèque de Textes Éducatifs</h2>
                     <p>Gérez et organisez du contenu textuel d'apprentissage pour les enfants</p>
                  </div>
                  
                  <button class="btn-add" id="addTexte">
                     <i class="fa fa-plus"></i> Ajouter un texte
                  </button>
                  
                  <div class="texte-grid" id="texteContainer">
                     <?php foreach ($textes as $texte): ?>
                        <div class="texte-card" data-id="<?= $texte['id'] ?>">
                           <div class="texte-header">
                              <h3><?= htmlspecialchars($texte['title']) ?></h3>
                              <div class="texte-badge"><?= htmlspecialchars($texte['category']) ?></div>
                           </div>
                           <div class="texte-content">
                              <div class="texte-body"><?= nl2br(htmlspecialchars($texte['content'])) ?></div>
                              
                              <?php if (!empty($texte['interactive_content'])): ?>
                                 <button class="btn-texte btn-interactive" onclick="toggleInteractive(<?= $texte['id'] ?>)">
                                    <i class="fa fa-eye"></i> Voir le contenu interactif
                                 </button>
                                 <div class="texte-interactive" id="interactive-<?= $texte['id'] ?>">
                                    <?= nl2br(htmlspecialchars($texte['interactive_content'])) ?>
                                 </div>
                              <?php endif; ?>
                              
                              <div class="texte-actions">
                                 <button class="btn-texte btn-edit" onclick="openModal(<?= $texte['id'] ?>)">
                                    <i class="fa fa-edit"></i> Modifier
                                 </button>
                                 <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $texte['id'] ?>">
                                    <button type="submit" class="btn-texte btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce texte ?')">
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
      <!-- end texte section -->

      <!-- CRUD Modal -->
      <div class="modal" id="texteModal">
         <div class="modal-content">
            <div class="modal-header">
               <h3 class="modal-title" id="modalTitle">Ajouter un nouveau texte</h3>
               <span class="close">&times;</span>
            </div>
            <form id="texteForm" method="POST">
               <input type="hidden" name="action" id="formAction" value="add">
               <input type="hidden" name="id" id="texteId">
               <div class="modal-body">
                  <div class="form-group">
                     <label class="form-label">Titre du texte</label>
                     <input type="text" class="form-control" id="texteTitle" name="title" required>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Catégorie</label>
                     <select class="form-control" id="texteCategory" name="category" required>
                        <option value="Langue">Langue</option>
                        <option value="Science">Science</option>
                        <option value="Histoire">Histoire</option>
                        <option value="Littérature">Littérature</option>
                        <option value="Géographie">Géographie</option>
                        <option value="Mathématiques">Mathématiques</option>
                     </select>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Contenu principal</label>
                     <textarea class="form-control" id="texteContent" name="content" required></textarea>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Contenu interactif (optionnel)</label>
                     <textarea class="form-control" id="texteInteractive" name="interactive_content"></textarea>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn-texte btn-delete modal-close">Annuler</button>
                  <button type="submit" class="btn-texte btn-edit">Enregistrer</button>
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
         document.getElementById('addTexte').addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Ajouter un nouveau texte';
            document.getElementById('formAction').value = 'add';
            document.getElementById('texteId').value = 0;
            document.getElementById('texteForm').reset();
            document.getElementById('texteModal').style.display = 'block';
         });

         document.querySelectorAll('.close, .modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
               document.getElementById('texteModal').style.display = 'none';
            });
         });

         function openModal(id) {
            fetch(`get_texte.php?id=${id}`)
               .then(response => response.json())
               .then(texte => {
                  document.getElementById('texteId').value = texte.id;
                  document.getElementById('texteTitle').value = texte.title;
                  document.getElementById('texteCategory').value = texte.category;
                  document.getElementById('texteContent').value = texte.content;
                  document.getElementById('texteInteractive').value = texte.interactive_content;
                  document.getElementById('formAction').value = 'update';
                  document.getElementById('modalTitle').textContent = 'Modifier le texte';
                  document.getElementById('texteModal').style.display = 'block';
               });
         }

         function toggleInteractive(id) {
            const interactive = document.getElementById(`interactive-${id}`);
            if (interactive.style.display === 'block') {
               interactive.style.display = 'none';
            } else {
               interactive.style.display = 'block';
            }
         }

         // Close modal when clicking outside
         document.getElementById('texteModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('texteModal')) {
               document.getElementById('texteModal').style.display = 'none';
            }
         });
      </script>
   </body>
</html>
