<template>
    <div  v-if="FromData">
        <Modal v-model="modals" scrollable  footer-hide closable :title="FromData.title"  :z-index="1" width="700" @on-cancel="cancel">
            <template>
                <div class="radio acea-row row-middle" v-if="FromData.action === '/marketing/coupon/save.html'">
                    <div class="name ivu-form-item-content">优惠券类型</div>
                    <Radio-group v-model="type" @on-change="couponsType">
                        <Radio :label=0>通用券</Radio>
                        <Radio :label=1>品类券</Radio>
                        <Radio :label=2>商品券</Radio>
                    </Radio-group>
                </div>
            </template>
            <form-create :option="config" :rule="Array.from(FromData.rules)" @on-submit="onSubmit"  class="formBox" ref="fc" handleIcon="false"></form-create>
        </Modal>
    </div>
</template>

<script>
import formCreate from '@form-create/iview'
import request from '@/libs/request'
import { mapState } from 'vuex'
export default {
  name: 'edit',
  components: {
    formCreate: formCreate.$form()
  },
  computed: {
    ...mapState('userLevel', [
      'taskId',
      'levelId'
    ])
  },
  props: {
    FromData: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      modals: false,
      type: 0,
      config: {
        global: {
          upload: {
            props: {
              onSuccess (res, file) {
                if (res.status === 200) {
                  file.url = res.data.src
                } else {
                  this.Message.error(res.msg)
                }
              }
            }
          }
        }
      }
    }
  },
  methods: {
    couponsType () {
      this.$parent.addType(this.type)
    },
    // 提交表单 group
    onSubmit (formData) {
      let datas = {}
      datas = formData
      request({
        url: this.FromData.action,
        method: this.FromData.method,
        data: datas
      }).then(res => {
        this.$parent.getList()
        this.$Message.success(res.msg)
        this.modals = false
        setTimeout(() => {
          this.$emit('submitFail')
        }, 1000)
      }).catch(res => {
        this.$Message.error(res.msg)
      })
    },
    // 关闭按钮
    cancel () {
      this.type = 0
      // this.$emit('onCancel')
    }
  }
}
</script>

<style scoped lang="stylus">
    .v-transfer-dom >>> .ivu-modal-content-drag{
        z-index: 2!important;
    }
    .radio{
        margin-bottom:14px;
    }
    .radio >>> .name{
        width: 125px;
        text-align: right;
        padding-right: 12px;
    }
</style>
