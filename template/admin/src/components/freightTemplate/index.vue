<template>
  <div>
    <el-dialog
      :visible.sync="isTemplate"
      title="运费模版"
      width="1000px"
      if="isTemplate"
      @on-cancel="cancel"
      @closed="close"
    >
      <div class="Modals">
        <el-form class="form" ref="formData" label-width="120px" label-position="right">
          <el-row :gutter="24">
            <el-col :xl="18" :lg="18" :md="18" :sm="24" :xs="24">
              <el-form-item label="模板名称：" prop="name">
                <el-input type="text" placeholder="请输入模板名称" :maxlength="20" v-model="formData.name" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="18" :lg="18" :md="18" :sm="24" :xs="24">
              <el-form-item label="计费方式：" props="state" label-for="state">
                <el-radio-group class="radio" v-model="formData.type" @input="changeRadio" element-id="state">
                  <el-radio :label="1">按件数</el-radio>
                  <el-radio :label="2">按重量</el-radio>
                  <el-radio :label="3">按体积</el-radio>
                </el-radio-group>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item class="label" label="配送区域及运费：" props="state" label-for="state">
                <el-table ref="table" :data="templateList" class="ivu-mt" empty-text="暂无数据" border>
                  <el-table-column label="可配送区域" minWidth="100">
                    <template slot-scope="scope">
                      <el-input v-model="templateList[scope.$index].regionName" />
                    </template>
                  </el-table-column>
                  <el-table-column
                    :label="formData.type === 2 ? '首件重量(KG)' : formData.type === 3 ? '首件体积(m³)' : '首件'"
                    minWidth="100"
                  >
                    <template slot-scope="scope">
                      <el-input type="number" v-model="templateList[scope.$index].first" />
                    </template>
                  </el-table-column>
                  <el-table-column label="运费（元）" minWidth="100">
                    <template slot-scope="scope">
                      <el-input type="number" v-model="templateList[scope.$index].price" />
                    </template>
                  </el-table-column>
                  <el-table-column
                    :label="formData.type === 2 ? '续件重量(KG)' : formData.type === 3 ? '续件体积(m³)' : '续件'"
                    minWidth="100"
                  >
                    <template slot-scope="scope">
                      <el-input type="number" v-model="templateList[scope.$index].continue" />
                    </template>
                  </el-table-column>
                  <el-table-column label="续费（元）" minWidth="100">
                    <template slot-scope="scope">
                      <el-input type="number" v-model="templateList[scope.$index].continue_price" />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" fixed="right" width="100">
                    <template slot-scope="scope">
                      <a
                        v-if="scope.row.regionName !== '默认全国'"
                        v-db-click
                        @click="delCity(scope.row, '配送区域', scope.$index, 1)"
                        >删除</a
                      >
                    </template>
                  </el-table-column>
                </el-table>
                <el-row class="addTop">
                  <el-col>
                    <el-button type="primary" icon="md-add" v-db-click @click="addCity(1)">添加配送区域</el-button>
                  </el-col>
                </el-row>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item label="指定包邮：" prop="store_name" label-for="store_name">
                <el-radio-group class="radio" v-model="formData.appoint_check">
                  <el-radio :label="1">开启</el-radio>
                  <el-radio :label="0">关闭</el-radio>
                </el-radio-group>
                <el-table
                  ref="table"
                  :data="appointList"
                  class="addTop mt10"
                  empty-text="暂无数据"
                  border
                  v-if="formData.appoint_check === 1"
                >
                  <el-table-column label="选择区域" minWidth="100">
                    <template slot-scope="scope">
                      <el-input v-model="appointList[scope.$index].placeName" />
                    </template>
                  </el-table-column>
                  <el-table-column
                    :label="formData.type === 2 ? '包邮重量' : formData.type === 3 ? '包邮体积(m³)' : '包邮件数'"
                    minWidth="100"
                  >
                    <template slot-scope="scope">
                      <el-input type="number" v-model="appointList[scope.$index].a_num" />
                    </template>
                  </el-table-column>
                  <el-table-column label="包邮金额（元）" minWidth="100">
                    <template slot-scope="scope">
                      <el-input type="number" v-model="appointList[scope.$index].a_price" />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" fixed="right" width="100">
                    <template slot-scope="scope">
                      <a
                        v-if="scope.row.regionName !== '默认全国'"
                        v-db-click
                        @click="delCity(scope.row, '配送区域', scope.$index, 2)"
                        >删除</a
                      >
                    </template>
                  </el-table-column>
                </el-table>
                <div v-if="formData.appoint_check === 1" class="free_tips">
                  指定地区需同时满足包邮（件数/重量/体积）和包邮金额的条件，才可实现包邮
                </div>
                <el-row class="addTop mt5" v-if="formData.appoint_check === 1">
                  <el-col>
                    <el-button type="primary" icon="md-add" v-db-click @click="addCity(2)">添加包邮区域</el-button>
                  </el-col>
                </el-row>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item label="指定不送达：" prop="store_name" label-for="store_name">
                <el-radio-group class="radio" v-model="formData.no_delivery_check">
                  <el-radio :label="1">开启</el-radio>
                  <el-radio :label="0">关闭</el-radio>
                </el-radio-group>
                <el-table
                  ref="table"
                  :data="noDeliveryList"
                  class="addTop mt10"
                  empty-text="暂无数据"
                  border
                  v-if="formData.no_delivery_check === 1"
                >
                  <el-table-column label="选择区域" minWidth="100">
                    <template slot-scope="scope">
                      <el-input v-model="noDeliveryList[scope.$index].placeName" />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" fixed="right" width="100">
                    <template slot-scope="scope">
                      <a
                        v-if="scope.row.regionName !== '默认全国'"
                        v-db-click
                        @click="delCity(scope.row, '配送区域', scope.$index, 3)"
                        >删除</a
                      >
                    </template>
                  </el-table-column>
                </el-table>
                <el-row class="addTop" v-if="formData.no_delivery_check === 1">
                  <el-col>
                    <el-button type="primary" icon="md-add" v-db-click @click="addCity(3)">添加不送达区域</el-button>
                  </el-col>
                </el-row>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="18" :lg="18" :md="18" :sm="24" :xs="24">
              <el-form-item label="排序：" prop="store_name" label-for="store_name">
                <el-input-number
                  :controls="false"
                  :min="0"
                  placeholder="输入值越大越靠前"
                  v-model="formData.sort"
                ></el-input-number>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col>
              <el-form-item prop="store_name" label-for="store_name">
                <el-button type="primary" v-db-click @click="handleSubmit">{{
                  id ? '立即修改' : '立即提交'
                }}</el-button>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>
      <div slot="footer"></div>
    </el-dialog>
    <city ref="city" @selectCity="selectCity" :type="type" :selectArr="selectArr"></city>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import city from '@/components/freightTemplate/city';
import { templatesSaveApi, shipTemplatesApi } from '@/api/setting';
export default {
  name: 'freightTemplate',
  components: { city },
  props: {},
  data() {
    let that = this;
    return {
      isTemplate: false,
      templateList: [
        {
          region: [
            {
              name: '默认全国',
              city_id: 0,
            },
          ],
          regionName: '默认全国',
          first: 1,
          price: 0,
          continue: 1,
          continue_price: 0,
        },
      ],
      appointList: [],
      noDeliveryList: [],
      type: 1,
      formData: {
        type: 1,
        sort: 0,
        name: '',
        appoint_check: 0,
        no_delivery_check: 0,
      },
      id: 0,

      addressModal: false,
      indeterminate: true,
      checkAll: false,
      checkAllGroup: [],
      activeCity: -1,
      provinceAllGroup: [],
      index: -1,
      displayData: '',
      currentProvince: '',
      selectArr: [], // 传递选中的城市
      noShippingArr: [], // 不包邮选择的城市数据
      yesShippingArr: [], // 包邮选择的城市数据
      noDeliveryArr: [], // 不送达选择的城市数据
    };
  },
  computed: {},
  methods: {
    close() {
      this.$emit('close');
    },
    editFrom(id) {
      this.id = id;
      shipTemplatesApi(id).then((res) => {
        let formData = res.data.formData;
        this.templateList = res.data.templateList;
        this.appointList = res.data.appointList;
        this.noDeliveryList = res.data.noDeliveryList;
        this.formData = {
          type: formData.type,
          sort: formData.sort,
          name: formData.name,
          appoint_check: formData.appoint_check,
          no_delivery_check: formData.no_delivery_check,
        };
        // this.headerType();
      });
    },
    selectCity: function (data, type) {
      let cityName = data
        .map(function (item) {
          return item.name;
        })
        .join(';');
      switch (type) {
        case 1:
          this.templateList.push({
            region: data,
            regionName: cityName,
            first: 1,
            price: 0,
            continue: 1,
            continue_price: 0,
          });
          this.noShippingArr = this.noShippingArr.concat(data);
          break;
        case 2:
          this.appointList.push({
            place: data,
            placeName: cityName,
            a_num: 0,
            a_price: 0,
          });
          this.yesShippingArr = this.yesShippingArr.concat(data);
          break;
        case 3:
          this.noDeliveryList.push({
            place: data,
            placeName: cityName,
          });
          this.noDeliveryArr = this.noDeliveryArr.concat(data);
          break;
      }
    },
    // 单独添加配送区域
    addCity(type) {
      this.selectArr = type == 1 ? this.noShippingArr : type == 2 ? this.yesShippingArr : this.noDeliveryArr;
      this.type = type;
      this.$refs.city.getCityList();
      this.$refs.city.addressModal = true;
    },
    changeRadio() {},
    // 提交
    handleSubmit: function () {
      let that = this;
      if (!that.formData.name.trim().length) {
        return that.$message.error('请填写模板名称');
      }
      for (let i = 0; i < that.templateList.length; i++) {
        if (that.templateList[i].first <= 0) {
          return that.$message.error('首件/重量/体积应大于0');
        }
        if (that.templateList[i].price < 0) {
          return that.$message.error('运费应大于等于0');
        }
        if (that.templateList[i].continue <= 0) {
          return that.$message.error('续件/重量/体积应大于0');
        }
        if (that.templateList[i].continue_price < 0) {
          return that.$message.error('续费应大于等于0');
        }
      }
      if (that.formData.appoint_check === 1) {
        for (let i = 0; i < that.appointList.length; i++) {
          if (that.appointList[i].a_num <= 0) {
            return that.$message.error('包邮件数应大于0');
          }
          if (that.appointList[i].a_price < 0) {
            return that.$message.error('包邮金额应大于等于0');
          }
        }
      }
      let data = {
        appoint_info: that.appointList,
        region_info: that.templateList,
        no_delivery_info: that.noDeliveryList,
        sort: that.formData.sort,
        type: that.formData.type,
        name: that.formData.name,
        appoint: that.formData.appoint_check,
        no_delivery: that.formData.no_delivery_check,
      };
      templatesSaveApi(that.id, data).then((res) => {
        this.isTemplate = false;
        // this.$parent.getList();
        this.formData = {
          type: 1,
          sort: 0,
          name: '',
          appoint_check: 0,
          no_delivery_check: 0,
        };
        this.appointList = [];
        this.noDeliveryList = [];
        this.addressModal = false;
        this.templateList = [
          {
            region: [
              {
                name: '默认全国',
                city_id: 0,
              },
            ],
            regionName: '默认全国',
            first: 1,
            price: 0,
            continue: 1,
            continue_price: 0,
          },
        ];
        this.$emit('addSuccess');
        this.$message.success(res.msg);
      });
    },
    // 删除
    delCity(row, tit, num, type) {
      if (type === 1) {
        this.templateList.splice(num, 1);
      } else if (type == 2) {
        this.appointList.splice(num, 1);
      } else {
        this.noDeliveryList.splice(num, 1);
      }
      //   let delfromData = {
      //     title: tit,
      //     num: num,
      //     url: `setting/shipping_templates/del/${row.id}`,
      //     method: "DELETE",
      //     ids: "",
      //   };
      //   this.$modalSure(delfromData)
      //     .then((res) => {
      //       this.$message.success(res.msg);
      //     })
      //     .catch((res) => {
      //       this.$message.error(res.msg);
      //     });
    },
    // 关闭
    cancel() {
      this.noShippingArr = [];
      this.noDeliveryArr = [];
      this.yesShippingArr = [];
      this.selectArr = [];
      this.formData = {
        type: 1,
        sort: 0,
        name: '',
        appoint_check: 0,
        no_delivery_check: 0,
      };
      this.appointList = [];
      this.noDeliveryList = [];
      this.addressModal = false;
      this.templateList = [
        {
          region: [
            {
              name: '默认全国',
              city_id: 0,
            },
          ],
          regionName: '默认全国',
          first: 0,
          price: 0,
          continue: 0,
          continue_price: 0,
        },
      ];
    },

    address() {
      this.addressModal = true;
    },
    enter(index) {
      this.activeCity = index;
    },
    leave() {
      this.activeCity = null;
    },
  },
  mounted() {},
};
</script>
<style lang="scss" scoped>
.ivu-table-wrapper {
  border-left: 1px solid #dcdee2;
  border-top: 1px solid #dcdee2;
}
.ivu-table-border th,
.ivu-table-border td {
  padding: 0 10px !important;
}
.addTop {
  margin-top: 15px;
}
.radio {
  padding: 5px 0;
}
.ivu-input-number {
  width: 100%;
}
.free_tips {
  font-size: 12px;
  color: #ccc;
}
</style>
