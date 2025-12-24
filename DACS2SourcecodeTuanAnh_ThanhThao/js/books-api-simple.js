// ============================================
// BOOKS API - Simple Version (Non-module)
// ============================================

const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
const OPEN_LIBRARY_API = 'https://openlibrary.org';
const GUTENBERG_API = 'https://gutendex.com';
const INTERNET_ARCHIVE_API = 'https://archive.org/advancedsearch.php';
const LIBRARY_OF_CONGRESS_API = 'https://www.loc.gov/books';
const HATHITRUST_API = 'https://catalog.hathitrust.org/api/volumes';

function getDefaultCover() {
    return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="300"%3E%3Crect fill="%23faf9f6" width="200" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23FFB347" font-family="sans-serif" font-size="14" font-weight="bold"%3EBook Cover%3C/text%3E%3C/svg%3E';
}

function formatBooksData(items) {
    return items.map(item => {
        const volumeInfo = item.volumeInfo || {};
        const saleInfo = item.saleInfo || {};
        
        return {
            id: item.id,
            title: volumeInfo.title || 'Không có tiêu đề',
            authors: volumeInfo.authors || ['Tác giả không xác định'],
            author: volumeInfo.authors?.[0] || 'Tác giả không xác định',
            description: volumeInfo.description || 'Không có mô tả',
            cover: volumeInfo.imageLinks?.thumbnail?.replace('http://', 'https://') || 
                   volumeInfo.imageLinks?.smallThumbnail?.replace('http://', 'https://') || 
                   getDefaultCover(),
            categories: volumeInfo.categories || ['Khác'],
            category: volumeInfo.categories?.[0] || 'Khác',
            publishedDate: volumeInfo.publishedDate || 'N/A',
            pageCount: volumeInfo.pageCount || 0,
            rating: volumeInfo.averageRating || 0,
            ratingsCount: volumeInfo.ratingsCount || 0,
            language: volumeInfo.language || 'vi',
            previewLink: volumeInfo.previewLink || '',
            infoLink: volumeInfo.infoLink || '',
            isFree: saleInfo.saleability === 'FREE' || saleInfo.isEbook === true,
            isbn: volumeInfo.industryIdentifiers?.[0]?.identifier || ''
        };
    });
}

function formatBookDetail(item) {
    const volumeInfo = item.volumeInfo || {};
    const saleInfo = item.saleInfo || {};
    
    return {
        id: item.id,
        title: volumeInfo.title || 'Không có tiêu đề',
        authors: volumeInfo.authors || ['Tác giả không xác định'],
        author: volumeInfo.authors?.[0] || 'Tác giả không xác định',
        description: volumeInfo.description || 'Không có mô tả',
        cover: volumeInfo.imageLinks?.large?.replace('http://', 'https://') || 
               volumeInfo.imageLinks?.medium?.replace('http://', 'https://') ||
               volumeInfo.imageLinks?.thumbnail?.replace('http://', 'https://') || 
               volumeInfo.imageLinks?.smallThumbnail?.replace('http://', 'https://') || 
               getDefaultCover(),
        categories: volumeInfo.categories || ['Khác'],
        category: volumeInfo.categories?.[0] || 'Khác',
        publishedDate: volumeInfo.publishedDate || 'N/A',
        pageCount: volumeInfo.pageCount || 0,
        rating: volumeInfo.averageRating || 0,
        ratingsCount: volumeInfo.ratingsCount || 0,
        language: volumeInfo.language || 'vi',
        previewLink: volumeInfo.previewLink || '',
        infoLink: volumeInfo.infoLink || '',
        isFree: saleInfo.saleability === 'FREE' || saleInfo.isEbook === true,
        isbn: volumeInfo.industryIdentifiers?.[0]?.identifier || '',
        publisher: volumeInfo.publisher || 'N/A',
        publishedYear: volumeInfo.publishedDate?.split('-')[0] || 'N/A'
    };
}

// Create BooksAPI object
console.log('Initializing BooksAPI...');

// Ensure window.BooksAPI exists
if (!window.BooksAPI) {
    window.BooksAPI = {};
}

// Create or extend BooksAPI object
window.BooksAPI = Object.assign(window.BooksAPI || {}, {
    async searchBooks(query, maxResults = 20) {
        try {
            console.log('Searching books:', query);
            
            if (!query || !query.trim()) {
                console.warn('Query rỗng, trả về mảng rỗng');
                return [];
            }
            
            let allBooks = [];
            const seenIds = new Set();
            const cleanQuery = query.trim();
            
            // Try multiple search strategies
            const searchQueries = [
                // 1. Try as author name (inauthor:)
                `inauthor:"${cleanQuery}"`,
                // 2. Try as title (intitle:)
                `intitle:"${cleanQuery}"`,
                // 3. Try general search
                cleanQuery,
                // 4. Try without quotes for partial match
                cleanQuery.replace(/"/g, '')
            ];
            
            // Search from multiple sources
            for (const searchQuery of searchQueries) {
                if (allBooks.length >= maxResults) break;
                
                try {
                    // Google Books API
            const response = await fetch(
                        `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(searchQuery)}&maxResults=${Math.min(maxResults * 2, 40)}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.items && data.items.length > 0) {
                            const formatted = formatBooksData(data.items);
                            formatted.forEach(book => {
                                // Check if book matches the query (author or title)
                                const queryLower = cleanQuery.toLowerCase();
                                const matchesAuthor = book.authors.some(author => 
                                    author.toLowerCase().includes(queryLower)
                                );
                                const matchesTitle = book.title.toLowerCase().includes(queryLower);
                                
                                // Add if matches and not duplicate
                                if ((matchesAuthor || matchesTitle) && !seenIds.has(book.id) && allBooks.length < maxResults) {
                                    seenIds.add(book.id);
                                    allBooks.push(book);
                                }
                            });
                            
                            if (allBooks.length >= maxResults) break;
                        }
                    }
                } catch (e) {
                    console.warn(`Error searching with query "${searchQuery}":`, e);
                }
            }
            
            // Also search Open Library for author/title
            if (allBooks.length < maxResults) {
                try {
                    // Try as author
                    const openLibResponse = await fetch(
                        `${OPEN_LIBRARY_API}/search.json?author=${encodeURIComponent(cleanQuery)}&limit=${Math.min(20, maxResults - allBooks.length)}`
                    );
                    
                    if (openLibResponse.ok) {
                        const data = await openLibResponse.json();
                        if (data.docs && data.docs.length > 0) {
                            data.docs.forEach(book => {
                                const id = `openlib_${book.key}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults) {
                                    const queryLower = cleanQuery.toLowerCase();
                                    const matchesAuthor = book.author_name?.some(name => 
                                        name.toLowerCase().includes(queryLower)
                                    );
                                    const matchesTitle = book.title?.toLowerCase().includes(queryLower);
                                    
                                    if (matchesAuthor || matchesTitle) {
                                        seenIds.add(id);
                                        allBooks.push({
                                            id: id,
                                            title: book.title || 'Không có tiêu đề',
                                            authors: book.author_name || ['Tác giả không xác định'],
                                            author: book.author_name?.[0] || 'Tác giả không xác định',
                                            description: book.first_sentence?.[0] || '',
                                            cover: book.cover_i ? 
                                                `https://covers.openlibrary.org/b/id/${book.cover_i}-L.jpg` : 
                                                getDefaultCover(),
                                            categories: book.subject || [],
                                            category: book.subject?.[0] || 'General',
                                            publishedDate: book.first_publish_year?.toString() || 'N/A',
                                            pageCount: 0,
                                            rating: 0,
                                            ratingsCount: 0,
                                            language: book.language?.[0] || 'en',
                                            previewLink: `https://openlibrary.org${book.key}`,
                                            infoLink: `https://openlibrary.org${book.key}`,
                                            isFree: true,
                                            isbn: book.isbn?.[0] || '',
                                            source: 'openlibrary'
                                        });
                                    }
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.warn('Error searching Open Library:', e);
                }
            }
            
            console.log(`Search results: ${allBooks.length} books found for query "${query}"`);
            return allBooks.slice(0, maxResults);
        } catch (error) {
            console.error('Lỗi khi tìm kiếm sách:', error);
            return [];
        }
    },

    async getPopularBooks(subject = 'fiction', maxResults = 20) {
        try {
            console.log('Loading popular books...', subject);
            
            // Build query - if subject is empty, use general search
            let query = '';
            if (subject && subject.trim()) {
                query = `subject:${subject}`;
            } else {
                query = 'best books';
            }
            
            // Try without langRestrict first, as it might limit results too much
            let url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&orderBy=relevance&maxResults=${maxResults}`;
            console.log('Fetching from:', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Popular books API response:', data);
            console.log('Total items:', data.totalItems, 'Items in response:', data.items?.length);
            
            if (!data.items || data.items.length === 0) {
                console.warn('Không có sách nào được trả về từ API');
                return [];
            }
            
            const formattedBooks = formatBooksData(data.items);
            console.log('Formatted books:', formattedBooks.length);
            return formattedBooks;
        } catch (error) {
            console.error('Lỗi khi lấy sách phổ biến:', error);
            // Return empty array instead of throwing to allow fallback
            return [];
        }
    },

    async getBookDetails(bookId) {
        try {
            // Check if it's a Gutenberg book
            if (bookId.startsWith('gutenberg_')) {
                const gutenbergId = bookId.replace('gutenberg_', '');
                const response = await fetch(`${GUTENBERG_API}/books/${gutenbergId}`);
                
                if (response.ok) {
                    const data = await response.json();
                    return {
                        id: bookId,
                        title: data.title || 'Unknown',
                        authors: data.authors?.map(a => a.name) || ['Unknown'],
                        author: data.authors?.[0]?.name || 'Unknown',
                        description: data.subjects?.join(', ') || '',
                        cover: data.formats?.['image/jpeg'] || data.formats?.['image/png'] || getDefaultCover(),
                        categories: data.subjects || [],
                        pageCount: 0,
                        previewLink: data.formats?.['text/html'] || data.formats?.['application/epub+zip'] || '',
                        infoLink: `https://www.gutenberg.org/ebooks/${gutenbergId}`,
                        source: 'gutenberg',
                        formats: data.formats || {}
                    };
                }
            }
            
            // Check if it's an Open Library book
            if (bookId.startsWith('openlib_')) {
                const openLibKey = bookId.replace('openlib_', '');
                const response = await fetch(`${OPEN_LIBRARY_API}${openLibKey}.json`);
                
                if (response.ok) {
                    const data = await response.json();
                    return {
                        id: bookId,
                        title: data.title || 'Unknown',
                        authors: data.authors?.map(a => a.name) || ['Unknown'],
                        author: data.authors?.[0]?.name || 'Unknown',
                        description: data.first_sentence?.[0] || '',
                        cover: data.covers?.[0] ? `https://covers.openlibrary.org/b/id/${data.covers[0]}-L.jpg` : getDefaultCover(),
                        categories: data.subjects || [],
                        pageCount: 0,
                        previewLink: `${OPEN_LIBRARY_API}${openLibKey}`,
                        infoLink: `${OPEN_LIBRARY_API}${openLibKey}`,
                        source: 'openlibrary'
                    };
                }
            }
            
            // Default to Google Books
            const response = await fetch(`${GOOGLE_BOOKS_API}/${bookId}`);
            
            if (!response.ok) {
                throw new Error('Không thể tải thông tin sách');
            }
            
            const data = await response.json();
            return formatBookDetail(data);
        } catch (error) {
            console.error('Lỗi khi lấy thông tin sách:', error);
            throw error;
        }
    },

    // Get full text content from Gutenberg
    async getGutenbergContent(bookId) {
        try {
            const gutenbergId = bookId.replace('gutenberg_', '');
            console.log('📚 Fetching Gutenberg content for ID:', gutenbergId);
            
            // Try to get HTML format first (most readable)
            const htmlUrl = `https://www.gutenberg.org/files/${gutenbergId}/${gutenbergId}-h/${gutenbergId}-h.htm`;
            const txtUrl = `https://www.gutenberg.org/files/${gutenbergId}/${gutenbergId}.txt`;
            
            // Try HTML first
            try {
                const response = await fetch(htmlUrl);
                if (response.ok) {
                    const html = await response.text();
                    console.log('✓ Got HTML content from Gutenberg');
                    return { type: 'html', content: html };
                }
            } catch (e) {
                console.warn('HTML not available, trying plain text...');
            }
            
            // Fallback to plain text
            try {
                const response = await fetch(txtUrl);
                if (response.ok) {
                    const text = await response.text();
                    console.log('✓ Got plain text content from Gutenberg');
                    return { type: 'text', content: text };
                }
            } catch (e) {
                console.warn('Plain text not available');
            }
            
            // Try alternative URL patterns
            const altUrls = [
                `https://www.gutenberg.org/cache/epub/${gutenbergId}/pg${gutenbergId}.txt`,
                `https://www.gutenberg.org/files/${gutenbergId}/${gutenbergId}.txt.utf8`
            ];
            
            for (const url of altUrls) {
                try {
                    const response = await fetch(url);
                    if (response.ok) {
                        const text = await response.text();
                        console.log('✓ Got content from alternative URL:', url);
                        return { type: 'text', content: text };
                    }
                } catch (e) {
                    continue;
                }
            }
            
            throw new Error('Không thể tải nội dung từ Gutenberg');
        } catch (error) {
            console.error('Error fetching Gutenberg content:', error);
            throw error;
        }
    },

    async getFreeBooks(maxResults = 40, startIndex = 0) {
        try {
            let allBooks = [];
            const seenIds = new Set();
            
            // First, get uploaded books from database
            try {
                const publicBooksResponse = await fetch(
                    `${window.SITE_URL ? window.SITE_URL : ''}/api/public-books.php?limit=${maxResults}&offset=${startIndex}`
                );
                
                if (publicBooksResponse.ok) {
                    const uploadedBooks = await publicBooksResponse.json();
                    uploadedBooks.forEach(book => {
                        if (!seenIds.has(book.id) && allBooks.length < maxResults * 2) {
                            seenIds.add(book.id);
                            allBooks.push(book);
                        }
                    });
                    console.log(`Uploaded books: added ${uploadedBooks.length} books, total: ${allBooks.length}`);
                }
            } catch (e) {
                console.warn('Error fetching uploaded books:', e);
            }
            
            // Strategy: Get books from ALL categories with FULL amount from each
            // This ensures "Tất cả" includes ALL books from all category filters
            const categories = ['fiction', 'literature', 'history', 'science', 'philosophy'];
            // Get FULL amount of books from each category (same as getFreeBooksByCategory)
            // Use maxResults (or more) per category to ensure "Tất cả" has ALL books from categories
            const booksPerCategory = Math.max(maxResults, 40); // Get at least maxResults from each category
            
            console.log(`Getting books from all categories (${categories.length} categories, ${booksPerCategory} books each to ensure full coverage)...`);
            
            // Get books from each category using getFreeBooksByCategory (same logic as category filters)
            const categoryPromises = categories.map(async (category) => {
                try {
                    if (typeof this.getFreeBooksByCategory === 'function') {
                        const categoryBooks = await this.getFreeBooksByCategory(category, booksPerCategory, startIndex);
                        console.log(`Category "${category}": fetched ${categoryBooks.length} books`);
                        return categoryBooks;
                    }
                } catch (e) {
                    console.warn(`Error getting books for category "${category}":`, e);
                }
                return [];
            });
            
            // Wait for all category requests in parallel
            const categoryResults = await Promise.all(categoryPromises);
            
            // Merge all category books (avoid duplicates)
            categoryResults.forEach(categoryBooks => {
                categoryBooks.forEach(book => {
                    if (!seenIds.has(book.id)) {
                        seenIds.add(book.id);
                        allBooks.push(book);
                    }
                });
            });
            
            console.log(`Total books from all categories: ${allBooks.length}`);
            
            // Also add general free books queries to ensure we have enough
            if (allBooks.length < maxResults) {
                const generalQueries = [
                    'free+ebooks',
                    'public+domain',
                    'classic+literature',
                    'free+novels',
                    'free+books'
                ];
                
                const booksPerQuery = Math.ceil((maxResults - allBooks.length) / generalQueries.length);
                
                for (const query of generalQueries) {
                    if (allBooks.length >= maxResults * 2) break;
                    
        try {
            const response = await fetch(
                            `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&filter=free-ebooks&maxResults=${Math.min(booksPerQuery * 2, 40)}&startIndex=${startIndex}`
                        );
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.items && data.items.length > 0) {
                                const formatted = formatBooksData(data.items);
                                formatted.forEach(book => {
                                    if (!seenIds.has(book.id) && allBooks.length < maxResults * 2) {
                                        seenIds.add(book.id);
                                        allBooks.push(book);
                                    }
                                });
                                if (allBooks.length >= maxResults * 2) break;
                            }
                        }
                    } catch (e) {
                        console.warn(`Error fetching from Google Books with query "${query}":`, e);
                    }
                }
            }
            
            // Source 2: Project Gutenberg API (Gutendex) - Always fetch for diversity
            try {
                const page = Math.floor(startIndex / 32) + 1;
                const response = await fetch(
                    `${GUTENBERG_API}/books/?page=${page}&languages=en`
                );
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.results && data.results.length > 0) {
                        const gutenbergBooks = data.results
                            .filter(book => {
                                const id = `gutenberg_${book.id}`;
                                if (seenIds.has(id)) return false;
                                seenIds.add(id);
                                return true;
                            })
                            .slice(0, 32) // Get more from Gutenberg
                            .map(book => ({
                                id: `gutenberg_${book.id}`,
                                title: book.title || 'Không có tiêu đề',
                                authors: book.authors?.map(a => a.name) || ['Tác giả không xác định'],
                                author: book.authors?.[0]?.name || 'Tác giả không xác định',
                                description: book.subjects?.join(', ') || 'Sách miễn phí từ Project Gutenberg',
                                cover: book.formats?.['image/jpeg'] || 
                                       book.formats?.['image/png'] || 
                                       getDefaultCover(),
                                categories: book.subjects || ['Classic'],
                                category: book.subjects?.[0] || 'Classic',
                                publishedDate: book.download_count ? 'Classic' : 'N/A',
                                pageCount: 0,
                                rating: 0,
                                ratingsCount: 0,
                                language: book.languages?.[0] || 'en',
                                previewLink: book.formats?.['text/html'] || book.formats?.['application/epub+zip'] || '',
                                infoLink: `https://www.gutenberg.org/ebooks/${book.id}`,
                                isFree: true,
                                isbn: '',
                                source: 'gutenberg'
                            }));
                        
                        allBooks = allBooks.concat(gutenbergBooks);
                    }
                }
            } catch (e) {
                console.warn('Error fetching from Gutenberg:', e);
            }
            
            // Source 3: Open Library API - Get books by multiple subjects
            if (allBooks.length < maxResults) {
                try {
                    // Query Open Library by multiple subjects to get diverse books
                    const openLibSubjects = ['fiction', 'literature', 'history', 'science', 'philosophy', 'novels', 'classics'];
                    const booksPerSubject = Math.ceil((maxResults - allBooks.length) / openLibSubjects.length);
                    
                    for (const subject of openLibSubjects) {
                        if (allBooks.length >= maxResults) break;
                        
                        try {
                            const offset = startIndex;
            const response = await fetch(
                                `${OPEN_LIBRARY_API}/search.json?subject=${encodeURIComponent(subject)}&limit=${Math.min(booksPerSubject, maxResults - allBooks.length)}&offset=${offset}&sort=editions`
                            );
                            
                            if (response.ok) {
                                const data = await response.json();
                                if (data.docs && data.docs.length > 0) {
                                    data.docs.forEach(book => {
                                        const id = `openlib_${book.key}`;
                                        if (!seenIds.has(id) && allBooks.length < maxResults) {
                                            seenIds.add(id);
                                            allBooks.push({
                                                id: id,
                                                title: book.title || 'Không có tiêu đề',
                                                authors: book.author_name || ['Tác giả không xác định'],
                                                author: book.author_name?.[0] || 'Tác giả không xác định',
                                                description: book.first_sentence?.[0] || book.subtitle || 'Sách miễn phí từ Open Library',
                                                cover: book.cover_i ? 
                                                    `https://covers.openlibrary.org/b/id/${book.cover_i}-L.jpg` : 
                                                    getDefaultCover(),
                                                categories: book.subject || [subject],
                                                category: book.subject?.[0] || subject,
                                                publishedDate: book.first_publish_year?.toString() || 'N/A',
                                                pageCount: book.number_of_pages_median || 0,
                                                rating: 0,
                                                ratingsCount: 0,
                                                language: book.language?.[0] || 'en',
                                                previewLink: `https://openlibrary.org${book.key}`,
                                                infoLink: `https://openlibrary.org${book.key}`,
                                                isFree: true,
                                                isbn: book.isbn?.[0] || '',
                                                source: 'openlibrary'
                                            });
                                        }
                                    });
                                    console.log(`Open Library subject "${subject}" returned ${data.docs.length} books, total: ${allBooks.length}`);
                                    if (allBooks.length >= maxResults) break;
                                }
                            }
                        } catch (e) {
                            console.warn(`Error fetching Open Library subject "${subject}":`, e);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching from Open Library:', e);
                }
            }
            
            // Source 4: Internet Archive - Free books
            if (allBooks.length < maxResults) {
                try {
                    const rows = Math.min(20, maxResults - allBooks.length);
                    const query = `mediatype:texts AND format:(EPUB OR PDF)`;
                    const page = Math.floor(startIndex / rows) + 1;
                    // Internet Archive Advanced Search API
                    const response = await fetch(
                        `${INTERNET_ARCHIVE_API}?q=${encodeURIComponent(query)}&fl=identifier,title,creator,date,mediatype&sort[]=downloads+desc&rows=${rows}&page=${page}&output=json`,
                        { headers: { 'Accept': 'application/json' } }
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.response && data.response.docs && data.response.docs.length > 0) {
                            const archiveBooks = data.response.docs
                                .filter(book => {
                                    const id = `archive_${book.identifier}`;
                                    if (seenIds.has(id) || !book.title) return false;
                                    seenIds.add(id);
                                    return true;
                                })
                                .slice(0, maxResults - allBooks.length)
                                .map(book => ({
                                    id: `archive_${book.identifier}`,
                                    title: book.title || 'Không có tiêu đề',
                                    authors: Array.isArray(book.creator) ? book.creator : (book.creator ? [book.creator] : ['Tác giả không xác định']),
                                    author: (Array.isArray(book.creator) ? book.creator[0] : book.creator) || 'Tác giả không xác định',
                                    description: `Sách miễn phí từ Internet Archive`,
                                    cover: `https://archive.org/services/img/${book.identifier}`,
                                    categories: ['Classic'],
                                    category: 'Classic',
                                    publishedDate: book.date || 'N/A',
                                    pageCount: 0,
                                    rating: 0,
                                    ratingsCount: 0,
                                    language: 'en',
                                    previewLink: `https://archive.org/details/${book.identifier}`,
                                    infoLink: `https://archive.org/details/${book.identifier}`,
                                    isFree: true,
                                    isbn: '',
                                    source: 'internetarchive'
                                }));
                            
                            allBooks = allBooks.concat(archiveBooks);
                            console.log(`Internet Archive returned ${archiveBooks.length} books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching from Internet Archive:', e);
                }
            }
            
            // Source 5: Library of Congress - Free books (classics)
            if (allBooks.length < maxResults) {
                try {
                    const query = 'format:book';
                    const response = await fetch(
                        `${LIBRARY_OF_CONGRESS_API}/?q=${encodeURIComponent(query)}&fo=json&c=20&at=results&sp=${startIndex}`
                    );
                    
                    if (response.ok) {
            const data = await response.json();
                        if (data.results && data.results.length > 0) {
                            const locBooks = data.results
                                .filter(item => {
                                    if (!item.title || item.format?.[0]?.toLowerCase().includes('video')) return false;
                                    const id = `loc_${item.id}`;
                                    if (seenIds.has(id)) return false;
                                    seenIds.add(id);
                                    return true;
                                })
                                .slice(0, maxResults - allBooks.length)
                                .map(item => ({
                                    id: `loc_${item.id}`,
                                    title: item.title || 'Không có tiêu đề',
                                    authors: item.contributors?.map(c => c.name) || ['Tác giả không xác định'],
                                    author: item.contributors?.[0]?.name || 'Tác giả không xác định',
                                    description: item.description?.[0] || 'Sách từ Library of Congress',
                                    cover: item.image_url?.[0] || getDefaultCover(),
                                    categories: item.subject?.map(s => s.name) || ['History'],
                                    category: item.subject?.[0]?.name || 'History',
                                    publishedDate: item.date || 'N/A',
                                    pageCount: 0,
                                    rating: 0,
                                    ratingsCount: 0,
                                    language: 'en',
                                    previewLink: item.url || '',
                                    infoLink: item.url || '',
                                    isFree: true,
                                    isbn: '',
                                    source: 'libraryofcongress'
                                }));
                            
                            allBooks = allBooks.concat(locBooks);
                            console.log(`Library of Congress returned ${locBooks.length} books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching from Library of Congress:', e);
                }
            }
            
            // Add some popular books (Stephen King and Harry Potter) to the mix
            if (allBooks.length < maxResults * 2 && startIndex === 0) {
                try {
                    // Add a few Stephen King books
                    if (typeof this.getStephenKingBooks === 'function') {
                        const stephenKingBooks = await this.getStephenKingBooks(10, 0);
                        stephenKingBooks.forEach(book => {
                            if (!seenIds.has(book.id) && allBooks.length < maxResults * 2) {
                                seenIds.add(book.id);
                                allBooks.push(book);
                            }
                        });
                    }
                    
                    // Add a few Harry Potter books
                    if (allBooks.length < maxResults * 2 && typeof this.getHarryPotterBooks === 'function') {
                        const harryPotterBooks = await this.getHarryPotterBooks(10, 0);
                        harryPotterBooks.forEach(book => {
                            if (!seenIds.has(book.id) && allBooks.length < maxResults * 2) {
                                seenIds.add(book.id);
                                allBooks.push(book);
                            }
                        });
                    }
                } catch (e) {
                    console.warn('Error adding popular books to getFreeBooks:', e);
                }
            }
            
            // Shuffle to mix books from different categories, then return
            const shuffled = allBooks.sort(() => Math.random() - 0.5);
            console.log(`Total books collected: ${allBooks.length}, returning ${Math.min(shuffled.length, maxResults)}`);
            return shuffled.slice(0, maxResults);
        } catch (error) {
            console.error('Lỗi khi lấy sách miễn phí:', error);
            return [];
        }
    },

    async getFreeBooksByCategory(category = 'fiction', maxResults = 20, startIndex = 0) {
        try {
            let allBooks = [];
            const seenIds = new Set();
            
            // First, get uploaded books from database for this category
            try {
                const publicBooksResponse = await fetch(
                    `${window.SITE_URL ? window.SITE_URL : ''}/api/public-books.php?category=${encodeURIComponent(category)}&limit=${Math.min(10, maxResults)}&offset=${startIndex}`
                );
                
                if (publicBooksResponse.ok) {
                    const uploadedBooks = await publicBooksResponse.json();
                    uploadedBooks.forEach(book => {
                        if (!seenIds.has(book.id) && allBooks.length < maxResults) {
                            seenIds.add(book.id);
                            allBooks.push(book);
                        }
                    });
                    console.log(`Uploaded books for category "${category}": ${uploadedBooks.length} books`);
                }
            } catch (e) {
                console.warn(`Error fetching uploaded books for category "${category}":`, e);
            }
            
            // Map Vietnamese category names to English/API-friendly terms
            const categoryMap = {
                'fiction': 'fiction',
                'literature': 'literature',
                'history': 'history',
                'science': 'science',
                'philosophy': 'philosophy',
                'tiểu thuyết': 'fiction',
                'văn học': 'literature',
                'lịch sử': 'history',
                'khoa học': 'science',
                'triết học': 'philosophy'
            };
            
            const apiCategory = categoryMap[category.toLowerCase()] || category;
            
            // Try multiple query variations for Google Books
            const googleQueries = [
                `subject:${apiCategory}+free+ebooks`,
                `subject:${apiCategory}`,
                `${apiCategory}+free`,
                `${apiCategory}`
            ];
            
            // Google Books by category - try multiple queries
            for (const query of googleQueries) {
                if (allBooks.length >= maxResults) break;
                
                try {
                    const response = await fetch(
                        `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&filter=free-ebooks&maxResults=${Math.min(maxResults * 2, 40)}&startIndex=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.items && data.items.length > 0) {
                            const formatted = formatBooksData(data.items);
                            formatted.forEach(book => {
                                if (!seenIds.has(book.id) && allBooks.length < maxResults) {
                                    seenIds.add(book.id);
                                    allBooks.push(book);
                                }
                            });
                            console.log(`Google Books query "${query}" returned ${formatted.length} books, total: ${allBooks.length}`);
                            if (allBooks.length >= maxResults) break;
                        }
                    }
                } catch (e) {
                    console.warn(`Error fetching from Google Books with query "${query}":`, e);
                }
            }
            
            // If still not enough, try without filter
            if (allBooks.length < maxResults) {
                try {
                    const response = await fetch(
                        `${GOOGLE_BOOKS_API}?q=subject:${encodeURIComponent(apiCategory)}&maxResults=${Math.min(maxResults * 2, 40)}&startIndex=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.items && data.items.length > 0) {
                            const formatted = formatBooksData(data.items);
                            formatted.forEach(book => {
                                if (!seenIds.has(book.id) && allBooks.length < maxResults) {
                                    // Only add if it has preview or is marked as free
                                    if (book.isFree || book.previewLink) {
                                        seenIds.add(book.id);
                                        allBooks.push(book);
                                    }
                                }
                            });
                            console.log(`Google Books without filter returned ${formatted.length} books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching from Google Books without filter:', e);
                }
            }
            
            // Open Library by subject
            if (allBooks.length < maxResults) {
                try {
                    const response = await fetch(
                        `${OPEN_LIBRARY_API}/search.json?subject=${encodeURIComponent(apiCategory)}&limit=${Math.min(20, maxResults - allBooks.length)}&offset=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.docs && data.docs.length > 0) {
                            data.docs.forEach(book => {
                                const id = `openlib_${book.key}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults) {
                                    seenIds.add(id);
                                    allBooks.push({
                                        id: id,
                                        title: book.title || 'Không có tiêu đề',
                                        authors: book.author_name || ['Tác giả không xác định'],
                                        author: book.author_name?.[0] || 'Tác giả không xác định',
                                        description: book.first_sentence?.[0] || 'Sách miễn phí từ Open Library',
                                        cover: book.cover_i ? 
                                            `https://covers.openlibrary.org/b/id/${book.cover_i}-M.jpg` : 
                                            getDefaultCover(),
                                        categories: book.subject || [category],
                                        category: category,
                                        publishedDate: book.first_publish_year?.toString() || 'N/A',
                                        pageCount: 0,
                                        rating: 0,
                                        ratingsCount: 0,
                                        language: book.language?.[0] || 'en',
                                        previewLink: `https://openlibrary.org${book.key}`,
                                        infoLink: `https://openlibrary.org${book.key}`,
                                        isFree: true,
                                        isbn: book.isbn?.[0] || '',
                                        source: 'openlibrary'
                                    });
                                }
                            });
                            console.log(`Open Library returned ${data.docs.length} books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching from Open Library by category:', e);
                }
            }
            
            // Internet Archive by category
            if (allBooks.length < maxResults) {
                try {
                    const categoryQueries = {
                        'fiction': 'fiction',
                        'literature': 'literature',
                        'history': 'history',
                        'science': 'science',
                        'philosophy': 'philosophy'
                    };
                    const archiveCategory = categoryQueries[apiCategory.toLowerCase()] || apiCategory;
                    const query = `mediatype:texts AND format:(EPUB OR PDF) AND subject:${archiveCategory}`;
                    const rows = Math.min(20, maxResults - allBooks.length);
                    const response = await fetch(
                        `${INTERNET_ARCHIVE_API}?q=${encodeURIComponent(query)}&fl=identifier,title,creator,date,mediatype&sort[]=downloads+desc&rows=${rows}&output=json`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.response && data.response.docs && data.response.docs.length > 0) {
                            data.response.docs.forEach(book => {
                                const id = `archive_${book.identifier}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults && book.title) {
                                    seenIds.add(id);
                                    allBooks.push({
                                        id: id,
                                        title: book.title,
                                        authors: Array.isArray(book.creator) ? book.creator : (book.creator ? [book.creator] : ['Tác giả không xác định']),
                                        author: (Array.isArray(book.creator) ? book.creator[0] : book.creator) || 'Tác giả không xác định',
                                        description: `Sách miễn phí từ Internet Archive - ${category}`,
                                        cover: `https://archive.org/services/img/${book.identifier}`,
                                        categories: [category],
                                        category: category,
                                        publishedDate: book.date || 'N/A',
                                        pageCount: 0,
                                        rating: 0,
                                        ratingsCount: 0,
                                        language: 'en',
                                        previewLink: `https://archive.org/details/${book.identifier}`,
                                        infoLink: `https://archive.org/details/${book.identifier}`,
                                        isFree: true,
                                        isbn: '',
                                        source: 'internetarchive'
                                    });
                                }
                            });
                            console.log(`Internet Archive category search returned ${data.response.docs.length} books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching from Internet Archive by category:', e);
                }
            }
            
            console.log(`getFreeBooksByCategory(${category}) returning ${allBooks.length} books`);
            return allBooks.slice(0, maxResults);
        } catch (error) {
            console.error('Lỗi khi lấy sách miễn phí theo thể loại:', error);
            return [];
        }
    },

    // Get books by Stephen King
    async getStephenKingBooks(maxResults = 40, startIndex = 0) {
        try {
            console.log('Searching for Stephen King books...');
            let allBooks = [];
            const seenIds = new Set();
            
            // Google Books - Stephen King
            try {
                const queries = [
                    'inauthor:"Stephen King"',
                    'inauthor:"Stephen King" free',
                    '"Stephen King"',
                    'Stephen King'
                ];
                
                for (const query of queries) {
                    if (allBooks.length >= maxResults) break;
                    
                    const response = await fetch(
                        `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${Math.min(40, maxResults)}&startIndex=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.items && data.items.length > 0) {
                            const formatted = formatBooksData(data.items);
                            formatted.forEach(book => {
                                // Only add if author contains "Stephen King" (case insensitive)
                                const authorMatch = book.authors.some(a => 
                                    a.toLowerCase().includes('stephen king')
                                );
                                if (authorMatch && !seenIds.has(book.id) && allBooks.length < maxResults) {
                                    seenIds.add(book.id);
                                    allBooks.push({
                                        ...book,
                                        category: 'Horror',
                                        categories: ['Horror', 'Fiction'],
                                        source: 'googlebooks'
                                    });
                                }
                            });
                            if (allBooks.length >= maxResults) break;
                        }
                    }
                }
                console.log(`Google Books returned ${allBooks.length} Stephen King books`);
            } catch (e) {
                console.warn('Error fetching Stephen King books from Google Books:', e);
            }
            
            // Open Library - Stephen King
            if (allBooks.length < maxResults) {
                try {
                    const response = await fetch(
                        `${OPEN_LIBRARY_API}/search.json?author=Stephen+King&limit=${Math.min(20, maxResults - allBooks.length)}&offset=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.docs && data.docs.length > 0) {
                            data.docs.forEach(book => {
                                const id = `openlib_${book.key}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults) {
                                    seenIds.add(id);
                                    allBooks.push({
                                        id: id,
                                        title: book.title || 'Không có tiêu đề',
                                        authors: ['Stephen King'],
                                        author: 'Stephen King',
                                        description: book.first_sentence?.[0] || 'Sách của Stephen King từ Open Library',
                                        cover: book.cover_i ? 
                                            `https://covers.openlibrary.org/b/id/${book.cover_i}-L.jpg` : 
                                            getDefaultCover(),
                                        categories: ['Horror', 'Fiction'],
                                        category: 'Horror',
                                        publishedDate: book.first_publish_year?.toString() || 'N/A',
                                        pageCount: 0,
                                        rating: 0,
                                        ratingsCount: 0,
                                        language: book.language?.[0] || 'en',
                                        previewLink: `https://openlibrary.org${book.key}`,
                                        infoLink: `https://openlibrary.org${book.key}`,
                                        isFree: true,
                                        isbn: book.isbn?.[0] || '',
                                        source: 'openlibrary'
                                    });
                                }
                            });
                            console.log(`Open Library returned ${data.docs.length} Stephen King books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching Stephen King books from Open Library:', e);
                }
            }
            
            // Internet Archive - Stephen King
            if (allBooks.length < maxResults) {
                try {
                    const query = `mediatype:texts AND creator:"Stephen King"`;
                    const rows = Math.min(20, maxResults - allBooks.length);
                    const response = await fetch(
                        `${INTERNET_ARCHIVE_API}?q=${encodeURIComponent(query)}&fl=identifier,title,creator,date&sort[]=downloads+desc&rows=${rows}&output=json`,
                        { headers: { 'Accept': 'application/json' } }
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.response && data.response.docs && data.response.docs.length > 0) {
                            data.response.docs.forEach(book => {
                                const id = `archive_${book.identifier}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults && book.title) {
                                    seenIds.add(id);
                                    allBooks.push({
                                        id: id,
                                        title: book.title,
                                        authors: ['Stephen King'],
                                        author: 'Stephen King',
                                        description: 'Sách của Stephen King từ Internet Archive',
                                        cover: `https://archive.org/services/img/${book.identifier}`,
                                        categories: ['Horror', 'Fiction'],
                                        category: 'Horror',
                                        publishedDate: book.date || 'N/A',
                                        pageCount: 0,
                                        rating: 0,
                                        ratingsCount: 0,
                                        language: 'en',
                                        previewLink: `https://archive.org/details/${book.identifier}`,
                                        infoLink: `https://archive.org/details/${book.identifier}`,
                                        isFree: true,
                                        isbn: '',
                                        source: 'internetarchive'
                                    });
                                }
                            });
                            console.log(`Internet Archive returned ${data.response.docs.length} Stephen King books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching Stephen King books from Internet Archive:', e);
                }
            }
            
            console.log(`Total Stephen King books found: ${allBooks.length}`);
            return allBooks.slice(0, maxResults);
        } catch (error) {
            console.error('Lỗi khi lấy sách Stephen King:', error);
            return [];
        }
    },

    // Get Harry Potter books
    async getHarryPotterBooks(maxResults = 40, startIndex = 0) {
        try {
            console.log('Searching for Harry Potter books...');
            let allBooks = [];
            const seenIds = new Set();
            
            // Google Books - Harry Potter by J.K. Rowling
            try {
                const queries = [
                    '"Harry Potter" inauthor:"J.K. Rowling"',
                    '"Harry Potter" inauthor:"Rowling"',
                    '"Harry Potter"',
                    'Harry Potter Rowling'
                ];
                
                for (const query of queries) {
                    if (allBooks.length >= maxResults) break;
                    
                    const response = await fetch(
                        `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${Math.min(40, maxResults)}&startIndex=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.items && data.items.length > 0) {
                            const formatted = formatBooksData(data.items);
                            formatted.forEach(book => {
                                // Only add if title contains "Harry Potter"
                                const titleMatch = book.title.toLowerCase().includes('harry potter');
                                const authorMatch = book.authors.some(a => 
                                    a.toLowerCase().includes('rowling') || a.toLowerCase().includes('j.k.')
                                );
                                if (titleMatch && authorMatch && !seenIds.has(book.id) && allBooks.length < maxResults) {
                                    seenIds.add(book.id);
                                    allBooks.push({
                                        ...book,
                                        category: 'Fantasy',
                                        categories: ['Fantasy', 'Fiction', 'Young Adult'],
                                        source: 'googlebooks'
                                    });
                                }
                            });
                            if (allBooks.length >= maxResults) break;
                        }
                    }
                }
                console.log(`Google Books returned ${allBooks.length} Harry Potter books`);
            } catch (e) {
                console.warn('Error fetching Harry Potter books from Google Books:', e);
            }
            
            // Open Library - Harry Potter
            if (allBooks.length < maxResults) {
                try {
                    const response = await fetch(
                        `${OPEN_LIBRARY_API}/search.json?title=Harry+Potter&author=Rowling&limit=${Math.min(20, maxResults - allBooks.length)}&offset=${startIndex}`
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.docs && data.docs.length > 0) {
                            data.docs.forEach(book => {
                                // Only add if title contains "Harry Potter"
                                if (!book.title?.toLowerCase().includes('harry potter')) return;
                                
                                const id = `openlib_${book.key}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults) {
                                    seenIds.add(id);
                                    allBooks.push({
                                        id: id,
                                        title: book.title,
                                        authors: book.author_name || ['J.K. Rowling'],
                                        author: book.author_name?.[0] || 'J.K. Rowling',
                                        description: book.first_sentence?.[0] || 'Sách Harry Potter từ Open Library',
                                        cover: book.cover_i ? 
                                            `https://covers.openlibrary.org/b/id/${book.cover_i}-L.jpg` : 
                                            getDefaultCover(),
                                        categories: ['Fantasy', 'Fiction', 'Young Adult'],
                                        category: 'Fantasy',
                                        publishedDate: book.first_publish_year?.toString() || 'N/A',
                                        pageCount: 0,
                                        rating: 0,
                                        ratingsCount: 0,
                                        language: book.language?.[0] || 'en',
                                        previewLink: `https://openlibrary.org${book.key}`,
                                        infoLink: `https://openlibrary.org${book.key}`,
                                        isFree: true,
                                        isbn: book.isbn?.[0] || '',
                                        source: 'openlibrary'
                                    });
                                }
                            });
                            console.log(`Open Library returned ${data.docs.length} Harry Potter books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching Harry Potter books from Open Library:', e);
                }
            }
            
            // Internet Archive - Harry Potter
            if (allBooks.length < maxResults) {
                try {
                    const query = `mediatype:texts AND title:"Harry Potter" AND creator:"Rowling"`;
                    const rows = Math.min(20, maxResults - allBooks.length);
                    const response = await fetch(
                        `${INTERNET_ARCHIVE_API}?q=${encodeURIComponent(query)}&fl=identifier,title,creator,date&sort[]=downloads+desc&rows=${rows}&output=json`,
                        { headers: { 'Accept': 'application/json' } }
                    );
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.response && data.response.docs && data.response.docs.length > 0) {
                            data.response.docs.forEach(book => {
                                if (!book.title?.toLowerCase().includes('harry potter')) return;
                                
                                const id = `archive_${book.identifier}`;
                                if (!seenIds.has(id) && allBooks.length < maxResults) {
                                    seenIds.add(id);
                                    allBooks.push({
                                        id: id,
                                        title: book.title,
                                        authors: ['J.K. Rowling'],
                                        author: 'J.K. Rowling',
                                        description: 'Sách Harry Potter từ Internet Archive',
                                        cover: `https://archive.org/services/img/${book.identifier}`,
                                        categories: ['Fantasy', 'Fiction', 'Young Adult'],
                                        category: 'Fantasy',
                                        publishedDate: book.date || 'N/A',
                                        pageCount: 0,
                                        rating: 0,
                                        ratingsCount: 0,
                                        language: 'en',
                                        previewLink: `https://archive.org/details/${book.identifier}`,
                                        infoLink: `https://archive.org/details/${book.identifier}`,
                                        isFree: true,
                                        isbn: '',
                                        source: 'internetarchive'
                                    });
                                }
                            });
                            console.log(`Internet Archive returned ${data.response.docs.length} Harry Potter books, total: ${allBooks.length}`);
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching Harry Potter books from Internet Archive:', e);
                }
            }
            
            // Sort by title to get the books in order (Philosopher's Stone, Chamber of Secrets, etc.)
            allBooks.sort((a, b) => {
                const getBookNumber = (title) => {
                    const titleLower = title.toLowerCase();
                    if (titleLower.includes('philosopher') || titleLower.includes('sorcerer')) return 1;
                    if (titleLower.includes('chamber')) return 2;
                    if (titleLower.includes('azkaban') || titleLower.includes('prisoner')) return 3;
                    if (titleLower.includes('goblet') || titleLower.includes('fire')) return 4;
                    if (titleLower.includes('phoenix') || titleLower.includes('order')) return 5;
                    if (titleLower.includes('prince') || titleLower.includes('half-blood')) return 6;
                    if (titleLower.includes('hallows') || titleLower.includes('deathly')) return 7;
                    return 0;
                };
                return getBookNumber(a.title) - getBookNumber(b.title);
            });
            
            console.log(`Total Harry Potter books found: ${allBooks.length}`);
            return allBooks.slice(0, maxResults);
        } catch (error) {
            console.error('Lỗi khi lấy sách Harry Potter:', error);
            return [];
        }
    },

    saveBooksToLocal(key, books) {
        try {
            localStorage.setItem(key, JSON.stringify(books));
        } catch (error) {
            console.error('Lỗi khi lưu vào localStorage:', error);
        }
    },

    getBooksFromLocal(key) {
        try {
            const data = localStorage.getItem(key);
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error('Lỗi khi đọc từ localStorage:', error);
            return [];
        }
    }
});

// Confirm BooksAPI is loaded
try {
console.log('BooksAPI initialized successfully', window.BooksAPI);
    console.log('Available methods:', Object.keys(window.BooksAPI));
    console.log('getFreeBooksByCategory type:', typeof window.BooksAPI.getFreeBooksByCategory);
    
    // Verify all methods exist
    const requiredMethods = ['searchBooks', 'getPopularBooks', 'getBookDetails', 'getFreeBooks', 'getFreeBooksByCategory'];
    requiredMethods.forEach(method => {
        if (typeof window.BooksAPI[method] === 'function') {
            console.log(`✓ ${method} is available`);
        } else {
            console.error(`✗ ${method} is NOT available! Type: ${typeof window.BooksAPI[method]}`);
        }
    });
} catch (error) {
    console.error('Error initializing BooksAPI:', error);
}

