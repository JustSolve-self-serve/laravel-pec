<?php

namespace JustSolve\LaravelPec\Contracts;

interface PecClient
{
    /**
     * Retrieve a collection of resources from the provider.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function listMessages(
        array $query = [],
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array;

    /**
     * Retrieve a single resource by its provider identifier.
     *
     * @return array<string, mixed>
     */
    public function getMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array;

    /**
     * Create a resource in the provider.
     *
     * @param array<string, mixed>|CreateSubmissionPayload $payload
     * @return array<string, mixed>
     */
    public function createSubmission(array|CreateSubmissionPayload $payload, ?string $mailboxId = null): array;

    /**
     * Delete a resource in the provider.
     */
    public function deleteMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): bool;
}
