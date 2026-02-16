<!--
id: testing_servers
tags: ''
-->

# Testing Servers

you can check if your server will respect rewrite rules by doing the following

1. Create `web/htaccess-probe/.htaccess` with the following content

    ```apacheconf
    # web/htaccess-probe/.htaccess

    Options -Indexes
    
    # 1) Plain Redirect (mod_alias)
    Redirect 302 /htaccess-probe/alias-test /htaccess-probe/target
    
    # 2) RedirectMatch (mod_alias + regex)
    RedirectMatch 302 ^/htaccess-probe/alias-regex$ /htaccess-probe/target
    
    # 3) RewriteRule (mod_rewrite)
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteRule ^rewrite-test$ /htaccess-probe/target [R=302,L]
    </IfModule>
    
    # This is where all redirects should land.
    # If you see this content, redirects didn’t fire.
    ```
2. Now run this test to make sure Drupal is not handling the first three, and DOES handle the fourth only.

    ```shell
    curl -I https://auroratime.app/htaccess-probe/alias-test
    curl -I https://auroratime.app/htaccess-probe/alias-regex
    curl -I https://auroratime.app/htaccess-probe/rewrite-test
    curl -i  https://auroratime.app/htaccess-probe/target
    ```

## How to Fix

You should look at the `redirects.error_handlers` feature if your server is not handling errors as you expect.
