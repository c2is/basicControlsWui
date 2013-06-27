<?php
/**
 * Created by JetBrains PhpStorm.
 * User: andre
 * Date: 27/06/13
 * Time: 15:00
 * To change this template use File | Settings | File Templates.
 */
namespace Walker;

use Goutte\Client as BaseClient;
use Symfony\Component\BrowserKit\Response;

class Client extends BaseClient
{
    private $walker;
    public function doRequest($request) {
        $uri = $request->getUri();
        if ($this -> walker->isUrlToCheck($uri,"")) {

            $response = parent::doRequest($request);
            $statusCode = $response->getStatus();
        }
        else {
            $headers[] = "";
            $statusCode = "202";
            $response = new Response("", $statusCode, $headers);
        }
        $this -> walker->urlsVisited[] = $uri;
        return $response;

    }
    public function setWalker(Walker $walker) {
        $this -> walker = $walker;
    }
}