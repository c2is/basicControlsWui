<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//


/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        /*
         * you can add parameters via shell export command before executing Behat, for example :
         * export BEHAT_PARAMS="context[parameters][base_url]=http://localhost"
         */
        $this ->parameters = $parameters;
        $this -> client = new \Goutte\Client();
    }

    /**
     * @Given /^the website is reachable$/
     */
    public function theWebsiteIsReachable()
    {
        try {
            if (! $this -> client ->request('GET', $this -> parameters["base_url"])) {
                throw new Exception(
                    "The website is unreachable'\n"
                );
            }

        } catch (Exception $e) {
            throw new Exception(
                "The website is unreachable".$e->getMessage()."'\n"
            );
        }
    }

    /**
     * @When /^I get the robots\.txt$/
     */
    public function iGetTheRobotsTxt()
    {
        $this -> client ->request('GET', $this -> parameters["base_url"].'/robots.txt');
    }

    /**
     * @When /^I get the home html source$/
     */
    public function iGetTheHomeHtmlSource()
    {
        $this -> client ->request('GET', $this -> parameters["base_url"]);
    }

    /**
     * @param mix $string The string or regexp which shouldn't be found
     *
     * @Then /^I should not get:$/
     */
    public function iShouldNotGet(PyStringNode $string)
    {
        if (preg_match("`$string`i", $this->client->getResponse()->getContent(), $matches)) {
            throw new Exception(
                "Forbidden string found: ".$matches[0]."\n"
            );
        }
    }

    /**
     * @When /^I crawl all the website$/
     */
    public function iCrawlAllTheWebsite()
    {
        // add any regexp for crawling other subdomains, example forl all subdomains :
        // $this -> walker = new \Walker\Walker($this -> parameters["base_url"], ".*");
        $this->walker = new \Walker\Walker($this -> parameters["base_url"]);
        $this->walker->storage->addColumn("stats","GOOGLE ANALYTICS");
        echo "\nCrawling Website in process...";
        $this -> walker -> run(function ($crawler, $client) {
            $stats = $client->getStats();
            echo "\n".$stats["URL"]." : ".$stats["STATUS"];

            if ($this->parameters["ga"] == "1") {
                $nodes = $crawler->filterXPath('//script');

                foreach ($nodes as $node) {
                    if (property_exists($node,"textContent")) {
                        if (preg_match("`(UA-[0-9-]*)`", $node->textContent, $matches)) {
                            $this->walker->storage->update("stats", "URL", $stats["URL"], "GOOGLE ANALYTICS", $matches[1]);
                        }
                    }
                }
            }

            flush();
        });
        echo "\n";
    }

    /**
     * @Then /^I should not get page with status$/
     */
    public function iShouldNotGetPageWithStatus(PyStringNode $string)
    {

        $stats = $this -> walker->storage->get("stats");
        $badUrls = array();
        foreach ($stats as $info) {
            if ((string) $info["STATUS"] == $string) {
                $badUrls[] = $info["URL"];
            }
        }

        if (count($badUrls) > 0) {
            throw new Exception(
                "Pages with status ".$string." found: \n".implode("\n",$badUrls)."\n"
            );
        }

    }
    /**
     * @Then /^I perform controls asked by wiwi$/
     */
    public function iPerformControlsAskedByWiwi()
    {

        if ($this->parameters["ga"] == "1") {
            $stats = $this -> walker->storage->get("stats");
            $badUrls = array();
            foreach ($stats as $info) {
                if ((string) $info["GOOGLE ANALYTICS"] == "") {
                    $badUrls[] = $info["URL"];
                } else {
                    $tags[] = $info["GOOGLE ANALYTICS"];
                }
            }

            if (count($badUrls) > 0) {
                throw new Exception(
                    "Pages with no Google Analytics tag found: \n".implode("\n",$badUrls)."\n"
                );
            } else {
                echo "NOTICE ! Google tags found: \n".implode("\n",array_unique($tags))."\n";
            }
        }
        if ($this->parameters["404"] == "1") {
            $this->iShouldNotGetPageWithStatus(new PyStringNode("404"));
        }

        $invalidUrls = $this -> walker -> getInvalidUrlsFound();

        if (count($invalidUrls) > 0) {
            foreach ($invalidUrls as $info) {
                $noticedUrls[] = $info[0]." linked by : ".$info[1];
            }
            echo "NOTICE ! some url with bad format have been ignored: \n".implode("\n",$noticedUrls)."\n";
        }
    }
}
