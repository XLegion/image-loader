<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\Crawler;


class History
{
    /**
     * @var array
     */
    private $visitedUrls = [];

    public function markUrlVisit($url)
    {
        $this->visitedUrls[$url] = true;
    }

    public function isNewUrl($url)
    {
        return !array_key_exists($url, $this->visitedUrls);
    }

    public function getSize()
    {
        return count($this->visitedUrls);
    }

}