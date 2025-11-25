<template>
  <div>
    <el-dialog :visible.sync="visible" title="自定义会员价" width="900"
      ><el-form :model="formData" label-width="120px">
        <el-form-item label="付费会员专属：">
          <el-switch
            v-model="formData.vip_product"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
            size="large"
            class="defineSwitch"
          >
          </el-switch>
        </el-form-item>
        <el-form-item label="付费会员价：">
          <el-switch
            v-model="formData.is_vip"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
            size="large"
            class="defineSwitch"
            @change="changeVip"
          >
          </el-switch>
        </el-form-item>
        <el-form-item label-width="0px">
          <el-table size="small" border max-height="460" :data="attrData" style="width: 100%">
            <el-table-column prop="pic" label="规格图" min-width="90" align="center">
              <template slot-scope="scope">
                <div class="tabBox_img m-auto" v-viewer>
                  <img v-lazy="scope.row.pic" />
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="suk" label="产品规格" min-width="120" align="center"></el-table-column>
            <el-table-column prop="price" label="售价" min-width="120" align="center"></el-table-column>
            <el-table-column min-width="140" align="center" v-if="formData.is_vip == 1">
              <template slot="header" slot-scope="scope">
                <span>付费会员价</span>
                <el-popover
                  ref="vipSetPopover"
                  :value.sync="vipSetPopoverPopver"
                  placement="top"
                  width="290"
                  trigger="click"
                >
                  <div class="pop-title">批量修改本列</div>
                  <div class="mt-14">
                    <el-radio-group v-model="vipSetType">
                      <el-radio :label="0">指定价格</el-radio>
                      <el-radio :label="1">折扣</el-radio>
                      <el-radio :label="2">减现</el-radio>
                    </el-radio-group>
                  </div>
                  <div class="mt10 mb10 acea-row row-middle">
                    <span class="mr5" v-show="vipSetType == 2">减</span>
                    <el-input type="number" class="popover-input" v-model="vipSetNum">
                      <template slot="suffix">
                        <span v-show="vipSetType == 0">元</span>
                        <span v-show="vipSetType == 1">%</span>
                      </template>
                    </el-input>
                    <div class="acea-row row-right row-middle ml14">
                      <el-button size="small" @click="closeVipSet">取消</el-button>
                      <el-button size="small" type="primary" class="ml-14" @click="vipSetConfirm">确认</el-button>
                    </div>
                  </div>
                  <span class="iconfont iconbianji1" slot="reference" @click.stop="vipSetPopoverPopver = true"></span>
                </el-popover>
              </template>
              <template slot-scope="scope">
                <el-input type="number" v-model="scope.row.vip_price" @change="vipRowReplace(scope.row)">
                  <template slot="suffix">
                    <span>元</span>
                  </template>
                </el-input>
                <div class="flex-x-center red" v-show="scope.row.vip_price == 0">会员价不可为0</div>
                <div class="flex-x-center red" v-show="Number(scope.row.vip_price) > Number(scope.row.price)">
                  会员价不可大于售价
                </div>
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="onCancel">取消</el-button>
        <el-button type="primary" @click="submitForm" :disabled="disabled" class="ml-14">确认</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import { productBrokerage, productBrokerageUpdate } from '@/api/product';
export default {
  name: 'vipPriceSet',
  data() {
    return {
      formData: {
        is_vip: 0,
        vip_product: 1,
      },
      brokerage: '',
      brokerage_two: '',
      loading: false,
      attrData: [],
      disabled: false,
      levelList: [],
      vipSetType: 0,
      vipSetNum: '',
      levelSetType: 0,
      levelSetNum: '',
      visible: false,
      vipSetPopoverPopver: false,
      popoverTwo: false,
    };
  },
  props: {
    productId: {
      type: Number,
      default: 0,
    },
  },
  watch: {
    visible(val) {
      if (val) {
        this.getData();
      }
    },
  },
  created() {},
  methods: {
    vipPriceReplace(value) {
      this.vipSetNum = value;
    },
    levelPriceReplace(value) {
      this.levelSetNum = value;
    },
    submitForm() {
      let isSuccess = true,
        step = true;
      if (this.formData.is_vip) {
        this.attrData.forEach((item) => {
          if (item.vip_price == 0 && this.formData.is_vip) {
            isSuccess = false;
          }
          if (Number(item.vip_price) > Number(item.price) && this.formData.is_vip) {
            step = false;
          }
        });
      }
      if (!isSuccess) return this.$message.error('会员价不可为0');
      if (!step) return this.$message.error('会员价不可大于售价');
      this.disabled = true;
      let data = {
        ...this.formData,
        attr_value: this.attrData,
      };
      productBrokerageUpdate(this.productId, 2, data)
        .then((res) => {
          this.$message.success(res.msg);
          this.disabled = false;
          this.visible = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
          this.disabled = false;
        });
    },
    closeVipSet(i) {
      console.log(this.$refs['vipSetPopover'].doClose);
      this.vipSetPopoverPopver = false;
      this.$refs.vipSetPopover.doClose();
      this.vipSetType = 0;
      this.vipSetNum = '';
    },
    closeLevelSet(i) {
      this.popoverTwo = false;
      // this.$refs['popoverRef_' + i].doClose(); //关闭的
      this.levelSetType = 0;
      this.levelSetNum = '';
    },
    vipSetConfirm() {
      if (this.vipSetNum == 0) return this.$message.error('会员价不可为0');
      if (this.vipSetType == 1 && this.vipSetNum > 100) return this.$message.error('折扣不可超过100');
      this.attrData.map((item) => {
        if (this.vipSetType == 0) {
          item.vip_price = this.vipSetNum;
        } else if (this.vipSetType == 1) {
          item.vip_price = ((this.vipSetNum / 100) * item.price).toFixed(2);
        } else {
          item.vip_price = item.price - this.vipSetNum;
        }
      });
      this.closeVipSet();
    },
    levelSetConfirm(index) {
      if (this.levelSetNum == 0) return this.$message.error('等级会员价不可为0');
      if (this.levelSetType == 1 && this.levelSetNum > 100) return this.$message.error('折扣不可超过100');
      this.attrData.map((item) => {
        if (this.levelSetType == 0) {
          item.level_price[index].price = this.levelSetNum;
        } else if (this.levelSetType == 1) {
          item.level_price[index].price = ((this.levelSetNum / 100) * item.price).toFixed(2);
        } else {
          item.level_price[index].price = item.price - this.levelSetNum;
        }
      });
      this.closeLevelSet(index);
    },
    onCancel() {
      this.visible = false;
    },
    changeLevelType(type) {
      this.attrData.map((item) => {
        this.$set(
          item,
          'level_price',
          this.levelList.map((val) => {
            return {
              id: val.id,
              price: type == 1 ? val.discount * 100 : 0,
            };
          }),
        );
      });
    },
    vipRowReplace(row) {
      // row.vip_price = row.vip_price.replace(/^-|\D/g, '');
    },
    levelRowReplace(row, i) {
      row.level_price[i].price = row.level_price[i].price.replace(/^-|\D/g, '');
    },
    getData() {
      //获取产品属性
      productBrokerage(this.productId, 2).then((res) => {
        this.formData.is_vip = res.data.storeInfo.is_vip;
        this.formData.vip_product = res.data.storeInfo.vip_product;
        this.attrData = Object.values(res.data.attrValue);
        // this.levelList = res.data.level_list;
        // if (res.data.level_list.length) {
        //   this.attrData.map((item) => {
        //     if (!item.level_price.length) {
        //       this.$set(
        //         item,
        //         'level_price',
        //         res.data.level_list.map((val) => {
        //           return {
        //             id: val.id,
        //             price: res.data.storeInfo.is_vip == 1 ? val.discount * 100 : 0,
        //           };
        //         }),
        //       );
        //     }
        //   });
        // }
      });
    },
    changeVip(is_vip) {
      this.attrData.map((item) => {
        this.$set(item, 'vip_price', is_vip == 1 ? item.price : 0);
      });
    },
  },
};
</script>
<style scoped>
.iconbianji1 {
  font-size: 12px;
  padding-left: 4px;
  cursor: pointer;
}

.popover-input {
  width: 100px;
}

.w-250 {
  width: 250px;
}

.pop-title {
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 14px;
}

.red {
  color: #e93323;
}

.h-23 {
  height: 23px;
}
</style>
