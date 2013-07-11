Feature: eachPageControls
  Check each page to perform some controls in the content
  As an anonymous user
  I check all web page
Scenario: crawl the entire website and check each status response
  Given the website is reachable
  When I crawl all the website
  Then I perform controls asked by wiwi