<?php

namespace JustSolve\LaravelPec\Facades;

use Illuminate\Support\Facades\Facade;
use JustSolve\LaravelPec\Legalmail\LegalmailClient;

/**
 * @method static array<string, mixed> listMessages(array $query = [], ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null, ?array $headers = null)
 * @method static array<string, mixed> getMessage(string $messageUid, ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null, ?array $headers = null)
 * @method static array<string, mixed> createSubmission(array $payload, ?string $mailboxId = null, ?array $headers = null)
 * @method static bool deleteMessage(string $messageUid, ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null, ?array $headers = null)
 * @method static array<string, mixed> updateMessage(string $messageUid, bool $seen, ?string $mailboxId = null, ?string $folderId = null, ?string $messageUidValidity = null)
 */
class Legalmail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LegalmailClient::class;
    }
}
