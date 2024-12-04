# intlab

На локальной машине нужен установленный: git, docker и docker compose Если их нет -пригодятся следующие ссылки:

    https://docs.docker.com/engine/install/
    https://docs.docker.com/engine/install/linux-postinstall/ (для линукс-пользователей)
    https://git-scm.com/downloads

Запуск приложения: Выполняем в терминале поочередно команды ниже

docker compose up --build -d
docker compose exec php composer install
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

Методы:

POST /api/user: Создает нового пользователя. Требуются валидные данные в теле запроса. Возвращает email пользователя и сообщение об успехе, либо ошибку с соответствующим кодом статуса.

DELETE /api/user/{id}: Удаляет пользователя по ID. Требуются права администратора. Возвращает сообщение об успехе или ошибку (например, пользователь не найден или неверный ID).

GET /api/user/{id}: Получает информацию о пользователе по ID. Возвращает данные пользователя (ID и email) или ошибку, если пользователь не найден или ID неверен.

PUT /api/user/{id}: Обновляет данные существующего пользователя по ID. Доступ имеет либо сам пользователь, либо администратор. Возвращает сообщение об успехе или ошибку, если пользователь не найден