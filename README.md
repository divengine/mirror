# Div PHP Mirror

`mirror` is a PHP library that allows you to access and invoke remote classes and functions as if they were local. By exposing classes and functions on a remote server, and generating their mirrored counterparts on the client, this library provides a seamless way to work with remote entities through serialized communication.

## Features

- **Remote Class and Function Mapping**: Expose classes and functions on a server and map them to mirrored versions on the client side.
- **Seamless Remote Access**: Invoke remote methods and functions from the client as if they were local.
- **Serialized Data Communication**: Send parameters and receive results through serialized HTTP communication, making the process efficient and transparent.
- **Client-Server Symmetry**: Acts as both a client and a server, providing flexible and dynamic integration of remote entities.

## How It Works

### Exposing Classes and Functions on the Server

Use the `prepare` method to expose the classes and functions you want to make available remotely:

```php
mirror::prepare(Dog::class);
mirror::prepare('sum');
echo mirror::expose($page);
```

### Discovering and Generating on the Client

On the client side, use discover to retrieve the available classes and functions, and generate to create local mirrored versions:

```php
use divengine\mirror;

$model = mirror::discover("http://localhost:9090/");
echo mirror::generate($model, "MyClient");
```

### Using Remote Classes and Functions Locally

Once the mirrored classes are generated, you can invoke them as if they were local:

```php
use divengine\mirror;

mirror::setServer("http://localhost:9090/");
include __DIR__ .'/Dog.php';

$result = \MyClient\sum(1, 2);
var_dump($result);
```

### Handling Remote Calls

The server receives calls from the client and processes them:

```php
$call = mirror::receiveCall();
```

## Installation

To install Div PHP Mirror, you can use Composer (or other relevant instructions depending on your setup):

```bash
composer require divengine/mirror
```

## Contributing

Contributions are welcome! Feel free to submit issues or pull requests to help improve Div PHP Mirror.

## Documentation

For more information, please refer to the [official documentation](https://divengine.org).
