<?php

namespace QCloud\COSSTS;

class Scope{
	var $action;
	var $bucket;
	var $region;
	var $resourcePrefix;
	var $effect = 'allow';
	function __construct($action, $bucket, $region, $resourcePrefix){
		$this->action = $action;
		$this->bucket = $bucket;
		$this->region = $region;
		$this->resourcePrefix = $resourcePrefix;
	}
	
	function set_effect($isAllow){
		if($isAllow){
			$this->effect = 'allow';
		}else{
			$this->effect = 'deny';
		}
	}
	
	function get_action(){
		if($this->action == null){
			throw new \Exception("action == null");
		}
		return $this->action;
	}
	
	function get_resource(){
		if($this->bucket == null){
			throw new \Exception("bucket == null");
		}
		if($this->resourcePrefix == null){
			throw new \Exception("resourcePrefix == null");
		}
		$index = strripos($this->bucket, '-');
		if($index < 0){
			throw new Exception("bucket is invalid: " + $this->bucket);
		}
		$appid = substr($this->bucket, $index + 1);
		if(!(strpos($this->resourcePrefix, '/') === 0)){
			$this->resourcePrefix = '/' . $this->resourcePrefix;
		}
		return 'qcs::cos:' . $this->region . ':uid/' . $appid . ':' . $this->bucket . $this->resourcePrefix;
	}
	
	function get_effect(){
		return $this->effect;
	}
}

?>