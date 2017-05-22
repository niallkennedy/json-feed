<?php

namespace NiallKennedy\WordPress\Plugin\JSONFeed;

/**
 * Build JSON Feed components
 */
class Builder
{
    /**
     * Build a Feed object from site values
     *
     * @return \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Feed Feed object
     */
    public static function buildFeedObject()
    {
        $feed = new Objects\Feed(get_wp_title_rss());
        $feed->setComment('JSON Feed experiment');

        $description = get_bloginfo_rss('description');
        if ($description) {
            $feed->setDescription($description);
        }
        unset($description);

        $url = get_bloginfo_rss('url');
        if($url) {
            $feed->setHTMLURL($url);
        }
        unset($url);

        $feed_link = get_feed_link(PluginLoader::FEED_TYPE);
        if ($feed_link) {
            $feed->setURL($feed_link);
        }
        unset($feed_link);

        $small_icon_url = get_site_icon_url(64);
        if ($small_icon_url) {
            $feed->setSmallIconURL($small_icon_url);

            $icon_url = get_site_icon_url('full');
            if ( $icon_url ) {
                $feed->setIconURL($icon_url);
            }
            unset($icon_url);
        }
        unset($small_icon_url);

        while(have_posts()) {
            $post = static::buildPostObject();
            if ($post) {
                $feed->addItem($post);
            }
            unset($post);
        }

        return $feed;
    }

    /**
     * Build a post object
     *
     * @return \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Item|null populated Item object or null if minimum requirements not met
     */
    public static function buildPostObject()
    {
        the_post();

        $post = get_post();
        if ( ! $post ) {
            return null;
        }
        $post_type = get_post_type( $post );
        if ( ! $post_type ) {
            return null;
        }
        // purposely omit check for post type public status to allow feeds for internal, non-public use

        $guid = get_the_guid($post);
        if ( ! $guid ) {
            // should not happen, could build an URN here instead
            return null;
        }
        $item = new \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Item($guid);

        if ( post_type_supports( $post_type, 'title' ) ) {
            $title = get_the_title_rss();
            if ($title) {
                $item->setTitle($title);
            }
            unset($title);
        }

        $url = apply_filters( 'the_permalink_rss', get_permalink() );
        if ($url) {
            $item->setURL($url);
        }
        unset($url);

        $published_str = get_post_time('U', /* gmt */ true);
        if ( $published_str ) {
            $published_date = \DateTimeImmutable::createFromFormat('U', $published_str, new \DateTimeZone('GMT'));
            if ( $published_date ) {
                $item->setPublishedDate($published_date);

                // set last modified only if published is also known
                $modified_str = get_post_modified_time('U', /* gmt */ true);
                if ($modified_str) {
                    $modified_date = \DateTimeImmutable::createFromFormat('U', $modified_str, new \DateTimeZone('GMT'));
                    if ( $modified_date ) {
                        $item->setLastModifiedDate($modified_date);
                    }
                    unset($modified_date);
                }
                unset($modified_str);
            }
            unset($published_date);
        }
        unset($published_str);

        if ( post_type_supports( $post_type, 'excerpt') ) {
            $item->setSummary(apply_filters('the_excerpt_rss', get_the_excerpt()));
        }

        // allow full content in feeds?
        if ( ! get_option('rss_use_excerpt') ) {
            $item->setContent( get_the_content_feed('json') );
        }

        if ( post_type_supports( $post_type, 'thumbnail' ) && function_exists( 'has_post_thumbnail' ) && has_post_thumbnail($post) ) {
            $image_url = get_the_post_thumbnail_url( $post, 'full' );
            if ( $image_url ) {
                $item->setImage($image_url);
            }
            unset($image_url);
        }

        if ( post_type_supports($post_type, 'author') ) {
            $author = static::buildAuthorObject($post);
            if ($author) {
                $item->setAuthor($author);
            }
            unset($author);
        }

        $tags = get_the_tags($post);
        if ( ! empty($tags) ) {
            foreach($tags as $tag) {
                if ( isset( $tag->name ) ) {
                    $item->addTag($tag->name);
                }
            }
        }
        unset($tags);

        /**
         * Add data for current item
         *
         * @param \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Item Item object
         * @param \WP_Post $post WordPress post
         */
        return apply_filters( PluginLoader::FEED_TYPE . '_item', $item, $post );
    }

    /**
     * Build an Author object with information about the post author
     *
     * @param \WP_Post $post single post
     *
     * @return \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Author|null Author object or null if no author data found
     */
    public static function buildAuthorObject(\WP_Post $post)
    {
        $author = new \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Author();

        $display_name = get_the_author();
        if ( $display_name ) {
            $author->setName($display_name);
        }
        unset($display_name);

        $url = get_the_author_meta('url');
        if ($url) {
            $author->setURL($url);
        }
        unset($url);

        /**
         * Add data for the current author
         *
         * @param \NiallKennedy\WordPress\Plugin\JSONFeed\Objects\Author $author post author object
         * @param \WP_Post $post WordPress post
         */
        $author = apply_filters( PluginLoader::FEED_TYPE . '_author', $author, $post );

        if ( method_exists( $author, 'isEmpty' ) && ! $author->isEmpty() ) {
            return $author;
        }

        return null;
    }
}