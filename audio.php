<?php
require_once 'Database.php';

class AudioManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllAudios() {
        $query = "SELECT * FROM audios";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addAudio($title, $category, $duration, $embed_code) {
        $stmt = $this->db->prepare("INSERT INTO audios (title, category, duration, embed_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $category, $duration, $embed_code);
        return $stmt->execute();
    }

    public function updateAudio($id, $title, $category, $duration, $embed_code) {
        $stmt = $this->db->prepare("UPDATE audios SET title = ?, category = ?, duration = ?, embed_code = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $category, $duration, $embed_code, $id);
        return $stmt->execute();
    }

    public function deleteAudio($id) {
        $stmt = $this->db->prepare("DELETE FROM audios WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAudioById($id) {
        $stmt = $this->db->prepare("SELECT * FROM audios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$audioManager = new AudioManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['title'], $_POST['category'], $_POST['duration'], $_POST['embed_code'])) {
                    $audioManager->addAudio(
                        $_POST['title'],
                        $_POST['category'],
                        $_POST['duration'],
                        $_POST['embed_code']
                    );
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['title'], $_POST['category'], $_POST['duration'], $_POST['embed_code'])) {
                    $audioManager->updateAudio(
                        $_POST['id'],
                        $_POST['title'],
                        $_POST['category'],
                        $_POST['duration'],
                        $_POST['embed_code']
                    );
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    $audioManager->deleteAudio($_POST['id']);
                }
                break;
        }
        // Redirect to prevent form resubmission
        header("Location: audio.php");
        exit();
    }
}

// Get all audios for display
$audios = $audioManager->getAllAudios();
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
      <title>KIDS PLACE - Gestion des Audios</title>
      <meta name="keywords" content="siteweb, éducation, enfants, apprentissage">
      <meta name="description" content="Gestion des contenus audio éducatifs pour enfants">
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
         .audio-section {
            padding: 90px 0;
            background: #fff;
         }
         .audio-titlepage h2 {
            color: #2b2b2b;
            padding-bottom: 15px;
         }
         .audio-titlepage p {
            color: #2b2b2b;
            font-size: 17px;
         }
         .audio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
         }
         .audio-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
         }
         .audio-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
         }
         .audio-cover {
            height: 200px;
            background: #f0f0f0;
            position: relative;
         }
         .audio-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #f0390f;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
         }
         .audio-content {
            padding: 20px;
         }
         .audio-content h3 {
            color: #2b2b2b;
            font-size: 20px;
            margin-bottom: 10px;
         }
         .audio-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
         }
         .audio-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
         }
         .btn-audio {
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

      <!-- audio section -->
      <div class="audio-section">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="audio-titlepage text_align_center">
                     <h2>Bibliothèque Audio Éducative</h2>
                     <p>Gérez et organisez du contenu audio d'apprentissage pour les enfants</p>
                  </div>
                  
                  <button class="btn-add" id="addAudio">
                     <i class="fa fa-plus"></i> Ajouter un audio
                  </button>
                  
                  <div class="audio-grid" id="audioContainer">
                     <?php foreach ($audios as $audio): ?>
                        <div class="audio-card" data-id="<?= $audio['id'] ?>">
                           <div class="audio-cover">
                              <img src="placeholder.jpg" alt="<?= htmlspecialchars($audio['title']) ?>">
                              <div class="audio-badge"><?= htmlspecialchars($audio['category']) ?></div>
                           </div>
                           <div class="audio-content">
                              <h3><?= htmlspecialchars($audio['title']) ?></h3>
                              <div class="audio-meta">
                                 <span><?= htmlspecialchars($audio['duration']) ?></span>
                                 <span><?= date('d/m/Y', strtotime($audio['timestamp'])) ?></span>
                              </div>
                              <div class="audio-player">
                                 <?= htmlspecialchars_decode($audio['embed_code']) ?>
                              </div>
                              <div class="audio-actions">
                                 <button class="btn-audio btn-edit" onclick="openModal(<?= $audio['id'] ?>)">
                                    <i class="fa fa-edit"></i> Modifier
                                 </button>
                                 <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $audio['id'] ?>">
                                    <button type="submit" class="btn-audio btn-delete">
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
      <!-- end audio section -->

      <!-- CRUD Modal -->
      <div class="modal" id="audioModal">
         <div class="modal-content">
            <div class="modal-header">
               <h3 class="modal-title" id="modalTitle">Ajouter un nouvel audio</h3>
               <span class="close">&times;</span>
            </div>
            <form id="audioForm" method="POST">
               <input type="hidden" name="action" value="add" id="formAction">
               <input type="hidden" name="id" id="audioId">
               <div class="modal-body">
                  <div class="form-group">
                     <label class="form-label">Titre de l'audio</label>
                     <input type="text" class="form-control" id="audioTitle" name="title" required>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Catégorie</label>
                     <select class="form-control" id="audioCategory" name="category" required>
                        <option>Langue</option>
                        <option>Science</option>
                        <option>Histoire</option>
                        <option>Littérature</option>
                     </select>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Code d'intégration SoundCloud</label>
                     <textarea class="form-control" id="audioEmbedCode" name="embed_code" required placeholder="Collez le code embed de SoundCloud ici"></textarea>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Durée</label>
                     <input type="text" class="form-control" id="audioDuration" name="duration" required placeholder="ex: 3:45">
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn-audio btn-delete modal-close">Annuler</button>
                  <button type="submit" class="btn-audio btn-edit">Enregistrer</button>
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
         document.getElementById('addAudio').addEventListener('click', () => {
            document.getElementById('formAction').value = 'add';
            document.getElementById('modalTitle').textContent = 'Ajouter un nouvel audio';
            document.getElementById('audioForm').reset();
            document.getElementById('audioModal').style.display = 'block';
         });

         document.querySelectorAll('.close, .modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
               document.getElementById('audioModal').style.display = 'none';
            });
         });

         function openModal(id) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('audioId').value = id;
            document.getElementById('modalTitle').textContent = 'Modifier l\'audio';
            
            // In a real application, you would fetch the audio data via AJAX or have it in a JS variable
            // For this example, we'll just show the modal
            document.getElementById('audioModal').style.display = 'block';
         }

         // Close modal when clicking outside
         document.getElementById('audioModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('audioModal')) {
               document.getElementById('audioModal').style.display = 'none';
            }
         });
      </script>
   </body>
</html>