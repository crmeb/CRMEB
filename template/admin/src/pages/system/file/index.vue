<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{$route.meta.title}}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <div class="box">
                <upload-file :isShow="0"></upload-file>
            </div>
        </Card>
    </div>
</template>
<script>
    import uploadFile from '@/components/uploadPictures/index'
    export default {
        components: { uploadFile },
        name: 'system_file',
        mounted () {

        },
        methods: {}
    }
</script>
<style scoped lang="stylus">
    .box {
        width :100%; background: #fff;
    }
</style>