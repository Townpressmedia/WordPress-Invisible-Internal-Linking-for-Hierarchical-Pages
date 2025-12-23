<?php
/**
 * Invisible Internal Links (v1.1.0)
 *
 * Author: CriticalWP.com
 * License: Open Source
 *
 * PURPOSE
 * -------
 * Appends invisible but crawlable internal links to page content
 * at render time, reinforcing hierarchical relationships between:
 *  - Parent pages
 *  - Sibling subpages
 *
 * This is designed for SEO-first architectures such as:
 *  
 *  - Hierarchical content hubs
 *
 * KEY CHARACTERISTICS
 * -------------------
 * ✔ No editor modification
 * ✔ No shortcodes
 * ✔ No JavaScript
 * ✔ No display:none or visibility:hidden
 * ✔ Crawlable and accessibility-safe
 * ✔ Runs only on front-end pages
 *
 * WHAT v1.1 ADDS
 * --------------
 * ✔ Transient caching (per page)
 * ✔ Hard limit on sibling links (safety guardrail)
 */

/**
 * Append invisible internal links to page content
 */
add_filter( 'the_content', function ( $content ) {

    /* ---------------------------------------------------------
     * Scope & Safety Checks
     * --------------------------------------------------------- */

    // Run only on front-end page views
    if ( ! is_page() || is_admin() ) {
        return $content;
    }

    global $post;

    // Bail if post context is unavailable
    if ( ! $post ) {
        return $content;
    }

    /* ---------------------------------------------------------
     * Transient Cache (Performance)
     * --------------------------------------------------------- */

    // Unique cache key per page
    $cache_key = 'criticalwp_internal_links_' . $post->ID;

    // Return cached HTML if available
    $cached = get_transient( $cache_key );
    if ( $cached !== false ) {
        return $content . $cached;
    }

    /* ---------------------------------------------------------
     * Determine Page Hierarchy
     * --------------------------------------------------------- */

    // Use parent if available, otherwise current page is the parent
    $parent_id = $post->post_parent ? $post->post_parent : $post->ID;
    $parent    = get_post( $parent_id );

    // Bail if parent cannot be resolved
    if ( ! $parent ) {
        return $content;
    }

    /* ---------------------------------------------------------
     * Retrieve Sibling Pages (with safety limit)
     * --------------------------------------------------------- */

    $siblings = get_pages([
        'parent'      => $parent_id,
        'post_status' => 'publish',
        'sort_column' => 'menu_order, post_title',
        'number'      => 50, // HARD LIMIT (v1.1 safety guard)
    ]);

    // No siblings = nothing to output
    if ( empty( $siblings ) ) {
        return $content;
    }

    /* ---------------------------------------------------------
     * Build HTML Output
     * --------------------------------------------------------- */

    ob_start();
    ?>
    <!--
        Invisible Internal Links
        - Intentionally hidden via CSS (off-screen)
        - Crawlable by search engines
        - Not visible to users
    -->
    <div class="seo-internal-links">
        <ul>

            <?php
            // Parent link (only when on a child page)
            if ( $post->ID !== $parent_id ) :
            ?>
                <li>
                    <a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>">
                        <?php echo esc_html( $parent->post_title ); ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php
            // Sibling links (exclude current page)
            foreach ( $siblings as $page ) :
                if ( $page->ID === $post->ID ) {
                    continue;
                }
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

    /* ---------------------------------------------------------
     * Cache Output (v1.1)
     * --------------------------------------------------------- */

    // Cache for 12 hours to minimize repeated hierarchy queries
    set_transient( $cache_key, $html, 12 * HOUR_IN_SECONDS );

    return $content . $html;

});
