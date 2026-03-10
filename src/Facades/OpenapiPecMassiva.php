<?php

namespace JustSolve\LaravelPec\Facades;

use Illuminate\Support\Facades\Facade;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;
use JustSolve\LaravelPec\Openapi\Models\OpenapiListMessagesResponse;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;

/**
 * @method static OpenapiListMessagesResponse listMessages(array $query = [], ?OpenapiHeaders $headers = null)
 * @method static array<string, mixed> getMessage(string $messageUid, ?OpenapiHeaders $headers = null)
 * @method static OpenapiCreateSubmissionResponse createSubmission(OpenapiCreateSubmissionPayload $payload)
 * @method static bool deleteMessage(string $messageUid, ?OpenapiHeaders $headers = null)
 */
class OpenapiPecMassiva extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OpenapiPecMassivaClient::class;
    }
}
