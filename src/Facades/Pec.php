<?php

namespace JustSolve\LaravelPec\Facades;

use Illuminate\Support\Facades\Facade;
use JustSolve\LaravelPec\Contracts\PecClientManager;

/**
 * @method static \JustSolve\LaravelPec\Contracts\PecClient driver(string $driver)
 * @method static array<string, mixed> listMessages(array $query = [], ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null)
 * @method static array<string, mixed> getMessage(string $messageUid, ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null)
 * @method static array<string, mixed> createSubmission(array|\JustSolve\LaravelPec\Contracts\CreateSubmissionPayload $payload, ?string $mailboxId = null)
 * @method static bool deleteMessage(string $messageUid, ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null)
 */
class Pec extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PecClientManager::class;
    }
}
