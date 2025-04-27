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
        header("Location: Texte.php");
        exit();
    }
}

$textes = $texteManager->getAllTextes();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>KIDS PLACE - Gestion des textes</title>
    <meta name="keywords" content="siteweb, éducation, enfants, apprentissage">
    <meta name="description" content="Gestion des textes éducatifs pour maternelle et primaire">
    <meta name="author" content="">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .class_box.text-management {
            padding: 30px;
            min-height: 400px;
        }
        
        .texte-card {
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .texte-header {
            background: #3e0bce;
            color: white;
            padding: 15px;
        }
        
        .texte-content {
            padding: 20px;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #f0390f;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-interactive {
            background: #ffc107;
            color: #212529;
        }
        
        .texte-interactive {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
    </style>
</head>
<body class="main-layout">
    <div class="loader_bg">
        <div class="loader"><img src="images/loading.gif" alt="#"/></div>
    </div>
    
    <div class="header">
        <div class="container-fluid">
            <div class="row d_flex">
                <div class="col-md-2 col-sm-3 col logo_section">
                    <div class="full">
                        <div class="center-desk">
                            <div class="logo">
                                <a href="index.php"><h1 style="font-size: 24px;color: #2b2b2b;margin: 0;">KIDS PLACE</h1></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 col-sm-12">
                    <nav class="navigation navbar navbar-expand-md navbar-dark">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarsExample04">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Accueil</a>
                                </li>
                                <li class="nav-item active">
                                    <a class="nav-link" href="Texte.php">Textes</a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" id="addTexte" style="margin-top: 10px;">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="class">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="titlepage text_align_center">
                        <h2>Gestion des Textes Éducatifs</h2>
                        <p>Créez et gérez du contenu textuel pour l'apprentissage des enfants</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="row" id="texteContainer">
                        <?php foreach ($textes as $texte): ?>
                        <div class="col-md-4 margi_bottom">
                            <div class="texte-card">
                                <div class="texte-header">
                                    <h3><?= htmlspecialchars($texte['title']) ?></h3>
                                    <span class="badge"><?= htmlspecialchars($texte['category']) ?></span>
                                </div>
                                <div class="texte-content">
                                    <div class="texte-body"><?= nl2br(htmlspecialchars($texte['content'])) ?></div>
                                    
                                    <?php if (!empty($texte['interactive_content'])): ?>
                                        <button class="btn btn-interactive" onclick="toggleInteractive(<?= $texte['id'] ?>)">
                                            <i class="fas fa-eye"></i> Voir plus
                                        </button>
                                        <div class="texte-interactive" id="interactive-<?= $texte['id'] ?>">
                                            <?= nl2br(htmlspecialchars($texte['interactive_content'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-actions">
                                        <button class="btn btn-primary" onclick="openModal(<?= $texte['id'] ?>)">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $texte['id'] ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="texteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Ajouter un nouveau texte</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="texteForm" method="POST">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="id" id="texteId">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Titre du texte</label>
                        <input type="text" class="form-control" id="texteTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select class="form-control" id="texteCategory" name="category" required>
                            <option>Langue</option>
                            <option>Science</option>
                            <option>Histoire</option>
                            <option>Littérature</option>
                            <option>Géographie</option>
                            <option>Mathématiques</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contenu principal</label>
                        <textarea class="form-control" id="texteContent" name="content" required rows="6"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Contenu interactif (optionnel)</label>
                        <textarea class="form-control" id="texteInteractive" name="interactive_content" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger modal-close">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="infoma text_align_center">
                            <h3>Liens utiles</h3>
                            <ul class="fullink">
                                <li><a href="index.php">Accueil</a></li>
                                <li><a href="Texte.php">Textes</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.0.0.min.js"></script>
    <script src="js/custom.js"></script>
    
    <script>
        document.getElementById('addTexte').addEventListener('click', () => {
            document.getElementById('formAction').value = 'add';
            document.getElementById('modalTitle').textContent = 'Ajouter un nouveau texte';
            document.getElementById('texteForm').reset();
            document.getElementById('texteModal').style.display = 'block';
        });

        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('texteModal').style.display = 'none';
            });
        });

        function openModal(id) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('texteId').value = id;
            document.getElementById('modalTitle').textContent = 'Modifier le texte';
            
            document.getElementById('texteModal').style.display = 'block';
        }

        function toggleInteractive(id) {
            const interactive = document.getElementById(`interactive-${id}`);
            interactive.style.display = interactive.style.display === 'block' ? 'none' : 'block';
        }

        document.getElementById('texteModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('texteModal')) {
                document.getElementById('texteModal').style.display = 'none';
            }
        });
    </script>
</body>
</html> 
