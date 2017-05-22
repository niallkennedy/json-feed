<?php

namespace NiallKennedy\WordPress\Plugin\JSONFeed\Objects;

/**
 * Main JSON Feed object
 */
class Feed
{
    /**
     * URL mapping the format provider and the version of the format
     *
     * @var string
     */
    const VERSION = 'https://jsonfeed.org/version/1';

    /**
     * Name of the feed and its contents
     *
     * @var string
     */
    protected $title;

    /**
     * Freeform text comment for humans reading the feed
     *
     * @var string
     */
    protected $comment;

    /**
     * A short description of the feed and its contents
     *
     * @var string
     */
    protected $description;

    /**
     * Absolute URL of the presented feed
     *
     * @var string
     */
    protected $url;

    /**
     * Absolute URL for the next items in a paginated result set
     *
     * @var string
     */
    protected $next_page_url;

    /**
     * Absolute URL of an alternate version of the content represented by the feed available in a HTML format
     *
     * @var string
     */
    protected $html_url;

    /**
     * Image representing the feed
     *
     * Assume JPEG, PNG, or GIF supported
     *
     * @var string
     */
    protected $icon_url;

    /**
     * Small image representing the feed
     *
     * Should have a 1:1 aspect ratio and small dimensions (e.g. minimum edge of 64px)
     *
     * @var string
     */
    protected $small_icon_url;

    /**
     * Author of the feed
     *
     * May represent the publishing organization or a person for single-author sites
     * A feed item without a specified author will inherit this value
     *
     * @var Author
     */
    protected $author;

    /**
     * Items for syndication
     *
     * @var array<Item>
     */
    protected $items = array();

    /**
     * Create a new Feed object with the required title field
     *
     * @param string $title feed title
     */
    public function __construct($title)
    {
        $this->setTitle($title);
    }

    /**
     * Set the title of the feed
     *
     * @param string $title title of the feed
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
     * Comment for humans reading the feed
     *
     * @param string $comment
     *
     * @return self support chaining
     */
    public function setComment($comment)
    {
        $comment = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::plainText($comment);
        if ( $comment ) {
            $this->comment = $comment;
        }

        return $this;
    }

    /**
     * Set a short description of the feed, capped at 200 characters
     *
     * @param string $description short description of the feed
     *
     * @return self support chaining
     */
    public function setDescription($description)
    {
        if ( ! ( is_string($description) && $description ) ) {
            return $this;
        }

        $description = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::plainText($description);
        if ( ! $description ) {
            return $this;
        }

        if ( strlen($description) > 200 ) {
            $description = substr($description, 0, 200) . '&hellip;';
            if ( ! $description ) {
                return $this;
            }
        }

        $this->description = $description;

        return $this;
    }

    /**
     * Set an absolute URL for the HTML alternate of the current feed
     *
     * @param string $url absolute URL
     *
     * @return self support chaining
     */
    public function setHTMLURL($url)
    {
        $url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($url);
        if ( $url ) {
            $this->html_url = $url;
        }

        return $this;
    }

    /**
     * Set an absolute URL for the current feed
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
     * Set an absolute URL for a feed representing the next page in a paginated result set
     *
     * @param string $url absolute URL
     *
     * @return self support chaining
     */
    public function setNextPageURL($url)
    {
        $url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($url);
        if ( $url ) {
            $this->next_page_url = $url;
        }

        return $this;
    }

    /**
     * Set an absolute URL for a feed icon
     *
     * @param string $url absolute URL
     *
     * @return self support chaining
     */
    public function setIconURL($url)
    {
        $url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($url);
        if ( $url ) {
            $this->icon_url = $url;
        }

        return $this;
    }

    /**
     * Set an absolute URL for a small version of a feed icon
     *
     * @param string $url absolute URL
     *
     * @return self support chaining
     */
    public function setSmallIconURL($url)
    {
        $url = \NiallKennedy\WordPress\Plugin\JSONFeed\Helpers\Sanitize\Strings::url($url);
        if ( $url ) {
            $this->small_icon_url = $url;
        }

        return $this;
    }

    /**
     * Set the author of the feed
     *
     * May be an organization or person
     * Feed authors may be considered authors of any items without an explicitly declared author
     *
     * @param Author $author Author object
     *
     * @return self support chaining
     */
    public function setAuthor(Author $author)
    {
        if ( method_exists($author, 'isEmpty') && ! $author->isEmpty() ) {
            $this->setAuthor($author);
        }

        return $this;
    }

    /**
     * Add an item to the feed
     *
     * @param Item $item single item
     *
     * @return self support chaining
     */
    public function addItem(Item $item)
    {
        $item_id = $item->getID();
        if ( $item_id ) {
            $this->items[$item_id] = $item;
        }

        return $this;
    }

    /**
     * Convert the object to a JSON Feed array suitable for JSON encoding
     *
     * @return array|null JSON Feed array
     */
    public function toArray()
    {
        // title is the only required property
        if ( ! (isset($this->title) && $this->title) ) {
            return null;
        }

        $feed = array(
            'version' => static::VERSION,
            'title' => $this->title,
        );

        // be overly explicit for now
        if ( isset($this->html_url) && $this->html_url ) {
            $feed['home_page_url'] = $this->html_url;
        }
        if ( isset($this->url) && $this->url ) {
            $feed['feed_url'] = $this->url;
        }
        if ( isset($this->next_page_url) && $this->next_page_url ) {
            $feed['next_url'] = $this->next_page_url;
        }

        if ( isset($this->description) && $this->description ) {
            $feed['description'] = $this->description;
        }

        if ( isset($this->icon_url) && $this->icon_url ) {
            $feed['icon'] = $this->icon_url;
        }
        if ( isset($this->small_icon_url) && $this->small_icon_url ) {
            $feed['favicon'] = $this->small_icon_url;
        }

        if ( isset($this->author) ) {
            $author_array = $this->author->toArray();
            if ( $author_array ) {
                $feed['author'] = $author_array;
            }
            unset($author_array);
        }

        if ( ! empty($this->items) ) {
            $items = array_values($this->items);
            foreach($items as $item) {
                $item_array = $item->toArray();
                if ( $item_array ) {
                    $feed['items'][] = $item_array;
                }
                unset($item_array);
            }
        }

        return $feed;
    }
}
