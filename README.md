# Laravel Cache Memory

Laravel cache memory driver which use shared memory functions.

Shmop is an easy to use set of functions that allows PHP to read, write, create and delete Unix shared memory segments.

Shared memory an IPC1 mechanism native to UNIX. In essence, it’s about two processes sharing a common
segment of memory that they can both read to and write from to communicate with one another.

## Installing

Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

``` bash
composer require sanchescom/laravel-cache-memory
```

### Laravel 5.x:

After updating composer, add the ServiceProvider to the providers array in `config/app.php`

 ```php
'providers' => [
    ...
    Sanchescom\Cache\MemoryServiceProvider::class,
],
```

### Lumen:

After updating composer add the following lines to register provider in `bootstrap/app.php`

```php
$app->register(Sanchescom\Cache\MemoryServiceProvider::class);
```

## Configuration

Put new driver in `config/cache.php` and set key and size for memory:

```php
    'memory' => [
        'driver' => 'memory',
        'key' => env('MEMORY_BLOCK_KEY', 1),
        'size' => env('MEMORY_BLOCK_SIZE', 900000),
    ],
```

#### Put data to memory in one process
```php
<?php

use Illuminate\Support\Facades\Cache;

Cache::store('memory')->put('some_key', ['value' => 'text']);
```

#### Get it from another process
```php
<?php

use Illuminate\Support\Facades\Cache;

$data = Cache::store('memory')->get('some_key');
```