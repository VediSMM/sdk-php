# Справочник методов PHP SDK

Фасад предоставляет ровно 83 операции пользовательского API v1. Административных методов намеренно нет.

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
- `createPostDraft(?CallOptions $options = null)`
- `deletePostDraft(?CallOptions $options = null)`
- `getPost(?CallOptions $options = null)`
- `listPosts(?CallOptions $options = null)`
- `schedulePost(?CallOptions $options = null)`
- `unschedulePost(?CallOptions $options = null)`
- `updatePostDraft(?CallOptions $options = null)`

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
