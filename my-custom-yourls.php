<?php
/*
Plugin Name: My Custom YOURLS
Plugin URI: https://github.com/5ally/my-custom-yourls
Description: A sample plugin based on the <a href="https://github.com/aaroneaton/better-yourls/blob/2.3.0/includes/class-better-yourls-actions.php"><code>Better_YOURLS_Actions</code> class for the "Better YOURLS" plugin v2.3.0</a>. <strong>You must install &amp; activate the <a href="https://github.com/timcrockford/yourls-api-edit-url">Update Shortened URL plugin</a> <em>on your YOURLS website</em> in order for updating short URLs' data to work!</strong>
Version: 20220511.1
*/
namespace My_Custom_YOURLS;


// Set the full URL of your YOURLS website, no trailing slash.
const YOURLS_URL        = 'https://sho.rt';

// Set your YOURLS secret signature token.
const YOURLS_API_TOKEN  = 'xxxxxxxxxx';

// Set the meta key for the short URL meta. (Default to the same meta key used
// by the "Better YOURLS" plugin for WordPress)
const SHORTURL_META_KEY = '_better_yourls_short_link';


// Set the post *types* allowed for automatic short URL creation.
const POST_TYPES    = array( 'lead', 'post', 'page' );

// Set the post *statuses* allowed for automatic short URL creation.
const POST_STATUSES = array( 'publish', 'future' );


// Set to true if short URL can be created/updated when the post is saved via
// Quick Edit.
const AUTO_CREATE_ON_QUICK_EDIT   = true;

// Set to true if short URL can be created/updated when the post is saved via
// the REST API.
const AUTO_CREATE_ON_REST_REQUEST = true;


require_once __DIR__ . '/api.php';
require_once __DIR__ . '/main.php';
