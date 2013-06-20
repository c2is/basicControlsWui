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
    private $domainWildCard;
    private $subDomainsMask;
    public function __construct($baseUrl, $subDomainsMask = ".*")
    {
        $this -> links  = array();
        $this -> urlsVisited  = array();
        $this -> baseUrl = $baseUrl;
        $this -> subDomainsMask = $subDomainsMask;

        $domain = $domain = parse_url($this -> baseUrl , PHP_URL_HOST);
        $domainWildCard = explode(".", $domain);
        $this -> domainWildCard = ".".$domainWildCard[count($domainWildCard)-2].".".$domainWildCard[count($domainWildCard)-1];

        $clientOptions = array();
        $this -> walkerClient  = new \Behat\Mink\Driver\Goutte\Client();
        $this -> walkerClient -> setClient(new \Guzzle\Http\Client('', $clientOptions));
        $this -> checkLinks($this -> baseUrl);

    }

    public function checkLinks($url, $referer = "")
    {
        $urlDomain = parse_url($url , PHP_URL_HOST);

        if ( ! preg_match("`".$this -> subDomainsMask.$this -> domainWildCard."`", $urlDomain) || strpos($url, "#") !== false || in_array($url, $this->urlsVisited)) {
            return true;
        }
        $this->urlsVisited[] = $url;
        $crawler = $this -> walkerClient->request('GET', $url);
        $statusCode = $this -> walkerClient->getResponse()->getStatus();
        $this->stats[] = array($url,$statusCode,$referer);
        // getting  href attributes belonging to nodes of type "a"
        // Todo : deal or not with shortlink like Drupal ? Ex. : <link rel="shortlink" href="http://www.c2is.fr/node/25" />
        $nodes = $crawler->filterXPath('//a/@href');

        foreach ($nodes as $node) {
            $prefix = "";
            if (strpos($node->value, "http:") === false) {
                $prefix = $this -> baseUrl;
                if (strpos($node->value, "/") !== 0) {
                    $prefix .= "/";
                }
            }

            $linkUri = $prefix.$node->value;

            if (! in_array($linkUri,$this -> links)) {
                $this -> links[] = $linkUri;
            }

            if (strpos($linkUri, "#") === false && strpos($linkUri, "mailto:") === false) {
                $this->checkLinks($linkUri, $url);
            }

        }
    }

    public function getStats()
    {
        return $this -> stats;
    }

    public function getLinks()
    {
        return $this -> links;
    }

}
