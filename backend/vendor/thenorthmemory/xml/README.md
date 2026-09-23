# A wrapper of the XML parser and builder

Split from the wechatpay-php project for general usages.

[![GitHub actions](https://github.com/TheNorthMemory/xml/workflows/CI/badge.svg)](https://github.com/TheNorthMemory/xml/actions)
[![Packagist Stars](https://img.shields.io/packagist/stars/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist Downloads](https://img.shields.io/packagist/dm/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist Version](https://img.shields.io/packagist/v/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist License](https://img.shields.io/packagist/l/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)

## Install

```shell
composer require thenorthmemory/xml
```

## Usage

```php
use TheNorthMemory\Xml\Transformer;
$array = Transformer::toArray('<xml><hello>world</hello></xml>');
// print_r($array);
// Array
// (
//     [hello] => world
// )
$xml = Transformer::toXml($array);
// print_r($xml);
// <xml><hello>world</hello></xml>
$xml = <<<TencentCOSRequest
<Request>
<Operation>
 <WatermarkTemplateId>t146d70eb241c44c63b6efc1cc93ccfc5d</WatermarkTemplateId>
 <WatermarkTemplateId>t12a74d11687d444deba8a6cc52051ac27</WatermarkTemplateId>
</Operation>
</Request>
TencentCOSRequest;
$array = Transformer::toArray($xml);
// Array
// (
//     [Operation] => Array
//         (
//             [WatermarkTemplateId] => Array
//                 (
//                     [0] => t146d70eb241c44c63b6efc1cc93ccfc5d
//                     [1] => t12a74d11687d444deba8a6cc52051ac27
//                 )

//         )

// )
$xml1 = Transformer::toXml($array, true, true, 'Request');
// print_r($xml1);
// <Request>
//  <Operation>
//   <WatermarkTemplateId>
//    <item>t146d70eb241c44c63b6efc1cc93ccfc5d</item>
//    <item>t12a74d11687d444deba8a6cc52051ac27</item>
//   </WatermarkTemplateId>
//  </Operation>
// </Request>
$array['Operation']['WatermarkTemplateId'] = Transformer::wrap($array['Operation']['WatermarkTemplateId'], true, 'WatermarkTemplateId');
$xml2 = Transformer::toXml($array, true, true, 'Request');
// print_r($xml2);
// <Request>
// <Operation>
//  <WatermarkTemplateId>t146d70eb241c44c63b6efc1cc93ccfc5d</WatermarkTemplateId>
//  <WatermarkTemplateId>t12a74d11687d444deba8a6cc52051ac27</WatermarkTemplateId>
// </Operation>
// </Request>
```

## API

**`Transformer::toArray(string $xml = '<xml/>')`**

Parse the XML `string` to `array`.

**`Transformer::sanitize(string $xml = '<xml/>')`**

Sanitize the XML `string` in the [XML1.0 20081126 Character Range](https://www.w3.org/TR/2008/REC-xml-20081126/#charsets).

**`Transformer::toXml(array $data, bool $headless = true, bool $indent = false, string $root = 'xml', string $item = 'item'): string`**

Build the data `array` to XML `string`.

**`Transformer::wrap(array $data, bool $wrapped = false, string $label = 'item'): LabeledArrayIterator`**

Wrap the `array` data with a `label` and `wrapped` flag.

## License

[Apache-2.0 License](LICENSE)
