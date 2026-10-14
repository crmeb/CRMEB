<template>
  <div>
    <el-dialog v-model="isTemplate" title="运费模板" width="1000px" if="isTemplate" @on-cancel="cancel" @closed="close">
      <div class="Modals">
        <el-form class="form" ref="formDataRef" label-width="120px" label-position="right">
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
                  <el-radio :label="1" :value="1">按件数</el-radio>
                  <el-radio :label="2" :value="2">按重量</el-radio>
                  <el-radio :label="3" :value="3">按体积</el-radio>
                </el-radio-group>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item class="label" label="配送区域及运费：" props="state" label-for="state">
                <el-table ref="table" :data="templateList" class="ivu-mt" empty-text="暂无数据" border>
                  <el-table-column label="可配送区域" minWidth="100">
                    <template #default="scope">
                      <el-input v-model="templateList[scope.$index].regionName" />
                    </template>
                  </el-table-column>
                  <el-table-column
                    :label="formData.type === 2 ? '首件重量(KG)' : formData.type === 3 ? '首件体积(m³)' : '首件'"
                    minWidth="100"
                  >
                    <template #default="scope">
                      <el-input type="number" v-model="templateList[scope.$index].first" />
                    </template>
                  </el-table-column>
                  <el-table-column label="运费（元）" minWidth="100">
                    <template #default="scope">
                      <el-input type="number" v-model="templateList[scope.$index].price" />
                    </template>
                  </el-table-column>
                  <el-table-column
                    :label="formData.type === 2 ? '续件重量(KG)' : formData.type === 3 ? '续件体积(m³)' : '续件'"
                    minWidth="100"
                  >
                    <template #default="scope">
                      <el-input type="number" v-model="templateList[scope.$index].continue" />
                    </template>
                  </el-table-column>
                  <el-table-column label="续费（元）" minWidth="100">
                    <template #default="scope">
                      <el-input type="number" v-model="templateList[scope.$index].continue_price" />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" fixed="right" width="100">
                    <template #default="scope">
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
                    <el-button type="primary" v-db-click @click="addCity(1)">添加配送区域</el-button>
                  </el-col>
                </el-row>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item label="指定包邮：" prop="store_name" label-for="store_name">
                <el-radio-group class="radio" v-model="formData.appoint_check">
                  <el-radio :label="1" :value="1">开启</el-radio>
                  <el-radio :label="0" :value="0">关闭</el-radio>
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
                    <template #default="scope">
                      <el-input v-model="appointList[scope.$index].placeName" />
                    </template>
                  </el-table-column>
                  <el-table-column
                    :label="formData.type === 2 ? '包邮重量' : formData.type === 3 ? '包邮体积(m³)' : '包邮件数'"
                    minWidth="100"
                  >
                    <template #default="scope">
                      <el-input type="number" v-model="appointList[scope.$index].a_num" />
                    </template>
                  </el-table-column>
                  <el-table-column label="包邮金额（元）" minWidth="100">
                    <template #default="scope">
                      <el-input type="number" v-model="appointList[scope.$index].a_price" />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" fixed="right" width="100">
                    <template #default="scope">
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
                    <el-button type="primary" v-db-click @click="addCity(2)">添加包邮区域</el-button>
                  </el-col>
                </el-row>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item label="指定不送达：" prop="store_name" label-for="store_name">
                <el-radio-group class="radio" v-model="formData.no_delivery_check">
                  <el-radio :label="1" :value="1">开启</el-radio>
                  <el-radio :label="0" :value="0">关闭</el-radio>
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
                    <template #default="scope">
                      <el-input v-model="noDeliveryList[scope.$index].placeName" />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" fixed="right" width="100">
                    <template #default="scope">
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
                    <el-button type="primary" v-db-click @click="addCity(3)">添加不送达区域</el-button>
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
      <template #footer><div></div></template>
    </el-dialog>
    <city ref="cityRef" @selectCity="selectCity" :type="type" :selectArr="selectArr"></city>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import city from '@/components/freightTemplate/city';
import { templatesSaveApi, shipTemplatesApi } from '@/api/freight';

defineOptions({ name: 'freightTemplate' });

const emit = defineEmits(['close', 'addSuccess']);

const cityRef = ref(null);

const isTemplate = ref(false);
const templateList = ref([
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
]);
const appointList = ref([]);
const noDeliveryList = ref([]);
const type = ref(1);
const formData = ref({
  type: 1,
  sort: 0,
  name: '',
  appoint_check: 0,
  no_delivery_check: 0,
});
const id = ref(0);

const addressModal = ref(false);
const indeterminate = ref(true);
const checkAll = ref(false);
const checkAllGroup = ref([]);
const activeCity = ref(-1);
const provinceAllGroup = ref([]);
const index = ref(-1);
const displayData = ref('');
const currentProvince = ref('');
const selectArr = ref([]); // 传递选中的城市
const noShippingArr = ref([]); // 不包邮选择的城市数据
const yesShippingArr = ref([]); // 包邮选择的城市数据
const noDeliveryArr = ref([]); // 不送达选择的城市数据

function close() {
  emit('close');
}
function editFrom(editId) {
  id.value = editId;
  shipTemplatesApi(editId).then((res) => {
    let formDataRes = res.data.formData;
    templateList.value = res.data.templateList;
    appointList.value = res.data.appointList;
    noDeliveryList.value = res.data.noDeliveryList;
    formData.value = {
      type: formDataRes.type,
      sort: formDataRes.sort,
      name: formDataRes.name,
      appoint_check: formDataRes.appoint_check,
      no_delivery_check: formDataRes.no_delivery_check,
    };
    // headerType();
  });
}
function selectCity(data, selectType) {
  let cityName = data
    .map(function (item) {
      return item.name;
    })
    .join(';');
  switch (selectType) {
    case 1:
      templateList.value.push({
        region: data,
        regionName: cityName,
        first: 1,
        price: 0,
        continue: 1,
        continue_price: 0,
      });
      noShippingArr.value = noShippingArr.value.concat(data);
      break;
    case 2:
      appointList.value.push({
        place: data,
        placeName: cityName,
        a_num: 0,
        a_price: 0,
      });
      yesShippingArr.value = yesShippingArr.value.concat(data);
      break;
    case 3:
      noDeliveryList.value.push({
        place: data,
        placeName: cityName,
      });
      noDeliveryArr.value = noDeliveryArr.value.concat(data);
      break;
  }
}
// 单独添加配送区域
function addCity(addType) {
  selectArr.value = addType == 1 ? noShippingArr.value : addType == 2 ? yesShippingArr.value : noDeliveryArr.value;
  type.value = addType;
  cityRef.value.getCityList();
  cityRef.value.addressModal = true;
}
function changeRadio() {}
// 提交
function handleSubmit() {
  if (!formData.value.name.trim().length) {
    return ElMessage.error('请填写模板名称');
  }
  for (let i = 0; i < templateList.value.length; i++) {
    if (templateList.value[i].first <= 0) {
      return ElMessage.error('首件/重量/体积应大于0');
    }
    if (templateList.value[i].price < 0) {
      return ElMessage.error('运费应大于等于0');
    }
    if (templateList.value[i].continue <= 0) {
      return ElMessage.error('续件/重量/体积应大于0');
    }
    if (templateList.value[i].continue_price < 0) {
      return ElMessage.error('续费应大于等于0');
    }
  }
  if (formData.value.appoint_check === 1) {
    for (let i = 0; i < appointList.value.length; i++) {
      if (appointList.value[i].a_num <= 0) {
        return ElMessage.error('包邮件数应大于0');
      }
      if (appointList.value[i].a_price < 0) {
        return ElMessage.error('包邮金额应大于等于0');
      }
    }
  }
  let data = {
    appoint_info: appointList.value,
    region_info: templateList.value,
    no_delivery_info: noDeliveryList.value,
    sort: formData.value.sort,
    type: formData.value.type,
    name: formData.value.name,
    appoint: formData.value.appoint_check,
    no_delivery: formData.value.no_delivery_check,
  };
  templatesSaveApi(id.value, data).then((res) => {
    isTemplate.value = false;
    // proxy.$parent.getList();
    formData.value = {
      type: 1,
      sort: 0,
      name: '',
      appoint_check: 0,
      no_delivery_check: 0,
    };
    appointList.value = [];
    noDeliveryList.value = [];
    addressModal.value = false;
    templateList.value = [
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
    emit('addSuccess');
    ElMessage.success(res.msg);
  });
}
// 删除
function delCity(row, tit, num, delType) {
  if (delType === 1) {
    templateList.value.splice(num, 1);
  } else if (delType == 2) {
    appointList.value.splice(num, 1);
  } else {
    noDeliveryList.value.splice(num, 1);
  }
  //   let delfromData = {
  //     title: tit,
  //     num: num,
  //     url: `setting/shipping_templates/del/${row.id}`,
  //     method: "DELETE",
  //     ids: "",
  //   };
  //   modalSure(delfromData)
  //     .then((res) => {
  //       ElMessage.success(res.msg);
  //     })
  //     .catch((res) => {
  //       ElMessage.error(res.msg);
  //     });
}
// 关闭
function cancel() {
  noShippingArr.value = [];
  noDeliveryArr.value = [];
  yesShippingArr.value = [];
  selectArr.value = [];
  formData.value = {
    type: 1,
    sort: 0,
    name: '',
    appoint_check: 0,
    no_delivery_check: 0,
  };
  appointList.value = [];
  noDeliveryList.value = [];
  addressModal.value = false;
  templateList.value = [
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
}

function address() {
  addressModal.value = true;
}
function enter(enterIndex) {
  activeCity.value = enterIndex;
}
function leave() {
  activeCity.value = null;
}

// 页面通过 $refs.template.editFrom(id) / isTemplate / id 驱动
defineExpose({ editFrom, isTemplate, id });
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
.ivu-input-number {
  width: 100%;
}
.free_tips {
  /* el-form-item 内容区是 flex 布局，提示文字须占满整行，添加包邮区域按钮才会换行到其下方 */
  width: 100%;
  font-size: 12px;
  color: #ccc;
}
</style>
