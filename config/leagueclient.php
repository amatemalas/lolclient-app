<?php

return [

    /*
    |--------------------------------------------------------------------------
    | League Client lockfile
    |--------------------------------------------------------------------------
    |
    | The running League of Legends client writes a "lockfile" into its
    | installation directory containing the port, auth token and protocol of
    | the local LCU (League Client Update) API. When the client is closed the
    | file is deleted, and the port/token change on every client restart.
    |
    | Paths are probed in order until a readable file is found. Set
    | LEAGUE_LOCKFILE_PATH to point at a custom installation directly.
    |
    */

    'lockfile_path' => env('LEAGUE_LOCKFILE_PATH'),

    'lockfile_paths' => [
        'darwin' => [
            '/Applications/League of Legends.app/Contents/LoL/lockfile',
            env('HOME').'/Library/Application Support/League of Legends/lockfile',
        ],

        'windows' => [
            'C:\\Riot Games\\League of Legends\\lockfile',
            env('LOCALAPPDATA').'\\Riot Games\\Riot Client\\Config\\lockfile',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Request timeouts
    |--------------------------------------------------------------------------
    |
    | The LCU is local, so requests should fail fast when the client is
    | unresponsive rather than hanging the API.
    |
    */

    'timeout' => env('LEAGUE_CLIENT_TIMEOUT', 3),

    'connect_timeout' => env('LEAGUE_CLIENT_CONNECT_TIMEOUT', 2),

];
