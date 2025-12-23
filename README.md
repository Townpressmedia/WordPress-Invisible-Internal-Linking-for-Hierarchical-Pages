# Invisible Internal Links

**Author:** CriticalWP.com  
**License:** Open Source  

---

## Latest Version

**v1.1.0**

- Transient caching (per page) for near-zero runtime overhead  
- Safety limit on sibling links (default: 50)  
- Improved inline documentation and maintainability  

---

## Overview

Invisible Internal Links is a lightweight WordPress utility that automatically appends **invisible but crawlable internal links** to page content at render time.

The goal is to strengthen internal linking and hierarchical relationships between **parent pages and sibling subpages** without modifying editor content, templates, or user-facing UI.

Links are hidden using an SEO-safe, accessibility-approved off-screen technique — **not** `display:none` or `visibility:hidden`.

---

## How This Helps SEO

This utility improves SEO by strengthening how pages are connected behind the scenes. By automatically linking parent pages and related subpages together in a crawlable (but invisible) way, it helps search engines better understand page relationships, distribute internal link authority, and crawl content more efficiently. While it doesn’t affect what users see, it improves structural clarity — supporting indexing, topical grouping, and long-term search performance.

---

## Key Features

- No editor modification required
- No shortcodes
- No JavaScript
- No plugin UI or settings
- Crawlable by search engines
- Invisible to users
- Accessibility-safe hiding technique
- Render-time injection via `the_content`
- Works with native WordPress page hierarchy

---

## What’s New in v1.1.0

- **Transient caching (per page)**  
  Reduces runtime overhead by caching generated HTML.

- **Safety limits**  
  Hard cap on sibling links (default: 50) to prevent over-linking.

- **Improved inline documentation**  
  Easier maintenance and collaboration.

---

## How It Works

On each front-end page load:

1. Determines the page’s parent (or uses the page itself if no parent exists)
2. Retrieves sibling pages under the same parent
3. Generates a structured list of internal links:
   - Parent page (when applicable)
   - Sibling pages (excluding the current page)
4. Appends the HTML to page content
5. Caches the output using a WordPress transient

The links are added to the DOM but hidden off-screen via CSS.

---

## Installation

This is **not a plugin**.

### Option 1: functions.php
Paste the PHP file contents directly into your theme’s `functions.php`.

### Option 2: Include File (Recommended)
Create a file such as:

