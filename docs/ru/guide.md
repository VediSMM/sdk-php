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
