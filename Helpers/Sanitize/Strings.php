<?php

namespace NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize;

/**
 * Clean up and prepare provided inputs
 */
class Strings
{
    /**
     * Remove HTML, leading and trailing whitespaces, and other unexpected qualities of a plain text string
     *
     * @since 1.0.0
     *
     * @param string $s string to sanitize
     *
     * @return string sanitized string
     */
    public static function plainText( $s )
    {
        if ( ! ( is_string( $s ) && $s ) ) {
            return '';
        }

        $s = trim( $s );
        if ( $s ) {
            // strip HTML
            $s = trim( wp_kses( $s, array() ) );
        }

        return $s;
    }

    /**
     * Test a provided URL is a string and passes WordPress raw URL scrubbing
     *
     * @since 1.0.0
     *
     * @param string $url absolute URL
     *
     * @return string|null absolute URL with a https or http scheme or null if minimum requirements not met
     */
    public static function url( $url )
    {
        if ( ! ( is_string( $url ) && $url ) ) {
            return null;
        }

        $url = esc_url_raw( $url, array('https', 'http') );
        if ( ! $url ) {
            return null;
        }

        return $url;
    }
}
