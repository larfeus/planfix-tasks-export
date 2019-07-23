## Сервис для работы с выгрузкой данных из Planfix

Проект основан на связке Slim 3.12 + Planfix API + Google API без использования СУБД

Промежуточные данные сохраняются в php-сессии.

## Deploy

#### Первая установка:
- Склонировать репозиторий.
- Скопировать .env.example в .env и настроить в соответствии с окружением.
- Инициализация вендоров: 
```shell
composer install --no-dev --optimize-autoloader
```

#### Обновление:
- Выполнить:
```shell
 composer install --no-dev --optimize-autoloader
```

## Окружение

Для корректной работы сервиса необходимо задать параметры окружения (.env):

Настройки Planfix API:
```
PLANFIX_URL=
PLANFIX_KEY=
PLANFIX_SECRET=
PLANFIX_ACCOUNT=
```
см. https://planfix.ru/docs/XML_API_v1

Настройки Google API:
```
GOOGLEDRIVE_CLIENT_ID=
GOOGLEDRIVE_CLIENT_SECRET=
```
см. https://telegra.ph/Kak-sozdat-klyuchi-dlya-Google-API-07-22

```
GOOGLEDRIVE_CALLBACK=
```
Последний параметр отвечает за обратный вызов при успешной авторизации в Google Drive.
Вынесено в окружение для возможности указания проксирующего сервиса при развертывании
проекта, например, на локальном хостинге.

Например,

```
GOOGLEDRIVE_CALLBACK=https://wideprime.ru/proxy.php?origin=http%3A%2F%2Fplanfix%2Fgoogle%2Fcallback
```
где ```https://wideprime.ru/proxy.php``` просто перенаправляет на origin с сохранением всех остальных аргументов.