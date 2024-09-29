# Akinator API

Реализация API Акинатора на PHP

## Как запустить

1. Разместите файлы проекта на вашем веб-сервере с поддержкой PHP (например, XAMPP, WAMP).

2. Убедитесь, что сервер настроен на обработку PHP-запросов.

3. Для тестирования API используйте `http://localhost/akinator-api/api.php`.

## Структура

- `AkinatorAPI.php` — основной PHP-класс, содержащий логику взаимодействия с API.
- `api.php` — интерфейс для клиентских запросов. Обрабатывает запросы от клиента и вызывает методы класса `AkinatorAPI`.
- `index.html` и `script.js` — пример клиентской части приложения.

## Методы API

API использует POST-запросы для взаимодействия с сервером. Формат ответа — JSON. В каждый запрос необходимо передавать обязательный параметр `action`, который указывает на действие, выполняемое API.

### `start`

- Описание: Запуск новой игры.
- Метод: `POST`
- Параметры:
  - `action`: "start"
- Ответ:
  ```json
  {
    "question": "Ваш персонаж реальный?",
    "answers": ["Да", "Нет"],
    "progress": 0
  }
  ```

### `step`

- Описание: Отправка ответа на текущий вопрос.
- Метод: `POST`
- Параметры:
  - `action`: "step"
  - `answer`: индекс выбранного ответа (целое число)
- Ответ (если персонаж угадан):
  ```json
  {
    "guess": "Гарри Поттер",
    "description": "Персонаж книг и фильмов о волшебнике.",
    "image_url": "http://example.com/images/harry_potter.png"
  }
  ```
  
  Ответ (если продолжается угадывание):
  ```json
  {
    "question": "Ваш персонаж мужчина?",
    "answers": ["Да", "Нет"],
    "progress": 25
  }
  ```  

### `continue`

- Описание: Продолжение игры, если ранее был выбран неправильный вариант.
- Метод: `POST`
- Параметры:
  - `action`: "continue"
- Ответ:
  ```json
  {
    "question": "Ваш персонаж человек?",
    "answers": ["Да", "Нет"],
    "progress": 50
  }
  ```

### `back`

- Описание: Возвращение к предыдущему вопросу.
- Метод: `POST`
- Параметры:
  - `action`: "back"
- Ответ:
  ```json
  {
    "question": "Ваш персонаж человек?",
    "answers": ["Да", "Нет"],
    "progress": 25
  }
  ```