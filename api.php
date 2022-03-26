<?php
namespace My_Custom_YOURLS;

/*
 * Makes an YOURLS API request.
 *
 * @param array $args Params like 'action' sent to the YOURLS API
 * @return object|null The response body (as an object) on success.
 */
function yourls_api_request( array $args ) {
	$timestamp = time();
	$data      = array_merge( $args, array(
		'timestamp' => $timestamp,
		'signature' => md5( $timestamp . YOURLS_API_TOKEN ),
		'format'    => 'json',
	) );

	$response = wp_remote_post(
		YOURLS_URL . '/yourls-api.php',
		array( 'body' => $data )
	);

	$body = wp_remote_retrieve_body( $response );
	$data = $body ? json_decode( $body ) : null;

	return is_object( $data ) ? $data : null;
}

/*
 * Retrieves the short URL for a post.
 *
 * @param int $post_id The post ID
 * @return string|false The short URL, if any and it's valid.
 */
function get_post_shorturl( $post_id ) {
	$shorturl = get_post_meta( $post_id, SHORTURL_META_KEY, true );

	if ( $shorturl && false !== strpos( $shorturl, YOURLS_URL ) ) {
		return $shorturl;
	}

	return false;
}

/*
 * Creates short URL for a post.
 *
 * Note: Does not send a custom keyword/slug for the short URL.
 *
 * @param int $post_id The post ID
 * @param bool $update_if_exists If the short URL meta is empty/missing, but there's
 *                               a short URL for the post permalink (i.e. the long
 *                               URL) in the YOURLS database, setting this parameter
 *                               to true will update the data for the existing short
 *                               URL. Default false
 * @return string|false The short URL on success.
 */
function create_post_shorturl( $post_id, $update_if_exists = false ) {
	if ( ! is_numeric( $post_id ) || $post_id < 1 || ! get_post( $post_id ) ) {
		return false;
	}

	// Return existing short URL, if any.
	if ( $shorturl = get_post_shorturl( $post_id ) ) {
		return $shorturl;
	}

	$data = yourls_api_request( array(
		'action'  => 'shorturl',
		'url'     => get_permalink( $post_id ),
		'title'   => get_the_title( $post_id ),
		'keyword' => '', // use the default keyword generated by YOURLS
	) );

	$exists = ( $data && 'error:url' === $data->code );
	if ( $exists || ( $data && 'success' === $data->status ) ) {
		// Save the short URL in a post meta.
		update_post_meta( $post_id, SHORTURL_META_KEY, $data->shorturl );

		if ( $exists && $update_if_exists ) {
			update_post_shorturl( $post_id );
		}

		return $data->shorturl;
	}

	return false;
}

/*
 * Updates the data for a short URL for a post.
 *
 * Note: Does not update the keyword/slug for the short URL. And for the 'update'
 * action to work, you must install & activate {@link https://github.com/timcrockford/yourls-api-edit-url Update Shortened URL}
 * on your YOURLS website - not your WordPress website!
 *
 * @param int $post_id The post ID
 * @return string|false|null The short URL on success. NULL is returned if the short
 *                           URL is invalid, or does not exist in the YOURLS database.
 */
function update_post_shorturl( $post_id ) {
	if ( ! is_numeric( $post_id ) || $post_id < 1 || ! get_post( $post_id ) ) {
		return false;
	}

	// Check existing short URL, if any.
	if ( ! $shorturl = get_post_shorturl( $post_id ) ) {
		return null;
	}

	$data = yourls_api_request( array(
		'action'   => 'update',
		'shorturl' => $shorturl,
		'url'      => get_permalink( $post_id ),
		'title'    => get_the_title( $post_id ),
	) );

	if ( $data && 200 === $data->statusCode ) {
		return $shorturl;
	}

	if ( $data && 404 === $data->statusCode ) {
		delete_post_meta( $post_id, SHORTURL_META_KEY );

		return null;
	}

	return false;
}
