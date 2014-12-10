<?php

namespace Journey\Queue;

use Journey\Daemon\DaemonControllerInterface;
use Journey\Daemon\Daemon;

class Queue implements DaemonControllerInterface
{

    // Internal registry of the queue items
    private $registry = array();

    // Early termination of the queue in seconds where 0 = never
    private $timeout = 0;

    // The daemon object
    private $daemon;

    // Cooldown between process commands
    private $coolDown = 10000;

    /**
     * Set the timeout for a number of seconds
     * @param integer $time   Number of seconds
     */
    public function setTimeout($time)
    {
        $this->timeout = $time;
    }


    /**
     * Add a new queue item
     */
    public function add($callable = null)
    {
        if ($callable instanceof QueueItem) {
            $queueItem = $callable;
        } else {
            $queueItem = new QueueItem();
            
            if (isset($callable)) {
                $queueItem->setAction($callable);
            }
        }
        $this->registry[] = $queueItem;
        
        return $queueItem;
    }



    /**
     * Semantic for add
     */
    public function push($callable = null)
    {
        return $this->add($callable);
    }


    /**
     * Semaphore to control our daemon process
     * @return boolean  true continues execution, false stops it
     */
    public function signal()
    {
        return count($this->registry);
    }


    /**
     * Handle messages from the daemon
     * @param  String $message a message from the daemon
     */
    public function notify($message)
    {
        // do nothing
    }


    /**
     * Run a this queue (as a daemon of course)
     */
    public function run()
    {
        $daemon = new Daemon($this);
    }


    /**
     * Run this queue until all the item are completed or we timeout
     */
    public function process()
    {
        foreach ($this->registry as $key => $item) {
            switch ($item->status()) {
                // The QueueItem has not run
                case 0:
                    $item->run();
                    break;

                // The QueueItem has run, but not completed
                case 1:
                    if ($item->check()) {
                        $item->callback($this);
                    }
                    break;

                // If the item has completed, remove it
                case 2:
                    unset($this->registry[$key]);
            }
        }
    }


    /**
     * Set the daemon object internally
     */
    public function setDaemon(Daemon $daemon)
    {
        $this->daemon = $daemon;
    }



    /**
     * Responsible for the speed that we repeat our process at
     */
    public function throttle()
    {
        usleep($this->coolDown);
    }
}
