<?php

namespace JustSolve\LegalmailPec\Services;

class LegalmailProviderClient extends AbstractHttpPecClient
{
    public function updateMessage(
        string $messageUid,
        bool $seen,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array {
        return $this->request(
            'PUT',
            $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity),
            ['query' => ['seen' => $seen]]
        );
    }
}
