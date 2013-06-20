<?php
/**
 * Created by JetBrains PhpStorm.
 * User: andre
 * Date: 19/06/13
 * Time: 18:23
 * To change this template use File | Settings | File Templates.
 */
namespace Walker;
/**
 * A simple wrapper around Goutte to crawl a website
 *
 * @author André Cianfarani <a.cianfarani@c2is.fr>
 *
 * @api
 */
class Walker
{
    private $links;
    private $stats;
    private $baseUrl;
    private $walkerClient;
    public function __construct($baseUrl)
    {
        $this -> links  = array();
        $this -> baseUrl = $baseUrl;
        $clientOptions = array();
        $this -> walkerClient  = new \Behat\Mink\Driver\Goutte\Client();
        $this -> walkerClient -> setClient(new \Guzzle\Http\Client('', $clientOptions));
        $this -> checkLinks("http://".$this -> baseUrl);

    }

    public function checkLinks($url)
    {
        if (strpos($url, $this -> baseUrl) === false || strpos($url, "#") !== false) {
            return true;
        }

        $crawler = $this -> walkerClient->request('GET', $url);
        $statusCode = $this -> walkerClient->getResponse()->getStatus();

        // getting  href attributes belonging to nodes of type "a"
        // Todo : deal or not with shortlink like Drupal ? Ex. : <link rel="shortlink" href="http://www.c2is.fr/node/25" />
        $nodes = $crawler->filterXPath('//a/@href');

        foreach ($nodes as $node) {
            $prefix = "";
            if (strpos($node->value, "http:") === false) {
                $prefix = "http://".$this -> baseUrl;
                if (strpos($node->value, "/") !== 0) {
                    $prefix .= "/";
                }
            }

            $linkUri = $prefix.$node->value;

            if (! in_array($linkUri, $this->links) && strpos($linkUri, "#") === false && strpos($linkUri, "mailto:") === false) {
                $this->links[] = $linkUri;
                $this->stats[] = array($linkUri,$statusCode);
                $this->checkLinks($linkUri);
            }

        }
    }

    public function getStats()
    {
        return $this -> stats;
    }
}
