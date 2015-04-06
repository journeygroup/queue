Journey Queue
--------------

Queue is a simple, hierarchical action-test-callback queue written in php. The php thread waits however long is necessary in order to complete the entire queue (using Journey\Daemon).

## Install

Use composer to install it and load it.

## Use

The queue is made up of a registry of `QueueItem` objects. Each `QueueItem` has 3 elements:

- Action
  - `__construct(Callable)`
  - `setAction(Callable)`
- Check 
  - `unitl(Callable)`
  - `succeedWhen(Callable)`
- Callback
  - `then(Callable)`
  - `after(Callable)`

The queue first performs the action. The queue will then call the check to determine if the action has completed until it receives a truthy value. Finally the queue will call the callback after the check passes.

```php
<?php

include "vendor/autoload.php";

$queue = new Journey\Queue\Queue;

$queue->add(function () {
    // The initial action
})->succeedWhen(function () {
    // A check to see if the action succeeded
})->then(function (Queue $queue) {
    // Do some action
});

$queue->run();

```

To create hierarchy, simply add another `QueueItem` in the success callback (`then()` in the above example). `$queue->add(new QueueItem...)`

**Note:** The check methods also support a second parameter `(optional) $interval = 0` which defines how frequently each check should wait before being called again (non-blocking). An example of how this would be useful is if you are calling a request-limited API for a status and you only want to check ever 30 seconds rather than 30 times a second