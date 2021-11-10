<template>
    <div class="label-wrapper">
        <div class="label-box">
            <div class="list">
                <div class="label-item" :class="{on:label.id == groupId}" v-for="(label,j) in labelList" :key="j" @click="selectLabel(label)">{{label.group_name}}</div>
            </div>
        </div>
        <!-- <div class="footer">
            <Button type="primary" class="btns" @click="subBtn">确定</Button>
            <Button type="primary" class="btns" ghost @click="cancel">取消</Button>
        </div> -->
    </div>
</template>

<script>
    import { userLabel,userLabelPut } from '@/api/kefu'
    export default {
        name: "userLabel",
        props:{
            uid:{
                type:String | Number,
                default:''
            },
            groupId:{
                type:String | Number,
                default:''
            },
            labelList:{
                type:Array,
                default:()=>{
                    []
                }
            }
        },
        data(){
            return {
                activeIds:[],
                labelLists
            }
        },
        methods:{
            getList(){
                userLabel(this.uid).then(res=>{
                    res.data.map(el=>{
                        el.label.map(label=>{
                            if(label.disabled){
                                this.activeIds.push(label.id)
                            }
                        })
                    })
                    this.labelList =res.data
                })
            },
            selectLabel(label){
                this.$emit('editUserLabel',label.id)
            },
        }
    }
</script>

<style lang="stylus" scoped>
.label-wrapper
    .list
        display flex
        flex-wrap wrap
        .label-item
            margin 10px 8px 10px 0
            padding: 3px 8px;
            background #EEEEEE
            color #333333
            border-radius 2px
            cursor pointer
            font-size 12px
            &.on
                color #fff
                background #1890FF
    .footer
        display flex
        justify-content flex-end
        margin-top 40px
        button
            margin-left 10px
.btn
    width 60px
    height 24px
.title
    font-size 13px

</style>
