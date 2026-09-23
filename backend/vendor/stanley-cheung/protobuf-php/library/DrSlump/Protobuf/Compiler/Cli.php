<?php

namespace DrSlump\Protobuf\Compiler;

require_once 'Console/CommandLine.php';

use DrSlump\Protobuf;

class Cli
{
    public static function run($pluginExecutable)
    {
        $stdin = '';

        // PHP doesn't implement non-blocking stdin on Windows
        // https://bugs.php.net/bug.php?id=34972
        $isWin = 'WIN' === strtoupper(substr(PHP_OS, 0, 3));
        if (!$isWin) {

            // Open STDIN in non-blocking mode
            stream_set_blocking(STDIN, FALSE);

            // Loop until STDIN is closed or we've waited too long for data
            $cnt = 0;
            while (!feof(STDIN) && $cnt++ < 10) {
                // give protoc some time to feed the data
                usleep(10000);
                // read the bytes
                $bytes = fread(STDIN, 1024);
                if (strlen($bytes)) {
                    $cnt = 0;
                    $stdin .= $bytes;
                }
            }

        // If on windows and no arguments were given
        } else if ($_SERVER['argc'] < 2) {
            $stdin = fread(STDIN, 1024 * 1024);
        }


        // If no input was given we launch protoc from here
        if (0 === strlen($stdin)) {
            if ($isWin) {
                $pluginExecutable .= '.bat';
            }
            self::runProtoc($pluginExecutable);
            exit(0);
        }

        // We have data from stdin so compile it
        try {
            // Create a compiler interface
            $comp = new Protobuf\Compiler(true);
            echo $comp->compile($stdin);
            exit(0);
        } catch(\Exception $e) {
            fputs(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
            fputs(STDERR, $e->getTraceAsString() . PHP_EOL);
            exit(255);
        }
    }

    public static function runProtoc($pluginExecutable)
    {
        $result = self::parseArguments();

        $protocBin = $result->options['protoc'];

        // Check if protoc is available
        exec("$protocBin --version", $output, $return);

        if (0 !== $return && 1 !== $return) {
            fputs(STDERR, "ERROR: Unable to find the protoc command.". PHP_EOL);
            fputs(STDERR, "       Please make sure it's installed and available in the path." . PHP_EOL);
            exit(1);
        }

        if (!preg_match('/[0-9\.]+/', $output[0], $m)) {
            fputs(STDERR, "ERROR: Unable to get protoc command version.". PHP_EOL);
            fputs(STDERR, "       Please make sure it's installed and available in the path." . PHP_EOL);
            exit(1);
        }

        if (version_compare($m[0], '2.3.0') < 0) {
            fputs(STDERR, "ERROR: The protoc command in your system is too old." . PHP_EOL);
            fputs(STDERR, "       Minimum version required is 2.3.0 but found {$m[0]}." . PHP_EOL);
            exit(1);
        }

        $cmd[] = $protocBin;
        $cmd[] = '--plugin=protoc-gen-php=' . escapeshellarg($pluginExecutable);

        // Include paths
        $cmd[] = '--proto_path=' . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'protos');
        if (!empty($result->options['include'])) {
            foreach($result->options['include'] as $include) {
                $include = realpath($include);
                $cmd[] = '--proto_path=' . escapeshellarg($include);
            }
        }

        // Convert proto files to absolute paths
        $protos = array();
        foreach ($result->args['protos'] as $proto) {
            $realpath = realpath($proto);
            if (FALSE === $realpath) {
                fputs(STDERR, "ERROR: File '$proto' does not exists");
                exit(1);
            }

            $protos[] = $realpath;
        }

        // Protoc will pass custom arguments to the plugin if they are given
        // before a colon character. ie: --php_out="foo=bar:/path/to/plugin"
        // We make use of it to pass arguments encoded as an URI query string

        $args = array();
        if ($result->options['comments']) {
            $args['comments'] = 1;
            // Protos are only needed for comments right now
            $args['protos'] = $protos;
        }
        if ($result->options['verbose']) {
            $args['verbose'] = 1;
        }
        if ($result->options['json']) {
            $args['json'] = 1;
        }
        if ($result->options['skipImported']) {
            $args['skip-imported'] = 1;
        }
        if ($result->options['define']) {
            $args['options'] = array();
            foreach($result->options['define'] as $define) {
                $parts = explode('=', $define);
                $parts = array_filter(array_map('trim', $parts));
                if (count($parts) === 1) {
                    $parts[1] = 1;
                }
                $args['options'][$parts[0]] = $parts[1];
            }
        }
        if ($result->options['insertions']) {
            $args['options']['insertions'] = 1;
        }

        $cmd[] = '--php_out=' .
                 escapeshellarg(
                     http_build_query($args, '', '&') .
                     ':' .
                     $result->options['out']
                 );

        // Add the chosen proto files to generate
        foreach ($protos as $proto) {
            $cmd[] = escapeshellarg($proto);
        }

        $cmdStr = implode(' ', $cmd);

        // Run command with stderr redirected to stdout
        passthru($cmdStr . ' 2>&1', $return);

        if ($return !== 0) {
            fputs(STDERR, PHP_EOL);
            fputs(STDERR, 'ERROR: protoc exited with an error (' . $return . ') when executed with: ' . PHP_EOL);
            fputs(STDERR, '  ' . implode(" \\\n    ", $cmd) . PHP_EOL);
            exit($return);
        }
    }


    public static function parseArguments()
    {
        $main = new \Console_CommandLine();

        $main->addOption('out', array(
            'short_name'    => '-o',
            'long_name'     => '--out',
            'action'        => 'StoreString',
            'description'   => 'destination directory for generated files',
            'default'       => './',
        ));

        $main->addOption('include', array(
            'short_name'    => '-i',
            'long_name'     => '--include',
            'action'        => 'StoreArray',
            'description'   => 'define an include path (can be repeated)',
            'multiple'      => true,
        ));


        $main->addOption('json', array(
            'short_name'    => '-j',
            'long_name'     => '--json',
            'action'        => 'StoreTrue',
            'description'   => 'turn on ProtoJson Javascript file generation',
        ));

        $main->addOption('protoc', array(
            'long_name'     => '--protoc',
            'action'        => 'StoreString',
            'default'       => 'protoc',
            'description'   => 'protoc compiler executable path',
        ));

        $main->addOption('skipImported', array(
            'long_name'     => '--skip-imported',
            'action'        => 'StoreTrue',
            'default'       => false,
            'description'   => 'do not generate imported proto files',
        ));

        $main->addOption('comments', array(
            'long_name'     => '--comments',
            'action'        => 'StoreTrue',
            'description'   => 'port .proto comments to generated code',
        ));

        $main->addOption('insertions', array(
            'long_name'     => '--insertions',
            'action'        => 'StoreTrue',
            'description'   => 'generate @@protoc insertion points',
        ));

        $main->addOption('define', array(
            'short_name'    => '-D',
            'long_name'     => '--define',
            'action'        => 'StoreArray',
            'multiple'      => true,
            'description'   => 'define a generator option (ie: -Dmultifile -Dsuffix=pb.php)',
        ));

        $main->addOption('verbose', array(
            'short_name'    => '-v',
            'long_name'     => '--verbose',
            'action'        => 'StoreTrue',
            'description'   => 'turn on verbose output',
        ));


        $main->addArgument('protos', array(
            'multiple'      => true,
            'description'   => 'proto files',
        ));

        try {
            echo 'Protobuf-PHP ' . Protobuf::VERSION . ' by Ivan -DrSlump- Montes' . PHP_EOL . PHP_EOL;
            $result = $main->parse();
            return $result;
        } catch (\Exception $e) {
            $main->displayError($e->getMessage());
            exit(1);
        }
    }

}
