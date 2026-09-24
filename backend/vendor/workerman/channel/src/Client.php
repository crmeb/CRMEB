<?php
namespace Channel;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Timer;
use Workerman\Protocols\Frame;

/**
 * Channel/Client
 */
class Client
{
    /**
     * onMessage.
     * @var callback
     */
    public static $onMessage = null;

    /**
     * onConnect
     * @var callback
     */
    public static $onConnect = null;

    /**
     * onClose
     * @var callback
     */
    public static $onClose = null;

    /**
     * Connction to channel server.
     * @var \Workerman\Connection\TcpConnection
     */
    protected static $_remoteConnection = null;

    /**
     * Channel server ip.
     * @var string
     */
    protected static $_remoteIp = null;

    /**
     * Channel server port.
     * @var int
     */
    protected static $_remotePort = null;

    /**
     * Reconnect timer.
     * @var Timer
     */
    protected static $_reconnectTimer = null;

    /**
     * Ping timer.
     * @var Timer
     */
    protected static $_pingTimer = null;

    /**
     * All event callback.
     * @var array
     */
    protected static $_events = array();

    /**
     * All queue callback.
     * @var callable
     */
    protected static $_queues = array();

    /**
     * @var bool
     */
    protected static $_isWorkermanEnv = true;

    /**
     * Ping interval.
     * @var int
     */
    public static $pingInterval = 55;

    /**
     * Connect to channel server
     * @param string $ip Channel server ip address or unix domain socket address
     * Ip like (TCP): 192.168.1.100
     * Unix domain socket like: unix:///tmp/workerman-channel.sock
     * @param int $port Port to connect when use tcp
     */
    public static function connect($ip = '127.0.0.1', $port = 2206)
    {
        if (self::$_remoteConnection) {
            return;
        }

        self::$_remoteIp = $ip;
        self::$_remotePort = $port;

        if (!in_array(PHP_SAPI, ['cli', 'micro']) || !class_exists('Workerman\Worker', false)) {
            self::$_isWorkermanEnv = false;
        }

        // For workerman environment.
        if (self::$_isWorkermanEnv) {
            if (strpos($ip, 'unix://') === false) {
                $conn = new AsyncTcpConnection('frame://' . self::$_remoteIp . ':' . self::$_remotePort);
            } else {
                $conn = new AsyncTcpConnection($ip);
                $conn->protocol = Frame::class;
            }

            $conn->onClose = [self::class, 'onRemoteClose'];
            $conn->onConnect = [self::class, 'onRemoteConnect'];
            $conn->onMessage = [self::class , 'onRemoteMessage'];
            $conn->connect();

            if (empty(self::$_pingTimer)) {
                self::$_pingTimer = Timer::add(self::$pingInterval, 'Channel\Client::ping');
            }
            // Not workerman environment.
        } else {
            $remote = strpos($ip, 'unix://') === false ? 'tcp://'.self::$_remoteIp.':'.self::$_remotePort : $ip;
            $conn = stream_socket_client($remote, $code, $message, 5);
            if (!$conn) {
                throw new \Exception($message);
            }
        }

        self::$_remoteConnection = $conn;
    }

    /**
     * onRemoteMessage.
     * @param \Workerman\Connection\TcpConnection $connection
     * @param string $data
     * @throws \Exception
     */
    public static function onRemoteMessage($connection, $data)
    {
        $data = unserialize($data);
        $type = $data['type'];
        $event = $data['channel'];
        $event_data = $data['data'];

        $callback = null;

        if ($type == 'event') {
	        if (!empty(self::$_events[$event])) {
		        call_user_func(self::$_events[$event], $event_data);
	        } elseif (!empty(Client::$onMessage)) {
		        call_user_func(Client::$onMessage, $event, $event_data);
	        } else {
		        throw new \Exception("event:$event have not callback");
	        }
        } else {
	        if (isset(self::$_queues[$event])) {
		        call_user_func(self::$_queues[$event], $event_data);
	        } else {
		        throw new \Exception("queue:$event have not callback");
	        }
        }
    }

    /**
     * Ping.
     * @return void
     */
    public static function ping()
    {
        if(self::$_remoteConnection)
        {
            self::$_remoteConnection->send('');
        }
    }

    /**
     * onRemoteClose.
     * @return void
     */
    public static function onRemoteClose()
    {
        Timer::add(0.5, function() {
            echo "Waring channel connection closed and try to reconnect\n";
        }, array(), false);
        self::$_remoteConnection = null;
        self::clearTimer();
        self::$_reconnectTimer = Timer::add(1, 'Channel\Client::connect', array(self::$_remoteIp, self::$_remotePort));
        if (self::$onClose) {
            call_user_func(Client::$onClose);
        }
    }

    /**
     * onRemoteConnect.
     * @return void
     */
    public static function onRemoteConnect()
    {
        $all_event_names = array_keys(self::$_events);
        if($all_event_names)
        {
            self::subscribe($all_event_names);
        }
        self::clearTimer();

        if (self::$onConnect) {
            call_user_func(Client::$onConnect);
        }
    }

    /**
     * clearTimer.
     * @return void
     */
    public static function clearTimer()
    {
        if (!self::$_isWorkermanEnv) {
            throw new \Exception('Channel\\Client not support clearTimer method when it is not in the workerman environment.');
        }
        if(self::$_reconnectTimer)
        {
            Timer::del(self::$_reconnectTimer);
            self::$_reconnectTimer = null;
        }
    }

    /**
     * On.
     * @param string $event
     * @param callback $callback
     * @throws \Exception
     */
    public static function on($event, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('callback is not callable for event.');
        }
        self::$_events[$event] = $callback;
        self::subscribe($event);
    }

    /**
     * Subscribe.
     * @param string $events
     * @return void
     */
    public static function subscribe($events)
    {
        $events = (array)$events;
        self::send(array('type' => 'subscribe', 'channels'=>$events));
        foreach ($events as $event) {
            if(!isset(self::$_events[$event])) {
                self::$_events[$event] = null;
            }
        }
    }

    /**
     * Unsubscribe.
     * @param string $events
     * @return void
     */
    public static function unsubscribe($events)
    {
        $events = (array)$events;
        self::send(array('type' => 'unsubscribe', 'channels'=>$events));
        foreach($events as $event) {
            unset(self::$_events[$event]);
        }
    }

    /**
     * Publish.
     * @param string $events
     * @param mixed $data
     */
    public static function publish($events, $data , $is_loop = false)
    {
        $type = $is_loop == true ? 'publishLoop' : 'publish';
        self::sendAnyway(array('type' => $type, 'channels' => (array)$events, 'data' => $data));
    }
    
    /**
     * Watch a channel of queue
     * @param string|array $channels
     * @param callable $callback
     * @param boolean $autoReserve Auto reserve after callback finished.
     * But sometime you may don't want reserve immediately, or in some asynchronous job,
     * you want reserve in finished callback, so you should set $autoReserve to false
     * and call Client::reserve() after watch() and in finish callback manually.
     * @throws \Exception
     */
    public static function watch($channels, $callback, $autoReserve=true)
    {
        if (!is_callable($callback)) {
            throw new \Exception('callback is not callable for watch.');
        }

        if ($autoReserve) {
        	$callback = static function($data) use ($callback) {
		        try {
			        call_user_func($callback, $data);
		        } catch (\Exception $e) {
			        throw $e;
		        } catch (\Error $e) {
			        throw $e;
		        } finally {
			        self::reserve();
		        }
	        };
        }

	    $channels = (array)$channels;
        self::send(array('type' => 'watch', 'channels'=>$channels));

        foreach ($channels as $channel) {
        	self::$_queues[$channel] = $callback;
        }

        if ($autoReserve) {
	        self::reserve();
        }
    }

    /**
     * Unwatch a channel of queue
     * @param string $channel
     * @throws \Exception
     */
    public static function unwatch($channels)
    {
	    $channels = (array)$channels;
        self::send(array('type' => 'unwatch', 'channels'=>$channels));
        foreach ($channels as $channel) {
	        if (isset(self::$_queues[$channel])) {
		        unset(self::$_queues[$channel]);
	        }
        }
    }

	/**
	 * Put data to queue
	 * @param string|array $channels
	 * @param mixed $data
	 * @throws \Exception
	 */
    public static function enqueue($channels, $data)
    {
        self::sendAnyway(array('type' => 'enqueue', 'channels' => (array)$channels, 'data' => $data));
    }

	/**
	 * Start reserve queue manual
	 * @throws \Exception
	 */
    public static function reserve()
    {
	    self::send(array('type' => 'reserve'));
    }

    /**
     * Send through workerman environment
     * @param $data
     * @throws \Exception
     */
    protected static function send($data)
    {
        if (!self::$_isWorkermanEnv) {
            throw new \Exception("Channel\\Client not support {$data['type']} method when it is not in the workerman environment.");
        }
        self::connect(self::$_remoteIp, self::$_remotePort);
        self::$_remoteConnection->send(serialize($data));
    }

    /**
     * Send from any environment
     * @param $data
     * @throws \Exception
     */
    protected static function sendAnyway($data)
    {
        self::connect(self::$_remoteIp, self::$_remotePort);
        $body = serialize($data);
        if (self::$_isWorkermanEnv) {
            self::$_remoteConnection->send($body);
        } else {
            $buffer = pack('N', 4+strlen($body)) . $body;
            fwrite(self::$_remoteConnection, $buffer);
        }
    }

}
