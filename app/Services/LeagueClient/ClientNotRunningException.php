<?php

namespace App\Services\LeagueClient;

use RuntimeException;

class ClientNotRunningException extends RuntimeException
{
    public static function noLockfile(): self
    {
        return new self('No League of Legends client lockfile was found. Is the client running?');
    }

    public static function unreadable(string $path, string $reason): self
    {
        return new self("Unable to parse the League client lockfile at {$path}: {$reason}");
    }
}
