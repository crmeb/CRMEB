# thinkphp5 常用的一些扩展类库

> 更新完善中

> 以下类库都在`\\think\\helper`命名空间下

## Str
> 字符串操作

```
// 检查字符串中是否包含某些字符串
Str::contains($haystack, $needles)

// 检查字符串是否以某些字符串结尾
Str::endsWith($haystack, $needles)

// 获取指定长度的随机字母数字组合的字符串
Str::random($length = 16)

// 字符串转小写
Str::lower($value)

// 字符串转大写
Str::upper($value)

// 获取字符串的长度
Str::length($value)

// 截取字符串
Str::substr($string, $start, $length = null)

```

## Hash
> 创建密码的哈希

```
// 创建
Hash::make($value, $type = null, array $options = [])

// 检查
Hash::check($value, $hashedValue, $type = null, array $options = [])

```

## Time
> 时间戳操作

```
// 今日开始和结束的时间戳
Time::today();

// 昨日开始和结束的时间戳
Time::yesterday();

// 本周开始和结束的时间戳
Time::week();

// 上周开始和结束的时间戳
Time::lastWeek();

// 本月开始和结束的时间戳
Time::month();

// 上月开始和结束的时间戳
Time::lastMonth();

// 今年开始和结束的时间戳
Time::year();

// 去年开始和结束的时间戳
Time::lastYear();

// 获取7天前零点到现在的时间戳
Time::dayToNow(7)

// 获取7天前零点到昨日结束的时间戳
Time::dayToNow(7, true)

// 获取7天前的时间戳
Time::daysAgo(7)

//  获取7天后的时间戳
Time::daysAfter(7)

// 天数转换成秒数
Time::daysToSecond(5)

// 周数转换成秒数
Time::weekToSecond(5)

```