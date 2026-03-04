<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Instagram App Credentials
    |--------------------------------------------------------------------------
    |
    | Your Instagram App ID and App Secret from the Meta App Dashboard:
    | App Dashboard > Instagram > API setup with Instagram login >
    | Business login settings
    |
    */
    'client_id'     => env('INSTAGRAM_APP_ID'),
    'client_secret' => env('INSTAGRAM_APP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Redirect URI
    |--------------------------------------------------------------------------
    |
    | Must exactly match one of the OAuth redirect URIs registered in the
    | Meta App Dashboard.
    |
    */
    'redirect_uri'  => env('INSTAGRAM_REDIRECT_URI'),

    /*
    |--------------------------------------------------------------------------
    | Default Scopes
    |--------------------------------------------------------------------------
    |
    | Scopes requested by default when building the authorization URL.
    | Available: instagram_business_basic, instagram_business_content_publish,
    |            instagram_business_manage_messages, instagram_business_manage_comments
    |
    */
    'scopes' => explode(',', env('INSTAGRAM_SCOPES', 'instagram_business_basic')),
];
