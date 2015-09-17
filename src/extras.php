<?php

if ( ! function_exists( 'is_post_type_viewable' ) ) {
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
        return $post_type_object->publicly_queryable || ( $post_type_object->_builtin && $post_type_object->public )
    }
}
