Use the 'redirects' key to set up .htaccess redirects. The most common use case would be to map legacy URLs to new ones. Redirects defined at the top level are shared across all .htaccess files. See also file-level.

You may also use this to return 1XX,2XX,4XX,5XX codes when a certain URL is hit. Most common use would be to forbid a url.

* <https://developer.mozilla.org/en-US/docs/Web/HTTP/Status>

## Regex is supported, see example below.

## Other status codes can be returned by giving the numeric status code as the value of status.

* If the status is between 300 and 399, the URL argument must be present.
* If the status is not between 400 and 499, the URL argument must be omitted.
* The status must be a valid HTTP status code, known to the Apache HTTP Server (see the function send_error_response in http_protocol.c).

To learn more see: https://httpd.apache.org/docs/current/mod/mod_alias.html#redirectmatch

```shell
redirects:
  301:
    - /some/old/path /some/new/path

    # Notice the use of the escaped $, e.g. '\$' in this example for the replace
    # side of this line.
    - /foo/(.+) /bar/\$1
  403:
    - /some/forbidden/path
```
