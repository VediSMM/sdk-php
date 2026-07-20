# Changelog

All notable changes to this project are documented here. The project follows Semantic Versioning.

## [0.1.1] - 2026-07-20

- Authenticated calls now send the same in-memory credential through both `Authorization: Bearer` and the API's `X-API-Token` proxy fallback.
- `X-API-Token` is reserved and cannot be replaced through custom request headers.

## [0.1.0] - 2026-07-19

- Initial native PHP SDK for VediSMM user API v1.
- Complete 83-operation service surface for contract `1.0.0`.
- Safe cURL transport, typed errors, bounded retries, pagination, jobs, streaming, idempotency, and webhook verification.
- English/Russian documentation, executable example, package inspection, and PHP 8.1–8.4 CI.
