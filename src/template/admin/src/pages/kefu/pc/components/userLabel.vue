<template>
    <div class="label-wrapper">
        <div class="label-box" v-for="(item,index) in labelList" :key="index">
            <div class="title">{{item.name}}</div>
            <div class="list">
                <div class="label-item" :class="{on:label.disabled}" v-for="(label,j) in item.label" :key="j" @click="selectLabel(label)">{{label.label_name}}</div>
            </div>
        </div>
        <div class="footer">
            <Button type="primary" class="btns" @click="subBtn">确定</Button>
            <Button type="primary" class="btns" ghost @click="cancel">取消</Button>
        </div>
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
            }
        },
        data(){
            return {
                labelList:[],
                activeIds:[]
            }
        },
        mounted() {
            this.getList()
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
                if(label.disabled){
                    let index = this.activeIds.indexOf(label.id)
                    this.activeIds.splice(index,1)
                    label.disabled = false
                }else{
                    this.activeIds.push(label.id)
                    label.disabled = true
                }
            },
            // 确定
            subBtn(){
                let unLaberids = [];
                this.labelList.map(item=>{
                    item.label.map( i => {
                        if(i.disabled == false){
                            unLaberids.push(i.id);
                        }
                    });
                });
                userLabelPut(this.uid,{
                    label_ids: this.activeIds,
                    un_label_ids: unLaberids
                }).then(res=>{
                    this.$Message.success(res.msg)
                    this.$emit('editLabel')
                }).catch(error=>{
                    this.$Message.error(error.msg)
                })
            },
            cancel(){
                this.$emit('close')
            }
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
