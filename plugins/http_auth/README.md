// This will add the http_auth code. You must still generate the password
// file manually. See
// https://httpd.apache.org/docs/current/programs/htpasswd.html for more
// info. Be aware that the first time a browser visits an URL, it will have
// to enter this password, if the URL is also redirected to SSL, they will
// immediately have to reenter the same password. This is due to having to
// unlock both SSL and non-SSL ports, and the fact that redirection occurs
// after authorization.
