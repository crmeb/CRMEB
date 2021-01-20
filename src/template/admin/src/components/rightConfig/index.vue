<template>
    <div class="right-box" v-if="rCom.length">
        <div class="title-bar">模块配置</div>
        <div class="mobile-config" v-if="rCom.length">

            <div v-for="(item,key) in rCom" :key="key">
                <component :is="item.components.name" :name="item.configNme" :configData="configData"></component>
            </div>
            <div style="text-align: center;" v-if="rCom.length">
                <Button type="primary" style="width:100%;margin:0 auto;height: 40px" @click="saveConfig">保存</Button>
            </div>
        </div>
    </div>

</template>

<script>
    import { getCategory, getByCategory, getProduct, diySave } from '@/api/diy'
    import toolCom from '@/components/diyComponents/index.js'
    import { mapMutations } from 'vuex'
    import { mapState } from 'vuex'
    export default {
        name: "rightConfig",
        components:{
          ...toolCom
        },
        props:{
            name: {
                type: null,
                default:''
            },
            pageId: {
                type: Number,
                default: 0
            }
        },
        computed:{
            // ...mapState({
            //     defultArr:(state)=>state.goodSelect.component,
            // })
            defultArr(){
                let names = this.$store.state.userInfo.pageName;
                return this.$store.state[names].component;
            }
        },
        watch:{
            name:{
                handler (nVal, oVal) {
                    this.rCom = [];
                    let diy = this.$store.state.userInfo.pageName;
                    this.configData = this.$store.state[diy].defaultConfig[nVal];
                    this.rCom = this.$store.state[diy].component[nVal].list;
                    if(this.configData.selectConfig){
                        let type = this.configData.selectConfig.type?this.configData.selectConfig.type:'';
                        this.configData.selectConfig.list = this.categoryList;
                        if(type){
                            this.getByCategory();
                        }else {
                            this.getCategory();
                        }
                    }
                },
                deep: true
            },
            'defultArr': {
                handler (nVal, oVal) {
                    this.rCom = []
                    let tempArr = this.objToArray(nVal)
                    this.rCom =nVal [this.name].list
                },
                deep: true
            }
        },
        data(){
            return {
                rCom:[],
                configData:{},
                isShow:true,
                categoryList:[]
            }
        },
        mounted() {},
        methods:{
            getCategory () {
                getCategory().then(res => {
                    let data = [];
                    res.data.map(item => {
                        data.push({ title: item.title, pid: item.pid, activeValue: item.id.toString() });
                    });
                    this.categoryList = data;
                    this.bus.$emit('upData',data)
                })
            },
            //获取二级分类
            getByCategory () {
                getByCategory().then(res=>{
                    let data = [];
                    res.data.map(item => {
                        data.push({ title: item.cate_name, pid: item.pid, activeValue: item.id.toString() });
                    });
                    this.categoryList = data;
                    this.bus.$emit('upData',data)
                })
            },
            // 保存数据
            saveConfig(){
                let diy = this.$store.state.userInfo.pageName;
                let data = this.$store.state[diy].defaultConfig;
                diySave(this.pageId,{
                    value:data
                }).then(res=>{
                    this.$Message.success('保存成功')
                })
            },
            // 对象转数组
            objToArray(array) {
                var arr = []
                for (var i in array) {
                    arr.push(array[i]);
                }
                return arr;
            }
        }
    }
</script>

<style scoped lang="stylus">
    .right-box
        width 400px
        margin-left 50px
        border:1px solid #ddd;
        border-radius 4px
        height 700px
        overflow-y scroll
        &::-webkit-scrollbar {
            /*滚动条整体样式*/
            width : 4px;  /*高宽分别对应横竖滚动条的尺寸*/
            height: 1px;
        }
        &::-webkit-scrollbar-thumb {
            /*滚动条里面小方块*/
            border-radius: 4px;
            box-shadow   : inset 0 0 5px rgba(0, 0, 0, 0.2);
            background   : #535353;
        }
        &::-webkit-scrollbar-track {
            /*滚动条里面轨道*/
            box-shadow   : inset 0 0 5px #fff;
            border-radius: 4px;
            background   : #fff;
        }
    .title-bar
        width 100%
        height 38px
        line-height 38px
        padding-left 24px
        color #333
        border-radius 4px
        border-bottom 1px solid #eee
</style>
