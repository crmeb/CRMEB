<?php

namespace Channel;

use Workerman\Connection\TcpConnection;

class Queue
{

    public $name = 'default';
    public $watcher = array();
    public $consumer = array();
    protected $queue = null;

    public function __construct($name)
    {
        $this->name = $name;
        $this->queue = new \SplQueue();
    }

    /**
     * @param TcpConnection $connection
     */
    public function addWatch($connection)
    {
    	if (!isset($this->watcher[$connection->id])) {
		    $this->watcher[$connection->id] = $connection;
		    $connection->watchs[] = $this->name;
	    }
    }

    /**
     * @param TcpConnection $connection
     */
    public function removeWatch($connection)
    {
        if (isset($connection->watchs) && in_array($this->name, $connection->watchs)) {
        	$idx = array_search($this->name, $connection->watchs);
            unset($connection->watchs[$idx]);
        }
        if (isset($this->watcher[$connection->id])) {
            unset($this->watcher[$connection->id]);
        }
        if (isset($this->consumer[$connection->id])) {
            unset($this->consumer[$connection->id]);
        }
    }

	/**
	 * @param TcpConnection $connection
	 */
    public function addConsumer($connection)
    {
    	if (isset($this->watcher[$connection->id]) && !isset($this->consumer[$connection->id])) {
    		$this->consumer[$connection->id] = $connection;
	    }
	    $this->dispatch();
    }

    public function enqueue($data)
    {
    	$this->queue->enqueue($data);
    	$this->dispatch();
    }

    private function dispatch()
    {
    	if ($this->queue->isEmpty() || count($this->consumer) == 0) {
    		return;
	    }

		while (!$this->queue->isEmpty()) {
    		$data = $this->queue->dequeue();
    		$idx = key($this->consumer);
    		$connection = $this->consumer[$idx];
    		unset($this->consumer[$idx]);
	        $connection->send(serialize(array('type'=>'queue', 'channel'=>$this->name, 'data' => $data)));
	        if (count($this->consumer) == 0) {
		        break;
	        }
		}
    }

    public function isEmpty()
    {
        return empty($this->watcher) && $this->queue->isEmpty();
    }

}