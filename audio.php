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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Academy - Gestion des audios éducatifs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --danger: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient-header: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-deep: 0 12px 32px rgba(0,0,0,0.15);
            --shadow-medium: 0 8px 24px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui;
        }

        body {
            background: var(--light);
            color: var(--dark);
        }

        /* Header */
        .header {
            background: var(--gradient-header);
            padding: 1.5rem;
            box-shadow: var(--shadow-medium);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title h1 {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
            position: relative;
        }

        .page-title h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent);
        }

        /* Audio Grid */
        .audio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .audio-card {
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
        }

        .audio-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-deep);
        }

        .audio-cover {
            height: 200px;
            background: #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        .audio-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .audio-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
        }

        .audio-content {
            padding: 1.5rem;
        }

        .audio-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .audio-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .audio-player {
            width: 100%;
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        /* CRUD Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-md);
            width: 500px;
            max-width: 95%;
            animation: modalSlide 0.3s ease;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        @keyframes modalSlide {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .audio-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">Audio Academy</div>
            <button class="btn btn-primary" id="addAudio">
                <i class="fas fa-plus"></i> Ajouter un audio
            </button>
        </div>
    </header>

    <main class="container">
        <div class="page-title">
            <h1>Bibliothèque audio éducative</h1>
            <p>Gérez et organisez du contenu audio d'apprentissage</p>
        </div>

        <div class="audio-grid" id="audioContainer">
            <?php foreach ($audios as $audio): ?>
                <div class="audio-card" data-id="<?= $audio['id'] ?>">
                    <div class="audio-cover">
                        <img src="placeholder.jpg" alt="<?= htmlspecialchars($audio['title']) ?>">
                        <div class="audio-badge"><?= htmlspecialchars($audio['category']) ?></div>
                    </div>
                    <div class="audio-content">
                        <h3 class="audio-title"><?= htmlspecialchars($audio['title']) ?></h3>
                        <div class="audio-meta">
                            <span><?= htmlspecialchars($audio['duration']) ?></span>
                            <span><?= date('d/m/Y', strtotime($audio['timestamp'])) ?></span>
                        </div>
                        <div class="audio-player">
                            <?= htmlspecialchars_decode($audio['embed_code']) ?>
                        </div>
                        <div class="card-actions" style="margin-top:1rem; display: flex; gap: 0.5rem;">
                            <button class="btn btn-primary" onclick="openModal(<?= $audio['id'] ?>)">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $audio['id'] ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- CRUD Modal -->
    <div class="modal" id="audioModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Ajouter un nouvel audio</h3>
                <button class="modal-close">&times;</button>
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
                <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="btn btn-danger modal-close">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addAudio').addEventListener('click', () => {
            document.getElementById('formAction').value = 'add';
            document.getElementById('modalTitle').textContent = 'Ajouter un nouvel audio';
            document.getElementById('audioForm').reset();
            document.getElementById('audioModal').style.display = 'flex';
        });

        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('audioModal').style.display = 'none';
            });
        });

        function openModal(id) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('audioId').value = id;
            document.getElementById('modalTitle').textContent = 'Modifier l\'audio';
            document.getElementById('audioModal').style.display = 'flex';
        }
        document.getElementById('audioModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('audioModal')) {
                document.getElementById('audioModal').style.display = 'none';
            }
        });
    </script>
</body>
</html>
