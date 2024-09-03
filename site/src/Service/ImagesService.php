<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImagesService
{
    public function __construct(
      private HttpClientInterface $httpClient
    ){}

    public function formattingUrl($url)
    {
        if (!preg_match('/^https?:\/\//i', $url)) {
            // Добавляем схему по умолчанию, если она отсутствует
            $url = 'http://' . $url;
        }

        return $url;
    }

    public function getHtml($url)
    {
        $response = $this->httpClient->request(
          'GET',
          $url
        );

        $content = $response->getContent();

        return $content;
    }

    public function getImgUrls($html)
    {
        preg_match_all('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        $imageUrls = array_unique($matches[1]);

        return $imageUrls;
    }

    public function resultImagesForLinks($imgUrls, $url)
    {
        $images = [];
        $total = 0;

        foreach ($imgUrls as $imgUrl) {
            if (parse_url($imgUrl, PHP_URL_SCHEME) === null) {
                $imgUrl = rtrim($url, '/') . '/' . ltrim($imgUrl, '/');
            }

            $response = $this->httpClient->request(
              'GET',
              $imgUrl
            );

            $imgSize = $response->getHeaders(false)['content-length'][0] ?? 0;
            $imgSizeMb = $imgSize / 1024 / 1024;
            $total += $imgSizeMb;

            $images[] = [
              'url' => $imgUrl
            ];
        }

        return [
          'count' => count($images),
          'images' => $images,
          'total' => $total
        ];
    }
}