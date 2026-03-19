<?php

namespace JustSolve\LaravelPec\Facades;

use Illuminate\Support\Facades\Facade;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiDeleteMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetAccettazioneConsegnaResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;
use JustSolve\LaravelPec\Openapi\Models\OpenapiListMessagesResponse;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;

/**
 * @method static OpenapiListMessagesResponse listMessages(array $query = [], ?OpenapiHeaders $headers = null)
 * @method static OpenapiGetMessageResponse getMessage(string $messageUid, ?OpenapiHeaders $headers = null)
 * @method static OpenapiGetAccettazioneConsegnaResponse getAccettazioneConsegna(string $messageUid, ?OpenapiHeaders $headers = null)
 * @method static OpenapiCreateSubmissionResponse createSubmission(OpenapiCreateSubmissionPayload $payload)
 * @method static OpenapiDeleteMessageResponse deleteMessage(string $messageUid, ?OpenapiHeaders $headers = null)
 */
class OpenapiPecMassiva extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OpenapiPecMassivaClient::class;
    }
}
