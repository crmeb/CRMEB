<?php

require('../../vendor/autoload.php');

use Volc\Service\Sms;
use Volc\Models\Vod\Request\VodApplyUploadInfoRequest;
use Volc\Models\Vod\Response\VodApplyUploadInfoResponse;

$client = Sms::getInstance('');
$client->setAccessKey("your ak");
$client->setSecretKey("your sk");

// 读取文件
$filePath = "your file path";
$file = fopen($filePath, 'r');

// 检查文件是否成功打开
if ($file) {
    // 读取文件内容
    $content = fread($file, filesize($filePath));
    // 关闭文件
    fclose($file);
    // 将文件内容转换为 Base64 编码
    $base64Content = base64_encode($content);
} else {
    echo "无法打开文件";
    exit(-1);
}


$body = [
    "purpose" => 1, // 资质用途； 1.自用，2.他用
    "materialName" => "your material name", // 资质名称
    //企业信息
    "businessInfo" => [
        "businessCertificateType" => 1, // 营业证件类型
        "businessCertificate" => [  // 营业证件
            "fileType" => 1,
            "fileContent" => $base64Content,  // 文件base64
            "fileSuffix" => "your file suffix",
            "fileUrl" => "", // 文件下载url
        ],
        "businessCertificateName" => "your biz name", // 营业证件名称
        "unifiedSocialCreditIdentifier" => "your code", // 统一社会信用代码
        "businessCertificateValidityPeriodStart" => "2000-01-01", // 营业证件有效期开始
        "businessCertificateValidityPeriodEnd" => "2099-01-01", // 营业证件有效期结束
        "legalPersonName" => "your name", // 法人名称
    ],
    // 经办人信息
    "operatorPerson" => [
        "certificateType" => 0,    // 证件类型 0.身份证
        "personCertificate" => [[   // 证件信息
            "fileType" => 1,
            "fileContent" => $base64Content,  // 文件base64
            "fileSuffix" => "your file suffix",
            "fileUrl" => "", // 文件下载url
        ], [
            "fileType" => 1,
            "fileContent" => $base64Content,  // 文件base64
            "fileSuffix" => "your file suffix",
            "fileUrl" => "", // 文件下载url
        ]],
        "personName" => "your name", // 名字
        "personIDCard" => "your Id Card Code", // 证件号码
        "personMobile" => "134********", // 手机号
    ],
    // 责任人信息
    "responsiblePersonInfo" => [
        "certificateType" => 0,    // 证件类型 0.身份证
        "personCertificate" => [[   // 证件信息
            "fileType" => 1,
            "fileContent" => $base64Content,  // 文件base64
            "fileSuffix" => "your file suffix",
            "fileUrl" => "", // 文件下载url
        ], [
            "fileType" => 1,
            "fileContent" => $base64Content,  // 文件base64
            "fileSuffix" => "your file suffix",
            "fileUrl" => "", // 文件下载url
        ]],
        "personName" => "your name", // 名字
        "personIDCard" => "your Id Card Code", // 证件号码
        "personMobile" => "134********", // 手机号
    ],
    //  授权书
    "powerOfAttorney" => [[
        "fileType" => 1,
        "fileContent" => $base64Content,  // 文件base64
        "fileSuffix" => "your file suffix",
        "fileUrl" => "", // 文件下载url
    ]],
    // 其他材料
    "otherMaterials" => [[
        "fileType" => 1,
        "fileContent" => $base64Content,  // 文件base64
        "fileSuffix" => "your file suffix",
        "fileUrl" => "", // 文件下载url
    ]],
    "effectSignatures" => ["sig1", "sig2"], // 生效签名范围
];


$response = $client->applySignatureIdent(['json' => $body]);
echo $response;