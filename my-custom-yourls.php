<?php
/*
Plugin Name: My Custom YOURLS
Plugin URI: https://wordpress.stackexchange.com/q/402515/137402
Description: A sample plugin based on the <a href="https://github.com/aaroneaton/better-yourls/blob/93975caea25d76c47f6e2b4068ba49ff4636970c/includes/class-better-yourls-actions.php">Better YOURLS plugin (v2.3.0)</a>, but this one doesn't have any settings page, so please set the settings in the main plugin file; however, there is a meta box which displays the post's short URL on the post editing screen. <strong>You must install &amp; activate the <a href="https://github.com/timcrockford/yourls-api-edit-url">Update Shortened URL plugin</a> <em>on your YOURLS website</em> in order for updating short URLs' data to work!</strong>
Version: 20220326.1
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
