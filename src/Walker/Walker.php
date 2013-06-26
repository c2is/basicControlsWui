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
    private $excludedFileExt;
    private $forbiddenPattern;
    public function __construct($baseUrl, $subDomainsMask = ".*")
    {
        $this -> links  = array();
        $this -> urlsVisited  = array();
        $this -> baseUrl = $baseUrl;
        if (strrpos($this -> baseUrl, "/") == strlen($this -> baseUrl)-1) {
            $this -> baseUrl = substr($this -> baseUrl,0,strlen($this -> baseUrl)-1);
        }
        $this -> subDomainsMask = $subDomainsMask;
        $this -> excludedFileExt = "`\.(jpg|jpeg|gif|png)$`i";

        $this -> forbiddenPattern = array("mailto", "#");

        $domain = parse_url($this -> baseUrl, PHP_URL_HOST);
        $domainWildCard = explode(".", $domain);
        $this -> domainWildCard = ".".$domainWildCard[count($domainWildCard)-2].".".$domainWildCard[count($domainWildCard)-1];

        $clientOptions = array();
        $this -> walkerClient  = new \Behat\Mink\Driver\Goutte\Client();
        $this -> walkerClient -> setClient(new \Guzzle\Http\Client('', $clientOptions));


    }

    public function start($callback = null) {
        $this -> checkLinks($this -> baseUrl, null, $callback);
    }

    public function run($callback = null) {
        $this -> checkLinks($this -> baseUrl, null, $callback);
    }

    public function checkLinks($url, $referer = "", $callback = null)
    {

        if (! $this -> isUrlToCheck($url, $referer)) {
            return true;
        }
        $this->urlsVisited[] = $url;
        $crawler = $this -> walkerClient->request('GET', $url);
        $statusCode = $this -> walkerClient->getResponse()->getStatus();
        $this->stats[] = array($url,$statusCode,$referer);

        if (null !== $callback) {
            call_user_func($callback, $this -> walkerClient,array($url,$statusCode,$referer));
        }

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

            if (! in_array($linkUri, $this -> links)) {
                $this -> links[] = $linkUri;
            }

            if ($this -> isValidUrl($linkUri)) {
                $this->checkLinks($linkUri, $url, $callback);
            }

        }
    }
    public function isUrlToCheck($url, $referer) {
        $urlDomain = parse_url($url, PHP_URL_HOST);

        if (in_array($url, $this->urlsVisited)) {
            if ($referer != "") {
                $this -> updateStat($url, $referer);
            }
            return false;
        }
        if (! $this -> isValidUrl($url)) {
            return false;
        }
        if ( ! preg_match("`".$this -> subDomainsMask.$this -> domainWildCard."`", $urlDomain) || preg_match($this -> excludedFileExt,$url)) {
            return false;
        }

        return true;
    }
    public function isValidUrl($url){
        foreach ($this -> forbiddenPattern as $pattern) {
            if (strpos($url,$pattern) !== false) {
                return false;
                break;
            }
        }
        return true;
    }
    public function updateStat($url, $referer){
        foreach ($this -> stats as $index=>$line) {
            if ($line[0] == $url) {
                $key = $index;
            }
        }
        if (strpos($this -> stats[$key][2], $referer) === false) {
            $tmpContent = explode(",", $this -> stats[$key][2]);
            $tmpContent[] = $referer;
            $this -> stats[$key][2] = implode(",", $tmpContent);
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
