<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class HttpHelper
{
    /** @var Client */
    private $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function retrievePageBody(string $url): string
    {
        try {
            $pageBody = $this->httpClient->get($url)->getBody()->getContents();
        } catch (\Exception $e) {
            $pageBody = '';
        }

        return $pageBody;

    }

    /**
     * Tries to access the url and return the contents of the 'title' tag.
     */
    public function getPageName(string $pageBody): string
    {
        try {
            $crawler = new Crawler($pageBody);
            $title = strip_tags($crawler->filterXpath('//title')->text());
        } catch (\Exception $e) {
            $title = 'No Title';
        }

        return $title;
    }

    public function getIcon(string $url, string $pageBody)
    {
        $icon = null;

        try {
            $iconPath = $this->getIconPath($pageBody);

            if ($iconPath) {
                $iconUrl = $this->constructIconUrl($url, $iconPath);
                $icon = $this->retrieveIcon($iconUrl);
            }
        } catch (\Exception $e) {
        }

        return $icon;
    }

    private function getIconPath(string $pageBody): string
    {
        $crawler = new Crawler($pageBody);

        return $crawler->filterXpath('//link[@rel="shortcut icon"]')->attr('href');
    }

    private function constructIconUrl(string $url, string $iconPath): string
    {
        if (substr($iconPath, 0, 1) === '/') {
            $urlParts = parse_url($url);

            return $urlParts['scheme'] . '://' . $urlParts['host'] . $iconPath;
        }

        return $iconPath;
    }

    private function retrieveIcon(string $iconUrl): ?string
    {
        $icon = null;

        try {
            $rawIcon = $this->httpClient->get($iconUrl)->getBody()->getContents();
            $icon = 'data:image/png;base64,' . base64_encode($rawIcon);
        } catch (\Exception $e) {
        }

        return $icon;
    }
}