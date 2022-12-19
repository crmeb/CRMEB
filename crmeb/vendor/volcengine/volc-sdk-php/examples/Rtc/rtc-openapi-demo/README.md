# 说明
```
演示了OpenAPI鉴权SDK的使用方式
使用时请参考rtc-openapi-demo的用法或把它拷贝到自己的项目中追加所需的API
```

# 运行
## 安装composer

```angular2html
参考文档：https://getcomposer.org/doc/00-intro.md
```

## 引入volc签名包

更新本地composer.josn，引入volc签名包
```go
{
    ...
    
    "require": {
        "volcengine/volc-sdk-php": "*"
    }
    
    ...
}
```


## 获取依赖

```angular2html
$ composer update
```