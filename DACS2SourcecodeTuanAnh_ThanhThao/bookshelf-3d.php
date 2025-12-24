<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Kệ sách 3D - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- 3D Canvas Container -->
    <div id="canvas-container"></div>

    <!-- Toggle Control Panel Button -->
    <button 
        id="toggle-control-panel-btn"
        onclick="toggleControlPanel()"
        class="toggle-control-btn"
        aria-label="Toggle Control Panel"
    >
        <i class="fas fa-chevron-left" id="toggle-icon"></i>
    </button>

    <!-- Control Panel -->
    <div class="control-panel" id="control-panel">
        <h3 class="text-lg font-bold mb-4 text-gray-900">
            <i class="fas fa-sliders-h text-[#FFB347] mr-2"></i>
            Điều khiển
        </h3>
        
        <div class="space-y-3">
            <button
                onclick="resetCamera()"
                class="w-full px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
            >
                <i class="fas fa-redo mr-2"></i>Reset Camera
            </button>
            
            <button
                onclick="toggleDecorations()"
                id="toggle-decorations-btn"
                class="w-full px-4 py-2 glass border border-gray-200 rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium"
            >
                <i class="fas fa-star mr-2"></i>Ẩn/Hiện trang trí
            </button>
            
            <button
                onclick="toggleAutoRotate()"
                id="toggle-rotate-btn"
                class="w-full px-4 py-2 glass border border-gray-200 rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium"
            >
                <i class="fas fa-sync mr-2"></i>Tự động xoay
            </button>
            
            <div class="pt-3 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tốc độ xoay</label>
                <input
                    type="range"
                    id="rotation-speed"
                    min="0"
                    max="2"
                    step="0.1"
                    value="0.5"
                    oninput="updateRotationSpeed(this.value)"
                    class="w-full"
                />
            </div>
            
            <div class="pt-3 border-t border-gray-200">
                <button
                    onclick="saveLayout()"
                    class="w-full px-4 py-2 bg-gradient-to-r from-[#4A7856] to-[#4A7856]/90 text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-save mr-2"></i>Lưu bố cục
                </button>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-600 mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Hướng dẫn:
            </p>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• Kéo chuột: Xoay camera</li>
                <li>• Scroll: Zoom in/out</li>
                <li>• Click sách: Xem chi tiết</li>
            </ul>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="info-panel" id="book-info-panel" style="display: none;">
        <div class="flex items-start justify-between mb-2">
            <h4 id="info-book-title" class="font-bold text-gray-900"></h4>
            <button onclick="closeBookInfo()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p id="info-book-author" class="text-sm text-gray-600 mb-2"></p>
        <a
            id="info-book-link"
            href="#"
            class="text-sm text-[#FFB347] hover:text-[#FF9500] font-medium"
        >
            Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 flex items-center justify-center pointer-events-none">
        <div class="text-center pointer-events-auto">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-white mb-4"></div>
            <p class="text-white font-semibold">Đang tải kệ sách 3D...</p>
            <p class="text-white/80 text-sm mt-2">Bạn vẫn có thể sử dụng menu</p>
        </div>
    </div>

    <style>
        body {
            margin: 0;
            overflow: hidden;
        }
        /* Hide footer for 3D bookshelf page - it should be fullscreen */
        footer {
            display: none !important;
        }
        /* Ensure header/nav is always accessible */
        header, nav {
            z-index: 1000 !important;
            position: relative !important;
        }
        #canvas-container {
            width: 100vw;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .toggle-control-btn {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            z-index: 101;
            width: 40px;
            height: 80px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 12px 0 0 12px;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: #4A7856;
            font-size: 18px;
        }
        
        .toggle-control-btn:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: -2px 0 12px rgba(0, 0, 0, 0.2);
            width: 45px;
        }
        
        .toggle-control-btn i {
            transition: transform 0.3s ease;
        }
        
        .control-panel {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 250px;
            max-width: 300px;
            pointer-events: auto;
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: translateX(0);
            opacity: 1;
        }
        
        .control-panel.hidden {
            transform: translateX(calc(100% + 20px));
            opacity: 0;
            pointer-events: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .control-panel {
                top: 60px;
                right: 10px;
                left: 10px;
                max-width: none;
                min-width: auto;
                width: calc(100% - 20px);
                max-height: calc(100vh - 80px);
                overflow-y: auto;
            }
            
            .toggle-control-btn {
                width: 35px;
                height: 60px;
                font-size: 16px;
            }
            
            .toggle-control-btn:hover {
                width: 40px;
            }
        }
        
        @media (max-width: 480px) {
            .control-panel {
                padding: 15px;
                font-size: 14px;
            }
            
            .control-panel h3 {
                font-size: 1.1rem;
            }
        }
        .info-panel {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 300px;
            pointer-events: auto;
        }
    </style>

    <!-- Three.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <!-- OrbitControls for Three.js r128 -->
    <script src="https://threejs.org/examples/js/controls/OrbitControls.js"></script>
    <!-- GLTFLoader for Three.js r128 -->
    <script src="https://threejs.org/examples/js/loaders/GLTFLoader.js"></script>
    
    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <!-- Model Loader for 3D models -->
    <script src="js/model-loader.js"></script>
    <!-- Bookshelf 3D V2 - Clean & Professional -->
    <script src="js/bookshelf-3d-v2.js"></script>
    <script>
        // Toggle control panel
        let controlPanelVisible = true;
        
        function toggleControlPanel() {
            const panel = document.getElementById('control-panel');
            const btn = document.getElementById('toggle-control-panel-btn');
            const icon = document.getElementById('toggle-icon');
            
            controlPanelVisible = !controlPanelVisible;
            
            if (controlPanelVisible) {
                panel.classList.remove('hidden');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            } else {
                panel.classList.add('hidden');
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            }
        }
        
        // Make function globally available
        window.toggleControlPanel = toggleControlPanel;
        
        // Auto-hide on mobile after 3 seconds
        if (window.innerWidth <= 768) {
            setTimeout(() => {
                if (controlPanelVisible) {
                    toggleControlPanel();
                }
            }, 3000);
        }
    </script>
    <script>
        // Initialize new bookshelf when page loads
        document.addEventListener('DOMContentLoaded', () => {
            // Wait for Three.js and scripts to load
            setTimeout(() => {
                if (typeof THREE === 'undefined') {
                    console.error('Three.js not loaded!');
                    return;
                }
                if (typeof init === 'function') {
                    try {
                        init();
                    } catch (error) {
                        console.error('Error initializing bookshelf:', error);
                    }
                } else {
                    console.error('init function not found. Available functions:', Object.keys(window).filter(k => k.includes('book')));
                }
            }, 200);
        });
        
        // Save layout function
        window.saveLayout = async function() {
            try {
                // Get current layout data from scene
                const layoutData = {
                    books: window.books || [],
                    decorations: window.decorations || [],
                    camera: {
                        position: window.camera ? {
                            x: window.camera.position.x,
                            y: window.camera.position.y,
                            z: window.camera.position.z
                        } : null
                    }
                };
                
                await window.APIClient.saveBookshelfLayout(layoutData);
                
                // Show success message
                const notification = document.createElement('div');
                notification.className = 'fixed top-20 right-20 glass rounded-lg px-4 py-2 text-sm text-gray-700 z-50';
                notification.innerHTML = '<i class="fas fa-check mr-2"></i>Đã lưu bố cục!';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            } catch (error) {
                console.error('Error saving layout:', error);
                alert('Lỗi khi lưu bố cục: ' + error.message);
            }
        };
    </script>

</body>
</html>

