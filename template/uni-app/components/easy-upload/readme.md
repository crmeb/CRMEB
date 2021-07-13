### easy-upload 组件

使用方法
```js
	<easy-upload
	:dataList="imageList" uploadUrl="http://localhost:3000/upload" :types="category"
	deleteUrl='http://localhost:3000/upload' :uploadCount="6"
	@successImage="successImage" @successVideo="successvideo"
	></easy-upload>
	
	//先引入组件
    import easyUpload from '@/components/easy-upload/easy-upload.vue'
    //注册组件
    components:{
        easyUpload
    }
	
	//使用 easycom 可以直接使用

```

|  参数   | 类型  | 是否必填 | 参数描述
|  ----  | ----  | ---- | ----
| types  | String | 否 | 上传类型 image/video
| dataList  | Array | 否 | 图片/视频数据展示
| clearIcon  | String | 否 | 删除图标(可以换成自己本地图片)
| uploadUrl  | String | 否 | 上传的接口
| deleteUrl  | String | 否 | 删除的接口
| uploadCount  | Number | 否 | 上传图片最大个数(默认为一张)
| upload_max  | Number | 否 | 上传大小(默认为3M)
| upload_max  | Number | 否 | 上传大小(默认为3M)
| upload_max  | Number | 否 | 上传大小(默认为3M)

|  事件  | 是否必填 | 参数描述
|  ---- | ---- | ----
| successImage  | 否 | 上传图片成功事件
| successVideo  |  否 | 上传视频成功回调

示例项目中有服务端代码 (node.js)

