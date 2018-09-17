<!DOCTYPE html>
<html lang="zh-CN">
<head> {include file="public/head"} <title>{$title}</title></head>
<body>
<div id="form-add" class="mp-form" v-cloak="">
    <form-builder></form-builder>
</div>
<script>    var _vm;
    _mpApi = parent._mpApi;
    mpFrame.start(function (Vue) {
        require(['axios', 'system/util/mpFormBuilder'], function (axios, mpFormBuilder) {
            axios.post('{$read}').then((result) = > {Vue.use(mpFormBuilder, _mpApi, result.data, {action: '{$update}'});
            new Vue({
                el: "#form-add", mounted: function () {
                }
            })
        })
        });
    });</script>
</body>