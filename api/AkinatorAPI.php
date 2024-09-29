<?php

class Akinator {
    private $currentStep;
    private $region;
    private $uri;
    private $session;
    private $progress;
    private $childMode;
    private $stepLastProposition;
    private $answers;
    private $guessCount;
    private $question;
    private $signature;
    private $guess;

    public function __construct($region, $childMode = false) {
        $regions = ['ru', 'en'];
        
        if (!in_array($region, $regions)) {
            throw new Exception('Please specify a correct region.');
        }

        $this->currentStep = 0;
        $this->region = $region;
        $this->uri = "https://{$this->region}.akinator.com";
        $this->guess = null;
        $this->progress = 0.00;
        $this->stepLastProposition = '';
        $this->guessCount = 0;
        $this->childMode = $childMode;
        $this->question = '';
        $this->answers = [];
    }

    private function sendRequest($url, $formData) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Request Error:' . curl_error($ch));
        }
        curl_close($ch);

        return $response;
    }

    public function start() {
        $url = $this->uri . '/game';

        $formData = [
            'sid' => '1',
            'cm' => $this->childMode ? 'true' : 'false'
        ];

        $response = $this->sendRequest($url, http_build_query($formData));

        preg_match('/<p class="question-text" id="question-label">(.+)<\/p>/', $response, $questionMatch);
        preg_match("/session: '(.+)'/", $response, $sessionMatch);
        preg_match("/signature: '(.+)'/", $response, $signatureMatch);

        $this->question = html_entity_decode($questionMatch[1]);
        $this->session = $sessionMatch[1];
        $this->signature = $signatureMatch[1];

        preg_match_all('/<a class="li-game" href="#" id="a_(\w+)" onclick="chooseAnswer\(\d+\)">(.+)<\/a>/', $response, $answerMatches);
        foreach ($answerMatches[2] as $answer) {
            $this->answers[] = html_entity_decode($answer);
        }

        return $this;
    }

    public function step($answer) {
        if (!$this->uri) {
            throw new Exception('No URI available.');
        }
        if (!$this->session || !$this->signature) {
            throw new Exception('No session available.');
        }

        $formData = [
            'step' => $this->currentStep,
            'progression' => $this->progress,
            'sid' => '1',
            'cm' => $this->childMode ? 'true' : 'false',
            'answer' => $answer,
            'session' => $this->session,
            'signature' => $this->signature
        ];

        $url = $this->uri . '/answer';
        $response = $this->sendRequest($url, http_build_query($formData));

        $result = json_decode($response, true);

        if (isset($result['id_base_proposition'])) {
            $this->guess = $result;
            return $result;
        }

        if (isset($result['progression'], $result['question'], $result['step'])) {
            $this->progress = (float)$result['progression'];
            $this->question = $result['question'];
            $this->currentStep = (int)$result['step'];
        } else {
            $result = [
                'progression' => $this->progress,
                'question' => $this->question,
                'step' => $this->currentStep
            ];
        }

        return $result;
    }

    public function back() {
        if (!$this->uri) {
            throw new Exception('No URI available.');
        }
        if (!$this->session || !$this->signature) {
            throw new Exception('No session available.');
        }

        $formData = [
            'step' => $this->currentStep,
            'progression' => $this->progress,
            'sid' => '1',
            'cm' => $this->childMode ? 'true' : 'false',
            'session' => $this->session,
            'signature' => $this->signature
        ];

        $url = $this->uri . '/cancel_answer';
        $response = $this->sendRequest($url, http_build_query($formData));

        $result = json_decode($response, true);

        if (isset($result['progression'], $result['question'], $result['step'])) {
            $this->progress = (float)$result['progression'];
            $this->question = $result['question'];
            $this->currentStep = (int)$result['step'];
        } else {
            $result = [
                'progression' => $this->progress,
                'question' => $this->question,
                'step' => $this->currentStep
            ];
        }

        return $result;
    }

    public function continue() {
        if (!$this->uri) {
            throw new Exception('No URI available.');
        }
        if (!$this->session || !$this->signature) {
            throw new Exception('No session available.');
        }

        $formData = [
            'step' => $this->currentStep,
            'progression' => $this->progress,
            'sid' => '1',
            'cm' => $this->childMode ? 'true' : 'false',
            'session' => $this->session,
            'signature' => $this->signature
        ];

        $url = $this->uri . '/exclude';
        $response = $this->sendRequest($url, http_build_query($formData));

        $result = json_decode($response, true);

        if (isset($result['progression'], $result['question'], $result['step'])) {
            $this->progress = (float)$result['progression'];
            $this->question = $result['question'];
            $this->currentStep = (int)$result['step'];
        } else {
            $result = [
                'progression' => $this->progress,
                'question' => $this->question,
                'step' => $this->currentStep
            ];
        }

        return $result;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function getAnswers() {
        return $this->answers;
    }

    public function getProgress() {
        return $this->progress;
    }
}
