<template>
    <div>
        <!--v-if="item.count && item.count!='0.00'"-->
        <Row type="flex" align="middle" :gutter="10" class="ivu-mt">
            <Col :xl="item.col" :lg="6" :md="12" :sm="24" :xs="24" class="ivu-mb" v-for="(item, index) in cardLists"
                 :key="index">
                <Card shadow :padding="0" class="card_cent">
                    <div class="card_box">
                        <div class="card_box_cir" :class="{'one':index%5==0,'two':index%5==1,'three':index%5==2,'four':index%5==3,'five':index%5==4}">
                            <div class="card_box_cir1" :class="{'one1':index%5==0,'two1':index%5==1,'three1':index%5==2,'four1':index%5==3,'five1':index%5==4}">
                                <Icon :type="item.className" />
                            </div>
                        </div>
                        <div class="card_box_txt">
                            <span class="sp1" v-text="item.count || 0"></span>
                            <span class="sp2" v-text="item.name"></span>
                        </div>
                    </div>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
export default {
  name: 'cards',
  data () {
    return {
    }
  },
  props: {
    cardLists: Array
  },
  methods: {
  },
  created () {
  }
}
</script>

<style scoped lang="stylus">
    /*.card_cent >>> .ivu-card-body*/
    /*    width 100%*/
    /*    height 100%*/
    .card_box_cir1 >>> .ivu-icon
       font-size: 26px
       color: #fff
     .one
        background #E4ECFF
    .two
        background #FFF3E0
    .three
        background #EAF9E1
    .four
        background #FFEAF4
    .five
        background #F1E4FF
    .one1
        background #4D7CFE
    .two1
        background #FFAB2B
    .three1
        background #6DD230
    .four1
        background #FF85C0
    .five1
        background #B37FEB
    .card_box
        width 100%
        height 100%
        display flex
        align-items: center
        /*justify-content: center*/
        padding: 25px
        box-sizing: border-box
        border-radius: 4px
        .card_box_cir
            width 60px
            height 60px
            border-radius: 50%
            overflow: hidden
            margin-right: 20px
            display: flex
            justify-content: center
            align-items: center
            .card_box_cir1
                width 48px
                height 48px
                border-radius: 50%
                display: flex
                justify-content: center
                align-items: center
        .card_box_txt
            .sp1
                display block
                color #252631
                font-size 24px
            .sp2
                display block
                color #98A9BC
                font-size 12px

</style>
