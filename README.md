![Wiwi screenshot](https://raw.github.com/c2is/wiwi/master/doc/screen.png "Preview")

What is Wiwi
------------
Wiwi is a small web application based on silex wich allows you to perform some basic functional tests against a website.
Currently it controls :
- robots.txt (checking there isn't any disallow /);
- home page (checking there isn't any meta robot tag containig "noindex" or "nofollow" string);
- all pages status (chekcing there isn't any 404).

Requirements
------------

Using Apache, in your php.ini set:
```
output_buffering = Off 
zlib.output_compression = Off 
```

Using, in your nginx.conf set:
```
gzip off; 
proxy_buffering off; 
```

After install need to hack this file /Users/andre/Documents/Work/Wiwi/vendor/symfony/browser-kit/Symfony/Component/BrowserKit/Client.php
to add:
 if(is_object($uri)){
   $uri = $uri -> __toString();
 }
as the first line of the function getAbsoluteUri($uri)