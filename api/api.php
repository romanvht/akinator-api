<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Only POST requests are allowed.']);
    exit;
}

require_once 'AkinatorAPI.php';

session_start();

function sendResponse(array $data, int $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'reset':
            unset($_SESSION['akinator']);
            sendResponse(['status' => 'Game reset']);
            break;

        case 'start':
            $_SESSION['akinator'] = new Akinator('ru', false);
            $akinator = $_SESSION['akinator'];
            $akinator->start();

            sendResponse([
                'question' => $akinator->getQuestion(),
                'answers' => $akinator->getAnswers(),
                'progress' => $akinator->getProgress()
            ]);
            break;

        case 'step':
            if (!isset($_SESSION['akinator'])) {
                throw new Exception('Game not started');
            }

            $akinator = $_SESSION['akinator'];

            if (!isset($_POST['answer'])) {
                throw new Exception('Answer is required');
            }

            $answer = filter_var($_POST['answer'], FILTER_VALIDATE_INT);
            if ($answer === false) {
                throw new Exception('Invalid answer format');
            }

            $result = $akinator->step($answer);

            if (isset($result['id_base_proposition'])) {
                sendResponse([
                    'guess' => $result['name_proposition'],
                    'description' => $result['description_proposition'],
                    'image_url' => $result['photo']
                ]);
            } else {
                sendResponse([
                    'question' => $akinator->getQuestion(),
                    'answers' => $akinator->getAnswers(),
                    'progress' => $akinator->getProgress()
                ]);
            }
            break;

        case 'back':
            if (!isset($_SESSION['akinator'])) {
                throw new Exception('Game not started');
            }

            $akinator = $_SESSION['akinator'];
            $akinator->back();

            sendResponse([
                'question' => $akinator->getQuestion(),
                'answers' => $akinator->getAnswers(),
                'progress' => $akinator->getProgress()
            ]);
            break;

        case 'continue':
            if (!isset($_SESSION['akinator'])) {
                throw new Exception('Game not started');
            }

            $akinator = $_SESSION['akinator'];
            $akinator->continue();

            sendResponse([
                'question' => $akinator->getQuestion(),
                'answers' => $akinator->getAnswers(),
                'progress' => $akinator->getProgress()
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
    $status_code = 400; 

    if ($error_message === 'Game not started') {
        $status_code = 400;
    } elseif ($error_message === 'Answer is required' || $error_message === 'Invalid answer format') {
        $status_code = 422;
    } elseif ($error_message === 'Invalid action') {
        $status_code = 400;
    }

    sendResponse(['error' => $error_message], $status_code);
}
