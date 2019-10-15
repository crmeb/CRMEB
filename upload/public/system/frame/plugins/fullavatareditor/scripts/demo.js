swfobject.addDomLoadEvent(function () {
    //------------------------------------------------------------------------------示例一
    var webcamAvailable = false;
    var currentTab = 'upload';
	var sourcePic1Url = $.Cookie('swf1');
	var sourcePic2Url = $.Cookie('swf2');
	if(sourcePic2Url == null)
	{
		sourcePic2Url = "samplePictures/Default.jpg";
	}
    var callback = function (json) {
		var id = this.id;
        switch (json.code) {
            case 2:
                //如果加载原图成功，说明进入了编辑面板，显示保存和取消按钮，隐藏拍照按钮
                if (json.type == 0) {
					if(id == "swf2")
					{
						$('#webcamPanelButton').hide();
						$('#editorPanelButtons').show();
					}
                }
                //否则会转到上传面板
                else {
                    //隐藏所有按钮
                    if(id == "swf1")$('#editorPanelButtons,#webcamPanelButton').hide();
                }
                break;
            case 3:
                //如果摄像头已准备就绪且用户已允许使用，显示拍照按钮。
                if (json.type == 0) {
                    if(id == "swf2")
					{
						$('.button_shutter').removeClass('Disabled');
						$('#webcamPanelButton').show();
						webcamAvailable = true;
					}
                }
                else {
					if(id == "swf2")
					{
						webcamAvailable = false;
						$('#webcamPanelButton').hide();
					}
                    //如果摄像头已准备就绪但用户已拒绝使用。
                    if (json.type == 1) {
                        alert('用户拒绝使用摄像头!');
                    }
                    //如果摄像头已准备就绪但摄像头被占用。
                    else {
                        alert('摄像头被占用!');
                    }
                }
                break;
            case 4:
                alert("您选择的原图片文件大小（" + json.content + "）超出了指定的值(2MB)。");
                break;
            case 5:
                //如果上传成功
                if (json.type == 0) {
					var e = this;
					var html = $('<div class="imgList"/>');
					for(var i = 0; i < json.content.avatarUrls.length; i++)
					{
						html.append('<dl><dt>头像图片'+(i+1)+'</dt><dd><img src="' + json.content.avatarUrls[i] + '" /></dd></dl>');
					}
					var button = [];
					//如果上传了原图，给个修改按钮，感受视图初始化带来的用户体验度提升
					if(json.content.sourceUrl)
					{
						button.push({text : '修改头像', callback:function(){
							this.close();
							$.Cookie(id, json.content.sourceUrl);
							location.reload();
							//e.call('loadPic', json.content.sourceUrl);
						}});
					}
					else
					{
						$.Cookie(id, null);
					}
					button.push({text : '关闭窗口'});
					$.dialog({
						title:'图片已成功保存至服务器',
						content:html,
						button:button,
						mask:true,
						draggable:false
					});
                }
                else {
					switch (json.type)
					{
						case 1:alert('图片上传失败，接口返回的内容为：'+json.content);break;
						case 2:alert('图片上传失败，指定的上传地址不存在或有问题！\n'+json.content);break;
						case 3:alert('图片上传失败，发生了安全性错误！\n'+json.content);break;
					}
                }
                break;
        }
    };
    //------------------------------------------------------------------------------示例一
    var swf1 = new fullAvatarEditor('swf1', {
        id: 'swf1',
        upload_url: 'upload.php',	//上传图片的接口地址
        src_url: sourcePic2Url,		//默认加载的原图片的url
        src_upload: 2,				//是否上传原图片的选项：2-显示复选框由用户选择，0-不上传，1-上传
		avatar_scale:2,				//头像保存时的缩放系数
		avatar_intro:'最终头像的尺寸为以下尺寸 * 2(设置的缩放系数)',	//头像尺寸的提示文本。其间用"|"号分隔，
		avatar_sizes_desc:'100*100像素，缩放系数为2，保存后的大小为200*200像素。|50*50像素，缩放系数为2，保存后的大小为100*100像素。|32*32像素，缩放系数为2，保存后的大小为64*64像素。'
    }, callback);
    var swf2 = new fullAvatarEditor('swf2', 335, {
		id : 'swf2',
		upload_url: 'upload.php?size_number=1',	//上传图片的接口地址
		method :'post',
		src_url : sourcePic1Url,			//默认加载的原图片的url
        tab_visible : false,				//不显示选项卡，外部自定义
        button_visible : false,				//不显示按钮，外部自定义
        src_upload : 2,						//是否上传原图片的选项：2-显示复选框由用户选择，0-不上传，1-上传
        checkbox_visible : false,			//不显示复选框，外部自定义
        browse_box_align : 38,				//图片选择框的水平对齐方式。left：左对齐；center：居中对齐；right：右对齐；数值：相对于舞台的x坐标
		webcam_box_align : 38,				//摄像头拍照框的水平对齐方式，如上。
		avatar_sizes : '258*200',			//定义单个头像
		avatar_sizes_desc :'258*200像素',	//头像尺寸的提示文本
		isShowUploadResultIcon:false,		//在上传完成时（无论成功和失败），是否显示表示上传结果的图标。
		//头像简介
		avatar_intro : '     最终会生成下面这个尺寸的头像',
		avatar_tools_visible:true			//是否显示颜色调整工具
    }, callback);
    //选项卡点击事件
    $('dt').click(function () {
        if (currentTab != this.id) {
            currentTab = this.id;
            $(this).addClass('current');
            $(this).siblings().removeClass('current');
            //如果是点击“相册选取”
            if (this.id === 'albums') {
                //隐藏flash
                hideSWF();
                showAlbums();
            }
            else {
                hideAlbums();
                showSWF();
                if (this.id === 'webcam') {
                    $('#editorPanelButtons').hide();
                    if (webcamAvailable) {
                        $('.button_shutter').removeClass('Disabled');
                        $('#webcamPanelButton').show();
                    }
                }
                else {
                    //隐藏所有按钮
                    $('#editorPanelButtons,#webcamPanelButton').hide();
                }
            }
            swf2.call('changepanel', this.id);
        }
    });
    //复选框事件
    $('#src_upload').change(function () {
        swf2.call('srcUpload', this.checked);
    });
    //点击上传按钮的事件
    $('.button_upload').click(function () {
        swf2.call('upload');
    });
    //点击取消按钮的事件
    $('.button_cancel').click(function () {
        var activedTab = $('dt.current')[0].id;
        if (activedTab === 'albums') {
            hideSWF();
            showAlbums();
        }
        else {
            swf2.call('changepanel', activedTab);
            if (activedTab === 'webcam') {
                $('#editorPanelButtons').hide();
                if (webcamAvailable) {
                    $('.button_shutter').removeClass('Disabled');
                    $('#webcamPanelButton').show();
                }
            }
            else {
                //隐藏所有按钮
                $('#editorPanelButtons,#webcamPanelButton').hide();
            }
        }
    });
    //点击拍照按钮的事件
    $('.button_shutter').click(function () {
        if (!$(this).hasClass('Disabled')) {
            $(this).addClass('Disabled');
            swf2.call('pressShutter');
        }
    });
    //从相册中选取
    $('#userAlbums a').click(function () {
        var sourcePic = this.href;
        swf2.call('loadPic', sourcePic);
        //隐藏相册
        hideAlbums();
        //显示flash
        showSWF();
        return false;
    });
    //隐藏flash的函数
    function hideSWF() {
        //将宽高设置为0的方式来隐藏flash，而不能使用将其display样式设置为none的方式来隐藏，否则flash将不会被加载，隐藏时储存其宽高，以便后期恢复
        $('#flash').data({
            w: $('#flash').width(),
            h: $('#flash').height()
        })
		.css({
		    width: '0px',
		    height: '0px',
		    overflow: 'hidden'
		});
        //隐藏所有按钮
        $('#editorPanelButtons,#webcamPanelButton').hide();
    }
    function showSWF() {
        $('#flash').css({
            width: $('#flash').data('w'),
            height: $('#flash').data('h')
        });
    }
    //显示相册的函数
    function showAlbums() {
        $('#userAlbums').show();
    }
    //隐藏相册的函数
    function hideAlbums() {
        $('#userAlbums').hide();
    }
});