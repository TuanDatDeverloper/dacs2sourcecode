// ============================================
// MODEL LOADER - GLTF/GLB 3D Model Loader
// Handles loading and caching of 3D models
// ============================================

class ModelLoader {
    constructor() {
        this.loader = null;
        this.dracoLoader = null;
        this.cache = new Map();
        this.loadingPromises = new Map(); // Prevent duplicate loads
        this.initLoaders();
    }

    initLoaders() {
        // Initialize GLTFLoader
        // For Three.js r128, GLTFLoader should be loaded via script tag
        if (typeof THREE !== 'undefined') {
            // Check if GLTFLoader is already available (loaded via script tag)
            if (typeof THREE.GLTFLoader !== 'undefined') {
                this.loader = new THREE.GLTFLoader();
                console.log('✓ GLTFLoader initialized');
            } else {
                // Wait a bit for script to load, then try again
                setTimeout(() => {
                    if (typeof THREE.GLTFLoader !== 'undefined') {
                        this.loader = new THREE.GLTFLoader();
                        console.log('✓ GLTFLoader initialized (delayed)');
                    } else {
                        console.warn('GLTFLoader not available, will load dynamically when needed');
                    }
                }, 100);
            }
        } else {
            console.error('Three.js not loaded!');
        }
    }

    loadGLTFLoader() {
        // Load GLTFLoader from CDN for Three.js r128
        return new Promise((resolve, reject) => {
            if (this.loader) {
                resolve(this.loader);
                return;
            }

            // Try multiple CDN sources for GLTFLoader
            const cdnSources = [
                'https://threejs.org/examples/js/loaders/GLTFLoader.js',
                'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js',
                'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/examples/js/loaders/GLTFLoader.js'
            ];

            let currentSource = 0;

            const tryLoad = () => {
                if (currentSource >= cdnSources.length) {
                    reject(new Error('Failed to load GLTFLoader from all sources'));
                    return;
                }

                const script = document.createElement('script');
                script.src = cdnSources[currentSource];
                script.onload = () => {
                    // GLTFLoader for r128 is added to THREE namespace
                    if (THREE.GLTFLoader) {
                        this.loader = new THREE.GLTFLoader();
                        console.log('✓ GLTFLoader loaded successfully from:', cdnSources[currentSource]);
                        resolve(this.loader);
                    } else {
                        // Try next source
                        currentSource++;
                        tryLoad();
                    }
                };
                script.onerror = () => {
                    // Try next source
                    currentSource++;
                    tryLoad();
                };
                document.head.appendChild(script);
            };

            tryLoad();
        });
    }

    /**
     * Load a 3D model (GLTF/GLB format)
     * @param {string} path - Path to model file
     * @param {object} options - Loading options
     * @returns {Promise<THREE.Group>} - Loaded model group
     */
    async loadModel(path, options = {}) {
        // Check cache first
        if (this.cache.has(path)) {
            console.log(`[ModelLoader] Using cached model: ${path}`);
            return this.cloneModel(this.cache.get(path));
        }

        // Check if already loading
        if (this.loadingPromises.has(path)) {
            console.log(`[ModelLoader] Model already loading: ${path}`);
            return this.loadingPromises.get(path);
        }

        // Start loading
        const loadPromise = this._loadModelInternal(path, options);
        this.loadingPromises.set(path, loadPromise);

        try {
            const model = await loadPromise;
            this.cache.set(path, model);
            this.loadingPromises.delete(path);
            return this.cloneModel(model);
        } catch (error) {
            this.loadingPromises.delete(path);
            throw error;
        }
    }

    async _loadModelInternal(path, options) {
        // Ensure GLTFLoader is loaded
        if (!this.loader) {
            // Check if it's available now
            if (typeof THREE !== 'undefined' && typeof THREE.GLTFLoader !== 'undefined') {
                this.loader = new THREE.GLTFLoader();
            } else {
                // Try to load it dynamically
                await this.loadGLTFLoader();
            }
        }
        
        if (!this.loader) {
            throw new Error('GLTFLoader not available. Please ensure GLTFLoader.js is loaded.');
        }

        return new Promise((resolve, reject) => {
            console.log(`[ModelLoader] Loading model: ${path}`);
            
            this.loader.load(
                path,
                // onLoad
                (gltf) => {
                    console.log(`[ModelLoader] ✓ Model loaded: ${path}`);
                    const model = gltf.scene || gltf;
                    
                    // Optimize model if requested
                    if (options.optimize !== false) {
                        this.optimizeModel(model);
                    }

                    // Apply scale if specified
                    if (options.scale) {
                        model.scale.set(options.scale, options.scale, options.scale);
                    }

                    // Apply position if specified
                    if (options.position) {
                        model.position.set(
                            options.position.x || 0,
                            options.position.y || 0,
                            options.position.z || 0
                        );
                    }

                    // Apply rotation if specified
                    if (options.rotation) {
                        model.rotation.set(
                            options.rotation.x || 0,
                            options.rotation.y || 0,
                            options.rotation.z || 0
                        );
                    }

                    // Enable shadows
                    if (options.shadows !== false) {
                        model.traverse((child) => {
                            if (child.isMesh) {
                                child.castShadow = true;
                                child.receiveShadow = true;
                            }
                        });
                    }

                    resolve(model);
                },
                // onProgress
                (progress) => {
                    if (options.onProgress) {
                        const percent = (progress.loaded / progress.total) * 100;
                        options.onProgress(percent);
                    }
                },
                // onError
                (error) => {
                    console.error(`[ModelLoader] ✗ Error loading model: ${path}`, error);
                    reject(error);
                }
            );
        });
    }

    /**
     * Clone a model (for instancing)
     * @param {THREE.Group} model - Model to clone
     * @returns {THREE.Group} - Cloned model
     */
    cloneModel(model) {
        return model.clone();
    }

    /**
     * Optimize model for better performance
     * @param {THREE.Group} model - Model to optimize
     */
    optimizeModel(model) {
        model.traverse((child) => {
            if (child.isMesh) {
                // Optimize geometry
                if (child.geometry) {
                    child.geometry.computeBoundingBox();
                    child.geometry.computeBoundingSphere();
                }

                // Optimize materials
                if (child.material) {
                    // Use simpler material if needed
                    if (Array.isArray(child.material)) {
                        child.material.forEach(mat => this.optimizeMaterial(mat));
                    } else {
                        this.optimizeMaterial(child.material);
                    }
                }
            }
        });
    }

    /**
     * Optimize material
     * @param {THREE.Material} material - Material to optimize
     */
    optimizeMaterial(material) {
        // Reduce texture size if too large
        if (material.map && material.map.image) {
            const img = material.map.image;
            if (img.width > 1024 || img.height > 1024) {
                console.warn('[ModelLoader] Large texture detected, consider resizing');
            }
        }

        // Enable shadow receiving
        material.needsUpdate = true;
    }

    /**
     * Preload models
     * @param {string[]} paths - Array of model paths to preload
     */
    async preloadModels(paths) {
        console.log(`[ModelLoader] Preloading ${paths.length} models...`);
        const promises = paths.map(path => this.loadModel(path, { optimize: true }));
        await Promise.all(promises);
        console.log('[ModelLoader] ✓ All models preloaded');
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
        console.log('[ModelLoader] Cache cleared');
    }

    /**
     * Get cache size
     * @returns {number} - Number of cached models
     */
    getCacheSize() {
        return this.cache.size;
    }
}

// Export for use in other files
if (typeof window !== 'undefined') {
    window.ModelLoader = ModelLoader;
}

