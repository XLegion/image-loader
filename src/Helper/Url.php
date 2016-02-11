<?php

namespace ImageLoader\Helper;

class Url
{

    /**
     * For building absolute url from relative
     * @param $url
     * @param $requestedUrl
     * @return string
     * @author sanchez
     */
    public static function createFullUrl($url, $requestedUrl)
    {
        if (substr($url, 0, 2) == '//') {
            $url = parse_url($requestedUrl, PHP_URL_SCHEME) . ':' . $url;
        }

        $pu = parse_url($requestedUrl);

        // A ./ in front of the URL is equivalent to the current path
        //   +more: http://tools.ietf.org/html/rfc3986#section-5.2.4
        if (substr($url, 0, 2) == './') {
            $url = substr($url, 1);
        }

        while (preg_match('~/([^\./]+)/\.\./~', $url, $m)) {
            $url = str_replace($m[0], '/', $url);
        }

        $parsedHref = parse_url($url);

        if (isset($parsedHref['scheme'])) {
            return self::composeUrl($parsedHref);
        }
        $sitePrefix = $pu['scheme'] . '://' . $pu['host'];
        if (isset($pu['port'])) {
            $sitePrefix .= ':' . $pu['port'];
        }
        if (substr($url, 0, 1) == '/') {
            $url = $sitePrefix . $url;
            return $url;
        }
        $path = isset($pu['path']) ? $pu['path'] : '/';

        // http://tut.by/  && ../page.html fix
        if ($path == '/' && substr($url, 0, 3) == '../') {
            $url = $sitePrefix . substr($url, 2);
            return $url;
        }
        $path = preg_replace('~/([^/]+)$~', '/', $path);
        $url = $path . $url;

        $url = explode('/', $url);
        $keys = array_keys($url, '..');
        foreach ($keys as $keypos => $key) {
            array_splice($url, $key - ($keypos * 2 + 1), 2);
        }
        $url = implode('/', $url);
        $url = str_replace('./', '', $url); //  http://site/zzz/./yyy === http://site/zzz/yyy
        return $sitePrefix . $url;
    }

    private static function composeUrl(array $parsedUrl)
    {
        $url = $parsedUrl['scheme'].'://'.$parsedUrl['host'];

        if (isset($parsedUrl['port'])) {
            $url .= ':' . $parsedUrl['port'];
        }
        $url .= isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';

        if (isset($parsedUrl['query'])) {
            $url .= '?'.$parsedUrl['query'];
        }

        if (isset($parsedUrl['fragment'])) {
            $url .= '#'.$parsedUrl['fragment'];
        }

        return $url;
    }

    /**
     * @param $url
     * @param $pageUrl
     * @return bool
     */
    public static function isLocalUrl($url, $pageUrl)
    {
        $urlComponents = parse_url($url);
        if (!isset($urlComponents['host'])) {
            return true;
        }

        $pageUrlComponents = parse_url($pageUrl);
        if (!isset($urlComponents['scheme'])) {
            $urlComponents['scheme'] = $pageUrlComponents['scheme'];
        }

        return ($urlComponents['scheme'] == $pageUrlComponents['scheme'])
            && ($urlComponents['host'] == $pageUrlComponents['host']);
    }

}
