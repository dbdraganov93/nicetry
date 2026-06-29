# API Specification

All management endpoints use `Authorization: Bearer <api-key>`.

## `GET /v1/countries`

Returns enabled countries and cities.

## `GET /v1/usage`

Returns the authenticated user's current billing-period usage.

## `POST /v1/api-keys`

Creates an API key and returns the secret once.

## `DELETE /v1/api-keys/{id}`

Revokes an API key.

## Proxy authentication

Use HTTP proxy basic authentication:

```text
username: de.customer123
password: generated-proxy-password
```

The prefix selects routing geography. Future compatible forms include `de-berlin.customer123`, sticky session suffixes, and dedicated-IP aliases.
