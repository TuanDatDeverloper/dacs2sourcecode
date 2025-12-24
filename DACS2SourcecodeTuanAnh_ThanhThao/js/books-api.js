// ============================================
// BOOKS API - Google Books API Integration
// ============================================

const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';

/**
 * Tìm kiếm sách từ Google Books API
 * @param {string} query - Từ khóa tìm kiếm
 * @param {number} maxResults - Số lượng kết quả tối đa (mặc định: 20)
 * @returns {Promise} Promise với danh sách sách
 */
export async function searchBooks(query, maxResults = 20) {
    try {
        const response = await fetch(
            `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${maxResults}&langRestrict=vi`
        );
        
        if (!response.ok) {
            throw new Error('Không thể tải dữ liệu sách');
        }
        
        const data = await response.json();
        return formatBooksData(data.items || []);
    } catch (error) {
        console.error('Lỗi khi tìm kiếm sách:', error);
        throw error;
    }
}

/**
 * Lấy sách phổ biến/được đề xuất
 * @param {string} subject - Chủ đề sách (mặc định: 'fiction')
 * @param {number} maxResults - Số lượng kết quả
 * @returns {Promise} Promise với danh sách sách
 */
export async function getPopularBooks(subject = 'fiction', maxResults = 20) {
    try {
        const response = await fetch(
            `${GOOGLE_BOOKS_API}?q=subject:${subject}&orderBy=relevance&maxResults=${maxResults}&langRestrict=vi`
        );
        
        if (!response.ok) {
            throw new Error('Không thể tải dữ liệu sách');
        }
        
        const data = await response.json();
        return formatBooksData(data.items || []);
    } catch (error) {
        console.error('Lỗi khi lấy sách phổ biến:', error);
        throw error;
    }
}

/**
 * Lấy thông tin chi tiết một cuốn sách
 * @param {string} bookId - ID của sách
 * @returns {Promise} Promise với thông tin sách
 */
export async function getBookDetails(bookId) {
    try {
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
}

/**
 * Lấy sách miễn phí (ebooks)
 * @param {number} maxResults - Số lượng kết quả
 * @returns {Promise} Promise với danh sách sách miễn phí
 */
export async function getFreeBooks(maxResults = 20) {
    try {
        const response = await fetch(
            `${GOOGLE_BOOKS_API}?q=free+ebooks&filter=free-ebooks&maxResults=${maxResults}&langRestrict=vi`
        );
        
        if (!response.ok) {
            throw new Error('Không thể tải sách miễn phí');
        }
        
        const data = await response.json();
        return formatBooksData(data.items || []);
    } catch (error) {
        console.error('Lỗi khi lấy sách miễn phí:', error);
        throw error;
    }
}

/**
 * Format dữ liệu sách từ API response
 * @param {Array} items - Mảng items từ API
 * @returns {Array} Mảng sách đã được format
 */
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
            cover: volumeInfo.imageLinks?.thumbnail || 
                   volumeInfo.imageLinks?.smallThumbnail || 
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

/**
 * Format thông tin chi tiết sách
 * @param {Object} item - Item từ API
 * @returns {Object} Thông tin sách đã format
 */
function formatBookDetail(item) {
    const volumeInfo = item.volumeInfo || {};
    const saleInfo = item.saleInfo || {};
    
    return {
        id: item.id,
        title: volumeInfo.title || 'Không có tiêu đề',
        authors: volumeInfo.authors || ['Tác giả không xác định'],
        author: volumeInfo.authors?.[0] || 'Tác giả không xác định',
        description: volumeInfo.description || 'Không có mô tả',
        cover: volumeInfo.imageLinks?.large || 
               volumeInfo.imageLinks?.medium ||
               volumeInfo.imageLinks?.thumbnail || 
               volumeInfo.imageLinks?.smallThumbnail || 
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

/**
 * Lấy ảnh bìa mặc định
 * @returns {string} URL ảnh mặc định
 */
function getDefaultCover() {
    return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="300"%3E%3Crect fill="%23faf9f6" width="200" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23FFB347" font-family="sans-serif" font-size="14" font-weight="bold"%3EBook Cover%3C/text%3E%3C/svg%3E';
}

/**
 * Lưu sách vào localStorage
 * @param {string} key - Key để lưu
 * @param {Array} books - Danh sách sách
 */
export function saveBooksToLocal(key, books) {
    try {
        localStorage.setItem(key, JSON.stringify(books));
    } catch (error) {
        console.error('Lỗi khi lưu vào localStorage:', error);
    }
}

/**
 * Lấy sách từ localStorage
 * @param {string} key - Key để lấy
 * @returns {Array} Danh sách sách
 */
export function getBooksFromLocal(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : [];
    } catch (error) {
        console.error('Lỗi khi đọc từ localStorage:', error);
        return [];
    }
}

// Export cho sử dụng trong HTML (non-module)
if (typeof window !== 'undefined') {
    // Create a global object that will be populated when module loads
    window.BooksAPI = window.BooksAPI || {};
    
    // If this is loaded as a module, export functions
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            searchBooks,
            getPopularBooks,
            getBookDetails,
            getFreeBooks,
            saveBooksToLocal,
            getBooksFromLocal
        };
    }
    
    // Also attach to window for direct script usage
    window.BooksAPI.searchBooks = searchBooks;
    window.BooksAPI.getPopularBooks = getPopularBooks;
    window.BooksAPI.getBookDetails = getBookDetails;
    window.BooksAPI.getFreeBooks = getFreeBooks;
    window.BooksAPI.saveBooksToLocal = saveBooksToLocal;
    window.BooksAPI.getBooksFromLocal = getBooksFromLocal;
}

