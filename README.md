![Wiwi screenshot](https://raw.github.com/c2is/wiwi/master/doc/screen.png "Preview")

With apache, for your php.ini:
```
output_buffering = Off 
zlib.output_compression = Off 
```

In nginx.conf:
```
gzip off; 
proxy_buffering off; 
```
