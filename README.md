# yildirim-routing

Simple PHP Routing without framework.

- Friendly URL's
- REST / Resourceful routing
- Dynamic URL parameters
- URL Parameter Regular Expression Constraint
- Middleware
- Dependency Injection


#### Install with Composer

```

$ composer require yildirim/routing

```

## Usage

Add a .htaccess file to your project root, this will redirect all requests to index.php.

```.htaccess
DirectoryIndex index.php

# enable apache rewrite engine
RewriteEngine on

# set your rewrite base
# Edit this in your init method too if you script lives in a subfolder
RewriteBase /

# Deliver the folder or file directly if it exists on the server
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
 
# Push every request to index.php
RewriteRule ^(.*)$ index.php [QSA]
```

The most basic usage is to add a route with a closure.

```php
# index.php

use Yildirim\Routing\Route;

require_once './vendor/autoload.php';

Route::get('/',function(){
    return '<h1>Hello World</h1>';
});

$response = app()->start();

$response->send();

```

Be sure to add the following after you have defined your routes:

`$response = app()->start();`

This method initiates the process to resolve the incoming request and returns a `Yildirim\Classes\Response` object.

`$response->send();`

This will output the response.

&nbsp;
___
&nbsp;

Refer to Wiki **[Documentation](https://github.com/NadK2/yildirim-routing/wiki)** for more information.

**[Routing](https://github.com/NadK2/yildirim-routing/wiki/1.-Routing)**

**[Middleware](https://github.com/NadK2/yildirim-routing/wiki/2.-Middleware)**

**[Container - Dependency Injection](https://github.com/NadK2/yildirim-routing/wiki/3.-Dependency-Injection)**

**[Custom Classes](https://github.com/NadK2/yildirim-routing/wiki/4.-Custom-Classes)**

**[Helper Functions](https://github.com/NadK2/yildirim-routing/wiki/Helper-Functions)**
