<template>
    <div>
        <div class="c_row-item" v-if="configData">
            <Col span="8" class="c_label" >{{configData[name].title}}</Col>
            <Col span="14" class="color-box">
                <div class="color-item" v-for="(color,key) in configData[name].color" :key="key">
                    <ColorPicker v-model="color.item" @on-change="changeColor($event,color)" alpha ></ColorPicker><span @click="resetBgA(color,index,key)">重置</span>
                </div>
            </Col>
        </div>
    </div>

</template>

<script>
    let restColor = ''
    export default {
        name: 'c_bg_color',
        props: {
            configData: {
                type: Object
            },
            name: {
                type: String
            }
        },
        data () {
            return {
                defaults: {
                },
                bgColor: {
                    bgStar: '',
                    bgEnd: ''
                },
                oldColor: {
                    bgStar: '',
                    bgEnd: ''
                },
                index: 0
            }
        },
        created () {
            this.defaults = this.configData
        },
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.defaults = nVal
                },
                immediate: true,
                deep: true
            }
        },
        methods: {
            changeColor (e, color) {
                if (!e) {
                    color.item = 'transparent'
                }
                // this.$emit('getConfig', this.defaults)
            },
            // 重置
            resetBgA (color, index, key) {
                // console.log(color)
                color.item = this.configData[this.name].default[key].item
            }
        }
    }
</script>

<style scoped lang="stylus">
    .c_row-item
        margin-top 10px
        margin-bottom 10px
        >>> .ivu-select-dropdown
            left -27px !important
    .color-box
        display flex
        align-items center
        justify-content flex-end
        .color-item
            margin-left 15px
            span
                margin-left 5px
                color #999
                font-size 13px
                cursor pointer
</style>
