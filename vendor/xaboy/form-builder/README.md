<p align="center">
    <a href="https://github.com/xaboy/form-builder">
        <img width="200" src="http://file.lotkk.com/form-builder.png">
    </a>
</p>
<h1 align="center">form-builder</h1>
<p align="center">
    <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="MIT" />
  <a href="https://github.com/xaboy">
    <img src="https://img.shields.io/badge/Author-xaboy-blue.svg" alt="xaboy" />
  </a>
  <a href="https://packagist.org/packages/xaboy/form-builder">
    <img src="https://img.shields.io/packagist/v/xaboy/form-builder.svg" alt="version" />
  </a>
  <a href="https://packagist.org/packages/xaboy/form-builder">
    <img src="https://img.shields.io/packagist/php-v/xaboy/form-builder.svg" alt="php version" />
  </a>
  <a href="#backers" alt="sponsors on Open Collective"><img src="https://opencollective.com/form-builder/backers/badge.svg" />
  </a> 
  <a href="#sponsors" alt="Sponsors on Open Collective"><img src="https://opencollective.com/form-builder/sponsors/badge.svg" />
  </a> 
</p>

<p align="center">
PHP表单生成器，快速生成现代化的form表单。包含复选框、单选框、输入框、下拉选择框等元素以及省市区三级联动、时间选择、日期选择、颜色选择、树型、文件/图片上传等功能。
</p>


> 1.2.4 版本支持字段验证 

> 计划对该项目重构和升级,期待的话就点个 star 吧

>如果对您有帮助，您可以点右上角 "Star" 支持一下 谢谢！
>本项目还在不断开发完善中,如有建议或问题请[在这里提出](https://github.com/xaboy/form-builder/issues/new)


## 演示项目
[开源的高品质微信商城](http://github.crmeb.net/u/xaboy)

演示地址: [http://demo25.crmeb.net](http://demo25.crmeb.net) 账号：demo 密码：crmeb.com

## 使用建议
1. 建议将静态资源加载方式从 CDN 加载修改为自己本地资源或自己信任的 CDN [静态资源链接](https://github.com/xaboy/form-builder/blob/master/src/Form.php#L89)
2. 建议根据自己的业务逻辑重写默认的表单生成页 [默认表单生成页](https://github.com/xaboy/form-builder/tree/master/src/view)

## 更新说明

#### 1.2.7 (2018-12-12)
- 完善时间选择组件,日期选择组件验证规则
- 新增 fields 类型验证规则
- 新增 使用 view 方法生成时,表单只能被创建一次
- 修复一些小问题

## 安装
`composer require xaboy/form-builder`

## 示例

![https://raw.githubusercontent.com/xaboy/form-create/master/images/sample110.jpg](https://raw.githubusercontent.com/xaboy/form-create/master/images/sample110.jpg)

### 例子 (TP框架)

#### 版本1 编辑权限
```php
$form = Form::create(Url::build('update',array('id'=>$id)),[
            Form::input('menu_name','按钮名称',$menu['menu_name']),
            Form::select('pid','父级id',(string)$menu->getData('pid'))->setOptions(function()use($id){
                $list = (Util::sortListTier(MenusModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','menu_name'));
                $menus = [['value'=>0,'label'=>'顶级按钮']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['menu_name']];
                }
                return $menus;
            })->filterable(1),
            Form::select('module','模块名',$menu['module'])->options([['label'=>'总后台','value'=>'admin'],['label'=>'总后台1','value'=>'admin1']]),
            Form::input('controller','控制器名',$menu['controller']),
            Form::input('action','方法名',$menu['action']),
            Form::input('params','参数',MenusModel::paramStr($menu['params']))->placeholder('举例:a/123/b/234'),
            Form::frameInputOne('icon','图标',Url::build('admin/widget.widgets/icon',array('fodder'=>'icon')),$menu['icon'])->icon('ionic'),
            Form::number('sort','排序',$menu['sort']),
            Form::radio('is_show','是否菜单',$menu['is_show'])->options([['value'=>0,'label'=>'隐藏'],['value'=>1,'label'=>'显示(菜单只显示三级)']])
        ]);
$form->setMethod('post')->setTitle('编辑权限');
$this->assign(compact('form'));
return $this->fetch('public/form-builder');

```
#### 效果
![https://raw.githubusercontent.com/xaboy/form-builder/master/images/demo02.jpg](https://raw.githubusercontent.com/xaboy/form-builder/master/images/demo02.jpg)

#### 版本2 添加产品
```php
$field = [
    Form::select('cate_id','产品分类')->setOptions(function(){
        $list = CategoryModel::getTierList();
        foreach ($list as $menu){
            $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name'],'disabled'=>$menu['pid']== 0];//,'disabled'=>$menu['pid']== 0];
        }
        return $menus;
    })->filterable(1)->multiple(1),
    Form::input('store_name','产品名称')->col(Form::col(8)),
    Form::input('store_info','产品简介')->type('textarea'),
    Form::input('keyword','产品关键字')->placeholder('多个用英文状态下的逗号隔开'),
    Form::input('unit_name','产品单位','件'),
    Form::frameImageOne('image','产品主图片(305*305px)',Url::build('admin/widget.images/index',array('fodder'=>'image')))->icon('image')->width('100%')->height('550px'),
    Form::frameImages('slider_image','产品轮播图(640*640px)',Url::build('admin/widget.images/index',array('fodder'=>'slider_image')))->maxLength(5)->icon('images')->width('100%')->height('550px')->spin(0),
    Form::number('price','产品售价')->min(0)->col(8),
    Form::number('ot_price','产品市场价')->min(0)->col(8),
    Form::number('give_integral','赠送积分')->min(0)->precision(0)->col(8),
    Form::number('postage','邮费')->min(0)->col(Form::col(8)),
    Form::number('sales','销量')->min(0)->precision(0)->col(8),
    Form::number('ficti','虚拟销量')->min(0)->precision(0)->col(8),
    Form::number('stock','库存')->min(0)->precision(0)->col(8),
    Form::number('cost','产品成本价')->min(0)->col(8),
    Form::number('sort','排序')->col(8),
    Form::radio('is_show','产品状态',0)->options([['label'=>'上架','value'=>1],['label'=>'下架','value'=>0]])->col(8),
    Form::radio('is_hot','热卖单品',0)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_benefit','促销单品',0)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_best','精品推荐',0)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_new','首发新品',0)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_postage','是否包邮',0)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8)
];
$form = Form::create(Url::build('save'));
$form->setMethod('post')->setTitle('添加产品')->components($field);
$this->assign(compact('form'));
return $this->fetch('public/form-builder');

```
#### 效果
![https://raw.githubusercontent.com/xaboy/form-builder/master/images/demo03.jpg](https://raw.githubusercontent.com/xaboy/form-builder/master/images/demo03.jpg)


#### 版本3 编辑产品
```php
$product = ProductModel::get($id);
$form = Form::create(Url::build('update',array('id'=>$id)),[
    Form::select('cate_id','产品分类',explode(',',$product->getData('cate_id')))->setOptions(function(){
        $list = CategoryModel::getTierList();
        foreach ($list as $menu){
            $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name'],'disabled'=>$menu['pid']== 0];//,'disabled'=>$menu['pid']== 0];
        }
        return $menus;
    })->filterable(1)->multiple(1),
    Form::input('store_name','产品名称',$product->getData('store_name')),
    Form::input('store_info','产品简介',$product->getData('store_info'))->type('textarea'),
    Form::input('keyword','产品关键字',$product->getData('keyword'))->placeholder('多个用英文状态下的逗号隔开'),
    Form::input('unit_name','产品单位',$product->getData('unit_name')),
    Form::frameImageOne('image','产品主图片(305*305px)',Url::build('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('550px'),
    Form::frameImages('slider_image','产品轮播图(640*640px)',Url::build('admin/widget.images/index',array('fodder'=>'slider_image')),json_decode($product->getData('slider_image'),1))->maxLength(5)->icon('images'),
    Form::number('price','产品售价',$product->getData('price'))->min(0)->precision(2)->col(8),
    Form::number('ot_price','产品市场价',$product->getData('ot_price'))->min(0)->col(8),
    Form::number('give_integral','赠送积分',$product->getData('give_integral'))->min(0)->precision(0)->col(8),
    Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(8),
    Form::number('sales','销量',$product->getData('sales'))->min(0)->precision(0)->col(8),
    Form::number('ficti','虚拟销量',$product->getData('ficti'))->min(0)->precision(0)->col(8),
    Form::number('stock','库存',ProductModel::getStock($id)>0?ProductModel::getStock($id):$product->getData('stock'))->min(0)->precision(0)->col(8),
    Form::number('cost','产品成本价',$product->getData('cost'))->min(0)->col(8),
    Form::number('sort','排序',$product->getData('sort'))->col(8),
    Form::radio('is_show','产品状态',$product->getData('is_show'))->options([['label'=>'上架','value'=>1],['label'=>'下架','value'=>0]])->col(8),
    Form::radio('is_hot','热卖单品',$product->getData('is_hot'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_benefit','促销单品',$product->getData('is_benefit'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_best','精品推荐',$product->getData('is_best'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_new','首发新品',$product->getData('is_new'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8),
    Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(8)
]);
$form->setMethod('post')->setTitle('编辑产品');
$this->assign(compact('form'));
return $this->fetch('public/form-builder');
```
#### 效果
![https://raw.githubusercontent.com/xaboy/form-builder/master/images/demo04.jpg](https://raw.githubusercontent.com/xaboy/form-builder/master/images/demo04.jpg)


**当form提交成功后会调用`window.formCreate.formSuccess(res,$f,formData)`作为回调方法**


## 代码
```php

namespace Test;
use FormBuilder\Form;

//input组件
$input = Form::input('goods_name','商品名称');

//日期区间选择组件
$dateRange = Form::dateRange(
    'limit_time',
    '区间日期',
    strtotime('- 10 day'),
    time()
);

//省市二级联动组件
$cityArea = Form::city('address','收货地址',[
    '陕西省','西安市'
]);

$checkbox = Form::checkbox('label','表单',[])->options([
    ['value'=>'1','label'=>'好用','disabled'=>true],
    ['value'=>'2','label'=>'方便','disabled'=>true]
])->col(Form::col(12));

$tree = Form::treeChecked('tree','权限',[])->data([
    Form::treeData(11,'leaf 1-1-1')->children([Form::treeData(13,'131313'),Form::treeData(14,'141414')]),
    Form::treeData(12,'leaf 1-1-2')
])->col(Form::col(12)->xs(12));

//创建form
$form = Form::create('/save.php',[
    $input,$dateRange,$cityArea,$checkbox,$tree
]);

$html = $form->formRow(Form::row(10))->setMethod('get')->setTitle('编辑商品')->view();

//输出form页面
echo $html;
```


## AJAX请求返回
`namespace \FormBuilder\Json`

* **Json::succ(msg,data = [])** 表单提交成功
* **Json::fail(errorMsg,data = [])** 表单提交失败
* **Json::uploadSucc(filePath,msg)** 文件/图片上传成功,上传成功后返回文件地址
* **Json::uploadFail(errorMsg)** 文件/图片上传失败

## Form 表单生成类
`namespace \FormBuilder\Form`

* **components(array $components = [])** 批量添加组件
* **formRow(Row $row)** 设置表单Row规则
* **formStyle(FormStyle $formStyle)** 设置表单样式
* **setAction($action)** 设置提交地址
* **getConfig($key='')** 设置配置文件
* **setMethod($method)** 设置提交方式
* **setMethod($method)** 设置提交方式
* **append(FormComponentDriver $component)** 追加组件
* **prepend(FormComponentDriver $component)** 开头插入组件
* **getRules()** 获得表单规则
* **view()** 获取表单视图
* **script()** 获取表单生成器所需全部js
* **formScript()** 获取生成表单的js代码,可用js变量接受生成函数`create`,执行`create(el,callback)`即可生成表单
* **getScript()** 获取表单生成器所需js
* **create($action, array $components = [])** 生成表单快捷方法
* **setTitle($title)** 设置title

## FormStyle表单样式
* **Form::style**
```php
 * @method $this inline(Boolean $bool) 是否开启行内表单模式
 * @method $this labelPosition(String $labelPosition) 表单域标签的位置，可选值为 left、right、top
 * @method $this labelWidth(Number $labelWidth) 表单域标签的宽度，所有的 FormItem 都会继承 Form 组件的 label-width 的值
 * @method $this showMessage(Boolean $bool) 是否显示校验错误信息
 * @method $this autocomplete($bool = false) 原生的 autocomplete 属性，可选值为 true = off 或 false = on
```

## Row栅格规则
* **Form::row**
```php
 * @method $this gutter(Number $gutter) 栅格间距，单位 px，左右平分
 * @method $this type(String $type) 栅格的顺序，在flex布局模式下有效
 * @method $this align(String $align) flex 布局下的垂直对齐方式，可选值为top、middle、bottom
 * @method $this justify(String $justify) flex 布局下的水平排列方式，可选值为start、end、center、space-around、space-between
 * @method $this className(String $className) 自定义的class名称
```
参考: [view row栅格布局](http://v2.iviewui.com/components/grid#API)

## Col栅格规则
* **Form::col**
```php
 * @method $this span(Number $span) 栅格的占位格数，可选值为0~24的整数，为 0 时，相当于display:none
 * @method $this order(Number $order) 栅格的顺序，在flex布局模式下有效
 * @method $this offset(Number $offset) 栅格左侧的间隔格数，间隔内不可以有栅格
 * @method $this push(Number $push) 栅格向右移动格数
 * @method $this pull(Number $pull) 栅格向左移动格数
 * @method $this labelWidth(Number $labelWidth) 表单域标签的的宽度,默认150px
 * @method $this className(String $className) 自定义的class名称
 * @method $this xs(Number|Col $span) <768px 响应式栅格，可为栅格数或一个包含其他属性的对象
 * @method $this sm(Number|Col $span) ≥768px 响应式栅格，可为栅格数或一个包含其他属性的对象
 * @method $this md(Number|Col $span) ≥992px 响应式栅格，可为栅格数或一个包含其他属性的对象
 * @method $this lg(Number|Col $span) ≥1200px 响应式栅格，可为栅格数或一个包含其他属性的对象
```
参考: [view col栅格布局](http://v2.iviewui.com/components/grid#API)


## select,checkbox,radio组件配置options专用方法
* **option($value, $label, $disabled = false)** 单独设置选项
* **options(array $options, $disabled = false)** 批量设置选项
* **setOptions($options, $disabled = false)** 批量设置选项 支持匿名函数


## 以下组件公共方法
* **col($span)** 配置col栅格规则,传入0-24的数字或`Col`类,默认为24
* **value($value)** 设置组件的值
* **validateAs(array $validate)** 添加验证规则
* **validate()** 设置验证规则[规则说明](https://github.com/xaboy/form-builder/blob/master/src/components/Validate.php)

## 组件
`namespace \FormBuilder\Form`

####  多级联动组件
* **Form::cascader** 多级联动组件,value为array类型
* **Form::city** 省市二级联动,value为array类型
* **Form::cityArea** 省市区三级联动,value为array类型
```php
    方法   返回值 方法名(参数)   注释
 * @method $this type(String $type) 数据类型, 支持 city_area(省市区三级联动), city (省市二级联动), other (自定义)
 * @method $this disabled(Boolean $bool) 是否禁用选择器
 * @method $this clearable(Boolean $bool) 是否支持清除
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this trigger(String $trigger) 次级菜单展开方式，可选值为 click 或 hover
 * @method $this changeOnSelect(Boolean $bool) 当此项为 true 时，点选每级菜单选项值都会发生变化, 默认为 false
 * @method $this size(String $size) 输入框大小，可选值为large和small或者不填
 * @method $this filterable(Boolean $bool) 是否支持搜索
 * @method $this notFoundText(String $text) 当搜索列表为空时显示的内容
 * @method $this transfer(Boolean $bool) /是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果
 * @method $this required($message = null, $trigger = 'change') 设为必选
 * @method $this data(array $data) 设置多级联动可选项的数据
 *  例如: {
 *    "value":"北京市", "label":"北京市", "children":[{
 *        "value":"东城区", "label":"东城区"
 *    }]
 *  }
 * @method $this jsData($var) 设置data为js变量
 * @method string getType($var) 获取组件类型
```

####  复选框组件
* **Form::checkbox**
```php
 * @method $this size(String $size) 多选框组的尺寸，可选值为 large、small、default 或者不设置
 * @method $this required($message = null, $trigger = 'change') 设为必选
```

####  颜色选择组件
* **Form::color**
```php
 * @method $this disabled(Boolean $bool) 是否禁用
 * @method $this alpha(Boolean $bool) 是否支持透明度选择, 默认为false
 * @method $this hue(Boolean $bool) 是否支持色彩选择, 默认为true
 * @method $this recommend(Boolean $bool) 是否显示推荐的颜色预设, 默认为false
 * @method $this size(String $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this format(String $format) 颜色的格式，可选值为 hsl、hsv、hex、rgb    String    开启 alpha 时为 rgb，其它为 hex
 * @method $this required($message = null, $trigger = 'change') 设为必选
 * @method $this colors($colors) 自定义颜色预设
```

#### 日期选择组件
* **Form::date** 日期选择
* **Form::dateRange** 日期区间选择,value为array类型
* **Form::dateTime** 日期+时间选择
* **Form::dateTimeRange** 日期+时间 区间选择,value为array类型
* **Form::year** 年份选择
* **Form::month** 月份选择
```php
 * @method $this type(String $type) 显示类型，可选值为 date、daterange、datetime、datetimerange、year、month
 * @method $this format(String $format) 展示的日期格式, 默认为yyyy-MM-dd HH:mm:ss
 * @method $this placement(String $placement) 日期选择器出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-end, left, left-start, left-end, right, right-start, right-end, 默认为bottom-start
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this confirm(Boolean $bool) 是否显示底部控制栏，开启后，选择完日期，选择器不会主动关闭，需用户确认后才可关闭, 默认为false
 * @method $this size(String $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this disabled(Boolean $bool) 是否禁用选择器
 * @method $this clearable(Boolean $bool) 是否显示清除按钮
 * @method $this readonly(Boolean $bool) 完全只读，开启后不会弹出选择器，只在没有设置 open 属性下生效
 * @method $this editable(Boolean $bool) 文本框是否可以输入, 默认为false
 * @method $this transfer(Boolean $bool) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 * @method $this splitPanels(Boolean $bool) 开启后，左右面板不联动，仅在 daterange 和 datetimerange 下可用。
 * @method $this showWeekNumbers(Boolean $bool) 开启后，可以显示星期数。
```

#### frame组件
* **Form::frame** frame组件
* **Form::frameInputs** frame组件,input类型,value为array类型
* **Form::frameFiles** frame组件,file类型,value为array类型
* **Form::frameImages** frame组件,image类型,value为array类型
* **Form::frameInputOne** frame组件,input类型,value为string|number类型
* **Form::frameFileOne** frame组件,file类型,value为string|number类型
* **Form::frameImageOne** frame组件,image类型,value为string|number类型
```php
 * @method $this type(String $type) frame类型, 有input, file, image, 默认为input
 * @method $this src(String $src) iframe地址
 * @method $this maxLength(int $length) value的最大数量, 默认无限制
 * @method $this icon(String $icon) 打开弹出框的按钮图标
 * @method $this height(String $height) 弹出框高度
 * @method $this width(String $width) 弹出框宽度
 * @method $this spin(Boolean $bool) 是否显示加载动画, 默认为 true
 * @method $this frameTitle(String $title) 弹出框标题
 * @method $this handleIcon(Boolean $bool) 操作按钮的图标, 设置为false将不显示, 设置为true为默认的预览图标, 类型为file时默认为false, image类型默认为true
 * @method $this allowRemove(Boolean $bool) 是否可删除, 设置为false是不显示删除按钮
```

#### hidden组件
* **Form::hidden** hidden组件

#### 数字输入框组件
* **Form::number**
```php
 * @method $this max(float $max) 最大值
 * @method $this min(float $min) 最小值
 * @method $this step(float $step) 每次改变的步伐，可以是小数
 * @method $this size(String $size) 输入框尺寸，可选值为large、small、default或者不填
 * @method $this disabled(Boolean $bool) 设置禁用状态，默认为false
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this readonly(Boolean $bool) 是否设置为只读，默认为false
 * @method $this editable(Boolean $bool) 是否可编辑，默认为true
 * @method $this precision(int $precision) 数值精度
```
#### input输入框组件
* **Form::input** input输入框
> 其他type: text类型`Form::text`,password类型`Form::password`,textarea类型`Form::textarea`,url类型`Form::url`,email类型`Form::email`,date类型`Form::idate`
```php
 * @method $this type(String $type) 输入框类型，可选值为 text、password、textarea、url、email、date;
 * @method $this size(String $size) 输入框尺寸，可选值为large、small、default或者不设置;
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this clearable(Boolean $bool) 是否显示清空按钮, 默认为false
 * @method $this disabled(Boolean $bool) 设置输入框为禁用状态, 默认为false
 * @method $this readonly(Boolean $bool) 设置输入框为只读, 默认为false
 * @method $this maxlength(int $length) 最大输入长度
 * @method $this icon(String $icon) 输入框尾部图标，仅在 text 类型下有效
 * @method $this rows(int $rows) 文本域默认行数，仅在 textarea 类型下有效, 默认为2
 * @method $this number(Boolean $bool) 将用户的输入转换为 Number 类型, 默认为false
 * @method $this autofocus(Boolean $bool) 自动获取焦点, 默认为false
 * @method $this autocomplete(Boolean $bool) 原生的自动完成功能, 默认为false
 * @method $this spellcheck(Boolean $bool) 原生的 spellcheck 属性, 默认为false
 * @method $this wrap(String $warp) 原生的 wrap 属性，可选值为 hard 和 soft, 默认为soft
 * @method $this autoSize($minRows, $maxRows) 自适应内容高度，仅在 textarea 类型下有效
```

#### 单选框组件
* **Form::radio** 
```php
 * @method $this size(String $size) 单选框的尺寸，可选值为 large、small、default 或者不设置
 * @method $this vertical(Boolean $bool) 是否垂直排列，按钮样式下无效
 * @method $this button() 使用按钮样式
 * @method $this required($message = null, $trigger = 'change') 设为必选
```

#### 评分组件
* **Form::rate** 
```php
 * @method $this count(int $star) star 总数, 默认为 5
 * @method $this allowHalf(Boolean $bool) 是否允许半选, 默认为 false
 * @method $this disabled(Boolean $bool) 是否只读，无法进行交互, 默认为
 * @method $this showText(Boolean $bool) 是否显示提示文字, 默认为 false
 * @method $this clearable(Boolean $bool) 是否可以取消选择, 默认为 false
```

#### select选择框组件
* **Form::select** 选择框
* **Form::selectMultiple** select选择框,多选,value为array类型
* **Form::selectOne** select选择框,单选
```php
 * @method $this multiple(Boolean $bool) 是否支持多选, 默认为false
 * @method $this disabled(Boolean $bool) 是否禁用, 默认为false
 * @method $this clearable(Boolean $bool) 是否可以清空选项，只在单选时有效, 默认为false
 * @method $this filterable(Boolean $bool) 是否支持搜索, 默认为false
 * @method $this size(String $size) 选择框大小，可选值为large、small、default或者不填
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this transfer(String $transfer) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 * @method $this placement(String $placement) 弹窗的展开方向，可选值为 bottom 和 top, 默认为bottom
 * @method $this notFoundText(String $text) 当下拉列表为空时显示的内容, 默认为 无匹配数据
 * @method $this required($message = null, $trigger = 'change') 设为必选
```

#### 滑块组件
* **Form::slider** 滑块组件
* **Form::sliderRange** 滑块组件,区间选择,
```php
 * @method $this min(float $min) 最小值, 默认 0
 * @method $this max(float $max) 最大值, 默认 100
 * @method $this step(float $step) 步长，取值建议能被（max - min）整除, 默认 1
 * @method $this disabled(Boolean $bool) 是否禁用滑块, 默认 false
 * @method $this range(Boolean $bool) 是否开启双滑块模式, 默认
 * @method $this showInput(Boolean $bool) 是否显示数字输入框，仅在单滑块模式下有效, 默认 false
 * @method $this showStops(Boolean $bool) 是否显示间断点，建议在 step 不密集时使用, 默认 false
 * @method $this showTip(String $tip) 提示的显示控制，可选值为 hover（悬停，默认）、always（总是可见）、never（不可见）
 * @method $this inputSize(String $size) 数字输入框的尺寸，可选值为large、small、default或者不填，仅在开启 show-input 时有效
```

#### 开关组件组件
* **Form::switches**
```php
 * @method $this size(String $size) 开关的尺寸，可选值为large、small、default或者不写。建议开关如果使用了2个汉字的文字，使用 large。
 * @method $this disabled(Boolean $bool) 禁用开关, 默认为false
 * @method $this trueValue(String $value) 选中时的值，默认为1
 * @method $this falseValue(String $value) 没有选中时的值，默认为0
 * @method $this openStr(String $open) 自定义显示打开时的内容
 * @method $this closeStr(String $close) 自定义显示关闭时的内容
```

#### 时间选择组件
* **Form::timePicker** 时间选择组件
* **Form::time** 时间选择
* **Form::timeRange** 时间区间选择,value为array类型
```php
 * @method $this type(String $type) 显示类型，可选值为 time、timerange
 * @method $this format(String $format) 展示的时间格式, 默认为HH:mm:ss
 * @method $this placement(String $placement) 时间选择器出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-end, left, left-start, left-end, right, right-start, right-end, 默认为bottom-start
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this confirm(Boolean $bool) 是否显示底部控制栏, 默认为false
 * @method $this size(String $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this disabled(Boolean $bool) 是否禁用选择器
 * @method $this clearable(Boolean $bool) 是否显示清除按钮
 * @method $this readonly(Boolean $bool) 完全只读，开启后不会弹出选择器，只在没有设置 open 属性下生效
 * @method $this editable(Boolean $bool) 文本框是否可以输入, 默认为false
 * @method $this transfer(Boolean $bool) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 * @method $this steps($h, $i = 0, $s = 0) 下拉列表的时间间隔，数组的三项分别对应小时、分钟、秒, 例如设置为 [1, 15] 时，分钟会显示：00、15、30、45。
```

#### 上传组件
* **Form::upload** 上传组件
* **Form::uploadImages** 多图上传组件,value为array类型
* **Form::uploadFiles** 多文件上传组件,value为array类型
* **Form::uploadImageOne** 单图上传组件
* **Form::uploadFileOne** 单文件上传组件
```php
 * @method $this uploadType(String $uploadType) 上传文件类型，可选值为 image（图片上传），file（文件上传）
 * @method $this action(String $action) 上传的地址
 * @method $this multiple(Boolean $bool) 是否支持多选文件
 * @method $this name(String $name) 上传的文件字段名
 * @method $this accept(String $accept) 接受上传的文件类型
 * @method $this maxSize(int $size) 文件大小限制，单位 kb
 * @method $this withCredentials(Boolean $bool) 支持发送 cookie 凭证信息, 默认为false
 * @method $this maxLength(Int $length) 最大上传文件数, 0为无限
 * @method $this headers(array $headers) 设置上传的请求头部
 * @method $this format(array $format) 支持的文件类型，与 accept 不同的是，format 是识别文件的后缀名，accept 为 input 标签原生的 accept 属性，会在选择文件时过滤，可以两者结合使用
 * @method $this data(array $data) 上传时附带的额外参数
 * @method $this required($message = null, $trigger = 'change') 设为必选
```


#### 树型组件
* **Form::tree** 树型组件
* **Form::treeSelected** 选中类型,value为array类型,当`type=selected`并且`multiple=false`,值为String或Number类型
* **Form::treeChecked** 选择类型,value为array类型
```php
 * @method $this type(String $type) 类型，可选值为 checked、selected
 * @method $this multiple(Boolean $bool) 是否支持多选,当`type=selected`并且`multiple=false`,默认为false,值为String或Number类型，其他情况为Array类型
 * @method $this showCheckbox(Boolean $bool) 是否显示多选框,默认为false
 * @method $this emptyText(String $emptyText) 没有数据时的提示,默认为'暂无数据'
 * @method $this data(array $treeData) 设置可选的data,**id必须唯一**
 * @method $this jsData($var) 设置data为js变量
```


## 树型组件data数据类 TreeData
* **Form::treeData** 树型组件data
```php
 * @method $this id(String $id) Id,必须唯一
 * @method $this title(String $title) 标题
 * @method $this expand(Boolean $bool) 是否展开直子节点,默认为false
 * @method $this disabled(Boolean $bool) 禁掉响应,默认为false
 * @method $this disableCheckbox(Boolean $bool) 禁掉 checkbox
 * @method $this selected(Boolean $bool) 是否选中子节点
 * @method $this checked(Boolean $bool) 是否勾选(如果勾选，子节点也会全部勾选)
 * @method $this children(array $children) 批量设置子集
 * @method $this child(TreeData $child) 设置子集
```

## 所有组件生成效果
![https://raw.githubusercontent.com/xaboy/form-builder/master/images/components.png](https://raw.githubusercontent.com/xaboy/form-builder/master/images/components.png)

## 参考

* **ui框架:** [iview2.x](http://v2.iviewui.com/docs/guide/install)
* **js表单生成器生成:** [form-create](https://github.com/xaboy/form-create)

## Contributors

This project exists thanks to all the people who contribute. 
<a href="https://github.com/xaboy/form-builder/graphs/contributors"><img src="https://opencollective.com/form-builder/contributors.svg?width=890&button=false" /></a>


## Backers

Thank you to all our backers! 🙏 [[Become a backer](https://opencollective.com/form-builder#backer)]

<a href="https://opencollective.com/form-builder#backers" target="_blank"><img src="https://opencollective.com/form-builder/backers.svg?width=890"></a>


## Sponsors

Support this project by becoming a sponsor. Your logo will show up here with a link to your website. [[Become a sponsor](https://opencollective.com/form-builder#sponsor)]

<a href="https://opencollective.com/form-builder/sponsor/0/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/0/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/1/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/1/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/2/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/2/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/3/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/3/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/4/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/4/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/5/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/5/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/6/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/6/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/7/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/7/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/8/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/8/avatar.svg"></a>
<a href="https://opencollective.com/form-builder/sponsor/9/website" target="_blank"><img src="https://opencollective.com/form-builder/sponsor/9/avatar.svg"></a>


