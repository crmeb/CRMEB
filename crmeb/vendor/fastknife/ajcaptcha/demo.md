### 范例

详情请查看test目录的PHP源码

#### 配置说明

```php
return [
    'font_file' => '', //自定义字体包路径， 不填使用默认值
    //文字验证码
    'click_world' => [
        'backgrounds' => [] 
    ],
    //滑动验证码
    'block_puzzle' => [
        'backgrounds' => [], //背景图片路径， 不填使用默认值
        'templates' => [], //模板图
        
        'offset' => 10, //容错偏移量

        'is_cache_pixel' => true, //是否开启缓存图片像素值，开启后能提升服务端响应性能（但要注意更换图片时，需要清除缓存）
    ],
    //水印
    'watermark' => [
        'fontsize' => 12,
        'color' => '#ffffff',
        'text' => '我的水印'
    ],
    'cache' => [
        'constructor' => \Fastknife\Utils\CacheUtils::class,//若您使用了框架，并且想使用类似于redis这样的缓存驱动，则应换成框架的中的缓存驱动
        'method' => [
            // 遵守PSR-16规范不需要设置此项（tp6, laravel,hyperf）。如tp5就不支持（tp5缓存方法是rm,所以要配置为"delete" => "rm"）,
            'get' => 'get', //获取
            'set' => 'set', //设置
            'delete' => 'delete',//删除
            'has' => 'has' //key是否存在
        ],
        'options' => [
        //如果您依然使用\Fastknife\Utils\CacheUtils做为您的缓存驱动，那么您可以自定义缓存配置。
            'expire'        => 300,//缓存有效期 （默认为0 表示永久缓存）
            'prefix'        => '', //缓存前缀
            'path'          => '', //缓存目录
            'serialize'     => [], //缓存序列化和反序列化方法
        ]       
    ]
];
```
##### 缓存配置

> config.cache.constructor类型为string|array|function 使用以访问回调的方式获得缓存实例;

+ laravel 配置：

 ```
 'constructor' => [Illuminate\Support\Facades\Cache::class, 'store']
```

+ tp6(tp5.1) 配置

```php
  'constructor' => [think\Facade\Cache::class, 'instance']
```

> 无论配置写成`[think\Facade\Cache::class, 'instance']` 还是写成 `[think\Facade\Cache::class, 'store']` 目的都是为了获取缓存实例，具体情况视框架而定


       
+ 灵活自定义：
1. 如果您的需要使用类似以下命令打包配置文件（ThinkPHP,Laravel 命令）
    - php think optimize:config
    - php artisan optimize  
  则需要写成下面这样：
```php
    $instance = \think\facade\Cache::store();//获取缓存想实例
    //省略分部代码
    'constructor' => serialize($instance);
```

因为在执行optimize打包命令时，会尝试将对象进行序列化。

2. 如果您不需要使用打包压缩命令，或者使用了像hyperf这样的框架，除了上述的写法，还可以写成这样：

```php
   'constructor' => function () {
            $container = \Hyperf\Utils\ApplicationContext::getContainer();
            //在构造函数中传入自已的配置
            return $container->get(\Psr\SimpleCache\CacheInterface::class);
    },
```

除此之处，您传入的缓存实例应遵守psr-16规范

##### 背景图与滑动图
> 配置中的backgrounds与templates项均支持，Array, String两种格式
+ 使用Array（索引数组）格式时，表明它每一项都是一张图片。可以是本地图片路径，也可以是网络图片路径。   
示例：
```php
    'backgrounds' => [
        '/public/images/xxx.jpg',
        'http://www.image.xx.jpg'
    ]
```
+ 使用String格式时，表明它是一个图片所在位置的目录   
示例：
```php
    'backgrounds' => ROOT_PATH . '/resources/defaultImages/jigsaw/original/'
```
当配置中的backgrounds与templates项为空时，会将`/resources/defaultImages/`目录内所有图片做为默认图片。

下面是社区人员一起维护的图片库，这些图片可以与本程序无缝衔接。    
https://gitee.com/anji-plus/AJ-Captcha-Images

##### 字体配置
字体配置在水印与文字点击验证功能中使用，其配置格式化String, 指向字体库。为空时会以`/resources/fonts`下的字体文件为默认值。  
示例：
```php
'font_file' => ROOT_PATH . '/resources/fonts/WenQuanZhengHei.ttf'
```

#### 获取滑动验证码

```php
public function get(){
        $config = require '../src/config.php';
        $service = new BlockPuzzleCaptchaService($config);
        $data = $service->get();
        echo json_encode([
            'error' => false,
            'repCode' => '0000',
            'repData' => $data,
            'repMsg' => null,
            'success' => true,
        ]);
}
```

#### 滑动验证

```php
     public function check()
    {
        $config = require '../src/config.php';
        $service = new BlockPuzzleCaptchaService($config);
        $data = $_REQUEST;
        $msg = null;
        $error = false;
        $repCode = '0000';
        try {
            $service->check($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $error = true;
            $repCode = '6111';
        }
        echo json_encode([
            'error' => $error,
            'repCode' => $repCode,
            'repData' => null,
            'repMsg' => $msg,
            'success' => ! $error,
        ]);
    }
```

#### 获取文字验证码

```php
    public function get()
    {
        $config = require '../src/config.php';
        $service = new ClickWordCaptchaService($config);
        $data = $service->get();
        echo json_encode([
            'error' => false,
            'repCode' => '0000',
            'repData' => $data,
            'repMsg' => null,
            'success' => true,
        ]);
    }
```

#### 文字验证

```php
    public function check()
    {
        $config = require '../src/config.php';
        $service = new ClickWordCaptchaService($config);
        $data = $_REQUEST;
        $msg = null;
        $error = false;
        $repCode = '0000';
        try {
            $service->check($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $error = true;
            $repCode = '6111';
        }
        echo json_encode([
            'error' => $error,
            'repCode' => $repCode,
            'repData' => null,
            'repMsg' => $msg,
            'success' => ! $error,
        ]);
    }
```

#### 前端请求头修改示例

```javascript
import axios from 'axios';
import qs from 'qs';

axios.defaults.baseURL = 'https://captcha.anji-plus.com/captcha-api';

const service = axios.create({
    timeout: 40000,
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    },
})
service.interceptors.request.use(
    config => {
        if (config.hasOwnProperty('data')) {
            config.data = qs.stringify(config.data)
        }
        return config
    },
    error => {
        Promise.reject(error)
    }
)
```

本包后续更新 ThinkPHP、Hyperf 等框架的demo，请持续关注
https://gitee.com/fastknife/aj-captcha