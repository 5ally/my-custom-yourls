<?php
namespace My_Custom_YOURLS;

// If you change these, make sure you know what you're doing.. :)
const NONCE_NAME   = 'myc_yourls_nonce';
const NONCE_ACTION = 'myc-yourls-save-post';

add_action( 'add_meta_boxes', 'My_Custom_YOURLS\add_meta_boxes' );
function add_meta_boxes( $post_type ) {
	if ( in_array( $post_type, POST_TYPES ) ) {
		add_meta_box( 'myc-yourls-shorturl-mb', 'YOURLS Short URL',
			'My_Custom_YOURLS\shorturl_meta_box', $post_type );
	}
}

function shorturl_meta_box( $post ) {
	if ( $shorturl = get_post_shorturl( $post->ID ) ) {
		printf( '<a href="%s" target="_blank">%s</a>',
			esc_url( $shorturl ), esc_html( $shorturl ) );
	} else {
		echo '<i>No short URL, yet.</i>';
	}

	wp_nonce_field( NONCE_ACTION, NONCE_NAME, false );
}

/*
 * Determines whether a post can be modified when it's being created/updated via
 * the classic or block/Gutenberg editor.
 *
 * @param int $post_id The post ID
 * @return bool Whether the post can be modified.
 */
function can_modify_post_on_save( $post_id ) {
	// Check if the current admin page is edit.php
	global $pagenow;
	if ( is_admin() && 'edit.php' === $pagenow ) {
		return false;
	}

	// Check if the post is being saved by Quick Edit (via edit.php)
	if ( AUTO_CREATE_ON_QUICK_EDIT && wp_doing_ajax() ) {
		if ( ! isset( $_REQUEST['action'], $_REQUEST['_inline_edit'] ) ||
			'inline-save' !== $_REQUEST['action']
		) {
			return false;
		}
	}

	// Check if WordPress is auto-saving the post, or is doing cron.
	$doing_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE );
	if ( $doing_autosave || wp_doing_cron() ) {
		return false;
	}

	// Check the post type.
	if ( ! in_array( get_post_type( $post_id ), POST_TYPES ) ) {
		return false;
	}

	// Check the post status.
	if ( ! in_array( get_post_status( $post_id ), POST_STATUSES ) ) {
		return false;
	}

	// Check user's capability.
	$post_type = get_post_type_object( get_post_type( $post_id ) );
	if ( ! current_user_can( $post_type->cap->edit_posts, $post_id ) ) {
		return false;
	}

	// Check the nonce (submitted via the classic editor or custom forms).
	$doing_rest = ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	if ( ! $doing_rest && ! wp_doing_ajax() && ( ! isset( $_POST[ NONCE_NAME ] ) ||
		! wp_verify_nonce( $_POST[ NONCE_NAME ], NONCE_ACTION ) )
	) {
		return false;
	}

	// Check if the post is being saved by the REST API via the /wp/v2/posts/<id>
	// route, or similar route for the current post type.
	if ( $doing_rest && AUTO_CREATE_ON_REST_REQUEST ) {
		$route = rest_get_route_for_post( $post_id );
		$path = $GLOBALS['wp']->query_vars['rest_route'];

		if ( $route !== $path ) {
			return false;
		}
	}

	return true;
}

add_action( 'wp_after_insert_post',  'My_Custom_YOURLS\action_save_shorturl_after_insert_post', 11, 4 );
function action_save_shorturl_after_insert_post( $post_id, $post, $update, $post_before ) {
	if ( ! can_modify_post_on_save( $post_id ) ) {
		return;
	}

	$create   = true;
	$new_slug = get_post_field( 'post_name', $post_id ); // use the updated post slug

	// If the post SLUG is changed, then we update the data for the existing short URL.
	if ( $update && $post_before && $post_before->post_name !== $new_slug ) {
		$shorturl = update_post_shorturl( $post_id );
		$create = ( null === $shorturl );
	}

	// If the update failed, then let's try to create the short URL.
	if ( $create ) {
		$shorturl = create_post_shorturl( $post_id );
	}
}

add_filter( 'pre_get_shortlink', 'My_Custom_YOURLS\filter_pre_get_shortlink', 10, 3 );
function filter_pre_get_shortlink( $shortlink, $id, $context ) {
	if ( 'query' === $context && is_singular() ) {
		$post = get_post( get_queried_object_id() );
	} elseif ( 'post' === $context ) {
		$post = get_post( $id );
	}

	if ( ! empty( $post ) ) {
		return get_post_shorturl( $post->ID );
	}

	return $shortlink;
}
