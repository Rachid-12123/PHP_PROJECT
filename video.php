<?php
require_once 'Database.php';
$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete_id'])) {
            // Delete video
            $stmt = $db->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->bind_param("i", $_POST['delete_id']);
            $stmt->execute();
            $message = "Video deleted successfully";
        } else {
            // Add/Update video
            $id = $_POST['id'] ?? 0;
            $title = htmlspecialchars($_POST['title']);
            $category = htmlspecialchars($_POST['category']);
            $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
            $duration = intval($_POST['duration']);
            $age = htmlspecialchars($_POST['age']);

            if ($id > 0) {
                $stmt = $db->prepare("UPDATE videos SET title=?, category=?, url=?, duration=?, age=? WHERE id=?");
                $stmt->bind_param("sssisi", $title, $category, $url, $duration, $age, $id);
                $message = "Video updated successfully";
            } else {
                $stmt = $db->prepare("INSERT INTO videos (title, category, url, duration, age) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $title, $category, $url, $duration, $age);
                $message = "Video added successfully";
            }
            $stmt->execute();
        }
        
        // Redirect to prevent form resubmission
        header("Location: videos.php?success=" . urlencode($message));
        exit();
    } catch (Exception $e) {
        header("Location: videos.php?error=" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
}

// Get all videos
$videos = $db->query("SELECT * FROM videos ORDER BY created_at DESC");

// Extract YouTube ID from URL
function getYouTubeId($url) {
    $parsed = parse_url($url);
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $query);
        return $query['v'] ?? '';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kids Space - Video Learning Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <style>
    /* Base Styles */
    :root {
      --primary: #FF6B6B;
      --secondary: #4ECDC4;
      --accent: #FFE66D;
      --dark: #292F36;
      --light: #F7FFF7;
      --radius: 12px;
      --shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    body {
      font-family: 'Comic Neue', cursive;
      background-color: #F0F8FF;
      color: var(--dark);
      line-height: 1.6;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header */
    .manager-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      padding: 1.5rem 0;
      border-bottom: 4px dashed var(--accent);
      margin-bottom: 2rem;
    }
    
    .manager-title {
      font-family: 'Fredoka One', cursive;
      color: white;
      font-size: 2.5rem;
      text-shadow: 2px 2px 0 var(--dark);
      margin-bottom: 0.5rem;
    }
    
    .manager-subtitle {
      color: white;
      font-size: 1.2rem;
    }
    
    /* Buttons */
    .btn-primary {
      background-color: var(--accent);
      color: var(--dark);
      border: none;
      border-radius: 50px;
      padding: 12px 24px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 0 rgba(0,0,0,0.1);
      font-family: 'Fredoka One', cursive;
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 0 rgba(0,0,0,0.1);
      background-color: #FFD166;
    }
    
    .btn-primary:active {
      transform: translateY(1px);
      box-shadow: 0 2px 0 rgba(0,0,0,0.1);
    }
    
    .btn-icon {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    /* Video Cards */
    .video-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      padding: 1rem 0;
    }
    
    .video-card {
      background: white;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      border: 3px solid white;
    }
    
    .video-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
      border-color: var(--accent);
    }
    
    .card-thumbnail {
      position: relative;
      padding-top: 56.25%; /* 16:9 aspect ratio */
      overflow: hidden;
    }
    
    .card-thumbnail iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
    }
    
    .card-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: var(--primary);
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
    }
    
    .card-duration {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: rgba(0,0,0,0.7);
      color: white;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
    }
    
    .card-content {
      padding: 1rem;
    }
    
    .card-title {
      font-family: 'Fredoka One', cursive;
      color: var(--dark);
      margin-bottom: 0.5rem;
      font-size: 1.2rem;
    }
    
    .card-meta {
      display: flex;
      justify-content: space-between;
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    
    .card-actions {
      display: flex;
      gap: 8px;
    }
    
    .action-btn {
      flex: 1;
      padding: 8px;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }
    
    .edit-btn {
      background-color: var(--secondary);
      color: white;
    }
    
    .delete-btn {
      background-color: #FF6B6B;
      color: white;
    }
    
    /* Modal */
    .crud-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    
    .modal-content {
      background-color: white;
      border-radius: var(--radius);
      width: 90%;
      max-width: 500px;
      padding: 1.5rem;
      box-shadow: var(--shadow);
      position: relative;
      animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .modal-header {
      margin-bottom: 1.5rem;
    }
    
    .modal-title {
      font-family: 'Fredoka One', cursive;
      color: var(--primary);
      font-size: 1.5rem;
      margin: 0;
    }
    
    .modal-close {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #666;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }
    
    .form-control {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 6px;
      font-family: 'Comic Neue', cursive;
    }
    
    .form-control:focus {
      border-color: var(--secondary);
      outline: none;
    }
    
    .form-select {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 6px;
      background-color: white;
      font-family: 'Comic Neue', cursive;
    }
    
    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 1.5rem;
    }
    
    /* Status Messages */
    .status-message {
      padding: 15px;
      margin: 1rem auto;
      max-width: 800px;
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideIn 0.5s ease;
    }
    
    @keyframes slideIn {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    
    .success-message {
      background-color: #D4EDDA;
      color: #155724;
      border-left: 5px solid #28A745;
    }
    
    .error-message {
      background-color: #F8D7DA;
      color: #721C24;
      border-left: 5px solid #DC3545;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .manager-title {
        font-size: 2rem;
      }
      
      .video-grid {
        grid-template-columns: 1fr;
      }
    }
    </style>
</head>
<body>
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

    <!-- Header Section -->
    <header class="manager-header">
        <div class="container">
            <div class="header-content">
                <h1 class="manager-title">Kids Space Video Hub</h1>
                <p class="manager-subtitle">Fun educational videos for young explorers!</p>
                <button class="btn-primary btn-icon" id="addVideo">
                    <i class="fas fa-plus"></i> Add New Video
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Section -->
    <section class="video-grid-section">
        <div class="container">
            <div class="grid-header">
                <h2 class="section-title">Our Learning Videos</h2>
                <div class="action-buttons">
                    <button class="btn-primary btn-icon" id="filterAll">
                        <i class="fas fa-filter"></i> All Categories
                    </button>
                    <button class="btn-primary btn-icon" id="sortVideos">
                        <i class="fas fa-sort"></i> Sort by Date
                    </button>
                </div>
            </div>

            <!-- Video Grid -->
            <div class="video-grid" id="videoContainer">
                <?php while($video = $videos->fetch_assoc()): ?>
                <div class="video-card" data-id="<?= $video['id'] ?>">
                    <div class="card-thumbnail">
                        <iframe width="100%" height="200" 
                            src="https://www.youtube.com/embed/<?= getYouTubeId($video['url']) ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                        <div class="card-badge"><?= htmlspecialchars($video['category']) ?></div>
                        <div class="card-duration"><?= $video['duration'] ?>m</div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($video['title']) ?></h3>
                        <div class="card-meta">
                            <span>Age <?= htmlspecialchars($video['age']) ?></span>
                            <span><?= date('M d, Y', strtotime($video['created_at'])) ?></span>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(<?= $video['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $video['id'] ?>">
                                <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this fun video?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Video Modal -->
    <div class="crud-modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Video</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" id="videoId" name="id" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Video Title</label>
                        <input type="text" class="form-control" id="videoTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="videoCategory" name="category" required>
                            <option value="math">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="reading">Reading</option>
                            <option value="history">History</option>
                            <option value="art">Art & Creativity</option>
                            <option value="music">Music & Dance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">YouTube URL</label>
                        <input type="url" class="form-control" id="videoUrl" name="url" 
                               placeholder="https://youtube.com/watch?v=..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="videoDuration" name="duration" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age Group</label>
                        <input type="text" class="form-control" id="videoAge" name="age" 
                               placeholder="3-5, 6-8, etc." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-primary modal-close">Cancel</button>
                    <button type="submit" class="btn-primary">Save Video</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal handling
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('videoModal');
        
        // Open modal for adding new video
        document.getElementById('addVideo').addEventListener('click', function() {
            document.getElementById('modalTitle').textContent = 'Add New Video';
            document.getElementById('videoId').value = 0;
            document.querySelector('form').reset();
            modal.style.display = 'flex';
        });
        
        // Close modal
        document.querySelectorAll('.modal-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Open modal for editing video
    function openEditModal(id) {
        fetch(`get_video.php?id=${id}`)
            .then(response => response.json())
            .then(video => {
                document.getElementById('modalTitle').textContent = 'Edit Video';
                document.getElementById('videoId').value = video.id;
                document.getElementById('videoTitle').value = video.title;
                document.getElementById('videoCategory').value = video.category;
                document.getElementById('videoUrl').value = video.url;
                document.getElementById('videoDuration').value = video.duration;
                document.getElementById('videoAge').value = video.age;
                document.getElementById('videoModal').style.display = 'flex';
            })
            .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html>
