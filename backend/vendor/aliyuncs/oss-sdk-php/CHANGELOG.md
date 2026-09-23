# ChangeLog - Aliyun OSS SDK for PHP

## v2.7.2 / 2024-10-28
* Added: presign supports response-* parameters
* Added: forcePathStyle option.

## v2.7.1 / 2024-02-28
* Fixed: fix deprecated

## v2.7.0 / 2024-02-02
* Added: support signature version 4.
* Added: support checkObjectEndcoding option.
* Added: support strictObjectName option.
* Added: support filePathCompatible option.
* Added: support path style.
* Added: support environment variables credentials provider.
* Update: add filed for some api.
* Fixed: fix some bugs.

## v2.6.0 / 2022-08-03
* Added: support credentials provider.
* Fixed: compatible with swoole curl handler.
* Added: support more bucket stat info.

## v2.5.0 / 2022-05-13
* Added: support bucket transfer acceleration.
* Added: support bucket cname token.
* Added: support listobjectsV2.

## v2.4.3 / 2021-08-25
* Fixed: integer overflow in PHP5.x.

## v2.4.2 / 2021-06-04
* Compatible with PHP8.
* Fixed: compatible with PHP5.4.
* Fixed: the signature is incorrect in some scenarios
* Update: change $requestUrl from a member variable to a local variable.

## v2.4.1 / 2020-09-29
* Fixed: the getBucketPolicy bug.


## v2.4.0 / 2020-08-31

* Added: disable Expect: 100-continue
* Added: support getBucketInfo
* Added: support getBucketStat
* Added: support bucket policy
* Added: support bucket encryption
* Added: support bucket tagging
* Added: support bucket  worm
* Added: support versioning
* Added: support request payment
* Added: support object tagging
* Added: support code archive
* Added: support process object 
* Added: support traffic limit paramter 
* Added: support upload object from file handle
* Added: support getSimplifiedObjectMeta
* Fixed: the object name can not be '0' stirng.
* Update: endpoint validity check
* Update: add new pre-signed url api


## v2.3.1 / 2019-01-15

* translate chinese comments into english
* Added: endpoint validity check

## v2.3.0 / 2018-01-05

* Fixed: putObject support creating empty files
* Fixed: createBucket support IA/Archive
* Added: support restoreObject
* Added: support the Symlink feature
* Added: support getBucketLocation
* Added: support getBucketMeta
* Added: support proxy server Proxy

## v2.2.4 / 2017-04-25

* Fixed getObject to local file bug

## v2.2.3 / 2017-04-14

* Fixed md5 check

## v2.2.2 / 2017-01-18

* Resolve to run the connection number and memory bug on php7

## v2.2.1 / 2016-12-01

* No HTTP curl is allowed to automatically populate accept-encoding

## v2.2.0 / 2016-11-22

* Fixed PutObject/CompleteMultipartUpload return values(#26)

## v2.1.0 / 2016-11-12

* Added[RTMP](https://help.aliyun.com/document_detail/44297.html)interface
* Add support[image service](https://help.aliyun.com/document_detail/44686.html)

## v2.0.7 / 2016-06-17

* Support append object

## v2.0.6

* Trim access key id/secret and endpoint
* Refine tests and setup travis CI

## v2.0.5

* Added Add/Delete/Get BucketCname interface

## v2.0.4

* Added Put/Get Object Acl interface

## v2.0.3

* Fixing the constants in Util is defined in a PHP version that is less than 5.6.

## v2.0.2

* The problem of content-type cannot be specified when restoring multipart uploads

## v2.0.1

* Increase the ListObjects/ListMultipartUploads special characters
* Provides the interface to get the details of the OssException


## 2015.11.25

* **Large version upgrade, no longer compatible with previous interface, new version has made great improvements to ease of use, suggesting that users migrate to a new version.**

## Modify the content

* PHP 5.2 is no longer supported

### Add the cotent

* Introduce namespace
* Interface naming and modification, using hump naming
* The interface is modified, and the common parameters are extracted from the Options parameter.
* The interface returns the result modification, processing the return result, and the user can directly get the data structure easily processed　
* OssClient's constructor changes
* The Endpoint address that support CNAME and IP formats
* Rearrange the sample file organization structure and use function to organize the function points
* Add an interface that sets the connection timeout and requests timeout
* Remove the outdated interface associated with the Object Group
* The message in the OssException is changed to English

### Repair problem

* The object name is not complete
