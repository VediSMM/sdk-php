# Руководство VediSMM PHP SDK

## Конфигурация и авторизация

По умолчанию `Config` использует `https://vedismm.ru/api/v1`. Пользовательский URL обязан работать по HTTPS; HTTP разрешён только для `localhost`, `127.0.0.1` и `::1`. Credential, query и fragment внутри base URL запрещены.

Передайте строку токена либо callback без аргументов. Callback вызывается перед каждым авторизованным запросом и подходит для короткоживущих токенов. SDK не сохраняет credential на диск. Не логируйте собственные token-provider callbacks.

Авторизованные запросы передают один credential через `Authorization: Bearer`
и proxy-fallback VediSMM `X-API-Token`; API отдаёт приоритет стандартному
заголовку. Оба заголовка управляются SDK, не переопределяются через
`CallOptions` и не должны логироваться.

## Вызов операций

В прикладном коде используйте именованные методы сервисов. Каждый принимает `CallOptions`:

- `path` безопасно подставляет параметры `{...}`;
- `query` поддерживает scalar и повторяющиеся массивы;
- `body` сериализуется в JSON;
- `rawBody` принимает строку или читаемый stream;
- `multipart` принимает cURL-поля, включая `new CURLFile(...)`;
- `idempotencyKey` задаёт `Idempotency-Key`;
- `ifMatch` передаёт сильный ETag через `If-Match`;
- `sink` потоково пишет ответ в принадлежащий приложению ресурс.

Неизвестная операция, пропущенный или лишний path-параметр, некорректный header, небезопасный base URL и повреждённый credential отклоняются до сети.

`CallOptions::idempotent($key)` и `CallOptions::ifMatch($etag)` — короткие
фабрики для двух управляемых conditional headers. Их валидация и сериализация
по-прежнему выполняются внутри `Client`.

## Трекинговые ссылки

При создании короткой ссылки исходный destination передаётся без изменений.
SDK не разбирает, не нормализует, не сокращает URL и не добавляет в него UTM:

```php
$created = $sdk->trackingLinks()->create(
    ['destination_url' => 'https://example.com/article?id=7#details'],
    CallOptions::idempotent('article-7-v1'),
);
```

`list()` получает одну cursor-страницу, а `iterate()` лениво проходит все
страницы. Одна ссылка читается через `get()`. Destination нельзя обновить: для
другого URL создайте новую ссылку. Единственные lifecycle mutations —
`disable()` и `archive()`; им требуется последний сильный ETag:

```php
$link = $sdk->trackingLinks()->get(42);
if ($link->etag !== null) {
    $sdk->trackingLinks()->disable(42, CallOptions::ifMatch($link->etag));
}
```

## Аналитика трекинга

Доступны шесть endpoints: `summary()`, `timeseries()`, `links()`, `posts()`,
`sources()` и `geo()`. Каждый принимает array с обязательными ISO-датами
`from`/`to`; `link_id`, `post_id` и `network` необязательны. Три списка также
поддерживают `limit`/`cursor`, а helpers `iterateLinks()`, `iteratePosts()` и
`iterateSources()` сохраняют весь исходный фильтр при переходе по непрозрачным
cursors.

```php
$geo = $sdk->trackingAnalytics()->geo([
    'from' => '2026-08-01',
    'to' => '2026-08-13',
    'network' => 'vk',
]);
```

Geo-ответ содержит агрегаты стран и privacy-minimized GeoJSON городов, но не
raw IP. Во время dark rollout API может вернуть обычный `ApiException` со
статусом `503` и `errorCode === 'feature_disabled'`.

## Настройки трекинга публикации

При создании поста передайте вложенный объект `options.tracking` с двумя
обязательными boolean-полями. Оба по умолчанию равны `false`. `add_source`
имеет смысл только при `shorten_links=true`; сервер применяет настройки при
формировании delivery snapshot и не меняет сохранённый авторский текст.

```php
$sdk->posts->createPostDraft(
    [
        'title' => 'Пример',
        'content' => 'Читайте https://example.com/article',
        'options' => [
            'tracking' => [
                'shorten_links' => true,
                'add_source' => true,
            ],
        ],
    ],
    CallOptions::idempotent('post-draft-1'),
);
```

Та же типизированная форма request доступна в `updatePostDraft()`; `id` и
текущий ETag передаются вторым аргументом `CallOptions`. Исходная форма с одним
`CallOptions` сохранена для совместимости.

## Ошибки и повторы

API-ошибки представлены `ApiException`. Для `429` существует `RateLimitException` с `retryAfterMs`, для `412` — `PreconditionFailedException`. Timeout, transport, redirect и decode также различаются. После маскирования сохраняются `status`, `errorCode`, `detail`, validation `errors` и `requestId`.

По умолчанию разрешены две дополнительные попытки. `429`, `502`, `503`, `504` и временные transport failures повторяются только для `GET`, `PUT`, `DELETE` либо при стабильном ключе идемпотентности. `Retry-After` ограничен 30 секундами. Stream не повторяется автоматически.

## Пагинация

`Paginator::iterate()` лениво вызывает callback страницы, отдаёт элементы `data` и пересылает только непрозрачный `meta.next_cursor` из ответа. Циклический cursor считается ошибкой.

## Конкурентные изменения

Сохраняйте `etag` из ответа и передавайте его через `CallOptions(ifMatch: $etag)`. После `PreconditionFailedException` перечитайте ресурс и применяйте изменение к новой версии только осознанно.

## Медиа

Multipart-загрузка: `CallOptions(multipart: ['file' => new CURLFile($path)])`. Для исходного потока используйте `rawBody`. Большой ответ можно направить в бинарный `sink`; SDK не закрывает ресурс приложения.

## Задания публикации

Команды публикации отвечают `202`. `JobWaiter::wait()` опрашивает callback с конечным timeout, поддерживает cancellation callback, возвращает `succeeded`/`partially_succeeded` и создаёт `JobFailedException` для `failed`/`cancelled`. Фоновая работа после выхода не остаётся.

## Webhooks

Передайте исходные байты тела, `X-VediSMM-Timestamp` и `X-VediSMM-Signature` формата `v1=<64 lowercase hex>` в `WebhookVerifier::verify()`. Используются HMAC-SHA256 и `hash_equals`, устаревшие timestamps отклоняются. Для production реализация `ReplayStoreInterface` должна атомарно фиксировать event ID через уникальную вставку в БД или распределённый `SET NX`.
