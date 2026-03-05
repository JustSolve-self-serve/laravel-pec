<?php

namespace JustSolve\LaravelPec\Services;

class OpenApiPecMassivaProviderClient extends AbstractHttpPecClient
{
    protected function messagesBasePath(
        ?string $mailboxId,
        ?string $folderId,
        ?string $messageUidValidity
    ): string {
        return '/inbox';
    }

    protected function submissionPath(?string $mailboxId): string
    {
        return '/send';
    }
}
