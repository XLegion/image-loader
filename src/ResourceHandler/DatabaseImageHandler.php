<?php

namespace ImageLoader\ResourceHandler;


/**
 * Сохраняет картинку в бд
 */
class DatabaseImageHandler implements ResourceHandlerInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var int
     */
    private $sessionId;

    public function __construct(\PDO $pdo, $sessionId)
    {
        $this->pdo = $pdo;
        $this->sessionId = $sessionId;
    }

    public function handleResource($content, $url)
    {
        $sql = 'insert into images (content, url, session) VALUES (:content, :url, :session)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'content' => $content,
            'url' => $url,
            'session' => $this->sessionId
        ]);
    }

    /**
     * @param $mime
     * @return bool
     */
    public function isSupport($mime)
    {
        return strpos($mime, 'image/') === 0;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'DatabaseResourceHandler';
    }
}