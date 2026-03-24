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
# Driver: legalmail
LEGALMAIL_PEC_BASE_URL=https://your-legalmail-base-url
LEGALMAIL_PEC_TOKEN=your-legalmail-token
LEGALMAIL_PEC_MAILBOX_ID=your-mailbox-id
LEGALMAIL_PEC_FOLDER_ID=your-folder-id
LEGALMAIL_PEC_MESSAGE_UID_VALIDITY=your-message-uid-validity

# Driver: openapi_pec_massiva
OPENAPI_PEC_MASSIVA_BASE_URL=https://test.ws.pecmassiva.com
OPENAPI_PEC_MASSIVA_TOKEN=your-openapi-token
```

## PEC Massiva Account Setup

Registering and activating the PEC Massiva account is a prerequisite for obtaining the credentials used by the `openapi_pec_massiva` driver.

### Registration and activation checklist

1. Open the PEC Massiva registration page:
   `https://www.pecmassiva.com/index.php/checkout/config/standard`
2. Create the account with:
   - PEC address to enable: `myorganization@pecmassiva.com`
   - Company / organization data: `partita iva`, `company name`, `pec`, `email`, `phone`, `address`
3. Accept contract / privacy policies and pay
4. Check email inbox for an Openapi communication
5. Complete and sign the received document with:
   - PEC address to enable
   - Company / organization data
   - "Tipologia casella acquistata": `PEC MASSIVA VIA API STANDARD`
   - "Durata del contratto": `1 anno`
6. Send it back to Openapi with copy of your ID
7. Wait for account credentials via email:
   - Typical activation time: `48 hours`
8. Generate a "Pec Massiva" production token from Openapi console:
   `https://console.openapi.com/oauth`
9. Save base url, token, credentials and pec massiva address in your Laravel app `.env`, and add configs in `config/pec.php`:
```env
OPENAPI_PEC_MASSIVA_BASE_URL=https://ws.pecmassiva.com
OPENAPI_PEC_MASSIVA_TOKEN=my_token
OPENAPI_PEC_MASSIVA_SENDER=myorganization@pecmassiva.com
OPENAPI_PEC_MASSIVA_USERNAME=my_username
OPENAPI_PEC_MASSIVA_PASSWORD=my_password
```
```php
return [
    'drivers' => [
        'openapi_pec_massiva' => [
            'base_url' => env('OPENAPI_PEC_MASSIVA_BASE_URL', 'https://test.ws.pecmassiva.com'),
            'token' => env('OPENAPI_PEC_MASSIVA_TOKEN'),
            'sender' => env('OPENAPI_PEC_MASSIVA_SENDER', null),
            'username' => env('OPENAPI_PEC_MASSIVA_USERNAME', null),
            'password' => env('OPENAPI_PEC_MASSIVA_PASSWORD', null),
        ],
  
```

## Usage

Resolve via container:

```php
use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;

$legalmailClient = app(LegalmailClient::class);
$openApiClient = app(OpenapiPecMassivaClient::class);
```

Or use helpers:

```php
$legalmailClient = legalmail_client();
$openApiClient = openapi_pec_massiva_client();
```

Or use facades:

```php
use JustSolve\LaravelPec\Facades\Legalmail;
use JustSolve\LaravelPec\Facades\OpenapiPecMassiva;
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
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;

$openApiClient = app(\JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient::class);

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

### getAccettazioneConsegna

OpenAPI only:

- `openapi_pec_massiva`: `GET /send/{messageUId}`

```php
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;

$openApiClient = app(\JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient::class);

$headers = new OpenapiHeaders('openapi-user', 'openapi-pass');
$response = $openApiClient->getAccettazioneConsegna('message-uid', $headers);

if ($response->success) {
    foreach ($response->data as $status) {
        $sender = $status->sender;
        $recipient = $status->recipient;
        $date = $status->date;
        $subject = $status->subject;
        $body = $status->body;
    }
}
```

The method returns an `OpenapiGetAccettazioneConsegnaResponse` with:

- `data`: array of `ResponseStatus`
- `success`: boolean
- `message`: string

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

`OpenapiAttachment::$file` must contain the attachment content as a base64-encoded string.

```php
use JustSolve\LaravelPec\Openapi\Models\OpenapiAttachment;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;

$openApiClient = app(\JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient::class);

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

The integration tests are split by driver and each driver has its own enable flag.

Legalmail integration test currently covers `listMessages()`.

Required variables for Legalmail:

```bash
export LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true
export LEGALMAIL_PEC_BASE_URL="https://your-sandbox-url"
export LEGALMAIL_PEC_TOKEN="your-token"
export LEGALMAIL_PEC_MAILBOX_ID="your-mailbox-id"
export LEGALMAIL_PEC_FOLDER_ID="your-folder-id"
export LEGALMAIL_PEC_MESSAGE_UID_VALIDITY="your-uid-validity"
```

OpenAPI integration tests currently cover:
- `listMessages()`
- `createSubmission()`

Required variables for OpenAPI:

```bash
export OPENAPI_PEC_RUN_INTEGRATION_TESTS=true
export OPENAPI_PEC_MASSIVA_BASE_URL="https://test.ws.pecmassiva.com"
export OPENAPI_PEC_MASSIVA_TOKEN="your-openapi-token"
export OPENAPI_PEC_TEST_SENDER="sender@pec.example"
export OPENAPI_PEC_TEST_RECIPIENT="recipient@pec.example"
export OPENAPI_PEC_TEST_USERNAME="openapi-username"
export OPENAPI_PEC_TEST_PASSWORD="openapi-password"
```

Optional variables for the OpenAPI send test:

```bash
export OPENAPI_PEC_TEST_SUBJECT="Integration test subject"
export OPENAPI_PEC_TEST_BODY="Integration test body"
```

Then run:

```bash
./vendor/bin/phpunit -c phpunit.xml.dist --testsuite Integration
```

Notes:

- `LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true` enables Legalmail integration tests.
- `OPENAPI_PEC_RUN_INTEGRATION_TESTS=true` enables OpenAPI integration tests.
- If `OPENAPI_PEC_TEST_SENDER`, `OPENAPI_PEC_TEST_RECIPIENT`, `OPENAPI_PEC_TEST_USERNAME`, or `OPENAPI_PEC_TEST_PASSWORD` are missing, the OpenAPI send test is skipped.
- `OPENAPI_PEC_MASSIVA_TOKEN` is required and is used as the bearer token for the OpenAPI client.

## Error Handling

The client throws `RuntimeException` when:

- API response status is not successful
- Required path parameters are missing (`mailbox_id`, `folder_id`, `message_uid_validity`, where applicable)

## License

MIT
