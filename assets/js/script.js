class AkinatorGame {
    constructor() {
        this.sessionId = 0;
        
        this.initElements();
        this.initHandlers();
    }

    initElements() {
        this.logos = document.getElementById('logos');
        this.questionText = document.getElementById('question-text');
        this.questionScreen = document.getElementById('question-screen');
        this.answersContainer = document.getElementById('answers-container');
        this.progressLine = document.getElementById('progress-line');
        this.backButton = document.getElementById('back-button');
        this.newgameButton = document.getElementById('newgame-button');

        this.guessInfo = document.getElementById('guess-info');
        this.guessImage = document.getElementById('guess-image');
        this.guessName = document.getElementById('guess-name');
        this.guessDescription = document.getElementById('guess-description');

        this.startButton = document.getElementById('start-button');
        this.yesButton = document.getElementById('yes-button');
        this.noButton = document.getElementById('no-button');
        this.startButtons = document.getElementById('start-buttons');
        this.endButtons = document.getElementById('end-buttons');

        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.classList.add('loading-indicator');
        this.loadingIndicator.innerHTML = `<span></span><span></span><span></span>`;
    }

    initHandlers() {
        this.startButton.addEventListener('click', () => this.startGame());
        this.newgameButton.addEventListener('click', () => this.startGame());
        this.backButton.addEventListener('click', () => this.goBack());
        this.yesButton.addEventListener('click', () => this.handleYes());
        this.noButton.addEventListener('click', () => this.handleNo());
    }

    showMainScreen() {
        this.questionText.innerHTML = `
            Здравствуй, дорогой мой гость!<br>
            Не хочешь сыграть со мной в игру?<br>
            Загадай любого реального или вымышленного персонажа из игры, фильма, мультика, телепередачи, из чего угодно, а я постараюсь его угадать!
        `;

        this.hideAll();
        this.showLogos(1);
        this.startButtons.classList.remove('hidden');
    }

    showGuessScreen(name, description, imageUrl) {
        this.guessName.textContent = name;
        this.guessDescription.textContent = description;
        this.guessImage.src = imageUrl || 'assets/logos/none.png';
        this.guessImage.alt = name;
        this.questionText.innerHTML = 'Я думаю это';

        this.hideAll();
        this.showLogos(6);
        this.guessInfo.classList.remove('hidden');
    }

    showQuestionScreen(question, answers, progress = 0) {
        this.startButtons.classList.add('hidden');
        this.questionText.textContent = question;
        this.answersContainer.innerHTML = '';

        answers.forEach((answer, index) => {
            const btn = document.createElement('button');
            btn.classList.add('btn');
            btn.textContent = answer;
            btn.addEventListener('click', () => this.handleAnswer(index));
            this.answersContainer.appendChild(btn);
        });

        this.hideAll();
        this.showLogos();
        this.showProgress(progress);
        this.questionScreen.classList.remove('hidden');
    }

    showEndScreen() {
        this.hideAll();
        this.showLogos(7);
        this.endButtons.classList.remove('hidden');
    }

    showLogos(logo) {
        logo = !logo ? Math.floor(Math.random() * 7) + 1 : logo;
        this.logos.src = `assets/logos/${logo}.png`;
    }

    showProgress(progress) {
        if (progress <= 25) {
            this.progressLine.style.background = 'repeating-linear-gradient(45deg, #FF0000, #FF0000 5px, #B22222 5px, #B22222 10px)';
        } else if (progress > 25 && progress <= 50) {
            this.progressLine.style.background = 'repeating-linear-gradient(45deg, #FFFF00, #FFFF00 5px, #FFD700 5px, #FFD700 10px)';
        } else if (progress > 50 && progress <= 75) {
            this.progressLine.style.background = 'repeating-linear-gradient(45deg, #FFA500, #FFA500 5px, #FF7F50 5px, #FF7F50 10px)';
        } else if (progress > 75) {
            this.progressLine.style.background = 'repeating-linear-gradient(45deg, #32CD32, #32CD32 5px, #008000 5px, #008000 10px)';
        }

        this.progressLine.style.width = Math.round(progress) + '%';
    }

    hideAll() {
        this.startButtons.classList.add('hidden');
        this.endButtons.classList.add('hidden');
        this.guessInfo.classList.add('hidden');
        this.questionScreen.classList.add('hidden');
    }
    
    showLoading() {
        this.questionText.innerHTML = '';
        this.questionText.appendChild(this.loadingIndicator);
    }

    hideLoading() {
        if (this.loadingIndicator.parentElement) {
            this.loadingIndicator.parentElement.removeChild(this.loadingIndicator);
        }
    }

    handleYes() {
        this.questionText.textContent = 'Хотите загадать еще?';

        this.endButtons.innerHTML = `
            <button id="restart-button" class="btn">Да</button>
            <button id="no-button-end" class="btn">Нет</button>
        `;

        document.getElementById('restart-button').addEventListener('click', () => this.startGame());
        document.getElementById('no-button-end').addEventListener('click', () => this.showMainScreen());

        this.showEndScreen();
    }

    handleNo() {
        this.questionText.textContent = 'Что вы хотите сделать дальше?';

        this.endButtons.innerHTML = `
            <button id="continue-button" class="btn">Продолжить</button>
            <button id="restart-button" class="btn">Загадать еще</button>
        `;

        document.getElementById('restart-button').addEventListener('click', () => this.startGame());
        document.getElementById('continue-button').addEventListener('click', () => this.continueGame());

        this.showEndScreen();
    }

    async apiRequest(action, params = {}) {
        this.showLoading();
        
        const url = new URL('api/api.php', window.location);
        const body = new URLSearchParams();

        body.append('action', action);
        body.append('session_id', this.sessionId);

        for (const key in params) {
            if (params.hasOwnProperty(key)) {
                body.append(key, params[key]);
            }
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body.toString(),
            });

            const data = await response.json();
    
            if (data.error) {
                throw new Error(data.error);
            }

            if (data.session_id) {
                this.sessionId = data.session_id;
            }
            
            return data;
        } catch (error) {
            console.error('Ошибка запроса к API:', error.stack);
            throw error;
        } finally {
            this.hideLoading();
        }
    }    

    async startGame() {
        try {
            const data = await this.apiRequest('start');
            this.showQuestionScreen(data.question, data.answers, data.progress);
        } catch (error) {
            this.showMainScreen();
            console.error('Ошибка при старте игры:', error);
        }
    }

    async continueGame() {
        try {
            const data = await this.apiRequest('continue');
            if (data.guess) {
                this.showGuessScreen(data.guess, data.description, data.image_url);
            } else {
                this.showQuestionScreen(data.question, data.answers, data.progress);
            }
        } catch (error) {
            console.error('Ошибка при продолжении игры:', error);
        }
    }

    async handleAnswer(answerIndex) {
        try {
            const data = await this.apiRequest('step', { answer: answerIndex });
            if (data.guess) {
                this.showGuessScreen(data.guess, data.description, data.image_url);
            } else {
                this.showQuestionScreen(data.question, data.answers, data.progress);
            }
        } catch (error) {
            console.error('Ошибка при обработке ответа:', error);
        }
    }

    async goBack() {
        try {
            const data = await this.apiRequest('back');
            this.showQuestionScreen(data.question, data.answers, data.progress);
        } catch (error) {
            console.error('Ошибка при возврате назад:', error);
        }
    } 
}

document.addEventListener('DOMContentLoaded', () => {
    new AkinatorGame();
});
