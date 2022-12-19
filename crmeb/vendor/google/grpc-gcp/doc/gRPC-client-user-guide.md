# Instructions for create a gRPC client for google cloud services

## Overview

This instruction includes a step by step guide for creating a gRPC 
client to test the google cloud service from an empty linux 
VM, using GCE ubuntu 16.04 TLS instance.

The main steps are followed as steps below: 

- Environment prerequisite
- Install protobuf plugin and gRPC-PHP/protobuf extension
- Generate client API from .proto files
- Create the client and send/receive RPC.

## Environment Prerequisite

**Linux**
```sh
$ [sudo] apt-get install build-essential autoconf libtool pkg-config zip unzip zlib1g-dev
```
**PHP**
* `php` 5.5 or above, 7.0 or above
* `pecl`
* `composer`
```sh
$ [sudo] apt-get install php php-dev
$ curl -sS https://getcomposer.org/installer | php
$ [sudo] mv composer.phar /usr/local/bin/composer
```

## Install protobuf plugin and gRPC-PHP/protobuf extension
`grpc_php_plugin` is used to generate client API from `*.proto `files. Currently,
The only way to install `grpc_php_plugin` is to build from the gRPC source.

**Install protobuf, gRPC, which will install the plugin**
```sh
$ git clone -b $(curl -L https://grpc.io/release) https://github.com/grpc/grpc
$ cd grpc
$ git submodule update --init
# install protobuf
$ cd third_party/protobuf
$ ./autogen.sh && ./configure && make -j8
$ [sudo] make install
$ [sudo] ldconfig
# install gRPC
$ cd ../..
$ make -j8
$ [sudo] make install
```
It will generate `grpc_php_plugin` under `/usr/local/bin`.


**Install gRPC-PHP extension**
```sh
$ [sudo] pecl install protobuf
$ [sudo] pecl install grpc
```
It will generate `protobuf.so` and `grpc.so` under PHP's extension directory.
Note gRPC-PHP extension installed by pecl doesn't work on RHEL6 system.

## Generate client API from .proto files 
The common way to generate the client API is to use `grpc_php_plugin` directly.
Since the plugin won't find the dependency by itself. It works if all your
service proto files and dependent proto files are inside one directory. The 
command looks like:
```sh
$ mkdir $HOME/project
$ protoc --proto_path=./ --php_out=$HOME/project \  
--grpc_out=$HOME/project \
--plugin=protoc-gen-grpc=./bins/opt/grpc_php_plugin \
path/to/your/proto_dependency_directory1/*.proto \
path/to/your/proto_dependency_directory2/*.proto \
path/to/your/proto_directory/*.proto

```

Take `Firestore` service under [googleapis github repo](https://github.com/googleapis/googleapis)
for example. The proto files required for generating client API are
```
google/api/annotations.proto
google/api/http.proto
google/api/httpbody.proto
google/longrunning/operations.proto
google/rpc/code.proto
google/rpc/error_details.proto
google/rpc/status.proto
google/type/latlng.proto
google/firestore/v1beta1/firestore.proto
google/firestore/v1beta1/common.proto
google/firestore/v1beta1/query.proto
google/firestore/v1beta1/write.proto
google/firestore/v1beta1/document.proto
```
Thus the command looks like:
```sh
$ protoc --proto_path=googleapis --plugin=protoc-gen-grpc=`which grpc_php_plugin` \
--php_out=./ --grpc_out=./ google/api/annotations.proto google/api/http.proto \
google/api/httpbody.proto google/longrunning/operations.proto google/rpc/code.proto \
google/rpc/error_details.proto google/rpc/status.proto google/type/latlng.proto \
google/firestore/v1beta1/firestore.proto google/firestore/v1beta1/common.proto \
google/firestore/v1beta1/query.proto google/firestore/v1beta1/write.proto \
google/firestore/v1beta1/document.proto
```

Since most of cloud services already publish proto files under 
[googleapis github repo](https://github.com/googleapis/googleapis),
you can use it's Makefile to generate the client API.
The `Makefile` will help you generate the client API as
well as find the dependencies. The command will simply be:
```sh
$ cd $HOME
$ mkdir project
$ git clone https://github.com/googleapis/googleapis.git
$ cd googleapis
$ make LANGUAGE=php OUTPUT=$HOME/project
# (It's okay if you see error like Please add 'syntax = "proto3";' 
# to the top of your .proto file.)
```
The client API library is generated under `$HOME/project`.
Take [`Firestore`](https://github.com/googleapis/googleapis/blob/master/google/firestore/v1beta1/firestore.proto)
as example, the Client API is under 
`project/Google/Cloud/Firestore/V1beta1/FirestoreClient.php` depends on your 
package name inside .proto file. An easy way to find your client is 
```sh
$ find ./ -name [service_name]Client.php
```

## Create the client and send/receive RPC.
Now it's time to use the client API to send and receive RPCs.
```sh
$ cd $HOME/project
```
**Install gRPC-PHP composer library**
```sh
$ vim composer.json
######## you need to change the path and service namespace.
{
    "require": {
        "google/cloud": "^0.52.1"
    },
    "autoload": {
        "psr-4": {
            "FireStore\\": "src/",
            "Google\\Cloud\\Firestore\\V1beta1\\": "Google/Cloud/Firestore/V1beta1/"
        }
    }
}
########
$ composer install
```
**Set credentials file**
``` sh
$ vim $HOME/key.json
## Paste you credential file downloaded from your cloud project
## which you can find in APIs&Services => credentials => create credentials
## => Service account key => your credentials
$ export GOOGLE_APPLICATION_CREDENTIALS=$HOME/key.json
```

**Implement Service Client**
Take a unary-unary RPC `listDocument` from `FirestoreClient` as example.
Create a file name `ListDocumentClient.php`.
- import library
```
require_once __DIR__ . '/vendor/autoload.php';
use Google\Cloud\Firestore\V1beta1\FirestoreClient;
use Google\Cloud\Firestore\V1beta1\ListDocumentsRequest;
use Google\Auth\ApplicationDefaultCredentials;
```
- Google Auth
```
$host = "firestore.googleapis.com";
$credentials = \Grpc\ChannelCredentials::createSsl();
// WARNING: the environment variable "GOOGLE_APPLICATION_CREDENTIALS" needs to be set
$auth = ApplicationDefaultCredentials::getCredentials();
$opts = [
    'credentials' => $credentials,
    'update_metadata' => $auth->getUpdateMetadataFunc(),
]
```
- Create Client
```
$firestoreClient = new FirestoreClient($host, $opts);
```
- Make and receive RPC call
```
$argument = new ListDocumentsRequest();
$project_id = xxxxxxx;
$argument->setParent("projects/$project_id/databases/(default)/documents");
list($Response, $error) = $firestoreClient->ListDocuments($argument)->wait();
```
- print RPC response
```
$documents = $Response->getDocuments();
$index = 0;
foreach($documents as $document) {
        $index++;
        $name = $document->getName();
        echo "=> Document $index: $name\n";
        $fields = $document->getFields();
        foreach ($fields as $name => $value) {
                echo "$name => ".$value->getStringValue()."\n";
        }
}
```

- run the script
```sh
$ php -d extension=grpc.so -d extension=protobuf.so ListDocumentClient.php
```

For different kinds of RPC(unary-unary, unary-stream, stream-unary, stream-stream),
please check [grpc.io PHP part](https://grpc.io/docs/tutorials/basic/php.html#calling-service-methods)
for reference.


