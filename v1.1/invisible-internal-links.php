<?php
/**
 * Plugin Name: Invisible Internal Links for WordPress
 * Description: Automatically injects invisible, crawlable internal links between parent and sibling pages for SEO.
 * Version: 1.1.0
 * Author: Open Source
 * License: MIT
 *
 * FEATURES (v1.1):
 * - Invisible but crawlable internal links
 * - Parent + sibling page linking
 * - Render-time injection (no editor changes)
 * - Transient caching for performance
 * - Safety limits on sibling links
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

    /**
     * ------------------------------------------------------------
     * Transient Cache (per page)
     * ------------------------------------------------------------
     * Prevents repeated hierarchy queries on each page load.
     */
    $cache_key = 'seo_internal_links_' . $post->ID;
    $cached    = get_transient( $cache_key );

    if ( $cached !== false ) {
        return $content . $cached;
    }

    /**
     * ------------------------------------------------------------
     * Determine Parent Page
     * ------------------------------------------------------------
     */
    $parent_id = $post->post_parent ? $post->post_parent : $post->ID;
    $parent    = get_post( $parent_id );

    if ( ! $parent ) {
        return $content;
    }

    /**
     * ------------------------------------------------------------
     * Retrieve Sibling Pages (with safety limit)
     * ------------------------------------------------------------
     */
    $siblings = get_pages([
        'parent'      => $parent_id,
        'sort_column' => 'menu_order, post_title',
        'post_status' => 'publish',
        'number'      => 50, // HARD SAFETY LIMIT
    ]);

    if ( empty( $siblings ) ) {
        return $content;
    }

    /**
     * ------------------------------------------------------------
     * Build HTML Output
     * ------------------------------------------------------------
     */
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

    $html = ob_get_clean();

    /**
     * ------------------------------------------------------------
     * Cache Output
     * ------------------------------------------------------------
     * Cache duration: 12 hours
     */
    set_transient( $cache_key, $html, 12 * HOUR_IN_SECONDS );

    return $content . $html;

});
