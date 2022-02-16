<template>
    <div>
        <div class="c_row-item" v-if="configData">
            <Col span="8" class="c_label" >{{configData.title}}</Col>
            <Col span="14" class="color-box">
                <div class="color-item" v-for="(color,key) in configData.color" :key="key">
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
            configObj: {
                type: Object
            },
            configNme: {
                type: String
            }
        },
        data () {
            return {
                defaults: {
                },
                configData: {},
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
            this.defaults = this.configObj
            this.configData = this.configObj[this.configNme]
        },
        watch: {
            configObj: {
                handler (nVal, oVal) {
                    this.defaults = nVal
                    this.configData = nVal[this.configNme]
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
                color.item = this.configData.default[key].item
            }
        }
    }
</script>

<style scoped lang="stylus">
    .c_row-item
        margin-bottom 20px
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
