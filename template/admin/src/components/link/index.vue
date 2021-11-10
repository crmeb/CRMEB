<template>
    <a
        :href="linkUrl"
        :target="target"
        class="i-link"
        :class="{ 'i-link-color': !linkColor }"
        @click.exact="handleClickItem($event, false)"
        @click.ctrl="handleClickItem($event, true)"
        @click.meta="handleClickItem($event, true)"
        ><slot></slot></a>
</template>
<script>
    import mixinsLink from 'view-design/src/mixins/link';
    export default {
        name: 'i-link',
        mixins: [ mixinsLink ],
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            // 开启后，链接颜色为默认的蓝色，默认关闭为继承效果
            linkColor: {
                type: Boolean,
                default: false
            }
        },
        methods: {
            handleClickItem (event, new_window = false) {
                if (this.disabled) return;

                this.handleCheckClick(event, new_window);
            }
        }
    }
</script>
<style lang="less">
    .i-link{
        cursor: pointer;
        &-color{
            &, &:hover, &:active{
                color: inherit;
            }
        }
    }
</style>
