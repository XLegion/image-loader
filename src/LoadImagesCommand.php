<?php

namespace ImageLoader;

use GuzzleHttp\Client;
use ImageLoader\Crawler\SiteCrawler;
use ImageLoader\ResourceHandler\DatabaseImageHandler;
use ImageLoader\ResourceHandler\ResourceHandlerInterface;
use ImageLoader\ResourceParser\Html\HtmlAnchorParser;
use ImageLoader\ResourceParser\Html\HtmlImgParser;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadImagesCommand extends Command
{
    protected function configure()
    {
        $this->setName('load-images')
            ->addArgument('url', InputArgument::REQUIRED)
            ->addOption('session', 's', InputOption::VALUE_OPTIONAL, '', rand(1, 999999))
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger('log', [new StreamHandler('php://output')]);
        $url = $input->getArgument('url');

        $client = new Client();

        $sessionId = $input->getOption('session');
        $logger->info('Start. Session: '.$sessionId);


        $crawler = new SiteCrawler($client, $logger);
        $crawler->setLimit($input->getOption('limit'));
        $crawler->addResourceHandler($this->createImageHandler($sessionId));
        $crawler->addParser(new HtmlAnchorParser($url));
        $crawler->addParser(new HtmlImgParser($url));
        $crawler->run($url);
    }

    /**
     * @param $session
     * @return ResourceHandlerInterface
     * @throws \Exception
     */
    private function createImageHandler($session)
    {
        $configPath = PROJECT_ROOT.'/config/database.php';
        if (file_exists($configPath)) {
            /** @noinspection PhpIncludeInspection */
            $config = require $configPath;
            if (isset($config['dsn']) && isset($config['user']) && isset($config['pass'])) {
                $pdo = new \PDO($config['dsn'], $config['user'], $config['pass']);
                return new DatabaseImageHandler($pdo, $session);
            }
        }

        throw new \Exception('Error configure DatabaseImageHandler');
    }


}