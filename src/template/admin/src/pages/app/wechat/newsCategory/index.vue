<template>
    <div style="height: 100%">
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <news-category :isShow="isShow"></news-category>
    </div>
</template>

<script>
    import newsCategory from '@/components/newsCategory/index'
    export default {
        name: 'newsCategoryIndex',
        data () {
            return {
                isShow: true
            }
        },
        components: {
            newsCategory
        }
    }
</script>
