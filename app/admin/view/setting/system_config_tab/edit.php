<!DOCTYPE html>

<html lang="zh-CN">

<head>

    {include file="public/head"}

    <title>{$title}</title>

</head>

<body>

<div id="form-add" class="mp-form" v-cloak="">



    <form-builder></form-builder>



</div>
<style>
    #eb-field-icon input{width: 80%}
</style>
<script>

    var _vm ;

    _mpApi = parent._mpApi;


    function openicon() {
        layer.open({
            type: 2,
            content: '/admin/system.system_menus/icon.html',
            area: ['600px', '600px'],
            maxmin: true
        });
//        $eb.createModalFrame(this.innerText,"/admin/system.system_menus/icon.html");
    }
    mpFrame.start(function(Vue){

        require(['axios','system/util/mpFormBuilder'],function(axios,mpFormBuilder){

            axios.post('{$read}').then((result)=>{

                Vue.use(mpFormBuilder,_mpApi,result.data,{

                    action:'{$update}'

                });

                new Vue({

                    el:"#form-add",

                    mounted:function(){
                        window._$setIcon = (icon)=>{
                            this.$formBuilder.set('icon',icon);
                        };
                        $('#eb-field-icon').find('input').after('<button type="button" class="ivu-btn" onclick="openicon()")>选择图标</button>');

                    }

                })

            })

        });

    });

</script>

</body>

