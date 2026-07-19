# VediSMM PHP SDK

Официальный нативный PHP-клиент для всех 83 операций пользовательского API VediSMM v1. Административного API в пакете нет.

[English](README.md) · [Документация API](https://vedismm.ru/docs/api) · [Политика безопасности](SECURITY.md)

## Требования

- PHP 8.1–8.4
- Composer 2
- `ext-curl` и `ext-json`

## Установка из неизменяемого GitHub-релиза

До публикации в Packagist один раз зарегистрируйте официальный VCS-репозиторий и установите тегированную версию:

```bash
composer config repositories.vedismm-sdk vcs https://github.com/VediSMM/sdk-php
composer require vedismm/sdk:0.1.0
```

## Быстрый старт

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use VediSMM\Client;
use VediSMM\Config;
use VediSMM\VediSMM;

$sdk = new VediSMM(new Client(new Config(
    accessToken: getenv('VEDISMM_TOKEN') ?: null,
)));

$me = $sdk->profile->getMe();
echo $me->data['data']['email'] . PHP_EOL;
```

Фасад предоставляет сервисы `system`, `auth`, `profile`, `sessions`, `audit`, `personalTokens`, `preferences`, `networks`, `connections`, `accounts`, `groups`, `media`, `posts`, `jobs`, `calendar`, `analytics` и `webhooks`. Каждый метод принимает необязательный immutable-объект `CallOptions`.

## Безопасные настройки по умолчанию

- Bearer-токен не сохраняется на диск и не попадает в строковые представления.
- Redirect авторизованного запроса отклоняется, поэтому credential не уходит на другой origin.
- Повторы конечны и разрешены только для безопасных запросов либо при стабильном ключе идемпотентности.
- Problem Details, rate limit, `412`, timeout, transport и decode представлены разными исключениями.
- Скачивание поддерживает caller-owned sink, загрузка — stream и cURL multipart.
- Подпись webhook проверяется по исходным байтам и может использовать атомарное replay-хранилище.

Подробности находятся в [русском руководстве](docs/ru/guide.md) и [справочнике методов](docs/ru/api-reference.md).
