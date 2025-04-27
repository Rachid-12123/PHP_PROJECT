<?php
require_once 'Database.php';
$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete_id'])) {
            // Handle delete operation
            $stmt = $db->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->bind_param("i", $_POST['delete_id']);
            $stmt->execute();
            $message = "Vidéo supprimée avec succès";
        } else {
            // Handle add/update operation
            $id = $_POST['id'] ?? 0;
            $title = htmlspecialchars($_POST['title']);
            $category = htmlspecialchars($_POST['category']);
            $embed_code = $_POST['embed_code']; // Store embed code directly
            $duration = intval($_POST['duration']);
            $age = htmlspecialchars($_POST['age']);

            if ($id > 0) {
                $stmt = $db->prepare("UPDATE videos SET title=?, category=?, embed_code=?, duration=?, age=? WHERE id=?");
                $stmt->bind_param("ssssii", $title, $category, $embed_code, $duration, $age, $id);
                $message = "Vidéo mise à jour avec succès";
            } else {
                $stmt = $db->prepare("INSERT INTO videos (title, category, embed_code, duration, age) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $title, $category, $embed_code, $duration, $age);
                $message = "Vidéo ajoutée avec succès";
            }
            $stmt->execute();
        }
        
        // Redirect to prevent form resubmission
        header("Location: videos.php?success=" . urlencode($message));
        exit();
    } catch (Exception $e) {
        header("Location: videos.php?error=" . urlencode("Erreur: " . $e->getMessage()));
        exit();
    }
}

// Get all videos
$videos = $db->query("SELECT * FROM videos ORDER BY created_at DESC");
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
      <title>KIDS PLACE - Gestion des Vidéos</title>
      <meta name="keywords" content="siteweb, éducation, enfants, apprentissage">
      <meta name="description" content="Gestion des contenus vidéo éducatifs pour enfants">
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
         .video-section {
            padding: 90px 0;
            background: #fff;
         }
         .video-titlepage h2 {
            color: #2b2b2b;
            padding-bottom: 15px;
         }
         .video-titlepage p {
            color: #2b2b2b;
            font-size: 17px;
         }
         .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
         }
         .video-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
         }
         .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
         }
         .video-cover {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
         }
         .video-cover iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
         }
         .video-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #f0390f;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
         }
         .video-duration {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
         }
         .video-content {
            padding: 20px;
         }
         .video-content h3 {
            color: #2b2b2b;
            font-size: 20px;
            margin-bottom: 10px;
         }
         .video-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
         }
         .video-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
         }
         .btn-video {
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

      <!-- video section -->
      <div class="video-section">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="video-titlepage text_align_center">
                     <h2>Bibliothèque Vidéo Éducative</h2>
                     <p>Gérez et organisez du contenu vidéo d'apprentissage pour les enfants</p>
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
                  
                  <button class="btn-add" id="addVideo">
                     <i class="fa fa-plus"></i> Ajouter une vidéo
                  </button>
                  
                  <div class="video-grid" id="videoContainer">
                     <?php while($video = $videos->fetch_assoc()): ?>
                        <div class="video-card" data-id="<?= $video['id'] ?>">
                           <div class="video-cover">
                              <?= htmlspecialchars_decode($video['embed_code']) ?>
                              <div class="video-badge"><?= htmlspecialchars($video['category']) ?></div>
                              <div class="video-duration"><?= $video['duration'] ?>m</div>
                           </div>
                           <div class="video-content">
                              <h3><?= htmlspecialchars($video['title']) ?></h3>
                              <div class="video-meta">
                                 <span>Âge <?= htmlspecialchars($video['age']) ?></span>
                                 <span><?= date('d/m/Y', strtotime($video['created_at'])) ?></span>
                              </div>
                              <div class="video-actions">
                                 <button class="btn-video btn-edit" onclick="openModal(<?= $video['id'] ?>)">
                                    <i class="fa fa-edit"></i> Modifier
                                 </button>
                                 <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?= $video['id'] ?>">
                                    <button type="submit" class="btn-video btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?')">
                                       <i class="fa fa-trash"></i> Supprimer
                                    </button>
                                 </form>
                              </div>
                           </div>
                        </div>
                     <?php endwhile; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end video section -->

      <!-- CRUD Modal -->
      <div class="modal" id="videoModal">
         <div class="modal-content">
            <div class="modal-header">
               <h3 class="modal-title" id="modalTitle">Ajouter une nouvelle vidéo</h3>
               <span class="close">&times;</span>
            </div>
            <form id="videoForm" method="POST">
               <input type="hidden" name="id" id="videoId" value="0">
               <div class="modal-body">
                  <div class="form-group">
                     <label class="form-label">Titre de la vidéo</label>
                     <input type="text" class="form-control" id="videoTitle" name="title" required>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Catégorie</label>
                     <select class="form-control" id="videoCategory" name="category" required>
                        <option value="math">Mathématiques</option>
                        <option value="science">Science</option>
                        <option value="reading">Lecture</option>
                        <option value="history">Histoire</option>
                     </select>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Code d'intégration (iframe)</label>
                     <textarea class="form-control" id="videoEmbed" name="embed_code" rows="3" required placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen></iframe>'></textarea>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Durée (minutes)</label>
                     <input type="number" class="form-control" id="videoDuration" name="duration" min="1" required>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Groupe d'âge</label>
                     <input type="text" class="form-control" id="videoAge" name="age" placeholder="3-5" required>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn-video btn-delete modal-close">Annuler</button>
                  <button type="submit" class="btn-video btn-edit">Enregistrer</button>
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
         document.getElementById('addVideo').addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Ajouter une nouvelle vidéo';
            document.getElementById('videoId').value = 0;
            document.getElementById('videoForm').reset();
            document.getElementById('videoModal').style.display = 'block';
         });

         document.querySelectorAll('.close, .modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
               document.getElementById('videoModal').style.display = 'none';
            });
         });

         function openModal(id) {
            fetch(`get_video.php?id=${id}`)
               .then(response => response.json())
               .then(video => {
                  document.getElementById('videoId').value = video.id;
                  document.getElementById('videoTitle').value = video.title;
                  document.getElementById('videoCategory').value = video.category;
                  document.getElementById('videoEmbed').value = video.embed_code;
                  document.getElementById('videoDuration').value = video.duration;
                  document.getElementById('videoAge').value = video.age;
                  document.getElementById('modalTitle').textContent = 'Modifier la vidéo';
                  document.getElementById('videoModal').style.display = 'block';
               });
         }

         // Close modal when clicking outside
         document.getElementById('videoModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('videoModal')) {
               document.getElementById('videoModal').style.display = 'none';
            }
         });
      </script>
   </body>
</html>