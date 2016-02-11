<?php
/**
 * @author Alexey Solodkiy <work@x1.by>
 */

namespace ImageLoader\Crawler;

use GuzzleHttp\Client;
use ImageLoader\Helper\Url;
use ImageLoader\ResourceHandler\ResourceHandlerInterface;
use ImageLoader\ResourceParser\ResourceParserInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class SiteCrawler
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @var UrlsQueue
     */
    private $queue;

    /**
     * @var History
     */
    private $history;

    /**
     * @var ResourceHandlerInterface[]
     */
    private $resourceHandlers = [];

    /**
     * @var ResourceParserInterface[]
     */
    private $parsers = [];

    /**
     * Останавливатся после N ресурсов
     * @var int|null
     */
    private $limit;

    /**
     * @var string
     */
    private $startUrl;

    public function __construct(Client $client, LoggerInterface $logger = null)
    {
        $this->client = $client;

        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;

        $this->queue = new UrlsQueue();
        $this->history = new History();
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function addResourceHandler(ResourceHandlerInterface $handler)
    {
        $this->resourceHandlers[] = $handler;
    }

    public function addParser(ResourceParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    public function run($startUrl)
    {
        $this->startUrl = $startUrl;
        $this->logger->info('Start from '.$startUrl.' url');
        if (!is_null($this->limit)) {
            $this->logger->info('Limit: ' . $this->limit);
        }

        $this->queue->pushUrl($startUrl);

        while (!$this->queue->isEmpty()) {

            $this->logger->debug('queue size: '.$this->queue->getSize());
            $url = $this->queue->popUrl();
            $this->visitUrl($url);

            if ($this->limit && $this->history->getSize() >= $this->limit) {
                $this->logger->info('stop by limit');
                break;
            }
        }

        $this->logger->info('Done');
    }

    private function visitUrl($url)
    {
        $this->logger->info('visit '.$url);
        $this->history->markUrlVisit($url);

        try {

            list($content, $realUrl, $mime) = $this->getResource($url);

            if ($url !== $realUrl) {
                $this->logger->debug('real url: ' . $realUrl);
                if (!Url::isLocalUrl($realUrl, $this->startUrl)) {
                    $this->logger->warning('out of site redirect');
                    return;
                }
            }


            $this->parseResource($content, $realUrl, $mime);


        } catch (\RuntimeException $e) {
            $this->logger->warning($e->getMessage());
        }
    }

    private function getResource($url)
    {
        // Может вернустся другой url посредством редиректа
        // Сохраним его в realUrl
        $realUrl = $url;

        $params = [
            'allow_redirects' => [
                'max'             => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'on_redirect'     => function (RequestInterface $request,
                                               ResponseInterface $response,
                                               UriInterface $uri) use (&$realUrl) {
                    $realUrl = (string)$uri;
                },
                'track_redirects' => true
            ],
            'verify' => false
        ];

        $response = $this->client->get($url, $params);
        $content = $response->getBody();

        return [$content, $realUrl, $this->getMimeByResponse($response)];
    }

    private function getMimeByResponse(ResponseInterface $response)
    {
        $mime = $response->getHeaderLine('Content-Type');
        if (strpos($mime, ';')) {
            $mime = substr($mime, 0, strpos($mime, ';'));
        }

        return $mime;
    }


    /**
     * @param $content
     * @param $pageUrl
     * @param $mime
     * @return array
     */
    private function parseResource($content, $pageUrl, $mime)
    {
        $this->logger->debug('get resource (size: ' . strlen($content) . ', mime: '.$mime.')');

        $isProcessed = false;

        // Find out links
        foreach ($this->parsers as $parser) {
            try {
                if ($parser->isSupport($mime)) {
                    $isProcessed = true;
                    $this->logger->debug('parse by '.$parser->getName());
                    $links = $parser->findResourceLinks($content, $pageUrl);
                    $newLinks = $this->filterUnVisitedLinks($links);

                    $added = $this->queue->pushUrls($newLinks, $parser->getPriority());
                    $this->logger->debug('found ' . count($links) . ' links, new: ' . $added);
                }
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
            }
        }

        // Run handlers
        foreach ($this->resourceHandlers as $handler) {
            try {
                if ($handler->isSupport($mime)) {
                    $isProcessed = true;
                    $this->logger->debug('handle by '.$handler->getName());
                    $handler->handleResource($content, $pageUrl);
                }

            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
            }
        }

        if (!$isProcessed) {
            $this->logger->warning('resource not processed');
        }
    }

    /**
     * @param $links
     * @return array
     */
    private function filterUnVisitedLinks($links)
    {
        return array_filter($links, function ($url) {
            return $this->history->isNewUrl($url);
        });
    }



}