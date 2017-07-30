<?php

namespace App;

use PHPHtmlParser\CurlInterface;
use PHPHtmlParser\Exceptions\CurlException;

class OlxClienteCurl implements CurlInterface
{

    /**
     * A simple curl implementation to get the content of the url.
     *
     * @param string $url
     * @return string
     * @throws CurlException
     */
    public function get($url)
    {
        $ch = curl_init($url);

        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);

        $content = curl_exec($ch);
        if ($content === false) {
            // there was a problem
            $error = curl_error($ch);
            throw new CurlException('Error retrieving "' . $url . '" (' . $error . ')');
        }

        return $content;
    }
}