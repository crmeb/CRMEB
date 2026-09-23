# Rtc Streaming Cloud Server-Side Library For PHP

## Features

- RoomToken 签发
    - [x] 生成 RoomToken: client->appToken() 

- App 管理
    - [x] 创建应用: client->createApp()
    - [x] 获取应用配置信息: client->getApp()
    - [x] 更新应用配置信息: client->updateApp()
    - [x] 删除应用: client->deleteApp()

- 房间管理
    - [x] 列举房间下的所有用户: client->listUser()
    - [x] 指定一个用户踢出房间: client->kickUser()
    - [x] 停止一个房间的合流转推: client->stopMerge()
    - [x] 获取当前所有活跃的房间: client->listActiveRooms()

## Demo
- RoomToken 签发
    - [生成 RoomToken](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_create_roomToken.php)

- App 管理
    - [创建应用](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_createApp.php)
    - [获取应用配置信息](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_getApp.php)
    - [更新应用配置信息](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_updateApp.php)
    - [删除应用](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_deleteApp.php)

- 房间管理
    - [列举房间下的所有用户](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_rooms_listUser.php)
    - [指定一个用户踢出房间](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_rooms_kickUser.php)
    - [停止一个房间的合流转推](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_rooms_stopMerge.php)
    - [获取当前所有活跃的房间](https://github.com/qiniu/php-sdk/tree/master/examples/rtc/rtc_rooms_listActiveRooms.php)   