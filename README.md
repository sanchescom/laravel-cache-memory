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

## About memory limits
Garbage collection (by removing expired items) will be performed when the cache is near the size limit.
If the garbage collection fails to reduce the size of the cache below the size limit,
then the cache will be invalidated and the underlying memory segment is marked for deletion.

Running out of memory will generate a warning or a notice in your logs, no matter if it is resolved by
a garbage collection or by segment deletion.

Note: **items that are stored as "forever" may be removed when the cache reaches its size limit**.

### Recreating the memory block
When recreating the memory block, the newest size limit defined in the Laravel config file will be used.

### Manually marking the memory segment for deletion
There are use cases to this, such as wanting to refresh the memory block now instead of waiting for 
another "out of memory" event. In this case, you may do the following:

```php
// the deletion will be managed by the OS kernel , and will happen at a future time
Cache::store('memory')->getStore()->requestDeletion();
```

This usage will not trigger any warnings or notices since this is an action taken deliberately.
