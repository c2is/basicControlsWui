Feature: PagesStatus
  Check if some page's responses give unexpected status
  As an anonymous user
  I check all web page
Scenario: crawl the entire website and check each status response
  Given the website is reachable
  When I crawl all the website
  Then I should not get page with status
  """
  404
  """