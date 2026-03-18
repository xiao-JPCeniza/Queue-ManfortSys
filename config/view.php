<?php

$compiledViewPath = storage_path('framework/views');

if (PHP_OS_FAMILY === 'Windows') {
    $localAppData = getenv('LOCALAPPDATA') ?: ($_SERVER['LOCALAPPDATA'] ?? $_ENV['LOCALAPPDATA'] ?? null);

    if (is_string($localAppData) && $localAppData !== '') {
        $compiledViewPath = $localAppData.DIRECTORY_SEPARATOR.'Queue-ManfortSys'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views';
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | OneDrive can temporarily lock files inside the project directory on
    | Windows, which breaks Blade's atomic rename when refreshing compiled
    | templates. Keep the default in-app storage everywhere else, but move
    | the cache to Local AppData on Windows where it is not synced.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH', $compiledViewPath),

];
