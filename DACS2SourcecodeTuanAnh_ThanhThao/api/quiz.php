<?php
/**
 * Quiz API - BookOnline
 * Generate quiz using Hugging Face AI and manage quiz attempts
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

// Hugging Face API Configuration
define('HUGGINGFACE_API_URL', 'https://api-inference.huggingface.co/models/');
define('HUGGINGFACE_MODEL', 'gpt2'); // Có thể đổi sang model khác
define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY') ?: ''); 

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                $data = $_POST;
            }
            
            $action = $data['action'] ?? '';
            
            if ($action === 'generate') {
                // Generate quiz questions using AI - unique for each user
                $bookId = $data['book_id'] ?? null;
                $numQuestions = $data['num_questions'] ?? 10;
                
                if (!$bookId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                    exit;
                }
                
                // Get book info
                $book = $db->fetchOne("SELECT * FROM books WHERE id = ?", [$bookId]);
                if (!$book) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Book not found']);
                    exit;
                }
                
                // Generate quiz questions using Hugging Face AI API
                $baseQuestions = generateQuizQuestions($book, $numQuestions + 5); // Generate more for variety
                
                // Shuffle and select unique questions for this user
                shuffle($baseQuestions);
                $selectedQuestions = array_slice($baseQuestions, 0, $numQuestions);
                
                // Shuffle each question's options and update correct_answer index
                $shuffledQuestions = [];
                foreach ($selectedQuestions as $q) {
                    $shuffledQ = shuffleQuestionOptions($q);
                    $shuffledQuestions[] = $shuffledQ;
                }
                
                // Shuffle question order
                shuffle($shuffledQuestions);
                
                // Create unique quiz session for this user
                $sessionId = createUserQuizSession($userId, $bookId, $shuffledQuestions);
                
                echo json_encode([
                    'success' => true,
                    'session_id' => $sessionId,
                    'quiz_id' => $sessionId, // For backward compatibility
                    'questions' => $shuffledQuestions
                ]);
                
            } elseif ($action === 'submit') {
                // Submit quiz answers
                $sessionId = $data['quiz_id'] ?? $data['session_id'] ?? null;
                $answers = $data['answers'] ?? [];
                
                if (!$sessionId || empty($answers)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Session ID and answers are required']);
                    exit;
                }
                
                // Get quiz session for this user
                $session = $db->fetchOne(
                    "SELECT * FROM user_quiz_sessions WHERE id = ? AND user_id = ?",
                    [$sessionId, $userId]
                );
                
                if (!$session) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Quiz session not found']);
                    exit;
                }
                
                // Parse questions from session
                $questions = json_decode($session['questions_data'], true);
                if (empty($questions)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid quiz session data']);
                    exit;
                }
                
                // Calculate score
                $score = 0;
                $totalQuestions = count($questions);
                
                foreach ($questions as $index => $question) {
                    $userAnswer = $answers[$index] ?? $answers['q' . $index] ?? null;
                    $correctAnswer = $question['correct_answer'] ?? 0;
                    
                    if ($userAnswer !== null && (int)$userAnswer === (int)$correctAnswer) {
                        $score++;
                    }
                }
                
                // Calculate coins earned
                $percentage = ($score / $totalQuestions) * 100;
                $coinsEarned = 0;
                
                if ($percentage >= 90) {
                    $coinsEarned = 50;
                } elseif ($percentage >= 70) {
                    $coinsEarned = 30;
                } elseif ($percentage >= 50) {
                    $coinsEarned = 15;
                } else {
                    $coinsEarned = 5;
                }
                
                // Save quiz attempt
                $bookId = $session['book_id'];
                
                // Create a reference quiz entry if needed
                $referenceQuiz = $db->fetchOne(
                    "SELECT id FROM quizzes WHERE book_id = ? LIMIT 1",
                    [$bookId]
                );
                $referenceQuizId = $referenceQuiz ? $referenceQuiz['id'] : null;
                
                // If no reference quiz exists, create one
                if (!$referenceQuizId) {
                    $db->execute(
                        "INSERT INTO quizzes (book_id, question, options, correct_answer, points, generated_by) 
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [$bookId, 'Reference quiz', json_encode(['A' => 'Ref']), 0, 20, 'system']
                    );
                    $referenceQuizId = $db->lastInsertId();
                }
                
                $db->execute(
                    "INSERT INTO quiz_attempts (user_id, quiz_id, book_id, score, correct_answers, total_questions, coins_earned) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$userId, $referenceQuizId, $bookId, $percentage, $score, $totalQuestions, $coinsEarned]
                );
                $attemptId = $db->lastInsertId();
                
                // Mark session as completed
                $db->execute(
                    "UPDATE user_quiz_sessions SET completed = 1, completed_at = CURRENT_TIMESTAMP WHERE id = ?",
                    [$sessionId]
                );
                
                // Award coins
                if ($coinsEarned > 0) {
                    $db->execute(
                        "UPDATE users SET coins = coins + ? WHERE id = ?",
                        [$coinsEarned, $userId]
                    );
                    
                    // Log transaction
                    $db->execute(
                        "INSERT INTO coins_transactions (user_id, amount, reason, reference_id) 
                         VALUES (?, ?, 'quiz_completed', ?)",
                        [$userId, $coinsEarned, $attemptId]
                    );
                }
                
                echo json_encode([
                    'success' => true,
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'percentage' => round($percentage, 2),
                    'coins_earned' => $coinsEarned
                ]);
            }
            break;
            
        case 'GET':
            $quizId = $_GET['id'] ?? null;
            
            if ($quizId) {
                // Get quiz details
                $questions = $db->fetchAll(
                    "SELECT * FROM quizzes WHERE id = ?",
                    [$quizId]
                );
                
                if ($questions) {
                    // Parse options JSON
                    foreach ($questions as &$question) {
                        $question['options'] = json_decode($question['options'], true);
                    }
                    echo json_encode($questions);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
                }
            } else {
                // Get user's quiz history
                $quizzes = $db->fetchAll(
                    "SELECT qa.*, b.title as book_title 
                     FROM quiz_attempts qa
                     JOIN books b ON qa.book_id = b.id
                     WHERE qa.user_id = ?
                     ORDER BY qa.completed_at DESC
                     LIMIT 20",
                    [$userId]
                );
                echo json_encode($quizzes);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Quiz API Error: " . $e->getMessage());
}

/**
 * Generate quiz questions using Hugging Face AI API
 * Falls back to intelligent question generation if AI API is not available
 */
function generateQuizQuestions($book, $numQuestions) {
    // Try to use Hugging Face API first (if available)
    if (!empty(HUGGINGFACE_API_KEY)) {
        $aiResponse = callHuggingFaceAPI($book, $numQuestions);
        if ($aiResponse) {
            $aiQuestions = parseAIResponse($aiResponse, $book);
            if (!empty($aiQuestions) && count($aiQuestions) >= 3) {
                // Merge with intelligent questions for variety
                $intelligentQuestions = generateIntelligentQuestions($book, max(5, $numQuestions - count($aiQuestions)));
                return array_merge($aiQuestions, $intelligentQuestions);
            }
        }
    }
    
    // Fallback: Generate intelligent questions from book metadata
    return generateIntelligentQuestions($book, $numQuestions);
}

/**
 * Generate intelligent quiz questions from book metadata
 */
function generateIntelligentQuestions($book, $numQuestions) {
    $questions = [];
    $usedTypes = [];
    
    // Parse categories
    $categories = [];
    if (!empty($book['categories'])) {
        if (is_string($book['categories'])) {
            $decoded = json_decode($book['categories'], true);
            $categories = is_array($decoded) ? $decoded : [$book['categories']];
        } else {
            $categories = is_array($book['categories']) ? $book['categories'] : [];
        }
    }
    
    // Question types with their generators
    $questionTypes = [
        'author' => function($book) {
            $author = $book['author'] ?? 'Không rõ';
            $wrongAuthors = generateWrongAuthors($author);
            return [
                'question' => "Ai là tác giả của cuốn sách '{$book['title']}'?",
                'options' => [
                    'A' => $author,
                    'B' => $wrongAuthors[0],
                    'C' => $wrongAuthors[1],
                    'D' => $wrongAuthors[2]
                ],
                'correct_answer' => 0
            ];
        },
        'category' => function($book) use ($categories) {
            $category = !empty($categories) ? $categories[0] : 'Không rõ';
            $wrongCategories = ['Tiểu thuyết', 'Khoa học', 'Lịch sử', 'Kinh tế', 'Tâm lý học', 'Triết học', 'Văn học', 'Kỹ năng sống'];
            shuffle($wrongCategories);
            return [
                'question' => "Cuốn sách '{$book['title']}' thuộc thể loại nào?",
                'options' => [
                    'A' => $category,
                    'B' => $wrongCategories[0],
                    'C' => $wrongCategories[1],
                    'D' => $wrongCategories[2]
                ],
                'correct_answer' => 0
            ];
        },
        'year' => function($book) {
            $year = null;
            if (!empty($book['published_date'])) {
                $date = new DateTime($book['published_date']);
                $year = $date->format('Y');
            }
            if (!$year && !empty($book['published_year'])) {
                $year = $book['published_year'];
            }
            
            if (!$year) {
                return null; // Skip if no year
            }
            
            $wrongYears = [
                (int)$year - 5,
                (int)$year + 3,
                (int)$year - 10,
                (int)$year + 7
            ];
            shuffle($wrongYears);
            
            return [
                'question' => "Cuốn sách '{$book['title']}' được xuất bản vào năm nào?",
                'options' => [
                    'A' => $year,
                    'B' => (string)$wrongYears[0],
                    'C' => (string)$wrongYears[1],
                    'D' => (string)$wrongYears[2]
                ],
                'correct_answer' => 0
            ];
        },
        'pages' => function($book) {
            $pages = $book['page_count'] ?? 0;
            if ($pages <= 0) {
                return null; // Skip if no page count
            }
            
            $wrongPages = [
                $pages - 50,
                $pages + 100,
                $pages - 20,
                $pages + 200
            ];
            $wrongPages = array_map(function($p) { return max(1, $p); }, $wrongPages);
            shuffle($wrongPages);
            
            return [
                'question' => "Cuốn sách '{$book['title']}' có bao nhiêu trang?",
                'options' => [
                    'A' => (string)$pages . ' trang',
                    'B' => (string)$wrongPages[0] . ' trang',
                    'C' => (string)$wrongPages[1] . ' trang',
                    'D' => (string)$wrongPages[2] . ' trang'
                ],
                'correct_answer' => 0
            ];
        },
        'description' => function($book) {
            $description = $book['description'] ?? '';
            if (empty($description) || strlen($description) < 50) {
                return null; // Skip if description is too short
            }
            
            // Extract key themes/words from description
            $keywords = extractKeywords($description);
            if (empty($keywords)) {
                return null;
            }
            
            $correctKeyword = $keywords[0];
            $wrongKeywords = ['Tình yêu', 'Phiêu lưu', 'Khoa học viễn tưởng', 'Lịch sử', 'Kinh tế', 'Tâm lý', 'Triết học'];
            shuffle($wrongKeywords);
            
            return [
                'question' => "Nội dung chính của cuốn sách '{$book['title']}' xoay quanh chủ đề gì?",
                'options' => [
                    'A' => $correctKeyword,
                    'B' => $wrongKeywords[0],
                    'C' => $wrongKeywords[1],
                    'D' => $wrongKeywords[2]
                ],
                'correct_answer' => 0
            ];
        },
        'title_author' => function($book) {
            $author = $book['author'] ?? 'Không rõ';
            $title = $book['title'] ?? '';
            $wrongTitles = generateWrongTitles($title);
            
            return [
                'question' => "Cuốn sách nào sau đây được viết bởi {$author}?",
                'options' => [
                    'A' => $title,
                    'B' => $wrongTitles[0],
                    'C' => $wrongTitles[1],
                    'D' => $wrongTitles[2]
                ],
                'correct_answer' => 0
            ];
        }
    ];
    
    // Generate questions, avoiding duplicates
    $typeKeys = array_keys($questionTypes);
    shuffle($typeKeys);
    
    for ($i = 0; $i < $numQuestions && $i < count($typeKeys); $i++) {
        $type = $typeKeys[$i % count($typeKeys)];
        
        // Skip if already used (unless we need more questions)
        if (in_array($type, $usedTypes) && count($usedTypes) < count($typeKeys)) {
            continue;
        }
        
        $question = $questionTypes[$type]($book);
        
        if ($question !== null) {
            // Shuffle options to randomize correct answer position
            $options = $question['options'];
            $correctAnswer = $question['correct_answer'];
            $correctValue = $options['A'];
            
            $optionValues = array_values($options);
            shuffle($optionValues);
            
            $newCorrectIndex = array_search($correctValue, $optionValues);
            
            $questions[] = [
                'question' => $question['question'],
                'options' => [
                    'A' => $optionValues[0],
                    'B' => $optionValues[1],
                    'C' => $optionValues[2],
                    'D' => $optionValues[3]
                ],
                'correct_answer' => $newCorrectIndex
            ];
            
            $usedTypes[] = $type;
        }
    }
    
    // If we don't have enough questions, repeat some types
    while (count($questions) < $numQuestions && count($typeKeys) > 0) {
        $type = $typeKeys[array_rand($typeKeys)];
        $question = $questionTypes[$type]($book);
        
        if ($question !== null) {
            $options = $question['options'];
            $correctValue = $options['A'];
            $optionValues = array_values($options);
            shuffle($optionValues);
            $newCorrectIndex = array_search($correctValue, $optionValues);
            
            $questions[] = [
                'question' => $question['question'],
                'options' => [
                    'A' => $optionValues[0],
                    'B' => $optionValues[1],
                    'C' => $optionValues[2],
                    'D' => $optionValues[3]
                ],
                'correct_answer' => $newCorrectIndex
            ];
        }
        
        if (count($questions) >= $numQuestions) break;
    }
    
    return array_slice($questions, 0, $numQuestions);
}

/**
 * Generate wrong author names
 */
function generateWrongAuthors($correctAuthor) {
    $wrongAuthors = [
        'Nguyễn Du', 'Hồ Chí Minh', 'Nam Cao', 'Vũ Trọng Phụng',
        'Nguyễn Nhật Ánh', 'Haruki Murakami', 'J.K. Rowling',
        'George Orwell', 'Ernest Hemingway', 'Mark Twain',
        'Jane Austen', 'Charles Dickens', 'Leo Tolstoy'
    ];
    
    // Remove correct author if exists
    $wrongAuthors = array_filter($wrongAuthors, function($author) use ($correctAuthor) {
        return stripos($author, $correctAuthor) === false && 
               stripos($correctAuthor, $author) === false;
    });
    
    shuffle($wrongAuthors);
    return array_slice($wrongAuthors, 0, 3);
}

/**
 * Generate wrong book titles
 */
function generateWrongTitles($correctTitle) {
    $wrongTitles = [
        'Đắc Nhân Tâm', 'Nhà Giả Kim', 'Tôi Tài Giỏi, Bạn Cũng Thế',
        'Sapiens', 'Homo Deus', '1984', 'Animal Farm',
        'To Kill a Mockingbird', 'The Great Gatsby', 'Pride and Prejudice',
        'Dế Mèn Phiêu Lưu Ký', 'Tắt Đèn', 'Chí Phèo'
    ];
    
    $wrongTitles = array_filter($wrongTitles, function($title) use ($correctTitle) {
        return $title !== $correctTitle;
    });
    
    shuffle($wrongTitles);
    return array_slice($wrongTitles, 0, 3);
}

/**
 * Extract keywords from description
 */
function extractKeywords($description) {
    // Common Vietnamese keywords
    $keywords = [];
    $commonWords = [
        'tình yêu', 'phiêu lưu', 'lịch sử', 'khoa học', 'kinh tế',
        'tâm lý', 'triết học', 'văn học', 'nghệ thuật', 'giáo dục',
        'công nghệ', 'y học', 'pháp luật', 'chính trị', 'xã hội',
        'văn hóa', 'tôn giáo', 'thể thao', 'ẩm thực', 'du lịch'
    ];
    
    $descriptionLower = mb_strtolower($description, 'UTF-8');
    
    foreach ($commonWords as $word) {
        if (mb_stripos($descriptionLower, $word) !== false) {
            $keywords[] = ucfirst($word);
        }
    }
    
    // If no keywords found, use generic ones
    if (empty($keywords)) {
        $keywords = ['Văn học', 'Giáo dục', 'Kỹ năng sống'];
    }
    
    return array_unique($keywords);
}

/**
 * Call Hugging Face API for quiz generation
 * Uses text generation models to create questions
 */
function callHuggingFaceAPI($book, $numQuestions) {
    // Try multiple models - use the best available
    $models = [
        'meta-llama/Llama-2-7b-chat-hf', // Best quality (requires API key)
        'mistralai/Mistral-7B-Instruct-v0.1', // Good quality
        'microsoft/DialoGPT-large', // Good for conversations
        'gpt2', // Fallback
    ];
    
    $title = $book['title'] ?? 'sách này';
    $author = $book['author'] ?? 'tác giả';
    $description = mb_substr($book['description'] ?? '', 0, 800); // Limit description length
    
    // Create detailed prompt for better AI generation
    $prompt = "Bạn là giáo viên tạo câu hỏi trắc nghiệm. Tạo {$numQuestions} câu hỏi về cuốn sách sau:\n\n";
    $prompt .= "Tên sách: {$title}\n";
    $prompt .= "Tác giả: {$author}\n";
    if (!empty($description)) {
        $prompt .= "Mô tả: {$description}\n";
    }
    $prompt .= "\nYêu cầu:\n";
    $prompt .= "- Câu hỏi phải đa dạng: về tác giả, nội dung, nhân vật, chủ đề, ý nghĩa\n";
    $prompt .= "- Mỗi câu có 4 lựa chọn A, B, C, D\n";
    $prompt .= "- Chỉ có 1 đáp án đúng\n";
    $prompt .= "- Đáp án sai phải hợp lý, không quá dễ đoán\n\n";
    $prompt .= "Định dạng:\n";
    $prompt .= "Q1. [Câu hỏi]\n";
    $prompt .= "A) [Lựa chọn A]\n";
    $prompt .= "B) [Lựa chọn B]\n";
    $prompt .= "C) [Lựa chọn C]\n";
    $prompt .= "D) [Lựa chọn D]\n";
    $prompt .= "Đáp án: A\n\n";
    
    // Try each model until one works
    foreach ($models as $model) {
        $url = HUGGINGFACE_API_URL . $model;
        $headers = [
            'Content-Type: application/json'
        ];
        
        if (!empty(HUGGINGFACE_API_KEY)) {
            $headers[] = 'Authorization: Bearer ' . HUGGINGFACE_API_KEY;
        }
        
        $data = json_encode([
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 2000,
                'temperature' => 0.7,
                'top_p' => 0.9,
                'do_sample' => true,
                'return_full_text' => false
            ]
        ]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("Hugging Face API ({$model}) cURL Error: " . $curlError);
            continue; // Try next model
        }
        
        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            
            // Log for debugging
            error_log("Hugging Face API ({$model}) Success: " . substr($response, 0, 200));
            
            // Handle different response formats
            $generatedText = null;
            if (isset($decoded[0]['generated_text'])) {
                $generatedText = $decoded[0]['generated_text'];
            } elseif (isset($decoded['generated_text'])) {
                $generatedText = $decoded['generated_text'];
            } elseif (is_string($decoded)) {
                $generatedText = $decoded;
            } elseif (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                // Some models return array of arrays
                foreach ($decoded as $item) {
                    if (isset($item['generated_text'])) {
                        $generatedText = $item['generated_text'];
                        break;
                    }
                }
            }
            
            if ($generatedText && strlen($generatedText) > 50) {
                error_log("Hugging Face API ({$model}) Generated " . strlen($generatedText) . " characters");
                return $generatedText;
            } else {
                error_log("Hugging Face API ({$model}) Response too short or invalid format");
            }
        } elseif ($httpCode === 503) {
            // Model is loading, wait a bit and try again
            error_log("Hugging Face API ({$model}) Model is loading (503), waiting...");
            sleep(3);
            // Try one more time with longer timeout
            $ch2 = curl_init($url);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_POST, true);
            curl_setopt($ch2, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 60); // Longer timeout for loading models
            curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response2 = curl_exec($ch2);
            $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);
            
            if ($httpCode2 === 200) {
                $decoded2 = json_decode($response2, true);
                $generatedText2 = null;
                if (isset($decoded2[0]['generated_text'])) {
                    $generatedText2 = $decoded2[0]['generated_text'];
                } elseif (isset($decoded2['generated_text'])) {
                    $generatedText2 = $decoded2['generated_text'];
                }
                
                if ($generatedText2 && strlen($generatedText2) > 50) {
                    error_log("Hugging Face API ({$model}) Success after retry");
                    return $generatedText2;
                }
            }
            continue; // Try next model
        } elseif ($httpCode === 401 || $httpCode === 403) {
            error_log("Hugging Face API ({$model}) Authentication failed (HTTP {$httpCode}) - Check API key");
            continue; // Try next model
        } else {
            error_log("Hugging Face API ({$model}) Error: HTTP {$httpCode} - Response: " . substr($response, 0, 200));
            continue; // Try next model
        }
    }
    
    return null; // All models failed
}

/**
 * Parse AI response into structured quiz questions
 */
function parseAIResponse($aiResponse, $book) {
    if (empty($aiResponse)) {
    return [];
    }
    
    $questions = [];
    
    // Try to parse the AI response
    // Pattern: Q1. [question] A) [opt1] B) [opt2] C) [opt3] D) [opt4] Đáp án: [A/B/C/D]
    $pattern = '/Q\d+\.\s*(.+?)\s*A\)\s*(.+?)\s*B\)\s*(.+?)\s*C\)\s*(.+?)\s*D\)\s*(.+?)(?:\s*Đáp án:\s*([A-D]))?/is';
    
    if (preg_match_all($pattern, $aiResponse, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $question = trim($match[1]);
            $options = [
                'A' => trim($match[2]),
                'B' => trim($match[3]),
                'C' => trim($match[4]),
                'D' => trim($match[5])
            ];
            
            // Determine correct answer
            $correctAnswer = 0; // Default to A
            if (!empty($match[6])) {
                $correctLetter = strtoupper(trim($match[6]));
                $correctAnswer = ord($correctLetter) - ord('A');
            }
            
            // Validate question and options
            if (strlen($question) > 10 && strlen($options['A']) > 3) {
                $questions[] = [
                    'question' => $question,
                    'options' => $options,
                    'correct_answer' => max(0, min(3, $correctAnswer))
                ];
            }
        }
    }
    
    // If parsing failed, try simpler pattern
    if (empty($questions)) {
        $lines = explode("\n", $aiResponse);
        $currentQuestion = null;
        $currentOptions = [];
        $currentCorrect = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Question line
            if (preg_match('/^Q\d+[\.:]\s*(.+)$/i', $line, $m)) {
                if ($currentQuestion !== null && !empty($currentOptions)) {
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $currentCorrect
                    ];
                }
                $currentQuestion = trim($m[1]);
                $currentOptions = [];
            }
            // Option line
            elseif (preg_match('/^([A-D])[\)\.:]\s*(.+)$/i', $line, $m)) {
                $letter = strtoupper($m[1]);
                $option = trim($m[2]);
                $currentOptions[$letter] = $option;
            }
            // Answer line
            elseif (preg_match('/Đáp án[:\s]+([A-D])/i', $line, $m)) {
                $currentCorrect = ord(strtoupper($m[1])) - ord('A');
            }
        }
        
        // Add last question
        if ($currentQuestion !== null && !empty($currentOptions)) {
            $questions[] = [
                'question' => $currentQuestion,
                'options' => $currentOptions,
                'correct_answer' => $currentCorrect
            ];
        }
    }
    
    return $questions;
}

/**
 * Create user quiz session - unique quiz for each user
 */
function createUserQuizSession($userId, $bookId, $questions) {
    global $db;
    
    // Save questions data as JSON
    $questionsData = json_encode($questions);
    
    // Create session
    $db->execute(
        "INSERT INTO user_quiz_sessions (user_id, book_id, questions_data, created_at) 
         VALUES (?, ?, ?, CURRENT_TIMESTAMP)",
        [$userId, $bookId, $questionsData]
    );
    
    return $db->lastInsertId();
}

/**
 * Shuffle question options and update correct answer index
 */
function shuffleQuestionOptions($question) {
    $options = $question['options'];
    $correctIndex = $question['correct_answer'];
    
    // Get correct answer value
    $optionKeys = array_keys($options);
    $correctValue = $options[$optionKeys[$correctIndex]];
    
    // Shuffle options
    $optionValues = array_values($options);
    shuffle($optionValues);
    
    // Find new index of correct answer
    $newCorrectIndex = array_search($correctValue, $optionValues);
    
    // Rebuild options array with new order
    $newOptions = [
        'A' => $optionValues[0],
        'B' => $optionValues[1],
        'C' => $optionValues[2],
        'D' => $optionValues[3]
    ];
    
    return [
        'question' => $question['question'],
        'options' => $newOptions,
        'correct_answer' => $newCorrectIndex !== false ? $newCorrectIndex : 0
    ];
}
?>

