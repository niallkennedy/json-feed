<?php

namespace NiallKennedy\WordPress\Plugin\JSONFeed;

/**
 * Load the plugin, register actions and filters
 */
class PluginLoader
{
    /**
     * WordPress feed type powered by this plugin
     *
     * @var string
     */
    const FEED_TYPE = 'json';

    /**
     * Content type of the response
     *
     * @var string
     */
    const CONTENT_TYPE = 'application/json';

    /**
     * Register actions and filter hooks
     *
     * @return void
     */
    public static function init()
    {
        $classname = get_called_class();
        if ( ! defined($classname.'::FEED_TYPE') || ! ( is_string($classname::FEED_TYPE) && $classname::FEED_TYPE ) ) {
            return;
        }

        static::registerFeedSupport();

        // output autodiscovery after feed_links (priority 2), before feed_links_extra (priority 3)
        add_action( 'wp_head', array($classname, 'autoDiscovery'), 2, 0 );

        add_filter( 'feed_content_type', array($classname, 'feedContentType'), 10, 2 );
    }

    /**
     * Register a new feed type and flush rewrite rules on activation
     *
     * @return void
     */
    public static function activationHook()
    {
        static::registerFeedSupport();
        flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules after uninstall
     *
     * @return void
     */
    public static function deactivationHook()
    {
        flush_rewrite_rules();
    }

    /**
     * Register the JSON feed type in feed rewrite rules
     *
     * @return void
     */
    public static function registerFeedSupport()
    {
        add_feed( static::FEED_TYPE, array(get_called_class(), 'doFeed') );
    }

    /**
     * Provide the Content-Type of the response
     *
     * @param string $content_type currently selected Content-Type
     * @param string $type feed type
     *
     * @return string Content-Type
     */
    public static function feedContentType($content_type, $type) {
        if ( static::FEED_TYPE === $type ) {
            return static::CONTENT_TYPE;
        }

        return $content_type;
    }

    /**
     * Output a default feed
     *
     * Comments feed not currently supported
     *
     * @param bool $is_comments_feed is a comments feed expected in the result?
     *
     * @return bool|null return false if something unexpected happened, null if feed not supported
     */
    public static function doFeed($is_comments_feed)
    {
        $is_comments_feed = (true === $is_comments_feed);
        if ( $is_comments_feed ) {
            // do or do not. exit early for now
            return null;
        }
        $feed = Builder::buildFeedObject();
        $feed_array = $feed->toArray();
        if ( ! $feed_array ) {
            return false;
        }

        $json = json_encode($feed_array);
        if ( ! $json ) {
            return false;
        }
        echo $json;
    }

    /**
     * Add a <link> HTML element to <head> for discovery by JSON Feed capable clients
     *
     * @return void
     */
    public static function autoDiscovery()
    {
        // does the site want to participate? or why activate the plugin and not have an autodiscovered link?
        if ( ! apply_filters( 'feed_links_show_posts_feed', true ) ) {
            return;
        }

        $feed_url = esc_url( get_feed_link(static::FEED_TYPE), array('https','http') );
        if ( ! $feed_url ) {
            return;
        }

        // handle unwanted filter responses. reject default feed content type
        $feed_content_type = esc_attr(feed_content_type(static::FEED_TYPE));
        if ( ! $feed_content_type || 'application/octet-stream' === $feed_content_type ) {
            return;
        }

        // escaped above while making sure type-url alternate pair exists
        // @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
        echo '<link rel="alternate" type="' . $feed_content_type . '" href="' . $feed_url . '" />' . PHP_EOL;
    }
}
