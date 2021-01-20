<template>
    <div class="goods-box" v-if="defaults.goodsList">
        <div class="wrapper">
            <draggable
                    class="dragArea list-group"
                    :list="defaults.goodsList.list"
                    group="peoples"
            >
                <div class="item" v-for="(goods,index) in defaults.goodsList.list" :key="index" v-if="defaults.goodsList.list.length">
                    <img :src="type?goods.pic:goods.image" alt="">
                    <span class="iconfont icondel_1" @click.stop="bindDelete(index)"></span>
                </div>
                <div class="add-item item" @click="modals = true"><span class="iconfont iconaddto"></span></div>
            </draggable>

        </div>

        <Modal v-model="modals" :loading="loading" :title="type?'分类列表':'商品列表'" class="paymentFooter" scrollable width="900" @on-cancel="cancel" @on-ok="ok">
            <sort-list ref="goodslist"  @getProductDiy="getProductDiy" v-if="modals && type"></sort-list>
            <goods-list ref="goodslist"  @getProductDiy="getProductDiy" :ischeckbox="true" :diy="true" v-if="modals && !type"></goods-list>
        </Modal>
    </div>
</template>

<script>
    import vuedraggable from 'vuedraggable'
    import goodsList from '@/components/goodsList'
    import sortList from '@/components/sortList'
    export default {
        name: 'c_goods',
        props: {
            name: {
                type: String
            },
            configData:{
                type:null
            }
        },
        components: {
            goodsList,
            sortList,
            draggable: vuedraggable
        },
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.defaults = nVal
                    this.type = nVal.selectConfig.type?nVal.selectConfig.type:''
                },
                immediate: true,
                deep: true
            }
        },
        data () {
            return {
                modals: false,
                goodsList: [],
                tempGoods: [],
                defaults:{},
                type:'',
                loading:true
            }
        },
        created () {
            this.defaults = this.configData
        },
        methods: {
            getProductDiy (data) {
                this.tempGoods = data
                this.loading = false
            },
            cancel () {
                this.tempGoods = []
            },
            //对象数组去重；
            unique(arr) {
                const res = new Map();
                return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1))
            },
            ok () {
                if(!this.tempGoods.length){
                    return this.$Message.warning('请先选择商品');
                }
                let list = this.defaults.goodsList.list;
                list.push.apply(list,this.tempGoods);
                // list.push(this.tempGoods);
                let picList = this.unique(list);
                this.defaults.goodsList.list= picList;
                console.log('kkkkk',picList);
                // this.defaults.goodsList.list.push(this.tempGoods);
            },
            bindDelete (index) {
                this.defaults.goodsList.list.splice(index, 1)
            }
        }
    }
</script>

<style scoped lang="stylus">
    .goods-box
        padding 16px 0
        margin-bottom 16px
        border-top 1px solid rgba(0,0,0,0.05)
        border-bottom 1px solid rgba(0,0,0,0.05)
        .wrapper,.list-group
            display flex
            flex-wrap wrap
        .add-item
            display flex
            align-items center
            justify-content center
            width 80px
            height 80px
            margin-bottom 10px
            background #F7F7F7
            .iconfont
                font-size 18px
                color #D8D8D8
        .item
            position relative
            width 80px
            height 80px
            margin-bottom 20px
            margin-right 12px
            &:nth-child(4n)
               margin-right 0
            img
                width 100%
                height 100%
            .icondel_1
                position absolute
                right -10px
                top -16px
                color #999999
                font-size 28px
                cursor pointer
</style>

