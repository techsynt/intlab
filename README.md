# intlab

На локальной машине нужен установленный: git, docker и docker compose Если их нет-пригодятся следующие ссылки:

    https://docs.docker.com/engine/install/
    https://docs.docker.com/engine/install/linux-postinstall/ (для линукс-пользователей)
    https://git-scm.com/downloads

Запуск приложения: Выполняем в терминале поочередно команды ниже

docker compose up --build -d
docker compose exec php composer install
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

Методы:

    POST http://localhost:8080/api/user: Создает нового пользователя. Требуются валидные данные в теле запроса. Возвращает email пользователя и сообщение об успехе, либо ошибку с соответствующим кодом статуса.
    
    DELETE http://localhost:8080/api/user/{id}: Удаляет пользователя по ID. Требуются права администратора. Возвращает сообщение об успехе или ошибку (например, пользователь не найден или неверный ID).
    
    GET http://localhost:8080/api/user/{id}: Получает информацию о пользователе по ID. Возвращает данные пользователя (ID и email) или ошибку, если пользователь не найден или ID неверен.
    
    PUT http://localhost:8080/api/user/{id}: Обновляет данные существующего пользователя по ID. Доступ имеет либо сам пользователь, либо администратор. Возвращает сообщение об успехе или ошибку, если пользователь не найден
    
    POST http://localhost:8080/api/login: Выполняет аутентификацию пользователя. В случае успешной аутентификации возвращается идентификатор пользователя и имитация токена-пустышки(security работает по умолчанию исп. сессии).
    
    GET http://localhost:8080/api/logout: Выполняет выход пользователя из системы. После выхода возвращается сообщение об успешном выходе.

Тестить методы можно постманом
пример создания пользователя - отправить json на роут POST http://localhost:8080/api/user
{
"email": "too@mail.ru",
"password": "123",
"name": "Adam"
}