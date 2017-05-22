<?php

namespace NiallKennedy\WordPress\Plugin\JSONFeed\Objects;

/**
 * Information about a WordPress author
 */
class Author
{
    /**
     * Full name of the author
     *
     * @var string
     */
    protected $name;

    /**
     * Author URL
     *
     * @var string
     */
    protected $url;

    /**
     * Image representing the author
     *
     * Should be square and relatively large (e.g. 512x512) although no size restriction is enforced
     *
     * @var string
     */
    protected $image_url;

    /**
     * Does the object contain at least one populated property?
     *
     * @return bool true if no properties set
     */
    public function isEmpty()
    {
        if ( (isset($this->name) && $this->name) || (isset($this->url) && $this->url) || (isset($this->image_url) && $this->image_url) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Set the author's full name
     *
     * @param string $name Author's full name
     *
     * @return self support chaining
     */
    public function setName($name)
    {
        $name = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::plainText($name);
        if ( $name ) {
            $this->name = $name;
        }
        return $this;
    }

    /**
     * Set the author URL
     *
     * @param string $url Author URL
     *
     * @return self support chaining
     */
    public function setURL($url)
    {
        $url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($url);
        if ( $url ) {
            $this->url = $url;
        }
        return $this;
    }

    /**
     * Set an image URL representing the author
     *
     * The JSON Feed specification does not specify an image type. Provide JPEG, PNG, or GIF.
     *
     * @param string $image_url absolute URL of an image
     *
     * @return self support chaining
     */
    public function setImageURL($image_url)
    {
        $image_url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($image_url);
        if ( $image_url ) {
            $this->image_url = $image_url;
        }
        return $this;
    }

    /**
     * Convert the object into a JSON Feed author
     *
     * @return array|null JSON Feed author
     */
    public function toArray()
    {
        $properties = array();

        if ( isset($this->name) && $this->name ) {
            $properties['name'] = $this->name;
        }
        if ( isset($this->url) && $this->url ) {
            $properties['url'] = $this->url;
        }
        if ( isset($this->image_url) && $this->image_url ) {
            $properties['avatar'] = $this->image_url;
        }

        // at least one property is required
        if ( empty( $properties ) ) {
            return null;
        }

        return $properties;
    }
}
