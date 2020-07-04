# yildirim-routing

Simple PHP Routing without framework.

This plugin

```

$ composer require yildirim/routing

```

## Basic usage

The most basic usage is to add a route with a closure.

```php
# index.php

use Yildirim\Routing\Route;

Route::get('/',function(){
	return '<h1>Hello World</h1>';
});

$response = app()->resolveRequest();

$response->send();

```

Be sure to add the following after you have defined your routes:

`$response = app()->resolveRequest();`
This method initiates the process to resolve the incoming request and returns a `Yildirim\Classes\Response` object.

`$response->send();`
This will output the response.

# Routing

### Available Router Methods

- **GET** --- `Route::get()`
- **POST** --- `Route::post()`
- **PUT** --- `Route::put()`
- **PATCH** --- `Route::patch()`
- **DELETE** --- `Route::delete()`
- **ANY** --- `Route::any()`

## Route Handler

Route handler parameter accepts 3 different values.

**Closure**

```php
Route::get('/',function(){
	return '<h1>Hello World</h1>';
});
```

**Function Name** (string)

```php
Route::get('/','foo');

function foo(){
	return '<h1>Hello World</h1>';
}
```

**Controller Method** (string)
The namespace must also be provided for the controller.

```php
Route::get('/','App\Controllers\Controller@index');
```

```php
# Controller.php

namespace App\Controllers;

class Controller
{
	public function index()
	{
		return '<h1>Hello World</h1>';
	}
}
```

## Global Controller Namespace

If your controllers are all in the same namespace then you can set the global namespace. This will then allow you to pass the controller without a namespace to the route handler.

```php
app()->setControllerNamespace("App\Controllers");

Route::get("/","Controller@method");
```

## Route Parameters

Route parameters can be defined by encasing them within `{}` braces.

#### #Required Parameters

```php
Route::get('post/{id}', function($id){

});
```

Route parameters are injected into the route handler based on their order - the names of the handler arguments do not matter.

#### #Optional Parameters

Optional Parameters are denoted by a `?` before the closing `}` brace. Be sure to add a default value to the corresponding handler argument.

```php
Route::get('post/{date?}', function($date = null){

});
```

## Route Groups

The Router also has the ability to group routes.

```php
Route::group('post', function(){
	Route::get('','PostController@index');
	Route::post('','PostController@store');

	Route::group('/{post}',function(){
		Route::get('','PostController@show');
		Route::patch('','PostController@update');
		Route::delete('','PostController@destroy');
	});
});
```

# Middleware

The router allows the use of middleware. They are run before a route handler is invoked.

```php

Route::get("/post/{post}")->middleware('App\Middlewares\Middleware');

```

```php
# Middleware.php

namespace App\Middlewares;

class Middleware
{
	public function run($request)
	{
		//do something.

		return $request;
	}
}
```

The Middleware class **must** include the `run()` method and accept the `$request` argument.

Be sure the `run()` method **must** return `$request`, Otherwise an `Exception` will be thrown.

**`$request`**
This is an instance of `Yildirim\Classes\Request` this class allows you to access request information and route parameters as well as `$_REQUEST`, `$_GET`, `$_POST` parameters etc.

## Global Middleware Namespace

Similar to the Controllers if a global namespace is not set, then you must provide the complete path to the middleware.

```php
app()->setMiddlewareNamespace("App\Middlewares");

Route::get("/post/{post}")->middleware('Middleware');
```

### Multiple Middleware

You can add as many middleware as you like to a route. They will be run in the order they are added.

```php
Route::get("/post/{post}")->middleware('Middleware', 'AnotherMiddleware', 'AThirdMiddleware');
```

## Group Middleware

You can also add middleware to groups. The middleware is then run on all routes within the group.

```php
Route::middleware('Middleware')->group(function(){

	Route::get("/post/{post}")->middleware('AnotherMiddleware');

});
```

This would be the same as

```php
Route::get("/post/{post}")->middleware('Middleware', 'AnotherMiddleware');
```

**Important!!**
The `middleware()` method **must** be called before the `group()` method.

# Dependancy Injection

The `app()` container also has the ability to resolve dependancies and inject them for you.

for example you can inject the `Request` instance into your handler.

```php
use Yildirim\Classes\Request;

Route::get("/",function(Request $request){

});
```

Be sure to add any dependancies like `Request` before your route parameters in your handler.

```php
use Yildirim\Classes\Request;

Route::post("post/{post}",function(Request $request, $post){

});
```

## Custom Dependancies

You can also create custom dependancies and register them with the `app()` container. They will then be resolved for you automatically.

lets say you create a custom DB class.

```php
namespace App\Database;

class DB
{
	...
}
```

you can then set the dependancy in the `app()` container.

```php
app()->set('App\Database\DB', new App\Database\DB);
#or
app()->set('App\Database\DB');
```

and use it as follows

```php
use Yildirim\Classes\Request;
use App\Database\DB;

Route::post("post/{post}",function(Request $request, DB $db, $post){

});
```

### Container Methods

**`set($abstract, $concrete = null)`**
Objects registered using this method will return a new instance every time the object is resolved.

**`setInstance($abstract, $instance)`**
Objects registered using this method will return the same instance every time the object is resolved.

**`get($abstract, $parameters = [])`**
Returns the resolved object/instance.

**`has($instance)`**
check if an object/instance is registered.

# Available Helper Functions

**`app($abstract = null, $parameters = [])`**
returns the container instance.

**`request($key = null, $defualt = null)`**
returns the request instance.

**`server($key = null, $defualt = null)`**
returns the server instance.

**`collect($data = [])`**
returns a Collection instance.

**`dd($data, $moreData)`**
dumps data and terminates execution. used for debugging.

# Customisation

There are 4 basic classes used within this package that contain the bare minimum required methods for the router to work. You may find yourself needing to customise these classes. This can be done by extending theses classes and registering them in the `app()` container.

### `Yildirim\Classes\Request`

This is the `request` instance used by the `app()` container . You can extend and register as follows.

```php
# CustomRequest.php

namespace App\Custom;

use Yildirim\Classes\Request

class CustomRequest extends Request
{
	...
}
```

Register the new `request` instance.

```php
app()->setInstance('request', new App\Custom\CustomRequest);
```

The `CustomRequest` class will now be used by the app instead of the standard `Yildirim\Classes\Request`.

### `Yildirim\Classes\Server`

This is the `server` instance used by the `app()` container .

Once you have extended this you can register the new `server` instance like below.

```php
app()->setInstance('server', new App\Custom\CustomServer);
```

### `Yildirim\Classes\Collection`

The `collection` instance used by the `app()` container .

Once you have extended this you can register the new `collection` instance like below.

```php
app()->set('collection', new App\Custom\CustomCollection);
```

### `Yildirm\Routing\Response`

The `response` instance used by the `app()` container .

Once you have extended this you can register the new `response` instance like below.

```php
app()->set('response', new App\Custom\CustomReponse);
```
