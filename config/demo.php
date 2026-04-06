<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Show Placeholder Images
    |--------------------------------------------------------------------------
    |
    | When enabled, properties without uploaded media will display the local
    | placeholder images shipped in `public/images/properties/`. This is
    | intended for the public demo deployment of this template — real
    | production deployments should leave this OFF and rely on properties
    | having actual photos uploaded via the admin panel.
    |
    | The local and testing environments always show placeholders regardless
    | of this flag, so dev experience is preserved.
    |
    */

    'show_placeholder_images' => env('SHOW_PLACEHOLDER_IMAGES', false),

];
