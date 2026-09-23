# SMS Server-Side Library For PHP

## Features

- 签名管理
    - [x] 创建签名: client->createSignature() 
    - [x] 列出签名: client->checkSignature() 
    - [x] 查询单个签名: client->checkSingleSignature() 
    - [x] 编辑签名: client->updateSignature() 
    - [x] 删除签名: client->deleteSignature() 

- 模板管理
    - [x] 创建模板: client->createTemplate()
    - [x] 列出模板: client->queryTemplate()
    - [x] 查询单个模板: client->querySingleTemplate()
    - [x] 编辑模板: client->updateTemplate()
    - [x] 删除模板: client->deleteTemplate()

- 发送短信
    - [x] 发送短信: client->sendMessage()

- 查询发送记录
    - [x] 查询发送记录: client->querySendSms()   

## Demo

- 签名管理
    - [创建签名](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_create_signature.php) 
    - [列出签名](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_query_signature.php) 
    - [查询单个签名](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_query_single_signature.php) 
    - [编辑签名](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_edit_signature.php) 
    - [删除签名](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_delete_signature.php) 

- 模板管理
    - [创建模板](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_create_template.php)
    - [列出模板](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_query_template.php)
    - [查询单个模板](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_query_single_template.php)
    - [编辑模板](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_edit_template.php)
    - [删除模板](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_delete_template.php)

- 发送短信
    - [发送短信](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_send_message.php)    

- 查询发送记录
    - [查询发送记录](https://github.com/qiniu/php-sdk/tree/master/examples/sms/sms_query_send_sms.php)
