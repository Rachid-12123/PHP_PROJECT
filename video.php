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
    <title>spacekids - Video Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="videos.css">
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
                <h1 class="manager-title">Educational Video Library</h1>
                <p class="manager-subtitle">Manage and organize educational content for young learners</p>
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
                <h2 class="section-title">Current Videos</h2>
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
                                <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">
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
                        <select class="form-control form-select" id="videoCategory" name="category" required>
                            <option value="math">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="reading">Reading</option>
                            <option value="history">History</option>
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
                               placeholder="3-5" required>
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
