<?php

namespace Journey\Queue;

use Closure;

class QueueItem
{
    // Primary callable action to perform
    private $action;

    // Callable check
    private $check;

    // Integer Status
    // 0 = Not Run
    // 1 = Run
    // 2 = Completed
    private $status = 0;

    // Callable final action
    private $callback;

    // Time interval (seconds) to wait between checks
    private $interval;

    // Last time (epoch) this queue was checked
    private $checked;


    /**
     * Constructor for creating a new QueueItem
     */
    public function __construct($action = null)
    {
        if ($action) {
            $this->action = $action;
        }

        $this->check = function () {
            return true;
        };

        $this->callback = function () {
            return true;
        };
    }


    /**
     * Set the internal status of this QueueItem
     * @param int $status 0 = Not Run, 1 = Run, 2 = Completed
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Assign the action to be performed in the queue
     * @param  callable $callable Generally this is a closure
     * @return self               
     */
    public function setAction(callable $action)
    {
        $this->action = $action;
        return $this;
    }



    /**
     * Perform a check to determine if this callable has been set or not
     * @param  callable $check  Function that should return the boolean status of action
     * @return self
     */
    public function setCheck(callable $check, $interval = 0)
    {
        $this->interval = $interval;
        $this->check = $check;
        return $this;
    }



    /**
     * What should be executed when the check passes
     * @param  Mixed    $callback  What to call
     * @return self
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }


    /**
     * Semantic mask for the callback method
     * @param  Mixed $callback  Callable object to use as callback when the item's check passes
     * @return self           
     */
    public function then($callback)
    {
        return $this->setCallback($callback);
    }


    /**
     * Semantic mask for the callback method
     * @param  Mixed  $callback [description]
     * @return self
     */
    public function after($callback)
    {
        return $this->setCallback($callback);
    }



    /**
     * Semantic mask for setCheck
     * @param  callable $callable   Set a check for success
     * @return self
     */
    public function until(callable $check, $interval = 0)
    {
        return $this->setCheck($check, $interval);
    }


    /**
     * Semantic mask for setCheck
     * @param  callable $callable   Set a check for success
     * @return self
     */
    public function succeedWhen(callable $check, $interval = 0)
    {
        return $this->setCheck($check, $interval);
    }


    /**
     * Run this particular QueueItem
     * @return this
     */
    public function run()
    {
        $action = $this->action;
        $action();
        $this->setStatus(1);
        return $this;
    }


    /**
     * Perform the check to determine if this QueueItem was completed
     * @return boolean
     */
    public function check()
    {
        if (($this->checked + $this->interval) < time()) {
            $check = $this->check;
            $this->setStatus((($check()) ? 2:1));
            $this->checked = time();
            return ($this->status > 1);
        }
        return false;
    }


    /**
     * Get the status if this QueueItem
     * @return integer  0 = Not Run, 1 = Run, 2 = Completed
     */
    public function status()
    {
        return $this->status;
    }

    /** 
     * Execute the final callback for this queue item
     * @param  Queue    $queue The actual queue
     * @return self
     */
    public function callback(Queue $queue)
    {
        if ($this->callback instanceof QueueItem) {
            $queue->add($this->callback);
        } else if (is_callable($this->callback)) {
            $callback = $this->callback;
            $callback($queue);
        }

        return $this;
    }
}
