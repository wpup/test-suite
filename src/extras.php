<?php

// Extras require WordPress to be loaded.
if ( ! function_exists( 'get_bloginfo' ) ) {
	return;
}

$less_than_4_4 = version_compare( get_bloginfo( 'version' ), '4.4', '<' );

if ( ! function_exists( 'is_post_type_viewable' ) && $less_than_4_4 ) {
    /**
     * Determines whether a post type is considered "viewable".
     *
     * @see https://core.trac.wordpress.org/ticket/33888
     * @see https://core.trac.wordpress.org/changeset/33666
     *
     * @param  object $post_type_object
     *
     * @return bool
     */
    function is_post_type_viewable( $post_type_object ) {
        return $post_type_object->publicly_queryable || ( $post_type_object->_builtin && $post_type_object->public );
    }
}
