<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Only POST requests are allowed.']);
    exit;
}

require_once 'AkinatorAPI.php';

$action = empty($_POST['action']) ? null : htmlspecialchars($_POST['action']);
$session_id = empty($_POST['session_id']) ? null : htmlspecialchars($_POST['session_id']);

if ($session_id) {
    session_id($session_id);
}

session_start();

try {
    switch ($action) {
        case 'reset':
            unset($_SESSION['state']);
            sendResponse(['status' => 'Game reset']);
            break;

        case 'start':
            $akinator = new Akinator('ru', false);
            $akinator->start();
            
            $_SESSION['state'] = $akinator->getState();

            sendResponse([
                'question' => $akinator->getQuestion(),
                'answers' => $akinator->getAnswers(),
                'progress' => $akinator->getProgress()
            ]);
            break;

        case 'step':
            if (!isset($_SESSION['state'])) {
                throw new Exception('Game not started');
            }

            if (!isset($_POST['answer'])) {
                throw new Exception('Answer is required');
            }

            $akinator = new Akinator('ru', false);
            $akinator->setState($_SESSION['state']);

            $answer = (int) $_POST['answer'];
            $result = $akinator->step($answer);

            $_SESSION['state'] = $akinator->getState();

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
            if (!isset($_SESSION['state'])) {
                throw new Exception('Game not started');
            }

            $akinator = new Akinator('ru', false);
            $akinator->setState($_SESSION['state']);
            $akinator->back();

            $_SESSION['state'] = $akinator->getState();

            sendResponse([
                'question' => $akinator->getQuestion(),
                'answers' => $akinator->getAnswers(),
                'progress' => $akinator->getProgress()
            ]);
            break;

        case 'continue':
            if (!isset($_SESSION['state'])) {
                throw new Exception('Game not started');
            }

            $akinator = new Akinator('ru', false);
            $akinator->setState($_SESSION['state']);
            $akinator->continue();

            $_SESSION['state'] = $akinator->getState();

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

function sendResponse(array $data, int $status_code = 200) {
    $data['session_id'] = session_id();
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}