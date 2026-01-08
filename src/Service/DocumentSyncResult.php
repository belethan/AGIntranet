<?php

namespace App\Service;

final class DocumentSyncResult
{
    private int $total = 0;
    private int $created = 0;
    private int $updated = 0;
    private int $ignored = 0;

    public function incrementTotal(): void   { ++$this->total; }
    public function incrementCreated(): void { ++$this->created; }
    public function incrementUpdated(): void { ++$this->updated; }
    public function incrementIgnored(): void { ++$this->ignored; }

    public function getTotal(): int   { return $this->total; }
    public function getCreated(): int { return $this->created; }
    public function getUpdated(): int { return $this->updated; }
    public function getIgnored(): int { return $this->ignored; }
}
