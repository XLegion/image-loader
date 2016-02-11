<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\ResourceParser;

/**
 * Интерфейс парсера для поиска других ресурсов
 */
interface ResourceParserInterface
{
    public function findResourceLinks($content, $resourceUrl);

    public function isSupport($mimeType);

    /**
     * UrlsQueue::HIGH_PRIORITY - помещаяет найденный список в начало очереди
     * UrlsQueue::LOW_PRIORITY  - в конец
     *
     * @return int
     */
    public function getPriority();

    public function getName();

}