 
# EmitPHP

EmitPHP is a PHP framework that works with non-blocking I/O.

You can write your web applications and APIs in HTTP, FCGI and WebSocket.

As of now, this is an experimental project and there still exists errors and bugs.

## Installation

- It requires PHP 7(CLI) and composer to run
- If you haven't installed composer, run below to install it.
```sh
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

- Download [EmitPHP source code](https://github.com/rnaga/EmitPHP.git) from github
- Run composer to create autoload
```sh
composer install
```
Now you can run [Examples](https://github.com/rnaga/EmitPHP/tree/master/examples) 

## Usage
### WebSocket
Yes, EmitPHP supports WebSocket. 

you can create your WebSocket Application with a few lines of code.

Below is an example of how to create a WebSocket Application.

```php
// Create a new WS Application
$app = (new WSServer())->listen(4000)->app();
// Triggers when messages received
$app->on('message', function($conn, $msg){
    // Echo message
    $conn->send("echo => ". $msg);
    // Close the connection
    $conn->close();
});

\Emit\Loop();
```
### HTTP

You can easily create a HTTP server as below
```php
$server = (new HTTPServer())->listen(4000);
$server->on('request', function($req, $res){
    // Send response
    $res->send("Hello World");
    // Close connection
    $res->end();
});

\Emit\Loop();
```
### Router
Example for using Router

```php
$server = (new HTTPServer())->listen(9000);
// Create a new Route
$route = $server->route();
// Get method 
$route->get("/", function($req, $res, $next){
    $res->send("Hello World");
    // Calling the next handler
    $next();
});
// Register the route
$server->use($route);

\Emit\Loop();
```
### FCGI
EmitPHP supports FCGI which works with Web Servers such as apache
```php
$server = (new FCGIServer())->listen(9000);
$server->on('request', function($req, $res){
    // Send response
    $res->send("Hello World");
    // Close connection
    $res->end();
});

\Emit\Loop();
```

See [examples](https://github.com/rnaga/EmitPHP/tree/master/examples) for more details.

## What's next

Please send me your feeback at emitphp@gmail.com and let me know how you like it.
If there are demands, I will work more.

And if any of you wants to join the project, please let me know.

## License

EmitPHP is licensed under the MIT license. See License File for more information.


