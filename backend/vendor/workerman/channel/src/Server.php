<?php
namespace Channel;

use Workerman\Protocols\Frame;
use Workerman\Worker;

/**
 * Channel server.
 */
class Server
{
    /**
     * Worker instance.
     * @var Worker
     */
    protected $_worker = null;

    /**
     * Queues
     * @var Queue[]
     */
    protected $_queues = array();

    private $ip;

    /**
     * Construct.
     * @param string $ip Bind ip address or unix domain socket.
     * Bind unix domain socket use 'unix:///tmp/channel.sock'
     * @param int $port Tcp port to bind, only used when listen on tcp.
     */
    public function __construct($ip = '0.0.0.0', $port = 2206)
    {
        if (strpos($ip, 'unix:') === false) {
            $worker = new Worker("frame://$ip:$port");
        } else {
            $worker = new Worker($ip);
            $worker->protocol = Frame::class;
        }
        $this->ip = $ip;
        $worker->count = 1;
        $worker->name = 'ChannelServer';
        $worker->channels = array();
        $worker->onMessage = array($this, 'onMessage') ;
        $worker->onClose = array($this, 'onClose');
        $this->_worker = $worker;
    }

    /**
     * onClose
     * @return void
     */
    public function onClose($connection)
    {
        if (!empty($connection->channels)) {
	        foreach ($connection->channels as $channel) {
		        unset($this->_worker->channels[$channel][$connection->id]);
		        if (empty($this->_worker->channels[$channel])) {
			        unset($this->_worker->channels[$channel]);
		        }
	        }
        }

        if (!empty($connection->watchs)) {
        	foreach ($connection->watchs as $channel) {
        		if (isset($this->_queues[$channel])) {
        			$this->_queues[$channel]->removeWatch($connection);
        			if ($this->_queues[$channel]->isEmpty()) {
        				unset($this->_queues[$channel]);
			        }
		        }
	        }
        }
    }

    /**
     * onMessage.
     * @param \Workerman\Connection\TcpConnection $connection
     * @param string $data
     */
    public function onMessage($connection, $data)
    {
        if(!$data)
        {
            return;
        }
        $worker = $this->_worker;
        $data = unserialize($data);
        $type = $data['type'];
        switch($type)
        {
            case 'subscribe':
                foreach($data['channels'] as $channel)
                {
                    $connection->channels[$channel] = $channel;
                    $worker->channels[$channel][$connection->id] = $connection;
                }
                break;
            case 'unsubscribe':
                foreach($data['channels'] as $channel) {
                    if (isset($connection->channels[$channel])) {
                        unset($connection->channels[$channel]);
                    }
                    if (isset($worker->channels[$channel][$connection->id])) {
                        unset($worker->channels[$channel][$connection->id]);
                        if (empty($worker->channels[$channel])) {
                            unset($worker->channels[$channel]);
                        }
                    }
                }
                break;
            case 'publish':
                foreach ($data['channels'] as $channel) {
                    if (empty($worker->channels[$channel])) {
                        continue;
                    }
                    $buffer = serialize(array('type' => 'event', 'channel' => $channel, 'data' => $data['data']));
                    foreach ($worker->channels[$channel] as $connection) {
                        $connection->send($buffer);
                    }
                }
                break;
            case 'publishLoop':
                //choose one subscriber from the list
                foreach ($data['channels'] as $channel) {
                    if (empty($worker->channels[$channel])) {
                        continue;
                    }
                    $buffer = serialize(array('type' => 'event', 'channel' => $channel, 'data' => $data['data']));

                    //这是要点，每次取出一个元素，如果取不到，说明已经到最后，重置到第一个
                    $connection = next($worker->channels[$channel]);
                    if( $connection == false ){
                        $connection = reset($worker->channels[$channel]);
                    }
                    $connection->send($buffer);
                }
                break;
            case 'watch':
            	foreach ($data['channels'] as $channel) {
		            $this->getQueue($channel)->addWatch($connection);
	            }
                break;
            case 'unwatch':
	            foreach ($data['channels'] as $channel) {
		            if (isset($this->_queues[$channel])) {
			            $this->_queues[$channel]->removeWatch($connection);
			            if ($this->_queues[$channel]->isEmpty()) {
				            unset($this->_queues[$channel]);
			            }
		            }
	            }
                break;
            case 'enqueue':
            	foreach ($data['channels'] as $channel) {
		            $this->getQueue($channel)->enqueue($data['data']);
	            }
                break;
            case 'reserve':
				if (isset($connection->watchs)) {
					foreach ($connection->watchs as $channel) {
						if (isset($this->_queues[$channel])) {
							$this->_queues[$channel]->addConsumer($connection);
						}
					}
				}
                break;
        }
    }

    private function getQueue($channel)
    {
        if (isset($this->_queues[$channel])) {
            return $this->_queues[$channel];
        }
        return ($this->_queues[$channel] = new Queue($channel));
    }

}
