<?php

namespace NiallKennedy\WordPress\Plugin\JSONFeed\Objects;

/**
 * Individual feed item
 */
class Item
{
    /**
     * Unique identifier for the feed item
     *
     * @var string
     */
    protected $id;

    /**
     * HTTP(s) URL containing a HTML version of the provided item
     *
     * @var string
     */
    protected $url;

    /**
     * HTTP(s) URL of the main subject of the item. Typically a link to another site
     *
     * @var string
     */
    protected $external_url;

    /**
     * Title or headline
     *
     * @var string
     */
    protected $title;

    /**
     * Short, plain text description of the item
     *
     * @var string
     */
    protected $summary;

    /**
     * Full-content representation of the item in HTML markup
     *
     * @var string
     */
    protected $content_html;

    /**
     * Featured image for the item
     *
     * @var string
     */
    protected $image;

    /**
     * Date time the item was first publicly available
     *
     * @var \DateTimeImmutable
     */
    protected $published;

    /**
     * Date time the item was last modified
     *
     * @var \DateTimeImmutable
     */
    protected $modified;

    /**
     * Author of the represented item
     *
     * @var Author
     */
    protected $author;

    /**
     * Keywords or short text classifiers relevant to the item
     *
     * @var array
     */
    protected $tags = array();

    /**
     * Item constructor.
     *
     * @param string $id unique identifier
     */
    public function __construct($id) {
        if ( ! ( is_string( $id ) && $id ) ) {
            return;
        }

        $this->setID($id);
    }

    /**
     * Get the item's unique identifier
     *
     * @return string unique identifier. empty string if none set
     */
    public function getID()
    {
        return $this->id ?: '';
    }

    /**
     * Set the item's unique identifier
     *
     * @param string $id unique identifier
     *
     * @return self support chainiing
     */
    public function setID($id) {
        if ( is_string($id) && $id ) {
            $this->id = $id;
        }

        return $this;
    }

    /**
     * Set an absolute URL for the item
     *
     * @param string $url absolute URL
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
     * Set an absolute URL for the item
     *
     * @param string $url absolute URL
     *
     * @return self support chaining
     */
    public function setExternalURL($url)
    {
        $url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($url);
        if ( $url ) {
            $this->external_url = $url;
        }

        return $this;
    }

    /**
     * Set the title of the item
     *
     * @param string $title title of the item
     *
     * @return self support chaining
     */
    public function setTitle($title)
    {
        $title = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::plainText($title);
        if ( $title ) {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * Set the item summary
     *
     * @param string $title title of the item
     *
     * @return self support chaining
     */
    public function setSummary($summary)
    {
        $summary = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::plainText($summary);
        if ( $summary ) {
            $this->summary = $summary;
        }

        return $this;
    }

    /**
     * Set the full HTML content of the item
     *
     * @param string $content full content HTML
     *
     * @return self support chaining
     */
    public function setContent($content)
    {
        if ( is_string($content) && $content ) {
            $this->content_html = $content;
        }

        return $this;
    }

    /**
     * Set an absolute URL an image representing the item
     *
     * @param string $image_url absolute URL
     *
     * @return self support chaining
     */
    public function setImage($image_url)
    {
        $image_url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($image_url);
        if ( $image_url ) {
            $this->image = $image_url;
        }

        return $this;
    }

    /**
     * Set the date time the item was first publicly available
     *
     * @param \DateTimeImmutable $published
     *
     * @return self support chaining
     */
    public function setPublishedDate(\DateTimeImmutable $published)
    {
        $this->published = $published->setTimezone( new \DateTimeZone('UTC') );

        return $this;
    }

    /**
     * Set the date time the item was first publicly available
     *
     * @param \DateTimeImmutable $last_modified
     *
     * @return self support chaining
     */
    public function setLastModifiedDate(\DateTimeImmutable $last_modified)
    {
        $this->modified = $last_modified->setTimezone( new \DateTimeZone('UTC') );

        return $this;
    }

    /**
     * Set the author of the item
     *
     * @param Author $author Author object
     *
     * @return self support chaining
     */
    public function setAuthor(Author $author)
    {
        if ( method_exists($author, 'isEmpty') && ! $author->isEmpty() ) {
            $this->author = $author;
        }

        return $this;
    }

    /**
     * Add a freeform text tag
     *
     * @param string $tag keyword or short text classifier
     *
     * @return self support chaining
     */
    public function addTag($tag)
    {
        if ( is_string($tag) && $tag ) {
            $tag = trim($tag);
            if ( $tag && ! array_key_exists(strtolower($tag), $this->tags) ) {
                $this->tags[$tag] = true;
            }
        }

        return $this;
    }

    /**
     * Get stored tags for the current item
     *
     * @return array<string>|null array of tag strings or null if none set
     */
    public function getTags()
    {
        if ( empty($this->tags) ) {
            return null;
        }

        return array_keys($this->tags);
    }

    /**
     * Convert the item into a JSON Feed formatted item
     *
     * @return array|null properties formatted for a JSON feed or null if minimum requirements not met
     */
    public function toArray()
    {
        if ( ! ( isset($this->id) && $this->id ) ) {
            return null;
        }

        $item = array(
            'id' => $this->id,
        );

        if ( isset($this->url) && $this->url ) {
            $item['url'] = $this->url;
        }
        if ( isset($this->external_url) && $this->external_url ) {
            $item['external_url'] = $this->external_url;
        }
        if ( isset($this->title) && $this->title ) {
            $item['title'] = $this->title;
        }
        if ( isset($this->summary) && $this->summary ) {
            $item['summary'] = $this->summary;
        }
        if ( isset($this->content_html) && $this->content_html ) {
            $item['content_html'] = $this->content_html;
        }
        if ( isset($this->image) && $this->image ) {
            $item['image'] = $this->image;
        }

        $tags = $this->getTags();
        if ( is_array($tags) && ! empty($tags) ) {
            $item['tags'] = $tags;
        }
        unset($tags);

        if ( isset($this->published) ) {
            $item['date_published'] = $this->published->format(\DateTime::RFC3339);

            if ( isset($this->modified) ) {
                $item['date_modified'] = $this->modified->format(\DateTime::RFC3339);
            }
        }

        if ( isset($this->author) ) {
            $author = $this->author->toArray();
            if ($author) {
                $item['author'] = $author;
            }
            unset($author);
        }

        return $item;
    }
}
