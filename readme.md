## Тестовое задание по amo-crm
Рабочий конфиг находится в .env.example
Для запуска решения:
```
yarn
gulp
php artisan migrate
php artisan queue:listen
```

Код контроллера app/Http/Controllers/Api/RequestController.php
Код вспомогательного класса app/AmoCRMHelper.php
Отложенное задание app/Jobs/SendNotificationTelegram.php
