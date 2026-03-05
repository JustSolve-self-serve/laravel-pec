# Legalmail PEC Laravel Package

Laravel package to interact with Legalmail PEC API endpoints for messages and submissions.

## Requirements

- PHP `^8.2`
- Laravel components `^11.0|^12.0`

## Installation

### Install from Packagist

```bash
composer require justsolve/legalmail-pec
```

### Install locally with a path repository (during development)

In your Laravel app `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../legalmail-pec"
    }
  ]
}
```

Then:

```bash
composer require justsolve/legalmail-pec:@dev
```

## Configuration

Publish config:

```bash
php artisan vendor:publish --tag=pec-config
```

Set these variables in your app `.env`:

```env
LEGALMAIL_PEC_DRIVER=legalmail # legalmail|openapi_pec_massiva

LEGALMAIL_PEC_MAILBOX_ID=your-mailbox-id
LEGALMAIL_PEC_FOLDER_ID=your-folder-id
LEGALMAIL_PEC_MESSAGE_UID_VALIDITY=your-message-uid-validity

# Driver: legalmail
LEGALMAIL_PEC_BASE_URL=https://your-legalmail-base-url
LEGALMAIL_PEC_TOKEN=your-legalmail-token

# Driver: openapi_pec_massiva
OPENAPI_PEC_MASSIVA_BASE_URL=https://your-openapi-base-url
OPENAPI_PEC_MASSIVA_TOKEN=your-openapi-token
```

## Usage

Resolve via container:

```php
use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\OpenApi\OpenApiPecMassivaClient;

$legalmailClient = app(LegalmailClient::class);
$openApiClient = app(OpenApiPecMassivaClient::class);
```

Or use helpers:

```php
$legalmailClient = legalmail_client();
$openApiClient = openapi_pec_massiva_client();
```

### listMessages

Driver endpoints:
- `legalmail`: `GET /{mailboxId}/folders/{folderId}/messages/{messageUIdValidity}`
- `openapi_pec_massiva`: `GET /inbox`

```php
$response = $client->listMessages(['limit' => 10]);
```

OpenAPI custom headers model (for `openapi_pec_massiva` list/get/delete):

```php
use JustSolve\LaravelPec\OpenApi\Models\OpenapiHeaders;

$openApiClient = app(\JustSolve\LaravelPec\OpenApi\OpenApiPecMassivaClient::class);

$headers = new OpenapiHeaders('openapi-user', 'openapi-pass');
$response = $openApiClient->listMessages(headers: $headers);
```

Override path parameters per call (optional):

```php
$response = $client->listMessages(
    query: ['limit' => 10],
    mailboxId: 'mailbox-override',
    folderId: 'folder-override',
    messageUidValidity: 'uid-validity-override'
);
```

### getMessage

Driver endpoints:
- `legalmail`: `GET /{mailboxId}/folders/{folderId}/messages/{messageUIdValidity}/{messageUId}`
- `openapi_pec_massiva`: `GET /inbox/{messageUId}`

```php
$response = $client->getMessage('message-uid');
```

### createSubmission

Driver endpoints:
- `legalmail`: `POST /{mailboxId}/submissions`
- `openapi_pec_massiva`: `POST /send`

```php
// legalmail
$response = $legalmailClient->createSubmission([
    'subject' => 'Test PEC',
    'body' => 'Message body',
]);
```

OpenAPI typed models (for `openapi_pec_massiva`):

```php
use JustSolve\LaravelPec\OpenApi\Models\OpenapiAttachment;
use JustSolve\LaravelPec\OpenApi\Models\OpenapiCreateSubmissionPayload;

$openApiClient = app(\JustSolve\LaravelPec\OpenApi\OpenApiPecMassivaClient::class);

$payload = new OpenapiCreateSubmissionPayload(
    sender: 'sender@example.test',
    recipient: ['recipient@example.test'],
    subject: 'Test PEC',
    body: 'Message body',
    attachments: [
        new OpenapiAttachment('invoice.pdf', base64_encode('file-content')),
    ],
    username: 'api-username',
    password: 'api-password',
);

$typedResponse = $openApiClient->createSubmission($payload);
```

### updateMessage

Legalmail only:

`PUT /{mailboxId}/folders/{folderId}/messages/{messageUIdValidity}/{messageUId}?seen={0|1}`

```php
$legalmailClient = app(\JustSolve\LaravelPec\Legalmail\LegalmailClient::class);
$response = $legalmailClient->updateMessage('message-uid', true);
```

### deleteMessage

Driver endpoints:
- `legalmail`: `DELETE /{mailboxId}/folders/{folderId}/messages/{messageUIdValidity}/{messageUId}`
- `openapi_pec_massiva`: `DELETE /inbox/{messageUId}`

```php
$deleted = $client->deleteMessage('message-uid');
```

## Testing

This package includes:

- Feature tests (mocked HTTP using `Http::fake`) in `tests/Feature`
- Integration tests (real sandbox calls) in `tests/Integration`

### Run all tests

```bash
./vendor/bin/phpunit -c phpunit.xml.dist
```

By default, integration tests are skipped.

### Run only feature tests

```bash
./vendor/bin/phpunit -c phpunit.xml.dist --testsuite Feature
```

### Run integration tests against sandbox

From package root, set env vars in your terminal session:

```bash
export LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true
export LEGALMAIL_PEC_DRIVER="legalmail" # or openapi_pec_massiva
export LEGALMAIL_PEC_BASE_URL="https://your-sandbox-url"
export LEGALMAIL_PEC_TOKEN="your-token"
export LEGALMAIL_PEC_MAILBOX_ID="your-mailbox-id"
export LEGALMAIL_PEC_FOLDER_ID="your-folder-id"
export LEGALMAIL_PEC_MESSAGE_UID_VALIDITY="your-uid-validity"
export LEGALMAIL_PEC_TEST_MESSAGE_UID="existing-message-uid"
```

Then run:

```bash
./vendor/bin/phpunit -c phpunit.xml.dist --testsuite Integration
```

Notes:

- `LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true` is required; otherwise integration tests are skipped.
- `LEGALMAIL_PEC_TEST_MESSAGE_UID` is required for the `getMessage` integration test.

## Error Handling

The client throws `RuntimeException` when:

- API response status is not successful
- Required path parameters are missing (`mailbox_id`, `folder_id`, `message_uid_validity`, where applicable)

## License

MIT
