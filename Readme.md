Journey Queue
--------------

Queue is a simple, hierarchical action-test-callback queue written in php. The php thread waits however long is necessary in order to complete the entire queue (using Journey\Daemon).

## Install

Use composer to install it and load it.

## Use

The queue is made up of a registry of QueueItem objects. Each QueueItem has 3 required elements:

- Action
- Check
- Callback

The queue first performs the action. The queue will then call the check to determine if the action has completed, and finally the queue will call the callback after the check passes.

```
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

To create heirarchy, simply add another QueueItem in the success callback (then() in the above example). $queue->add(new QueueItem...)
