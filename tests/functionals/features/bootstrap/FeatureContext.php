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
        $this -> walker = new \Walker\Walker($this -> parameters["base_url"]);
        echo "\nCrawling Website in process...";
        $this -> walker -> run(function ($client, $stats) {
            echo "\n".$stats[0]." : ".$stats[1];
            flush();
        });
        echo "\n";
    }

    /**
     * @Then /^I should not get page with status$/
     */
    public function iShouldNotGetPageWithStatus(PyStringNode $string)
    {

        $stats = $this -> walker -> getStats();
        $badUrls = array();
        foreach ($stats as $info) {
            if ((string) $info[1] == $string) {
                $badUrls[] = $info[0];
            }
        }

        if (count($badUrls) > 0) {
            throw new Exception(
                "Pages with status ".$string." found: \n".implode("\n",$badUrls)."\n"
            );
        }

    }
}
