![Wiwi screenshot](https://raw.github.com/c2is/wiwi/master/doc/screen.png "Preview")

What is Wiwi
------------
Wiwi is a small web application based on silex wich allows you to perform some basic functional tests against a website.

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
