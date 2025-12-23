<?php
/**
 * Plugin Name: Invisible Internal Links for WordPress
 * Description: Automatically injects invisible, crawlable internal links between parent and sibling pages for SEO.
 * Version: 1.0.0
 * Author: Open Source
 * License: MIT
 */

add_filter( 'the_content', function ( $content ) {

    // Only run on front-end pages
    if ( ! is_page() || is_admin() ) {
        return $content;
    }

    global $post;

    if ( ! $post ) {
        return $content;
    }

    // Determine parent page
    $parent_id = $post->post_parent ? $post->post_parent : $post->ID;
    $parent    = get_post( $parent_id );

    if ( ! $parent ) {
        return $content;
    }

    // Get sibling pages
    $siblings = get_pages([
        'parent'      => $parent_id,
        'sort_column' => 'menu_order, post_title',
        'post_status' => 'publish',
    ]);

    if ( empty( $siblings ) ) {
        return $content;
    }

    ob_start();
    ?>
    <div class="seo-internal-links">
        <ul>
            <?php if ( $post->ID !== $parent_id ) : ?>
                <li>
                    <a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>">
                        <?php echo esc_html( $parent->post_title ); ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php foreach ( $siblings as $page ) :
                if ( $page->ID === $post->ID ) continue;
            ?>
                <li>
                    <a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>">
                        <?php echo esc_html( $page->post_title ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php

    return $content . ob_get_clean();

});
