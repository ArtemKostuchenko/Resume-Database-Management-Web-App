# Resume Database Management Web App
# Опис
Веб-додаток для управління базою резюме.
# Технології
- PHP 8.2
- Symfony 5.4
- Doctrine ORM, MySQL 8.4
# Запуск
1. `composer install`
2. `npm install`
3. `npm dev`
4. `php bin/console (symfony console) doctrine:database:create`
5. `php bin/console (symfony console) doctrine:migrations:migrate`
6. `symfony server:start`
# Додатково
- Шлях завантажених резюме `public/uploads/resumes`