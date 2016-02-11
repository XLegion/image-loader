<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\Crawler;

use RuntimeException;

class UrlsQueue
{
    const HIGH_PRIORITY = 1;
    const LOW_PRIORITY = 2;

    /**
     * @var array
     */
    private $queue = [];

    /**
     * @param array $urls
     * @param int $priority
     * @return int
     */
    public function pushUrls(array $urls, $priority = self::LOW_PRIORITY)
    {
        $len = count($this->queue);
        if ($priority == self::LOW_PRIORITY) {
            $this->queue = array_merge($this->queue, $urls);
        } else {
            $this->queue = array_merge($urls, $this->queue);
        }
        $this->queue = array_unique($this->queue);
        return count($this->queue) - $len;
    }

    public function pushUrl($url)
    {
        array_unshift($this->queue, $url);
    }

    public function popUrl()
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('queue is empty');
        }
        return array_shift($this->queue);
    }

    public function isEmpty()
    {
        return empty($this->queue);
    }

    public function getSize()
    {
        return count($this->queue);
    }

}