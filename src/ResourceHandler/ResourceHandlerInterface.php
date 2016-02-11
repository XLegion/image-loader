<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\ResourceHandler;

/**
 * Интерфейс обработки ресурсов
 */
interface ResourceHandlerInterface
{
    public function handleResource($content, $url);

    /**
     * @param $mime
     * @return bool
     */
    public function isSupport($mime);

    /**
     * @return string
     */
    public function getName();
}