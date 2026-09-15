<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand identity (PLACEHOLDER)
    |--------------------------------------------------------------------------
    | The final app name and logo are not decided yet. Everything the sidebar
    | renders about the brand is read from here, so swapping in the real
    | identity later is a ONE-FILE / ENV change — no view edits needed.
    |
    | How to replace later:
    |   1. App name  -> set BRAND_NAME in .env
    |   2. Logo file -> drop the file in public/ (e.g. public/images/logo.svg)
    |                   then set BRAND_LOGO=images/logo.svg
    |   3. If no logo file is set, the sidebar falls back to the BRAND_MARK
    |      monogram below inside a rounded accent tile.
    */

    'name' => env('BRAND_NAME', 'Sectors Copilot'),

    // Path relative to public/. Leave null to use the monogram fallback.
    'logo' => env('BRAND_LOGO'),

    // Short monogram shown when no logo file is configured.
    'mark' => env('BRAND_MARK', 'SC'),

];
