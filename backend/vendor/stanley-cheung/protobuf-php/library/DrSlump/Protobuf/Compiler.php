<?php

namespace DrSlump\Protobuf;

// Load descriptor messages
require_once __DIR__ . '/Compiler/protos/descriptor.pb.php';
require_once __DIR__ . '/Compiler/protos/plugin.pb.php';
require_once __DIR__ . '/Compiler/protos/php.pb.php';
require_once __DIR__ . '/Compiler/protos/json.pb.php';

use DrSlump\Protobuf;
use google\protobuf as proto;

class Compiler
{
    /** @var bool */
    protected $verbose = false;
    /** @var array */
    protected $packages = array();
    /** @var \DrSlump\Protobuf\Compiler\CommentsParser */
    protected $comments;
    /** @var bool */
    protected $skipImported = false;
    /** @var array */
    protected $options = array();
    /** @var array */
    protected $protos = array();

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        $this->comments = new Compiler\CommentsParser();
    }

    public function stderr($str)
    {
        $str = str_replace("\n", PHP_EOL, $str);
        fputs(STDERR, $str . PHP_EOL);
    }

    public function notice($str)
    {
        if ($this->verbose) {
            $this->stderr('NOTICE: ' . $str);
        }
    }

    public function warning($str)
    {
        $this->stderr('WARNING: ' . $str);
    }

    protected function error($str)
    {
        $this->stderr('ERROR: ' . $str);
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function hasPackage($package)
    {
        return isset($this->packages[$package]);
    }

    public function getPackage($package)
    {
        return $this->packages[$package];
    }

    public function setPackage($package, $namespace)
    {
        $this->packages[$package] = $namespace;
    }

    public function getOption($option, $type = 'string')
    {
        $value = isset($this->options[$option])
                 ? $this->options[$option]
                 : null;

        switch ($type) {
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            default:
                return $value;
        }
    }

    public function camelize($name)
    {
        return preg_replace_callback(
                    '/_([a-z0-9])/i',
                    function($m){ return strtoupper($m[1]); },
                    $name
                 );
    }

    public function compile($data)
    {
        // Parse the request
        $req = new \google\protobuf\compiler\CodeGeneratorRequest($data);

        // Set default generator class
        $generator = __CLASS__ . '\PhpGenerator';

        // Reset comments parser
        $this->comments->reset();
        $parseComments = false;

        // Get plugin arguments
        if ($req->hasParameter()) {
            parse_str($req->getParameter(), $args);
            foreach ($args as $arg=>$val) {
                switch($arg){
                case 'verbose':
                    $this->verbose = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    break;
                case 'json':
                    $this->notice("Using ProtoJson generator");
                    $generator = __CLASS__ . '\JsonGenerator';
                    break;
                case 'comments':
                    $parseComments = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'protos':
                    $this->protos = $val;
                    break;
                case 'skip-imported':
                    $this->skipImported = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'options':
                    $this->options = $val;
                    break;
                default:
                    $this->warning('Skipping unknown option ' . $arg);
                }
            }
        }

        // Parse comments if we're told to do so
        if ($parseComments) {
            if (empty($this->protos)) {
                throw new \RuntimeException('Unable to port comments if .proto files are not passed as argument');
            }
            foreach ($this->protos as $fname) {
                $src = file_get_contents($fname);
                if (FALSE === $src) {
                    throw new \RuntimeException('Unable to parse file ' . $fname . ' for comments');
                }
                $this->comments->parse($src);
            }
        }

        /** @var $generator \DrSlump\Protobuf\Compiler\AbstractGenerator */
        $generator = new $generator($this);

        // Setup response object
        $resp = new \google\protobuf\compiler\CodeGeneratorResponse();

        // First iterate over all the protos to get a map of namespaces
        $this->packages = array();
        foreach($req->getProtoFileList() as $proto) {
            $package = $proto->getPackage();
            $namespace = $generator->getNamespace($proto);
            if (isset($this->packages[$package]) && $namespace !== $this->packages[$package]) {
                $this->warning("Package $package was already mapped to {$this->packages[$package]} but has now been overridden to $namespace");
            }
            $this->packages[$package] = $namespace;
            $this->notice("Mapping $package to $namespace");
        }

        // Get the list of files to generate
        $files = $req->getFileToGenerate();

        // Run each file
        foreach ($req->getProtoFileList() as $file) {
            // Only compile those given to generate, not the imported ones
            if ($this->skipImported && !in_array($file->getName(), $files)) {
                $this->notice('Skipping generation of imported file "' . $file->getName() . '"');
                continue;
            }

            $sources = $generator->generate($file);
            foreach ($sources as $source) {
                $this->notice('Generating "' . $source->getName() . '"');
                $resp->addFile($source);
            }
        }

        // Finally serialize the response object
        return $resp->serialize();
    }

    public function getComment($ident, $prefix = '')
    {
        if (!$this->comments->hasComment($ident)) {
            return null;
        }

        $comment = $this->comments->getComment($ident);
        if (0 < strlen($prefix)) {
            $comment = $prefix . str_replace("\n", "\n$prefix", $comment);
        }

        return $comment;
    }
}
