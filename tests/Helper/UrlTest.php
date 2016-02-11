<?php

namespace ImageLoader\Helper;

use PHPUnit_Framework_TestCase;

class UrlTest extends PHPUnit_Framework_TestCase
{


    /**
     * @dataProvider fullUrlProvider
     * @param $urls
     * @param $result
     */
    public function testCreateFullUrl($urls, $result)
    {
        $this->assertEquals($result, Url::createFullUrl($urls['href'], $urls['current_page']));
    }

    public function fullUrlProvider()
    {
        return [
            [
                // Нормальная обработка простой ссылки
                [
                    'href' => '/page.html',
                    'current_page' => 'http://tut.by/',
                ],
                'http://tut.by/page.html'
            ],

            [
                // Проверка полной ссылки
                [
                    'href' => 'http://www.example.com/about.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/about.php'
            ],

            [
                // Проверка бага с полной ссылкой
                [
                    'href' => 'http://www.example.com?page=about',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/?page=about'
            ],

            [
                // Проверка ./ в начале ссылки
                [
                    'href' => './about.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/about.php'
            ],

            [
                // Урл без http, но с "//", страница с http
                [
                    'href' => '//www.example.com/meta.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/meta.php'
            ],

            [
                // Урл с https
                [
                    'href' => 'https://www.example.com/about.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'https://www.example.com/about.php'
            ],

            [
                // Урл со страницы с портом
                [
                    'href' => 'about.js',
                    'current_page' => 'http://www.example.com:8000/',
                ],
                'http://www.example.com:8000/about.js'
            ],

            [
                // Урл начинается со слэша
                [
                    'href' => '/slash.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/slash.php'
            ],

            [
                // Урл вида ../about.php и корневая страница
                [
                    'href' => '../something.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/something.php'
            ],

            [
                // Удаление пути перед / с конца страницы
                [
                    'href' => 'about.php',
                    'current_page' => 'http://www.example.com/data/page.php?get=1',
                ],
                'http://www.example.com/data/about.php'
            ],

            [
                // Удаление ../ в урле
                [
                    'href' => 'main/data/docs/../../faq/faq.php',
                    'current_page' => 'http://www.example.com/',
                ],
                'http://www.example.com/main/faq/faq.php'
            ],

            [
                // Переход на другой http-домен
                [
                    'href' => 'http://www.othersite.ro/files.exe',
                    'current_page' => 'https://www.example.com/index.html',
                ],
                'http://www.othersite.ro/files.exe'
            ],

            [
                // Комплексный тест c доменом и локальной ссылкой
                [
                    'href' => '/var/www/data/../../faq/faq.php?go=no&params=0#tail',
                    'current_page' => 'ftp://subdomain.two.example.com:8080/main.asp?get=someRequest&data=false#nodata',
                ],
                'ftp://subdomain.two.example.com:8080/var/faq/faq.php?go=no&params=0#tail'
            ],

            [
                // Комплексный тест с IP и локальной ссылкой
                [
                    'href' => '/var/www/data/../../faq/faq.php?go[]=path&go[]=home#tail',
                    'current_page' => 'telnet://127.0.0.1:8080/main.asp?get[]=datum&data=false#nodata',
                ],
                'telnet://127.0.0.1:8080/var/faq/faq.php?go[]=path&go[]=home#tail'
            ],

            [
                // Комплексный тест с переходом
                [
                    'href' => 'ftp://www.example.com:1212/var/www/data/../../faq/faq.php?go[]=path&go[]=home#tail',
                    'current_page' => 'telnet://127.0.0.1:8080/main.asp?get[]=datum&data=false#nodata',
                ],
                'ftp://www.example.com:1212/var/faq/faq.php?go[]=path&go[]=home#tail'
            ],
        ];
    }

    /**
     * @param $url
     * @param $isLocal
     * @dataProvider localUrlDataProvider
     */
    public function testIsLocalUrl($url, $isLocal)
    {
        $this->assertEquals($isLocal, Url::isLocalUrl($url, 'http://test.ru/test-page'));
    }

    /**
     * @return array
     */
    public function localUrlDataProvider()
    {
        return [
            [
                'http://test.ru/test',
                true
            ],
            [
                'https://test.ru/test',
                false
            ],
            [
                '//test.ru/test',
                true
            ],
            [
                'http://sub.test.ru/test',
                false
            ],
            [
                '/test',
                true
            ],
            [
                'http://other-domain.ru/test',
                false
            ],
        ];
    }
}