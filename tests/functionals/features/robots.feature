Feature: robots
  Check if rules exist forbidding bots to crawl the website
  As an anonymous user
  I check the html code and the robots.txt file
Scenario: read robots.txt file
  Given the website is reachable
  When I get the robots.txt
  Then I should not get:
    """
    (\n|^)[^#a-zA-Z]*Disallow: /[* ]*\n
    """
  When I get the home html source
  Then I should not get:
    """
    noindex
    """
  Then I should not get:
    """
    nofollow
    """