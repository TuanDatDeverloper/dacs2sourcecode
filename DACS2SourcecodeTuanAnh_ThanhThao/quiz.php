<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$pageTitle = 'AI Quiz - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6 max-w-4xl">
            <!-- Header -->
            <div class="text-center mb-8 reveal">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="animated-gradient">AI Quiz</span>
                </h1>
                <p class="text-lg text-gray-600">Chọn sách bạn đang đọc và trả lời câu hỏi để nhận Book Coins!</p>
                <p class="text-sm text-gray-500 mt-2">Mỗi câu đúng = 20 xu • Mỗi sách tối đa 10 câu</p>
            </div>

            <!-- Book Selection -->
            <div id="book-selection" class="glass rounded-2xl p-8 card-modern mb-8 reveal">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">
                    <i class="fas fa-book text-[#FFB347] mr-2"></i>
                    Chọn sách bạn đang đọc
                </h2>
                
                <div id="reading-books-list" class="grid md:grid-cols-2 gap-4 mb-6">
                    <!-- Books will be loaded here -->
                </div>
                
                <div id="no-books-message" class="hidden text-center py-8 text-gray-500">
                    <i class="fas fa-book-open text-4xl mb-4"></i>
                    <p>Bạn chưa có sách nào đang đọc.</p>
                    <p class="text-sm mt-2">Hãy bắt đầu đọc sách để có thể làm quiz!</p>
                    <a href="history.php" class="inline-block mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                        Xem sách
                    </a>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="hidden text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">AI đang tạo câu hỏi...</p>
            </div>

            <!-- Quiz Area -->
            <div id="quiz-area" class="hidden">
                <!-- Selected Book Info -->
                <div class="glass rounded-2xl p-6 card-modern mb-6">
                    <div class="flex items-center gap-4">
                        <div id="selected-book-cover" class="w-16 h-24 rounded bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center overflow-hidden">
                            <i class="fas fa-book text-2xl text-[#FFB347]"></i>
                        </div>
                        <div class="flex-1">
                            <h3 id="selected-book-title" class="text-xl font-bold text-gray-900"></h3>
                            <p id="selected-book-author" class="text-gray-600"></p>
                            <p class="text-sm text-gray-500 mt-2">
                                Đã trả lời: <span id="answered-count">0</span>/10 câu
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="glass rounded-2xl p-6 card-modern mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Câu hỏi</span>
                        <span class="text-sm font-semibold text-gray-900">
                            <span id="current-question">1</span> / <span id="total-questions">10</span>
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div id="quiz-progress" class="progress-fill" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Question Card -->
                <div class="glass rounded-2xl p-8 card-modern mb-6">
                    <h3 id="question-text" class="text-2xl font-bold mb-6 text-gray-900"></h3>
                    
                    <div id="options-container" class="space-y-4">
                        <!-- Options will be inserted here -->
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between">
                    <button
                        onclick="previousQuestion()"
                        id="prev-btn"
                        class="px-6 py-3 glass border border-gray-200 rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled
                    >
                        <i class="fas fa-chevron-left mr-2"></i>Trước
                    </button>
                    
                    <button
                        onclick="nextQuestion()"
                        id="next-btn"
                        class="px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all"
                    >
                        Tiếp<i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div id="results-area" class="hidden">
                <div class="glass rounded-2xl p-8 card-modern text-center">
                    <div class="mb-6">
                        <i class="fas fa-trophy text-6xl text-[#FFB347] mb-4"></i>
                        <h2 class="text-3xl font-bold mb-2 text-gray-900">Hoàn thành!</h2>
                        <p class="text-gray-600">Điểm số của bạn</p>
                    </div>
                    
                    <div class="mb-6">
                        <div class="text-6xl font-bold gradient-text mb-2" id="final-score">0</div>
                        <div class="text-lg text-gray-600">
                            <span id="correct-answers">0</span> / <span id="total-answers">0</span> câu đúng
                        </div>
                    </div>
                    
                    <div class="mb-6 p-4 bg-green-50 rounded-lg">
                        <p class="text-green-700 font-semibold">
                            <i class="fas fa-coins mr-2"></i>
                            Bạn nhận được <span id="coins-earned">0</span> Book Coins!
                        </p>
                    </div>
                    
                    <div class="flex gap-4 justify-center">
                        <button
                            onclick="resetQuiz()"
                            class="px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all"
                        >
                            Chọn sách khác
                        </button>
                        <a
                            href="dashboard.php"
                            class="px-6 py-3 glass border border-gray-200 rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium"
                        >
                            Về Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script>
        // Quiz state
        let quizData = {
            questions: [],
            currentQuestion: 0,
            answers: {},
            selectedBook: null,
            quizId: null
        };

        const MAX_QUESTIONS_PER_BOOK = 10;
        const COINS_PER_CORRECT = 20;

        // Load reading books
        async function loadReadingBooks() {
            const container = document.getElementById('reading-books-list');
            const noBooksMsg = document.getElementById('no-books-message');
            
            try {
                const books = await window.APIClient.getBooks('reading');
                
                if (!books || books.length === 0) {
                    container.classList.add('hidden');
                    noBooksMsg.classList.remove('hidden');
                    return;
                }
                
                container.classList.remove('hidden');
                noBooksMsg.classList.add('hidden');
                container.innerHTML = '';
                
                books.forEach(book => {
                    const bookCard = document.createElement('div');
                    bookCard.className = 'book-card p-4 rounded-lg border-2 border-gray-200 hover:border-[#FFB347] hover:bg-[#FFB347]/5 cursor-pointer transition-all';
                    bookCard.innerHTML = `
                        <div class="flex gap-4">
                            <div class="w-16 h-24 rounded bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center overflow-hidden">
                                ${book.cover_url ? 
                                    `<img src="${escapeHtml(book.cover_url)}" alt="${escapeHtml(book.title)}" class="w-full h-full object-cover">` :
                                    `<i class="fas fa-book text-2xl text-[#FFB347]"></i>`
                                }
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 mb-1">${escapeHtml(book.title)}</h3>
                                <p class="text-sm text-gray-600 mb-2">${escapeHtml(book.author)}</p>
                                <div class="text-xs text-gray-500 mb-2">
                                    <div>Tiến độ đọc: ${Math.round(book.progress || 0)}%</div>
                                </div>
                                <button onclick="selectBook('${book.id}')" class="mt-2 px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                                    Chọn sách này
                                </button>
                            </div>
                        </div>
                    `;
                    container.appendChild(bookCard);
                });
            } catch (error) {
                console.error('Error loading books:', error);
                container.classList.add('hidden');
                noBooksMsg.classList.remove('hidden');
            }
        }

        // Select a book
        async function selectBook(bookId) {
            try {
                const book = await window.APIClient.getBook(bookId);
                quizData.selectedBook = book;
                
                // Show loading and generate quiz
                document.getElementById('book-selection').classList.add('hidden');
                document.getElementById('loading-state').classList.remove('hidden');
                
                // Generate quiz via API
                await generateQuiz(bookId);
            } catch (error) {
                console.error('Error selecting book:', error);
                alert('Lỗi khi chọn sách: ' + error.message);
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('book-selection').classList.remove('hidden');
            }
        }

        // Generate quiz via API
        async function generateQuiz(bookId) {
            try {
                const response = await window.APIClient.generateQuiz(bookId, MAX_QUESTIONS_PER_BOOK);
                
                if (!response.success) {
                    throw new Error(response.message || 'Không thể tạo quiz');
                }
                
                quizData.questions = response.questions || [];
                quizData.quizId = response.session_id || response.quiz_id || null;
                quizData.sessionId = response.session_id || response.quiz_id || null;
                quizData.currentQuestion = 0;
                quizData.answers = {};
                
                if (quizData.questions.length === 0) {
                    throw new Error('Không có câu hỏi nào được tạo');
                }
                
                // Show quiz
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('quiz-area').classList.remove('hidden');
                
                // Update selected book info
                const coverEl = document.getElementById('selected-book-cover');
                if (quizData.selectedBook.cover_url) {
                    coverEl.innerHTML = `<img src="${escapeHtml(quizData.selectedBook.cover_url)}" alt="${escapeHtml(quizData.selectedBook.title)}" class="w-full h-full object-cover">`;
                }
                document.getElementById('selected-book-title').textContent = quizData.selectedBook.title;
                document.getElementById('selected-book-author').textContent = quizData.selectedBook.author;
                document.getElementById('answered-count').textContent = '0';
                
                displayQuestion();
            } catch (error) {
                console.error('Error generating quiz:', error);
                alert('Lỗi khi tạo quiz: ' + error.message);
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('book-selection').classList.remove('hidden');
            }
        }

        // Display current question
        function displayQuestion() {
            const q = quizData.questions[quizData.currentQuestion];
            document.getElementById('question-text').textContent = q.question || q.text || '';
            
            const optionsContainer = document.getElementById('options-container');
            optionsContainer.innerHTML = '';
            
            const options = q.options || {};
            const optionKeys = ['A', 'B', 'C', 'D'];
            optionKeys.forEach((key, index) => {
                const option = options[key] || '';
                const questionIndex = quizData.currentQuestion;
                const isSelected = quizData.answers[questionIndex] === index;
                const optionDiv = document.createElement('div');
                optionDiv.className = `p-4 rounded-lg border-2 cursor-pointer transition-all ${
                    isSelected 
                        ? 'border-[#FFB347] bg-[#FFB347]/10' 
                        : 'border-gray-200 hover:border-[#FFB347]/50 hover:bg-gray-50'
                }`;
                optionDiv.innerHTML = `
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center ${
                            isSelected ? 'border-[#FFB347] bg-[#FFB347]' : 'border-gray-300'
                        }">
                            ${isSelected ? '<i class="fas fa-check text-white text-xs"></i>' : ''}
                        </div>
                        <span class="text-gray-900">${escapeHtml(option)}</span>
                    </div>
                `;
                optionDiv.onclick = () => selectOption(questionIndex, index);
                optionsContainer.appendChild(optionDiv);
            });

            // Update progress
            const progress = ((quizData.currentQuestion + 1) / quizData.questions.length) * 100;
            document.getElementById('quiz-progress').style.width = progress + '%';
            document.getElementById('current-question').textContent = quizData.currentQuestion + 1;
            document.getElementById('total-questions').textContent = quizData.questions.length;

            // Update buttons
            document.getElementById('prev-btn').disabled = quizData.currentQuestion === 0;
            document.getElementById('next-btn').textContent = 
                quizData.currentQuestion === quizData.questions.length - 1 ? 'Hoàn thành' : 'Tiếp';
        }

        // Select option
        function selectOption(questionIndex, answerIndex) {
            quizData.answers[questionIndex] = answerIndex;
            displayQuestion();
        }

        // Navigation
        function previousQuestion() {
            if (quizData.currentQuestion > 0) {
                quizData.currentQuestion--;
                displayQuestion();
            }
        }

        async function nextQuestion() {
            if (quizData.currentQuestion < quizData.questions.length - 1) {
                quizData.currentQuestion++;
                displayQuestion();
            } else {
                await submitQuiz();
            }
        }

        // Submit quiz
        async function submitQuiz() {
            try {
                // Format answers as array indexed by question position
                const formattedAnswers = {};
                for (let i = 0; i < quizData.questions.length; i++) {
                    formattedAnswers[i] = quizData.answers[i];
                    formattedAnswers['q' + i] = quizData.answers[i]; // Also support q0, q1 format
                }
                
                const response = await window.APIClient.submitQuiz(
                    quizData.sessionId || quizData.quizId, 
                    formattedAnswers
                );
                
                if (!response.success) {
                    throw new Error(response.message || 'Không thể nộp bài');
                }
                
                const score = response.score || 0;
                const correct = response.correct_answers || 0;
                const total = response.total_questions || quizData.questions.length;
                const coinsEarned = response.coins_earned || 0;
                
                // Show results
                document.getElementById('quiz-area').classList.add('hidden');
                document.getElementById('results-area').classList.remove('hidden');
                document.getElementById('final-score').textContent = score;
                document.getElementById('correct-answers').textContent = correct;
                document.getElementById('total-answers').textContent = total;
                document.getElementById('coins-earned').textContent = coinsEarned;
                
                // Refresh user data to get updated coins
                const authCheck = await window.APIClient.checkAuth();
                if (authCheck.logged_in && authCheck.user) {
                    window.Auth.setUser(authCheck.user);
                }
            } catch (error) {
                console.error('Error submitting quiz:', error);
                alert('Lỗi khi nộp bài: ' + error.message);
            }
        }

        function resetQuiz() {
            document.getElementById('results-area').classList.add('hidden');
            document.getElementById('quiz-area').classList.add('hidden');
            document.getElementById('book-selection').classList.remove('hidden');
            quizData = {
                questions: [],
                currentQuestion: 0,
                answers: {},
                selectedBook: null,
                quizId: null
            };
            loadReadingBooks();
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadReadingBooks();
        });
        
        // Make functions global
        window.selectBook = selectBook;
        window.previousQuestion = previousQuestion;
        window.nextQuestion = nextQuestion;
        window.resetQuiz = resetQuiz;
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

