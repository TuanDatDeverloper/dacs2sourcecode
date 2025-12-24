<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$bookId = $_GET['id'] ?? null;

if (!$bookId) {
    header('Location: history.php');
    exit;
}

$pageTitle = 'Đọc sách - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Top Navigation Bar -->
    <nav class="navbar fixed top-0 left-0 right-0 z-50 px-6 py-4">
        <div class="container mx-auto flex items-center justify-between">
            <a href="book-info.php?id=<?php echo htmlspecialchars($bookId); ?>" class="flex items-center gap-2 text-gray-700 hover:text-[#FFB347] transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Quay lại</span>
            </a>
            
            <div class="flex items-center gap-4">
                <!-- Book Title -->
                <div class="hidden md:block text-center">
                    <div class="font-semibold text-gray-900" id="nav-book-title">Đang tải...</div>
                    <div class="text-xs text-gray-600" id="nav-book-chapter">-</div>
                </div>

                <!-- Settings -->
                <div class="flex items-center gap-2">
                    <!-- Font Size -->
                    <div class="relative group">
                        <button class="w-10 h-10 rounded-lg glass flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="fas fa-font"></i>
                        </button>
                        <div class="absolute right-0 top-full mt-2 glass rounded-lg p-2 hidden group-hover:block z-50">
                            <div class="flex gap-2">
                                <button class="font-size-btn px-3 py-1 rounded text-sm hover:bg-gray-50 text-gray-700" data-size="14px">A-</button>
                                <button class="font-size-btn px-3 py-1 rounded text-sm hover:bg-gray-50 text-gray-700 active" data-size="16px">A</button>
                                <button class="font-size-btn px-3 py-1 rounded text-sm hover:bg-gray-50 text-gray-700" data-size="18px">A+</button>
                                <button class="font-size-btn px-3 py-1 rounded text-sm hover:bg-gray-50 text-gray-700" data-size="20px">A++</button>
                            </div>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="w-10 h-10 rounded-lg glass flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-700">
                        <i class="fas fa-sun"></i>
                    </button>

                    <!-- Bookmark -->
                    <button onclick="toggleBookmark()" id="bookmark-btn" class="w-10 h-10 rounded-lg glass flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-700">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Reader Container -->
    <main class="pt-20 pb-8">
        <div class="container mx-auto px-6 max-w-4xl">
            <!-- Progress Bar -->
            <div class="mb-6 reveal">
                <div class="flex items-center justify-between mb-2 text-sm text-gray-600">
                    <span id="page-info">Trang 0 / 0</span>
                    <span class="font-semibold text-gray-900" id="progress-percent">0%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-bar-fill" style="width: 0%;"></div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="reading-loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải nội dung...</p>
            </div>

            <!-- Reading Content -->
            <div id="reading-content" class="reader-container glass rounded-2xl p-8 md:p-12 mb-8 reading-content text-lg leading-relaxed reveal" style="font-size: 18px; color: #2d2d2d; display: none;">
                <!-- Content will be loaded here -->
            </div>

            <!-- Navigation Controls -->
            <div class="flex items-center justify-between reveal" id="nav-controls" style="display: none;">
                <button onclick="previousPage()" class="px-6 py-3 glass rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium">
                    <i class="fas fa-chevron-left mr-2"></i>Trang trước
                </button>
                
                <div class="flex items-center gap-4">
                    <button onclick="toggleBookmark()" class="w-12 h-12 rounded-full glass flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-700">
                        <i class="far fa-bookmark" id="bookmark-icon"></i>
                    </button>
                    <button onclick="showTableOfContents()" class="w-12 h-12 rounded-full glass flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-700">
                        <i class="fas fa-list"></i>
                    </button>
                </div>

                <button onclick="nextPage()" class="px-6 py-3 glass rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium">
                    Trang sau<i class="fas fa-chevron-right ml-2"></i>
                </button>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <div class="fixed bottom-0 left-0 right-0 md:hidden glass border-t border-gray-200 p-4 z-40">
        <div class="flex items-center justify-between">
            <button onclick="previousPage()" class="w-12 h-12 rounded-full glass flex items-center justify-center text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="toggleBookmark()" class="w-12 h-12 rounded-full glass flex items-center justify-center text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="far fa-bookmark" id="bookmark-icon-mobile"></i>
            </button>
            <button onclick="nextPage()" class="w-12 h-12 rounded-full glass flex items-center justify-center text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/books-api-simple.js"></script>
    <script>
        const bookId = '<?php echo htmlspecialchars($bookId); ?>';
        let currentBook = null;
        let currentPage = 0;
        let totalPages = 0;
        let currentProgress = 0;
        let bookmarkPage = null;
        let autoSaveTimeout = null;
        
        // Load book and content
        async function loadBookContent() {
            const loadingEl = document.getElementById('reading-loading');
            const contentEl = document.getElementById('reading-content');
            const navControls = document.getElementById('nav-controls');
            
            try {
                console.log('📚 Loading book content for bookId:', bookId);
                
                // First, ensure book is in user_books with status 'reading'
                await ensureBookInLibrary();
                
                // Load book info
                console.log('📖 Fetching book info from API...');
                let book;
                try {
                    book = await window.APIClient.getBook(bookId);
                    console.log('✓ Book info loaded:', book);
                    
                    // If book doesn't have previewLink, try to get from BooksAPI
                    if (!book.previewLink && !book.preview_link && window.BooksAPI && typeof window.BooksAPI.getBookDetails === 'function') {
                        try {
                            console.log('⚠️ Book missing previewLink, fetching from BooksAPI...');
                            const apiBook = await window.BooksAPI.getBookDetails(bookId);
                            if (apiBook.previewLink) {
                                book.previewLink = apiBook.previewLink;
                                book.infoLink = apiBook.infoLink || book.infoLink;
                                console.log('✓ Preview link added:', book.previewLink);
                            }
                        } catch (apiError) {
                            console.warn('⚠️ Could not fetch preview link from API:', apiError);
                        }
                    }
                } catch (getBookError) {
                    console.error('❌ Error getting book:', getBookError);
                    // Try to get from BooksAPI if not in database
                    if (window.BooksAPI && typeof window.BooksAPI.getBookDetails === 'function') {
                        console.log('⚠️ Book not in database, trying BooksAPI...');
                        const apiBook = await window.BooksAPI.getBookDetails(bookId);
                        book = {
                            id: bookId,
                            title: apiBook.title || 'Unknown',
                            author: apiBook.authors?.[0] || apiBook.author || 'Unknown',
                            description: apiBook.description || '',
                            cover_url: apiBook.imageLinks?.thumbnail || apiBook.cover || '',
                            page_count: apiBook.pageCount || 100,
                            previewLink: apiBook.previewLink || '',
                            infoLink: apiBook.infoLink || '',
                            source: 'google_books'
                        };
                        console.log('✓ Book info from API:', book);
                    } else {
                        throw getBookError;
                    }
                }
                
                if (!book) {
                    throw new Error('Không thể tải thông tin sách');
                }
                
                currentBook = book;
                
                // Update title
                const titleEl = document.getElementById('nav-book-title');
                if (titleEl) {
                    titleEl.textContent = book.title || 'Đang tải...';
                }
                
                // Load progress
                await loadProgress();
                
                // Load book content
                await loadContent();
                
                // Hide loading, show content
                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) {
                    contentEl.style.display = 'block';
                    console.log('✓ Content displayed');
                }
                if (navControls) navControls.style.display = 'flex';
                
            } catch (error) {
                console.error('❌ Error loading book:', error);
                console.error('Error details:', error.message, error.stack);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Không thể tải nội dung sách</p>
                            <p class="text-gray-500 text-sm mb-4">${error.message || 'Lỗi không xác định'}</p>
                            <a href="book-info.php?id=${bookId}" class="inline-block mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                Quay lại
                            </a>
                        </div>
                    `;
                }
            }
        }
        
        // Ensure book is in user's library with status 'reading'
        async function ensureBookInLibrary() {
            console.log('🔍 ensureBookInLibrary: Starting for bookId=', bookId);
            try {
                // Try to get book first
                let book;
                try {
                    book = await window.APIClient.getBook(bookId);
                    console.log('✓ Book found in database:', book.title);
                } catch (getBookError) {
                    // Book doesn't exist in database - try to fetch from API and add it
                    console.warn('⚠️ Book not in database, trying to fetch from API...', getBookError);
                    
                    // Try to get book details from BooksAPI
                    if (typeof window.BooksAPI !== 'undefined' && typeof window.BooksAPI.getBookDetails === 'function') {
                        try {
                            const apiBook = await window.BooksAPI.getBookDetails(bookId);
                            console.log('✓ Book found in API, adding to database...', apiBook);
                            
                            // Add book to database
                            const bookData = {
                                id: bookId,
                                title: apiBook.title || 'Unknown',
                                author: apiBook.authors?.[0] || apiBook.author || 'Unknown',
                                description: apiBook.description || '',
                                cover_url: apiBook.imageLinks?.thumbnail || apiBook.cover || '',
                                page_count: apiBook.pageCount || 0,
                                published_date: apiBook.publishedDate || null,
                                isbn: apiBook.industryIdentifiers?.[0]?.identifier || '',
                                categories: apiBook.categories || [],
                                source: 'google_books',
                                status: 'reading'
                            };
                            
                            await window.APIClient.addBook(bookData);
                            console.log('✓ Book added to database from API');
                            book = bookData;
                        } catch (apiError) {
                            console.error('❌ Could not fetch book from API:', apiError);
                            alert('Sách này chưa có trong database và không thể tải từ API. Vui lòng thêm sách vào thư viện trước khi đọc.');
                            window.location.href = 'new-books.php';
                            return;
                        }
                    } else {
                        console.error('❌ BooksAPI not available');
                        alert('Sách này chưa có trong database. Vui lòng thêm sách vào thư viện trước khi đọc.');
                        window.location.href = 'new-books.php';
                        return;
                    }
                }
                
                // If book exists, try to update/add to user_books with status 'reading'
                // This will create the record if it doesn't exist
                try {
                    // Try to update status to 'reading'
                    console.log('🔄 Attempting to update book status to reading...');
                    await window.APIClient.updateBook(bookId, { status: 'reading' });
                    console.log('✓ Book status updated to reading via updateBook');
                } catch (updateError) {
                    // If update fails (book not in user_books), add it
                    console.log('⚠️ Book not in user_books, adding it...', updateError);
                    const bookData = {
                        id: bookId,
                        title: book.title || 'Unknown',
                        author: book.author || 'Unknown',
                        description: book.description || '',
                        cover_url: book.cover_url || book.cover || '',
                        page_count: book.page_count || 0,
                        published_date: book.published_date || null,
                        isbn: book.isbn || '',
                        categories: book.categories || [],
                        source: book.source || 'google_books',
                        status: 'reading'
                    };
                    await window.APIClient.addBook(bookData);
                    console.log('✓ Book added to library with reading status via addBook');
                }
            } catch (error) {
                console.error('❌ Error ensuring book in library:', error);
            }
        }
        
        // Load progress
        async function loadProgress() {
            try {
                const progress = await window.APIClient.getProgress(bookId);
                
                if (progress && progress.current_page !== undefined) {
                    currentPage = progress.current_page || 0;
                    totalPages = progress.total_pages || currentBook?.page_count || 0;
                    currentProgress = progress.progress_percent || 0;
                    bookmarkPage = progress.bookmark || null;
                    
                    // Update UI
                    updateProgressDisplay();
                    
                    // Update bookmark icon
                    if (bookmarkPage) {
                        document.getElementById('bookmark-icon').classList.remove('far');
                        document.getElementById('bookmark-icon').classList.add('fas');
                        if (document.getElementById('bookmark-icon-mobile')) {
                            document.getElementById('bookmark-icon-mobile').classList.remove('far');
                            document.getElementById('bookmark-icon-mobile').classList.add('fas');
                        }
                    }
                } else {
                    // Initialize progress - save initial progress to ensure tracking starts
                    totalPages = currentBook?.page_count || 100; // Default to 100 if unknown
                    currentPage = 0;
                    currentProgress = 0;
                    updateProgressDisplay();
                    
                    // Save initial progress to create tracking record and set status to 'reading'
                    // Even with 0% progress, this will mark the book as 'reading'
                    console.log('🔄 Initializing progress tracking...');
                    try {
                        const result = await window.APIClient.updateProgress(bookId, 0, 0);
                        console.log('✓ Initialized progress tracking:', result);
                    } catch (error) {
                        console.error('❌ Could not initialize progress:', error);
                        console.error('Error details:', error.message, error.stack);
                    }
                }
            } catch (error) {
                console.error('Error loading progress:', error);
                totalPages = currentBook?.page_count || 100;
                currentPage = 0;
                currentProgress = 0;
                updateProgressDisplay();
                
                // Try to initialize progress anyway
                if (totalPages > 0) {
                    try {
                        await window.APIClient.updateProgress(bookId, 0, 0);
                    } catch (initError) {
                        console.warn('Could not initialize progress:', initError);
                    }
                }
            }
        }
        
        // Load content (simplified - in production, load actual book content)
        async function loadContent() {
            const contentEl = document.getElementById('reading-content');
            
            // Check if book is uploaded file
            if (currentBook?.source === 'uploaded' && currentBook?.cover_url) {
                // For uploaded books, show file download/view option
                let fileUrl = currentBook.cover_url;
                
                // Fix URL path: convert absolute path to relative path
                // Path from database: /assets/uploads/books/...
                // Need to make it relative: assets/uploads/books/...
                if (!fileUrl.startsWith('http://') && !fileUrl.startsWith('https://')) {
                    // Remove leading slash to make it relative to current directory
                    if (fileUrl.startsWith('/')) {
                        fileUrl = fileUrl.substring(1);
                    }
                }
                
                const fileExtension = fileUrl.split('.').pop().toLowerCase();
                
                if (fileExtension === 'pdf') {
                    // Load PDF with PDF.js for page-by-page reading and progress tracking
                    console.log('📄 Loading PDF file:', currentBook.cover_url, '→', fileUrl);
                    await loadPDFContent(fileUrl);
                    return;
                } else if (fileExtension === 'txt' || fileExtension === 'html' || fileExtension === 'htm') {
                    // Load text/HTML content
                    try {
                        const response = await fetch(fileUrl);
                        const text = await response.text();
                        contentEl.innerHTML = `
                            <div class="text-center mb-6">
                                <h1 class="text-3xl font-bold mb-4 text-gray-900">${currentBook.title || 'Đang tải...'}</h1>
                                <p class="text-gray-600 mb-6">Tác giả: ${currentBook.author || 'Unknown'}</p>
                            </div>
                            <div class="prose max-w-none">
                                ${fileExtension === 'html' || fileExtension === 'htm' ? text : '<pre class="whitespace-pre-wrap font-sans">' + (text.replace(/</g, '&lt;').replace(/>/g, '&gt;')) + '</pre>'}
                            </div>
                        `;
                        return;
                    } catch (error) {
                        console.error('Error loading file content:', error);
                    }
                } else if (fileExtension === 'epub') {
                    // EPUB requires special handling - show download option for now
                    contentEl.innerHTML = `
                        <div class="text-center py-12">
                            <h1 class="text-3xl font-bold mb-4 text-gray-900">${currentBook.title || 'Đang tải...'}</h1>
                            <p class="text-gray-600 mb-6">Tác giả: ${currentBook.author || 'Unknown'}</p>
                            <p class="text-gray-600 mb-8">File EPUB cần phần mềm đọc sách chuyên dụng.</p>
                            <a href="${fileUrl}" download class="inline-block px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-download mr-2"></i>Tải xuống file EPUB
                            </a>
                        </div>
                    `;
                    return;
                }
            }
            
            // For books from APIs - try to get full content
            const bookSource = currentBook.source || '';
            console.log('📚 Book source:', bookSource, 'Book ID:', bookId);
            
            // Try to get full content from Gutenberg
            if ((bookSource === 'gutenberg' || bookId.startsWith('gutenberg_')) && window.BooksAPI && typeof window.BooksAPI.getGutenbergContent === 'function') {
                try {
                    console.log('📚 Attempting to fetch full content from Gutenberg...');
                    const contentData = await window.BooksAPI.getGutenbergContent(bookId);
                    
                    if (contentData && contentData.content && contentData.content.length > 100) {
                        console.log('✓ Got content from Gutenberg, type:', contentData.type, 'Length:', contentData.content.length);
                        await displayBookContent(contentData.content, contentData.type);
                        return;
                    } else {
                        console.warn('⚠️ Gutenberg content too short or empty');
                    }
                } catch (gutenbergError) {
                    console.warn('⚠️ Could not fetch from Gutenberg:', gutenbergError);
                    console.warn('Error details:', gutenbergError.message);
                }
            } else {
                console.log('⚠️ Not a Gutenberg book or getGutenbergContent not available');
                console.log('Book source:', bookSource, 'Book ID starts with gutenberg_:', bookId.startsWith('gutenberg_'));
            }
            
            // For other API books, show sample content with pagination
            // This provides a reading experience even without full content
            console.log('📖 Using sample content for API book');
            await displaySampleContent();
        }
        
        // Display book content from Gutenberg or other sources
        async function displayBookContent(rawContent, contentType = 'text') {
            const contentEl = document.getElementById('reading-content');
            if (!contentEl) return;
            
            // Split content into pages (approximately 2000 characters per page)
            const charsPerPage = 2000;
            let pages = [];
            
            if (contentType === 'html') {
                // For HTML, extract text and split
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = rawContent;
                const text = tempDiv.textContent || tempDiv.innerText || '';
                pages = splitIntoPages(text, charsPerPage);
            } else {
                // Plain text
                pages = splitIntoPages(rawContent, charsPerPage);
            }
            
            // Update total pages
            totalPages = pages.length;
            if (totalPages === 0) {
                totalPages = 1;
                pages = ['Nội dung sách không có sẵn.'];
            }
            
            // Save total pages to progress
            try {
                await window.APIClient.updateProgress(bookId, (currentPage / totalPages) * 100, currentPage);
            } catch (e) {
                console.warn('Could not update total pages:', e);
            }
            
            // Store pages globally for navigation
            window.bookPages = pages;
            
            // Display current page
            displayCurrentPage();
        }
        
        // Split text into pages
        function splitIntoPages(text, charsPerPage) {
            const pages = [];
            let currentPage = '';
            const paragraphs = text.split(/\n\s*\n/);
            
            for (const para of paragraphs) {
                if ((currentPage + para).length > charsPerPage && currentPage.length > 0) {
                    pages.push(currentPage.trim());
                    currentPage = para + '\n\n';
                } else {
                    currentPage += para + '\n\n';
                }
            }
            
            if (currentPage.trim().length > 0) {
                pages.push(currentPage.trim());
            }
            
            return pages.length > 0 ? pages : [text];
        }
        
        // Display current page from stored pages
        function displayCurrentPage() {
            const contentEl = document.getElementById('reading-content');
            if (!contentEl || !window.bookPages) return;
            
            const pageContent = window.bookPages[currentPage] || window.bookPages[0] || 'Nội dung không có sẵn.';
            
            // Format content nicely
            const formattedContent = `
                <div class="prose prose-lg max-w-none text-gray-800 leading-relaxed">
                    <div class="whitespace-pre-wrap font-sans">${escapeHtml(pageContent)}</div>
                </div>
            `;
            
            contentEl.innerHTML = formattedContent;
            updateProgressDisplay();
        }
        
        // Load PDF content with PDF.js
        let pdfDoc = null;
        let pdfPageNum = 1;
        let currentPdfUrl = null; // Store current PDF URL for error handling
        let finalPdfUrl = null; // Store final PDF URL for use in renderPDFPage
        
        async function loadPDFContent(pdfUrl) {
            const contentEl = document.getElementById('reading-content');
            if (!contentEl) return;
            
            try {
                // Set PDF.js worker
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                
                // Fix URL: ensure it's accessible from web root
                // Path from database: /assets/uploads/books/...
                // Website is at: /dacs2sourcecode/DACS2SourcecodeTuanAnh_ThanhThao/
                // So /assets/... needs to be relative to current page or use full path
                let finalPdfUrl = pdfUrl;
                
                if (!pdfUrl.startsWith('http://') && !pdfUrl.startsWith('https://')) {
                    // If path starts with /assets, we need to make it relative to current page
                    // Current page: /dacs2sourcecode/DACS2SourcecodeTuanAnh_ThanhThao/reading.php
                    // So /assets/... should become: ./assets/... or assets/...
                    if (pdfUrl.startsWith('/assets/')) {
                        // Remove leading slash to make it relative to current directory
                        finalPdfUrl = pdfUrl.substring(1);
                    } else if (!pdfUrl.startsWith('/')) {
                        // Already relative, keep as is
                        finalPdfUrl = pdfUrl;
                    } else {
                        // Other absolute paths, try to make relative
                        // Remove leading slash
                        finalPdfUrl = pdfUrl.substring(1);
                    }
                }
                
                currentPdfUrl = finalPdfUrl; // Store for error handling
                window.finalPdfUrl = finalPdfUrl; // Store globally for renderPDFPage
                
                console.log('📄 Loading PDF from:', pdfUrl, '→', finalPdfUrl);
                contentEl.innerHTML = `
                    <div class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                        <p class="mt-4 text-gray-600">Đang tải PDF...</p>
                    </div>
                `;
                
                // Load PDF document with the fixed URL
                const loadingTask = pdfjsLib.getDocument(finalPdfUrl);
                pdfDoc = await loadingTask.promise;
                
                // Update total pages
                totalPages = pdfDoc.numPages;
                console.log('✓ PDF loaded, total pages:', totalPages);
                
                // Update current page if we have saved progress
                if (currentPage > 0 && currentPage < totalPages) {
                    pdfPageNum = currentPage + 1; // PDF pages are 1-indexed
                }
                
                // Save total pages to database
                try {
                    await window.APIClient.updateProgress(bookId, (pdfPageNum / totalPages) * 100, pdfPageNum - 1);
                } catch (e) {
                    console.warn('Could not update total pages:', e);
                }
                
                // Render first page
                await renderPDFPage(pdfPageNum);
                
            } catch (error) {
                console.error('❌ Error loading PDF:', error);
                contentEl.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                        <p class="text-gray-600 mb-2">Không thể tải file PDF</p>
                        <p class="text-gray-500 text-sm mb-4">${error.message || 'Lỗi không xác định'}</p>
                        <div class="flex gap-4 justify-center">
                            <a href="${pdfUrl}" target="_blank" class="inline-block px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-external-link-alt mr-2"></i>Mở trong tab mới
                            </a>
                            <a href="${pdfUrl}" download class="inline-block px-6 py-3 glass border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 transition-all text-gray-700">
                                <i class="fas fa-download mr-2"></i>Tải xuống
                            </a>
                        </div>
                    </div>
                `;
            }
        }
        
        async function renderPDFPage(pageNum) {
            const contentEl = document.getElementById('reading-content');
            if (!contentEl || !pdfDoc) {
                console.warn('⚠️ Cannot render PDF page: contentEl or pdfDoc is missing');
                return;
            }
            
            try {
                console.log(`🖼️ Rendering PDF page ${pageNum}...`);
                
                // Get page
                const page = await pdfDoc.getPage(pageNum);
                
                // Set scale for rendering - increased for better readability
                // Calculate optimal scale based on container width (max-w-3xl = 768px)
                const containerWidth = 768; // max-w-3xl width
                const pageWidth = page.getViewport({ scale: 1.0 }).width;
                const optimalScale = Math.min((containerWidth - 32) / pageWidth, 2.5); // -32 for padding, max 2.5x
                const viewport = page.getViewport({ scale: optimalScale });
                
                // Update current page
                pdfPageNum = pageNum;
                currentPage = pageNum - 1; // Our system uses 0-indexed
                
                // Clear content and build structure with improved layout FIRST
                contentEl.innerHTML = `
                    <div class="max-w-4xl mx-auto">
                        <!-- Book Header (only show on first page or when needed) -->
                        ${pageNum === 1 ? `
                            <div class="text-center mb-8 pb-6 border-b border-gray-200">
                                <h1 class="text-4xl font-bold mb-3 text-gray-900">${currentBook.title || 'Đang tải...'}</h1>
                                <p class="text-lg text-gray-600">Tác giả: ${currentBook.author || 'Unknown'}</p>
                            </div>
                        ` : ''}
                        
                        <!-- PDF Canvas Container -->
                        <div class="flex justify-center mb-6">
                            <div class="w-full max-w-5xl bg-white rounded-xl shadow-2xl overflow-auto" id="pdf-canvas-container">
                                <!-- Canvas will be appended here -->
                            </div>
                        </div>
                        
                        <!-- Download Button (only show on first page) -->
                        ${pageNum === 1 ? `
                            <div class="text-center mt-6">
                                <a href="${window.finalPdfUrl || currentPdfUrl || currentBook.cover_url}" download class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all hover:scale-105">
                                    <i class="fas fa-download mr-2"></i>Tải xuống file PDF
                                </a>
                            </div>
                        ` : ''}
                    </div>
                `;
                
                // NOW get the container and create canvas
                const canvasContainer = document.getElementById('pdf-canvas-container');
                if (!canvasContainer) {
                    console.error('❌ Canvas container not found');
                    return;
                }
                
                // Create canvas
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                // Style canvas for better display - make it fill the container
                canvas.style.display = 'block';
                canvas.style.margin = '0 auto';
                canvas.style.maxWidth = '100%';
                canvas.style.height = 'auto';
                canvas.style.width = '100%';
                
                // Append canvas to container FIRST
                canvasContainer.appendChild(canvas);
                console.log(`✓ Canvas created and appended to DOM (scale: ${optimalScale.toFixed(2)})`);
                
                // Render page AFTER canvas is in DOM
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                
                await page.render(renderContext).promise;
                console.log(`✓ PDF page ${pageNum} rendered successfully`);
                
                // Update progress
                updateProgressDisplay();
                await saveProgress();
                
            } catch (error) {
                console.error('❌ Error rendering PDF page:', error);
                contentEl.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                        <p class="text-gray-600 mb-2">Không thể hiển thị trang PDF</p>
                        <p class="text-gray-500 text-sm">${error.message || 'Lỗi không xác định'}</p>
                    </div>
                `;
            }
        }
        
        // Display sample content (for books without full content)
        async function displaySampleContent() {
            const contentEl = document.getElementById('reading-content');
            if (!contentEl) return;
            
            // Ensure totalPages is set
            if (totalPages === 0) {
                totalPages = currentBook?.page_count || 100;
            }
            
            // Generate varied sample content based on current page
            const chapter = Math.floor(currentPage / 20) + 1;
            const pageInChapter = (currentPage % 20) + 1;
            
            // Large pool of different content variations
            const contentTemplates = [
                // Template 1: Adventure/Journey
                {
                    title: `Chương ${chapter}: Khởi đầu hành trình`,
                    paragraphs: [
                        `Trang ${pageInChapter} của chương ${chapter} mở ra với một cảnh tượng đầy hứa hẹn. Ánh nắng ban mai chiếu qua cửa sổ, đánh thức tôi khỏi giấc ngủ sâu. Tôi mở mắt và nhìn ra ngoài, thấy bầu trời trong xanh với những đám mây trắng trôi nhẹ nhàng.`,
                        `Hôm nay là một ngày mới, một khởi đầu mới cho hành trình của tôi. Tôi cảm thấy một năng lượng tích cực tràn ngập trong lòng, như thể mọi thứ đều có thể xảy ra.`,
                        `Tôi ngồi dậy và đi đến bàn làm việc, nơi có cuốn sách mà tôi đang đọc dở. Mỗi trang sách như một cánh cửa mở ra thế giới mới, đưa tôi đến những nơi xa xôi và những câu chuyện thú vị.`
                    ]
                },
                // Template 2: Discovery
                {
                    title: `Chương ${chapter}: Những khám phá mới`,
                    paragraphs: [
                        `Câu chuyện tiếp tục phát triển với những tình tiết bất ngờ ở trang ${pageInChapter}. Nhân vật chính đang đối mặt với những thử thách mới, và tôi cảm thấy như mình đang sống trong câu chuyện đó.`,
                        `Tác giả đã tạo ra một bức tranh sống động về cuộc sống, với những nhân vật đầy tính cách và những tình huống hấp dẫn. Mỗi chi tiết được mô tả một cách tinh tế, khiến tôi cảm thấy như mình đang chứng kiến mọi thứ đang diễn ra.`,
                        `Đọc sách không chỉ là một hoạt động giải trí, mà còn là một cách để mở rộng tầm nhìn và hiểu biết. Mỗi cuốn sách mang đến những bài học quý giá, những góc nhìn mới về cuộc sống.`
                    ]
                },
                // Template 3: Reflection
                {
                    title: `Chương ${chapter}: Suy ngẫm sâu sắc`,
                    paragraphs: [
                        `Trang ${pageInChapter} của chương ${chapter} mang đến những suy nghĩ sâu sắc về cuộc sống. Tôi cảm thấy như mình đang được dẫn dắt vào một thế giới của tri thức và trí tuệ, nơi mỗi câu văn đều có ý nghĩa riêng.`,
                        `Tôi tiếp tục đọc, chìm đắm trong thế giới của câu chuyện. Thời gian như ngừng lại, và tôi chỉ tập trung vào từng dòng chữ, từng câu văn. Đây là khoảnh khắc tuyệt vời nhất của việc đọc sách.`,
                        `Khi đọc đến cuối trang, tôi cảm thấy một cảm giác hài lòng và mong đợi. Câu chuyện đang trở nên thú vị hơn, và tôi không thể chờ đợi để đọc tiếp trang tiếp theo.`
                    ]
                },
                // Template 4: Mystery/Intrigue
                {
                    title: `Chương ${chapter}: Bí ẩn hé lộ`,
                    paragraphs: [
                        `Trang ${pageInChapter} tiết lộ những manh mối quan trọng. Những sự kiện bất ngờ xảy ra, khiến tôi không thể rời mắt khỏi trang sách. Mỗi câu văn như một mảnh ghép trong bức tranh lớn của câu chuyện.`,
                        `Tác giả đã xây dựng một cốt truyện hấp dẫn với nhiều lớp nghĩa. Tôi cảm thấy như mình đang tham gia vào một cuộc phiêu lưu trí tuệ, nơi mỗi trang sách mở ra những điều mới mẻ.`,
                        `Đọc đến đây, tôi nhận ra rằng cuốn sách này không chỉ là một câu chuyện, mà còn là một hành trình khám phá bản thân và thế giới xung quanh.`
                    ]
                },
                // Template 5: Character Development
                {
                    title: `Chương ${chapter}: Phát triển nhân vật`,
                    paragraphs: [
                        `Ở trang ${pageInChapter}, nhân vật chính đang trải qua những thay đổi quan trọng. Tôi cảm nhận được sự phát triển tâm lý và tính cách của họ qua từng dòng mô tả tinh tế.`,
                        `Tác giả đã khéo léo xây dựng nhân vật với nhiều chiều sâu. Mỗi hành động, mỗi suy nghĩ đều có lý do riêng, tạo nên một bức tranh sống động về con người và cuộc sống.`,
                        `Tôi cảm thấy đồng cảm với nhân vật, như thể mình đang sống trong câu chuyện đó. Đây chính là sức mạnh của văn học - kết nối người đọc với những trải nghiệm và cảm xúc.`
                    ]
                },
                // Template 6: Action/Conflict
                {
                    title: `Chương ${chapter}: Xung đột và hành động`,
                    paragraphs: [
                        `Trang ${pageInChapter} đưa câu chuyện đến cao trào. Những xung đột và thử thách xuất hiện, khiến tôi không thể dừng lại. Mỗi tình huống được mô tả một cách sinh động và hấp dẫn.`,
                        `Tác giả đã tạo ra một nhịp điệu câu chuyện hoàn hảo. Những khoảnh khắc căng thẳng được xen kẽ với những phút giây suy ngẫm, tạo nên một trải nghiệm đọc sách đầy cảm xúc.`,
                        `Tôi cảm thấy như mình đang chứng kiến một bộ phim sống động, nơi mỗi cảnh quay đều được mô tả chi tiết và ấn tượng. Đây là dấu ấn của một tác giả tài năng.`
                    ]
                },
                // Template 7: Emotional Journey
                {
                    title: `Chương ${chapter}: Hành trình cảm xúc`,
                    paragraphs: [
                        `Trang ${pageInChapter} chứa đựng những cảm xúc sâu sắc. Tôi cảm nhận được niềm vui, nỗi buồn, sự hy vọng và cả những nỗi lo lắng của nhân vật. Mỗi cảm xúc đều được mô tả một cách chân thực và tinh tế.`,
                        `Đọc sách là một cách để trải nghiệm những cảm xúc mà có thể chúng ta chưa từng trải qua trong cuộc sống thực. Nó giúp chúng ta hiểu hơn về bản thân và người khác.`,
                        `Tôi cảm thấy biết ơn vì có cơ hội được đọc và học hỏi từ những tác phẩm tuyệt vời như thế này. Đọc sách không chỉ mở rộng kiến thức mà còn nuôi dưỡng tâm hồn và trí tuệ.`
                    ]
                },
                // Template 8: Philosophical
                {
                    title: `Chương ${chapter}: Triết lý cuộc sống`,
                    paragraphs: [
                        `Trang ${pageInChapter} mang đến những suy ngẫm về cuộc sống và ý nghĩa của nó. Tác giả đã lồng ghép những bài học triết học một cách tự nhiên và sâu sắc vào câu chuyện.`,
                        `Mỗi câu văn như một hạt giống của tư duy, nảy mầm trong tâm trí tôi. Tôi cảm thấy như mình đang được mở rộng tầm nhìn về thế giới và cuộc sống.`,
                        `Đọc đến đây, tôi nhận ra rằng sách không chỉ là nguồn giải trí, mà còn là người thầy vĩ đại dạy chúng ta về cuộc sống, về con người, và về chính bản thân mình.`
                    ]
                }
            ];
            
            // Use page number to select variation (ensures different content for each page)
            const variationIndex = currentPage % contentTemplates.length;
            const variation = contentTemplates[variationIndex];
            
            // Add some dynamic elements based on page number
            const dynamicElements = [
                `Trang ${currentPage + 1} của cuốn sách này tiếp tục mở ra những điều mới mẻ.`,
                `Ở trang ${currentPage + 1}, câu chuyện đang phát triển theo một hướng thú vị.`,
                `Trang ${currentPage + 1} mang đến những thông tin quan trọng cho cốt truyện.`,
                `Đọc đến trang ${currentPage + 1}, tôi cảm thấy như mình đang khám phá một thế giới mới.`
            ];
            
            const dynamicIntro = dynamicElements[currentPage % dynamicElements.length];
            
            const sampleContent = `
                <h1 class="text-3xl font-bold mb-6 text-gray-900">${variation.title}</h1>
                
                <p class="mb-6 text-gray-800 leading-relaxed">
                    ${dynamicIntro}
                </p>
                
                ${variation.paragraphs.map(para => `
                    <p class="mb-6 text-gray-800 leading-relaxed">
                        ${para}
                    </p>
                `).join('')}
                
                <p class="mb-6 text-gray-800 leading-relaxed">
                    Mỗi trang sách là một bước tiến trong hành trình khám phá. Tôi cảm thấy biết ơn vì có cơ hội được đọc và học hỏi từ những tác phẩm tuyệt vời như thế này. Đọc sách không chỉ mở rộng kiến thức mà còn nuôi dưỡng tâm hồn và trí tuệ.
                </p>
            `;
            
            contentEl.innerHTML = sampleContent;
            updateProgressDisplay();
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Update progress display
        function updateProgressDisplay() {
            document.getElementById('page-info').textContent = `Trang ${currentPage} / ${totalPages}`;
            document.getElementById('progress-percent').textContent = Math.round(currentProgress) + '%';
            document.getElementById('progress-bar-fill').style.width = currentProgress + '%';
        }
        
        // Save progress
        async function saveProgress() {
            if (totalPages === 0) {
                console.warn('⚠️ Cannot save progress: totalPages is 0');
                return;
            }
            
            const progress = (currentPage / totalPages) * 100;
            currentProgress = progress;
            
            console.log(`💾 Saving progress: BookID=${bookId}, Page=${currentPage}/${totalPages}, Progress=${progress.toFixed(1)}%`);
            try {
                const result = await window.APIClient.updateProgress(bookId, progress, currentPage);
                console.log('✓ Progress saved:', result);
                updateProgressDisplay();
            } catch (error) {
                console.error('❌ Error saving progress:', error);
                console.error('Error details:', error.message);
            }
        }
        
        // Auto-save progress on scroll
        function setupAutoSave() {
            const contentEl = document.getElementById('reading-content');
            if (!contentEl) return;
            
            let lastScrollTop = 0;
            contentEl.addEventListener('scroll', () => {
                clearTimeout(autoSaveTimeout);
                
                // Calculate approximate page based on scroll position
                const scrollTop = contentEl.scrollTop;
                const scrollHeight = contentEl.scrollHeight;
                const clientHeight = contentEl.clientHeight;
                
                if (scrollHeight > clientHeight) {
                    const scrollPercent = (scrollTop / (scrollHeight - clientHeight)) * 100;
                    const estimatedPage = Math.round((scrollPercent / 100) * totalPages);
                    
                    if (estimatedPage !== currentPage && estimatedPage >= 0 && estimatedPage <= totalPages) {
                        currentPage = estimatedPage;
                        updateProgressDisplay();
                        
                        // Auto-save after 2 seconds of no scrolling
                        autoSaveTimeout = setTimeout(() => {
                            saveProgress();
                        }, 2000);
                    }
                }
            });
        }
        
        // Previous page
        function previousPage() {
            if (pdfDoc && pdfPageNum > 1) {
                renderPDFPage(pdfPageNum - 1);
            } else if (currentPage > 0) {
                currentPage--;
                if (window.bookPages) {
                    displayCurrentPage();
                } else {
                loadContent();
                }
                saveProgress();
            }
        }
        
        // Next page
        function nextPage() {
            if (pdfDoc && pdfPageNum < pdfDoc.numPages) {
                renderPDFPage(pdfPageNum + 1);
            } else if (currentPage < totalPages - 1) {
                currentPage++;
                if (window.bookPages) {
                    displayCurrentPage();
                } else {
                loadContent();
                }
                saveProgress();
            } else if (currentPage >= totalPages - 1) {
                // Book completed
                if (confirm('Bạn đã đọc xong cuốn sách này! Cập nhật trạng thái thành "Đã đọc"?')) {
                    window.APIClient.updateBook(bookId, { status: 'completed', progress: 100 });
                    alert('Chúc mừng! Bạn đã hoàn thành cuốn sách này!');
                    window.location.href = `book-info.php?id=${bookId}`;
                }
            }
        }
        
        // Toggle bookmark
        async function toggleBookmark() {
            bookmarkPage = bookmarkPage ? null : currentPage;
            
            try {
                // Update bookmark in progress
                await window.APIClient.updateProgress(bookId, currentProgress, currentPage);
                
                // Update icon
                const icons = document.querySelectorAll('#bookmark-icon, #bookmark-icon-mobile');
                icons.forEach(icon => {
                    if (bookmarkPage !== null) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                });
                
                // Show notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-20 right-4 glass rounded-lg px-4 py-2 text-sm text-gray-700 z-50';
                notification.textContent = bookmarkPage ? 'Đã đánh dấu trang ' + (bookmarkPage + 1) : 'Đã xóa đánh dấu';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            } catch (error) {
                console.error('Error toggling bookmark:', error);
            }
        }
        
        // Font size
        function setupFontSize() {
            const fontSizeBtns = document.querySelectorAll('.font-size-btn');
            const contentEl = document.getElementById('reading-content');
            
            fontSizeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    fontSizeBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const size = btn.getAttribute('data-size');
                    if (contentEl) {
                        contentEl.style.fontSize = size;
                    }
                });
            });
        }
        
        // Theme toggle
        function setupThemeToggle() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            const contentEl = document.getElementById('reading-content');
            
            themeToggle.addEventListener('click', () => {
                const isDark = document.body.classList.contains('reading-dark');
                
                if (isDark) {
                    // Switch to light
                    document.body.classList.remove('reading-dark');
                    document.body.className = 'bg-[#faf9f6] text-gray-900';
                    themeIcon.className = 'fas fa-sun';
                    if (contentEl) {
                        contentEl.style.background = '#ffffff';
                        contentEl.style.color = '#2d2d2d';
                    }
                } else {
                    // Switch to dark
                    document.body.classList.add('reading-dark');
                    document.body.className = 'bg-[#0a0e1a] text-white';
                    themeIcon.className = 'fas fa-moon';
                    if (contentEl) {
                        contentEl.style.background = '#1a1f2e';
                        contentEl.style.color = '#ffffff';
                    }
                }
            });
        }
        
        // Show table of contents
        function showTableOfContents() {
            alert('Mục lục sẽ được hiển thị ở đây');
        }
        
        // Keyboard shortcuts
        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    previousPage();
                } else if (e.key === 'ArrowRight') {
                    nextPage();
                } else if (e.key === 'b' || e.key === 'B') {
                    toggleBookmark();
                }
            });
        }
        
        // Load on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadBookContent();
            setupFontSize();
            setupThemeToggle();
            setupAutoSave();
            setupKeyboardShortcuts();
        });
        
        // Make functions global
        window.previousPage = previousPage;
        window.nextPage = nextPage;
        window.toggleBookmark = toggleBookmark;
        window.showTableOfContents = showTableOfContents;
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

