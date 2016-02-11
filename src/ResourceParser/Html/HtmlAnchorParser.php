<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\ResourceParser\Html;

use ImageLoader\Crawler\UrlsQueue;
use ImageLoader\Helper\Url;

/**
 * Ищет теги <a> в html
 */
class HtmlAnchorParser extends AbstractDomParser
{
    public function findResourceLinks($content, $resourceUrl)
    {
        $this->checkOutSiteLink($resourceUrl);
        $result = [];

        $dom = $this->createDom($content);
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $element) {
            /** @var \DOMElement $element */
            $href = $element->getAttribute('href');

            if (Url::isLocalUrl($href, $resourceUrl)) {
                $url = Url::createFullUrl($href, $resourceUrl);
                $result[] = $url;
            }
        }
        return $result;
    }

    /**
     * UrlsQueue::HIGH_PRIORITY - помещаяет найденный список в начало очереди
     * UrlsQueue::LOW_PRIORITY  - в конец
     *
     * @return int
     */
    public function getPriority()
    {
        return UrlsQueue::LOW_PRIORITY;
    }

    public function getName()
    {
        return 'HtmlAnchorParser';
    }

}