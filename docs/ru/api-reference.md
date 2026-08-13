# Справочник методов PHP SDK

Фасад предоставляет ровно 94 операции пользовательского API v1. Административных методов намеренно нет.

Каждый метод принимает необязательный `VediSMM\Value\CallOptions` и возвращает `VediSMM\Value\ApiResponse`.

## `system`

- `getOpenApi(?CallOptions $options = null)`
- `ping(?CallOptions $options = null)`

## `auth`

- `forgotPassword(?CallOptions $options = null)`
- `login(?CallOptions $options = null)`
- `logout(?CallOptions $options = null)`
- `logoutAll(?CallOptions $options = null)`
- `refresh(?CallOptions $options = null)`
- `register(?CallOptions $options = null)`
- `resendVerification(?CallOptions $options = null)`
- `resetPassword(?CallOptions $options = null)`
- `verifyEmail(?CallOptions $options = null)`

## `profile`

- `changePassword(?CallOptions $options = null)`
- `deleteMe(?CallOptions $options = null)`
- `getMe(?CallOptions $options = null)`
- `updateMe(?CallOptions $options = null)`

## `sessions`

- `getSession(?CallOptions $options = null)`
- `listSessions(?CallOptions $options = null)`
- `revokeSession(?CallOptions $options = null)`

## `audit`

- `listAuditEvents(?CallOptions $options = null)`

## `personalTokens`

- `createPersonalToken(?CallOptions $options = null)`
- `getPersonalToken(?CallOptions $options = null)`
- `listPersonalTokens(?CallOptions $options = null)`
- `revokePersonalToken(?CallOptions $options = null)`
- `rotatePersonalToken(?CallOptions $options = null)`
- `updatePersonalToken(?CallOptions $options = null)`

## `preferences`

- `createContentTemplate(?CallOptions $options = null)`
- `deleteContentTemplate(?CallOptions $options = null)`
- `getContentTemplate(?CallOptions $options = null)`
- `getSignatures(?CallOptions $options = null)`
- `listContentTemplates(?CallOptions $options = null)`
- `replaceSignatures(?CallOptions $options = null)`
- `updateContentTemplate(?CallOptions $options = null)`

## `networks`

- `getNetwork(?CallOptions $options = null)`
- `listNetworks(?CallOptions $options = null)`

## `connections`

- `cancelAccountConnection(?CallOptions $options = null)`
- `confirmAccountConnection(?CallOptions $options = null)`
- `getAccountConnection(?CallOptions $options = null)`
- `startAccountConnection(?CallOptions $options = null)`

## `accounts`

- `disconnectAccount(?CallOptions $options = null)`
- `getAccount(?CallOptions $options = null)`
- `listAccounts(?CallOptions $options = null)`
- `verifyAccount(?CallOptions $options = null)`

## `groups`

- `createGroup(?CallOptions $options = null)`
- `deleteGroup(?CallOptions $options = null)`
- `getGroup(?CallOptions $options = null)`
- `listGroups(?CallOptions $options = null)`
- `replaceGroupAccounts(?CallOptions $options = null)`
- `updateGroup(?CallOptions $options = null)`

## `media`

- `deleteMedia(?CallOptions $options = null)`
- `getMedia(?CallOptions $options = null)`
- `getMediaContent(?CallOptions $options = null)`
- `getSignedMediaContent(?CallOptions $options = null)`
- `listMedia(?CallOptions $options = null)`
- `uploadMedia(?CallOptions $options = null)`

## `posts`

- `checkPostConstraints(?CallOptions $options = null)`
- `createPostDraft(array|CallOptions|null $options = null, ?CallOptions $callOptions = null)` принимает типизированный `PostCreateRequest` array или исходную low-level форму `CallOptions`.
- `deletePostDraft(?CallOptions $options = null)`
- `getPost(?CallOptions $options = null)`
- `listPosts(?CallOptions $options = null)`
- `schedulePost(?CallOptions $options = null)`
- `unschedulePost(?CallOptions $options = null)`
- `updatePostDraft(array|CallOptions|null $options = null, ?CallOptions $callOptions = null)` принимает типизированный `PostUpdateRequest` array или исходную low-level форму `CallOptions`.

## `jobs`

- `deletePostEverywhere(?CallOptions $options = null)`
- `getPublicationJob(?CallOptions $options = null)`
- `listPublicationJobs(?CallOptions $options = null)`
- `publishPost(?CallOptions $options = null)`
- `retryPostTargets(?CallOptions $options = null)`

## `calendar`

- `listCalendarEvents(?CallOptions $options = null)`

## `analytics`

- `getAnalyticsAudience(?CallOptions $options = null)`
- `getAnalyticsNetworks(?CallOptions $options = null)`
- `getAnalyticsSummary(?CallOptions $options = null)`
- `getAnalyticsTimeseries(?CallOptions $options = null)`
- `listAnalyticsPosts(?CallOptions $options = null)`

## `trackingLinks`

Специализированный сервис описывает типизированные array shapes через PHPDoc и
возвращает `ApiResponse<TrackingLinkResponse>`. Destination неизменяем: метода
обновления нет.

- `create(array $data, ?CallOptions $options = null)` — `createTrackingLink(...)`; передайте `array{destination_url: string}` и `CallOptions::idempotent($key)`.
- `list(array $query = [], ?CallOptions $options = null)` — `listTrackingLinks(...)`; форма query: `array{cursor?: string, limit?: int}`.
- `iterate(array $query = [], ?CallOptions $options = null)` лениво следует по `meta.next_cursor` через `Paginator`.
- `get(int $id, ?CallOptions $options = null)` — `getTrackingLink(...)`.
- `disable(int $id, ?CallOptions $options = null)` — `disableTrackingLink(...)`; текущий ETag передаётся через `CallOptions::ifMatch($etag)`.
- `archive(int $id, ?CallOptions $options = null)` — `archiveTrackingLink(...)`; текущий ETag передаётся через `CallOptions::ifMatch($etag)`.

## `trackingAnalytics`

Каждый метод принимает обязательные ISO-даты `from`/`to` и необязательные
`link_id`, `post_id` и канонический request `network`. Ключ сети в ответе остаётся
открытой строкой для прямой совместимости с будущими сетями.

- `summary(array $query, ?CallOptions $options = null)` — `getTrackingAnalyticsSummary(...)`.
- `timeseries(array $query, ?CallOptions $options = null)` — `getTrackingAnalyticsTimeseries(...)`.
- `links(array $query, ?CallOptions $options = null)` — `listTrackingAnalyticsLinks(...)`.
- `posts(array $query, ?CallOptions $options = null)` — `listTrackingAnalyticsPosts(...)`.
- `sources(array $query, ?CallOptions $options = null)` — `listTrackingAnalyticsSources(...)`.
- `geo(array $query, ?CallOptions $options = null)` — `getTrackingAnalyticsGeo(...)`.
- `iterateLinks(...)`, `iteratePosts(...)` и `iterateSources(...)` сохраняют исходные фильтры и лениво следуют только по непрозрачным server cursors через `Paginator`.

## `webhooks`

- `createWebhook(?CallOptions $options = null)`
- `deleteWebhook(?CallOptions $options = null)`
- `getWebhook(?CallOptions $options = null)`
- `getWebhookDelivery(?CallOptions $options = null)`
- `listWebhookDeliveries(?CallOptions $options = null)`
- `listWebhooks(?CallOptions $options = null)`
- `retryWebhookDelivery(?CallOptions $options = null)`
- `rotateWebhookSecret(?CallOptions $options = null)`
- `testWebhook(?CallOptions $options = null)`
- `updateWebhook(?CallOptions $options = null)`
