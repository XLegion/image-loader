<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\ResourceParser\Html;


use DOMDocument;
use ImageLoader\Helper\Url;
use ImageLoader\ResourceParser\ResourceParserInterface;

abstract class AbstractDomParser implements ResourceParserInterface
{
    /**
     * @var string
     */
    private $startUrl;

    /**
     * AbstractDomParser constructor.
     * @param string $startUrl
     */
    public function __construct($startUrl)
    {
        $this->startUrl = $startUrl;
    }


    /**
     * @param $html
     * @return DOMDocument
     */
    protected function createDom($html)
    {
        // todo cache dom
        $dom = new DOMDocument('1.0');
        @$dom->loadHTML($html);
        return $dom;
    }

    public function isSupport($mimeType)
    {
        return in_array($mimeType, ['text/html']);
    }

    /**
     * Проверка не вышли ли мы за границы исходного сайта
     * @param $resourceUrl
     * @throws \Exception
     */
    protected function checkOutSiteLink($resourceUrl)
    {
        if (!Url::isLocalUrl($resourceUrl, $this->startUrl)) {
            throw new \Exception('Out of site');
        }
    }
}