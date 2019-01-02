layui.use(['layer','upload'], function() {
    var upload = layui.upload;
    var layer = layui.layer;
    //点击选择图片
    $('img').on('click',function (e) {
        var parentNode = $(this).parent();
        parentNode.toggleClass('on');
//            if(Array.from(parentNode.classList).indexOf('on') !== -1) {
//                parentNode.removeClass('on');
//                choices.splice(value.indexOf(this.id),1);
//            }else{
//                parentNode.addClass('on');
//                choices.push(this.id);
//            }
    });
    //图片上传
    upload.render({
        elem: '#upload'
        ,url: uploadurl
        ,multiple: true
        ,size: 2097152 //限制文件大小，单位 KB
        ,done: function(res){
            layer.msg(res.msg,{time:1000});
//                var e = $($(".imagesbox").children("div").get(0));
//                $(e).before('<div class="image-item"><div class="image-delete" data-url=""></div><img class="pic" src="'+res.src+'"></img> </div>');
//                onloadimg();
            setTimeout(function () {
                window.location.reload();
            }, 1000);

        }
    });

    //确定选择
    $("#ConfirmChoices").on('click',function (e) {
        if(parent.$f){
            var value = parent.$f.getValue(parentinputname);//父级input 值
            var list = value||[];
            // console.log(list);
            var images = Array.from(self.document.getElementsByTagName('img'));
            images.forEach(function (image) {
                if(Array.from(image.parentNode.classList).indexOf('on') !== -1 && value.indexOf(image.src) == -1){
                    list.push(image.src);
                    // list.push(image.getAttribute("src"));
                }
            });
            parent.$f.changeField(parentinputname,list);
            parent.$f.closeModal();
        }else{
            //普通弹出选择图片
            var images = Array.from(self.document.getElementsByTagName('img'));
            images.forEach(function (image) {
                if(Array.from(image.parentNode.classList).indexOf('on') !== -1 ){
                    parent.changeIMG(parentinputname,image.src);
                    // parent.changeIMG(parentinputname,image.getAttribute("src"));
                }
            });
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
        }

    });
    //删除选择图片
    $('#deleteimg').on('click',function (e) {
        var images = Array.from(self.document.getElementsByTagName('img'));
        var list = [];
        images.forEach(function (image) {
            if(Array.from(image.parentNode.classList).indexOf('on') !== -1){
                list.push(image.id);
            }
        });
        if(list==''){
            layer.msg('还没选择要删除的图片呢？');
        }else{
            layer.confirm('确定删除吗？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                $.post(deleteurl,{imageid:list},function(result){
                    // console.log(result);
                    layer.msg(result.msg);
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                },'json');
            });
        }
    });

    //移动选择图片
    $('#moveimg').on('click',function (e) {
        var images = Array.from(self.document.getElementsByTagName('img'));
        var list = [];
        images.forEach(function (image) {
            if(Array.from(image.parentNode.classList).indexOf('on') !== -1){
                list.push(image.id);
            }
        });
        if(list==''){
            layer.msg('还没选择要移动的图片呢？');
        }else{
            var url = moveurl+'?imgaes='+list.join();
            layer.open({
                type: 2,
                title: '编辑分类',
                shade: [0],
                area: ['340px', '365px'],
                anim: 2,
                content: [url, 'no'],
                end: function () {
                    window.location.reload();
                }
            });
        }
    });
    //添加图片分类
    $('#addcate').on('click',function (e) {
        layer.open({
            type: 2,
            title: '编辑分类',
            shade: [0],
            area: ['340px', '265px'],
            anim: 2,
            content: [addcate, 'no'],
            end: function () {
                window.location.reload();
            }
        });
    });
    //编辑图片分类
    $('#editcate').on('click',function (e) {
        if(pid == 0){
            layer.msg('禁止编辑');
        }else{
            layer.open({
                type: 2,
                title: '编辑分类',
                shade: [0],
                area: ['340px', '265px'],
                anim: 2,
                content: [editcate, 'no'],
                end: function () {
                    window.location.reload();
                }
            });
        }
    });
    //删除图片分类
    $('#deletecate').on('click',function (e) {
        layer.confirm('确定删除吗？', {
            btn: ['确定','取消'] //按钮
        }, function(){
            $.post(deletecate,{id:pid},function(result){
                layer.msg(result.msg);
                if(result.code == 200){
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            },'json');
        });
    });
});
//非组件修改样式
if(!parent.$f){
    $('.main-top').hide();
    $('.main').css('margin','0px');
    $('.foot-tool').css('bottom','20px');
}