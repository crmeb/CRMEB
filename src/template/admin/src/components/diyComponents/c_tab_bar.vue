<template>
    <div class="tabBars">
        <div class="title">{{datas[name].title}}</div>
        <div class="header acea-row row-middle">
            <div class="item">名称</div>
            <div class="item">选中</div>
            <div class="item">未选中</div>
        </div>
        <div class="list">
            <div class="module acea-row row-middle" v-for="(item,index) in datas[name].list" :key="index">
                <div class="item">{{item.name}}</div>
                <div class="item" @click="modalPicTap('单选',index,1)"><img :src="item.selectedIconPath" alt=""></div>
                <div class="item" @click="modalPicTap('单选',index,0)"><img :src="item.iconPath" alt=""></div>
            </div>
        </div>
        <div>
            <Modal v-model="modalPic" width="60%" scrollable footer-hide closable title='上传商品图'
                   :mask-closable="false" :z-index="1">
                <uploadPictures :isChoice="isChoice" @getPic="getPic" :gridBtn="gridBtn" :gridPic="gridPic"
                                v-if="modalPic"></uploadPictures>
            </Modal>
        </div>
    </div>
</template>

<script>
    import uploadPictures from '@/components/uploadPictures';
    export default {
        name: 'c_tab_bar',
        props: {
            name: {
                type: String
            },
            configData:{
                type:null
            }
        },
        components: {
            uploadPictures
        },
        data () {
            return {
                defaults: {},
                modalPic: false,
                isChoice: '单选',
                gridBtn: {
                    xl: 4,
                    lg: 8,
                    md: 8,
                    sm: 8,
                    xs: 8
                },
                gridPic: {
                    xl: 6,
                    lg: 8,
                    md: 12,
                    sm: 12,
                    xs: 12
                },
                activeIndex: 0,
                datas: this.configData,
                isSelect:1
            }
        },
        mounted () {},
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.datas = nVal
                },
                deep: true
            }
        },
        methods: {
            // 点击图文封面
            modalPicTap(title, index, select) {
                this.activeIndex = index
                this.modalPic = true;
                this.isSelect = select;
            },
            // 获取图片信息
            getPic(pc) {
                this.$nextTick(() => {
                    if(this.isSelect){
                        this.configData[this.name].list[this.activeIndex].selectedIconPath = pc.att_dir;
                    }else {
                        this.configData[this.name].list[this.activeIndex].iconPath = pc.att_dir;
                    }
                    this.modalPic = false;
                })
            }
        }
    }
</script>
<style scoped lang="stylus">
    .tabBars
           .title
                margin-bottom: 10px;
                padding-bottom 10px
                border-bottom:1px solid rgba(0,0,0,0.05);
                font-size:12px;
                color:#999;
           .header
                 .item
                      width 33.33%
                      text-align center
                      font-weight bold
           .list
                margin-bottom 20px
               .module
                     .item
                         width 33.33%
                         text-align center
                         padding 15px 0
                         img
                          width 60px
                          height 60px
                          display block
                          margin 0 auto
</style>