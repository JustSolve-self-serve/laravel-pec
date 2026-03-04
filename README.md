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
LEGALMAIL_PEC_TIMEOUT=20

# Driver: openapi_pec_massiva
OPENAPI_PEC_MASSIVA_BASE_URL=https://your-openapi-base-url
OPENAPI_PEC_MASSIVA_TOKEN=your-openapi-token
OPENAPI_PEC_MASSIVA_TIMEOUT=20
```

## Usage

Resolve via container:

```php
use JustSolve\LegalmailPec\Contracts\PecClient;
use JustSolve\LegalmailPec\Contracts\PecClientManager;

// Default driver from config(pec.default)
$client = app(PecClient::class);

// Explicit driver selection at runtime
$manager = app(PecClientManager::class);
$legalmailClient = $manager->driver('legalmail');
$massivaClient = $manager->driver('openapi_pec_massiva');
```

Use facade:

```php
use JustSolve\LegalmailPec\Facades\Pec;

Pec::createSubmission(['subject' => 'Hello']); // default driver
Pec::driver('openapi_pec_massiva')->createSubmission(['subject' => 'Hello']); // explicit driver
```

Or use helper:

```php
$client = pec_client(); // default driver
$massivaClient = pec_client('openapi_pec_massiva');
```

### listMessages

Driver endpoints:
- `legalmail`: `GET /{mailboxId}/folders/{folderId}/messages/{messageUIdValidity}`
- `openapi_pec_massiva`: `GET /inbox`

```php
$response = $client->listMessages(['limit' => 10]);
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
$response = $client->createSubmission([
    'subject' => 'Test PEC',
    'body' => 'Message body',
]);
```

### updateMessage

Legalmail only:

`PUT /{mailboxId}/folders/{folderId}/messages/{messageUIdValidity}/{messageUId}?seen={0|1}`

```php
$legalmailClient = app(\JustSolve\LegalmailPec\Contracts\PecClientManager::class)->driver('legalmail');
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
