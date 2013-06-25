![basicControlsWui screenshot](https://raw.github.com/c2is/basicControlsWui/flatSkin/doc/screen.png "Preview")

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
