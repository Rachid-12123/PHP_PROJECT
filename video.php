<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WonderLearn - Video Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* === Custom Properties === */
        :root {
            --primary: #2F80ED;
            --secondary: #56CCF2;
            --accent: #F2C94C;
            --success: #6FCF97;
            --warning: #F2994A;
            --danger: #EB5757;
            --dark: #2D3436;
            --light: #F8F9FA;
            --gradient-primary: linear-gradient(135deg, #2F80ED 0%, #56CCF2 100%);
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-light: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-medium: 0 8px 24px rgba(0,0,0,0.12);
            --transition-fast: all 0.2s ease;
            --transition-smooth: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        /* === Base Styles === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito', sans-serif;
        }

        body {
            background: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* === Video Manager Header === */
        .manager-header {
            background: var(--gradient-primary);
            padding: 4rem 0;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-medium);
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            color: white;
        }

        .manager-title {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .manager-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        /* === Video Grid Section === */
        .video-grid-section {
            padding: 4rem 0;
        }

        .grid-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2.2rem;
            color: var(--dark);
            position: relative;
            padding-left: 1rem;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 80%;
            background: var(--accent);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        /* === Video Grid === */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .video-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: var(--transition-smooth);
            position: relative;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .card-thumbnail {
            height: 200px;
            position: relative;
            overflow: hidden;
        }

        .thumbnail-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-smooth);
        }

        .video-card:hover .thumbnail-image {
            transform: scale(1.05);
        }

        .card-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--accent);
            color: var(--dark);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .card-duration {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
        }

        .card-content {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
            color: var(--dark);
            font-weight: 700;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #666;
        }

        .card-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.6rem 1rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .edit-btn {
            background: var(--primary);
            color: white;
        }

        .delete-btn {
            background: var(--danger);
            color: white;
        }

        /* === CRUD Modal === */
        .crud-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            width: 600px;
            max-width: 95%;
            animation: modalSlide 0.4s ease;
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
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: var(--transition-fast);
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #eee;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: var(--transition-fast);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.1);
        }

        .form-select {
            appearance: none;
            background: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 1rem center/1em;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        /* === Animations === */
        @keyframes modalSlide {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* === Utility Classes === */
        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .btn-primary:hover {
            background: #296cc7;
        }

        .btn-icon {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-message {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 2rem;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-light);
            display: none;
            animation: slideIn 0.3s ease;
        }

        .success-message {
            background: var(--success);
            color: white;
        }

        .error-message {
            background: var(--danger);
            color: white;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }

        /* === Responsive Design === */
        @media (max-width: 1200px) {
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .grid-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .action-buttons {
                width: 100%;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }

            .manager-title {
                font-size: 2rem;
            }

            .card-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Video Manager Header -->
    <header class="manager-header">
        <div class="container">
            <div class="header-content">
                <h1 class="manager-title">Educational Video Library</h1>
                <p class="manager-subtitle">Manage and organize educational content for young learners</p>
                <button class="btn-primary btn-icon" id="addVideo">
                    <i class="fas fa-plus"></i>
                    Add New Video
                </button>
            </div>
        </div>
    </header>

    <!-- Video Grid Section -->
    <section class="video-grid-section">
        <div class="container">
            <div class="grid-header">
                <h2 class="section-title">Current Videos</h2>
                <div class="action-buttons">
                    <button class="btn-primary btn-icon" id="filterAll">
                        <i class="fas fa-filter"></i>
                        All Categories
                    </button>
                    <button class="btn-primary btn-icon" id="sortVideos">
                        <i class="fas fa-sort"></i>
                        Sort by Date
                    </button>
                </div>
            </div>

            <div class="video-grid" id="videoContainer">
                <!-- Video cards will be dynamically inserted here -->
            </div>
        </div>
    </section>

    <!-- CRUD Modal -->
    <div class="crud-modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Video</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="videoForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Video Title</label>
                        <input type="text" class="form-control" id="videoTitle" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-control form-select" id="videoCategory" required>
                            <option value="math">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="reading">Reading</option>
                            <option value="history">History</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Video URL</label>
                        <input type="url" class="form-control" id="videoUrl" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="videoDuration" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age Group</label>
                        <input type="text" class="form-control" id="videoAge" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-primary modal-close">Cancel</button>
                    <button type="submit" class="btn-primary">Save Video</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Messages -->
    <div class="status-message" id="successMessage">
        <i class="fas fa-check-circle"></i> Operation completed successfully!
    </div>

    <script>
        // Video Data Management
        let videos = JSON.parse(localStorage.getItem('videos')) || [];
        let currentEditId = null;

        // DOM Elements
        const videoContainer = document.getElementById('videoContainer');
        const videoModal = document.getElementById('videoModal');
        const videoForm = document.getElementById('videoForm');
        const addVideoBtn = document.getElementById('addVideo');

        // Event Listeners
        addVideoBtn.addEventListener('click', () => openModal());
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => closeModal());
        });

        videoForm.addEventListener('submit', handleFormSubmit);

        // Initialize
        renderVideos();

        // Functions
        function openModal(editId = null) {
            currentEditId = editId;
            if(editId) {
                const video = videos.find(v => v.id === editId);
                document.getElementById('videoTitle').value = video.title;
                document.getElementById('videoCategory').value = video.category;
                document.getElementById('videoUrl').value = video.url;
                document.getElementById('videoDuration').value = video.duration;
                document.getElementById('videoAge').value = video.age;
                document.querySelector('.modal-title').textContent = 'Edit Video';
            }
            videoModal.style.display = 'flex';
        }

        function closeModal() {
            videoModal.style.display = 'none';
            videoForm.reset();
            currentEditId = null;
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            const videoData = {
                id: currentEditId || Date.now(),
                title: document.getElementById('videoTitle').value,
                category: document.getElementById('videoCategory').value,
                url: document.getElementById('videoUrl').value,
                duration: document.getElementById('videoDuration').value,
                age: document.getElementById('videoAge').value,
                timestamp: currentEditId ? new Date().toISOString() : new Date().toISOString()
            };

            if(currentEditId) {
                const index = videos.findIndex(v => v.id === currentEditId);
                videos[index] = videoData;
            } else {
                videos.push(videoData);
            }

            localStorage.setItem('videos', JSON.stringify(videos));
            renderVideos();
            showMessage('success', `Video ${currentEditId ? 'updated' : 'added'} successfully`);
            closeModal();
        }

        function renderVideos() {
            videoContainer.innerHTML = videos.map(video => `
                <div class="video-card" data-id="${video.id}">
                    <div class="card-thumbnail">
                        <img src="${getThumbnail(video.url)}" class="thumbnail-image" alt="${video.title}">
                        <div class="card-badge">${video.category}</div>
                        <div class="card-duration">${video.duration}m</div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">${video.title}</h3>
                        <div class="card-meta">
                            <span>Age ${video.age}</span>
                            <span>${new Date(video.timestamp).toLocaleDateString()}</span>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn edit-btn" onclick="openModal(${video.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteVideo(${video.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function deleteVideo(id) {
            if(confirm('Are you sure you want to delete this video?'))
            	                                videos = videos.filter(v => v.id !== id);
            localStorage.setItem('videos', JSON.stringify(videos));
            renderVideos();
            showMessage('success', 'Video deleted successfully');
        }
        
        function showMessage(type, text) {
            const message = document.getElementById(`${type}Message`);
            message.textContent = text;
            message.style.display = 'flex';
            setTimeout(() => {
                message.style.display = 'none';
            }, 3000);
        }

        function getThumbnail(url) {
            // Simple YouTube thumbnail extraction
            const videoId = url.split('v=')[1];
            return videoId ? `https://img.youtube.com/vi/${videoId}/hqdefault.jpg` : 'placeholder.jpg';
        }
    </script>

    <!-- Add 10 sample video cards -->
    <script>
        // Initial sample videos
        if(videos.length === 0) {
            videos = [
                {
                    id: 1,
                    title: "Counting with Animals",
                    category: "math",
                    url: "https://youtu.be/dQw4w9WgXcQ",
                    duration: 12,
                    age: "3-5",
                    timestamp: "2024-03-15"
                },
                {
                    id: 2,
                    title: "Solar System Exploration",
                    category: "science",
                    url: "https://youtu.be/dQw4w9WgXcQ",
                    duration: 18,
                    age: "6-8",
                    timestamp: "2024-03-14"
                },
                // Add 8 more sample videos with unique IDs
            ];
            localStorage.setItem('videos', JSON.stringify(videos));
        }
        renderVideos();
    </script>
</body>
</html>
