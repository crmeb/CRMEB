<template>
  <div>
    <el-dialog v-model="addressModal" title="选择可配送区域" width="50%" class="modal">
      <el-row :gutter="24">
        <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24" class="item">
          <div class="acea-row row-right row-middle">
            <el-checkbox v-model="iSselect" @change="allCheckbox">全选</el-checkbox>
            <div class="empty" v-db-click @click="empty">清空</div>
          </div>
        </el-col>
      </el-row>
      <el-row :gutter="24" v-loading="loading">
        <template v-for="(item, index) in cityList" :key="index">
          <el-col :xl="6" :lg="6" :md="6" :sm="8" :xs="6" class="item" v-if="item.isShow">
            <el-popover placement="top-start" width="600" trigger="hover" :open-delay="600">
              <div>
                <div class="city">
                  <div class="checkBox">
                    <div class="arrow"></div>
                    <div>
                      <el-checkbox
                        v-model="city.checked"
                        :label="city.name"
                        @change="primary(index, indexn)"
                        class="itemn"
                        v-for="(city, indexn) in item.children"
                        :key="indexn"
                        v-show="city.isShow"
                        >{{ city.name }}</el-checkbox
                      >
                    </div>
                  </div>
                </div>
              </div>
              <template #reference
                ><el-checkbox v-model="item.checked" :label="item.name" @change="checkedClick(index)">{{
                  item.name
                }}</el-checkbox></template
              ><span class="red">({{ (item.count || 0) + '/' + item.childNum }})</span>
            </el-popover>
          </el-col>
        </template>
      </el-row>
      <template #footer
        ><div>
          <el-button v-db-click @click="close">取消</el-button>
          <el-button type="primary" v-db-click @click="confirm">确定</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { templatesCityListApi } from '@/api/freight';

defineOptions({ name: 'city' });

const emit = defineEmits(['selectCity']);

const props = defineProps({
  type: {
    type: Number,
    default: 0,
  },
  selectArr: {
    type: Array,
    default: [],
  },
});

const iSselect = ref(false);
const addressModal = ref(false);
const cityList = ref([]);
const activeCity = ref(-1);
const loading = ref(false);

function enter(index) {
  activeCity.value = index;
}
function leave() {
  activeCity.value = null;
}
function getCityList() {
  loading.value = true;
  templatesCityListApi().then((res) => {
    loading.value = false;
    const selectArr = [];
    res.data.forEach((el, index, arr) => {
      el.isShow = true;
      el.children.forEach((child, j) => {
        child.isShow = true;
        if (selectArr.length > 0) {
          selectArr.forEach((sel, sindex) => {
            sel.children.forEach((sitem, sj) => {
              if (child.city_id == sitem.city_id) {
                child.isShow = false;
              }
            });
          });
        }
      });
    });
    res.data.forEach((el, index, arr) => {
      let num = 0;
      let oldNum = 0;
      el.children.forEach((child, j) => {
        if (!child.isShow) {
          num++;
        } else {
          oldNum++;
        }
      });
      if (num == el.children.length) {
        el.isShow = false;
      }
      el.childNum = oldNum;
    });
    cityList.value = res.data;
    iSselect.value = false;
  });
}
/**
 * 全选或者反选
 * @param checked
 */
function allCheckbox() {
  const checked = iSselect.value;
  cityList.value.forEach(function (item, key) {
    cityList.value[key]['checked'] = checked;
    if (checked) {
      cityList.value[key]['count'] = cityList.value[key].children.length;
    } else {
      cityList.value[key]['count'] = 0;
    }
    cityList.value[key].children.forEach(function (val, k) {
      cityList.value[key].children[k]['checked'] = checked;
    });
  });
  // render();
}
// 清空；
function empty() {
  cityList.value.forEach(function (item, key) {
    cityList.value[key]['checked'] = false;
    cityList.value[key].children.forEach(function (val, k) {
      cityList.value[key].children[k]['checked'] = false;
    });
    cityList.value[key]['count'] = 0;
  });
  iSselect.value = false;
}
/**
 * 点击省
 * @param index
 */
function checkedClick(index) {
  if (cityList.value[index].checked) {
    cityList.value[index]['count'] = cityList.value[index].childNum;
    cityList.value[index].children.forEach(function (item, key) {
      cityList.value[index].children[key]['checked'] = true;
    });
  } else {
    cityList.value[index]['count'] = 0;
    cityList.value[index]['checked'] = false;
    cityList.value[index].children.forEach(function (item, key) {
      cityList.value[index].children[key]['checked'] = false;
    });
    iSselect.value = false;
  }
  // render();
}
/**
 * 点击市区
 * @param index
 * @param ind
 */
function primary(index, ind) {
  let checked = false,
    count = 0;
  cityList.value[index].children.forEach(function (item, key) {
    if (item.checked) {
      checked = true;
      count++;
    }
  });
  cityList.value[index]['count'] = count;
  cityList.value[index]['checked'] = checked;
  // render();
}
// 确定;
function confirm() {
  // 被选中的省市；
  let selectList = [];
  cityList.value.forEach(function (item, key) {
    let data = {};
    if (item.checked) {
      data = {
        name: item.name,
        city_id: item.city_id,
        children: [],
      };
    }
    cityList.value[key].children.forEach(function (i, k) {
      if (i.checked) {
        data.children.push({
          city_id: i.city_id,
        });
      }
    });
    if (data.city_id !== undefined) {
      selectList.push(data);
    }
  });
  if (selectList.length === 0) {
    return ElMessage.error('至少选择一个省份或者城市');
  } else {
    emit('selectCity', selectList, props.type);
    addressModal.value = false;
    cityList.value = [];
  }
  // parent.selectCity(selectList,type);
  // var index = parent.layer.getFrameIndex(window.name);
  // parent.layer.close(index);
}
function close() {
  addressModal.value = false;
  cityList.value = [];
}

// freightTemplate 通过 $refs.city.getCityList() / $refs.city.addressModal 驱动
defineExpose({ getCityList, addressModal });
</script>

<style lang="scss" scoped>
.modal .item {
  margin-bottom: 20px;
}
.modal .item .city {
  position: absolute;
  z-index: 9;
  top: 17px;
  width: 100%;
  padding-top: 18px;
}
.modal .item .city .checkBox {
  width: 97%;
  padding: 10px;
  border: 1px solid #eee;
  background-color: #fff;
  max-height: 100px;
  overflow-x: hidden;
  overflow-y: auto;
}
.modal .item .city .checkBox .arrow {
  position: absolute;
  top: 3px;
  width: 0;
  height: 0;
  border: 8px solid transparent;
  border-bottom-color: #ddd;
}
.modal .item .city .checkBox .arrow:before {
  position: absolute;
  bottom: -8px;
  right: -7px;
  content: '';
  width: 0;
  height: 0;
  border: 7px solid transparent;
  border-bottom-color: #fff;
}
.modal .item .city .checkBox .itemn {
  margin-bottom: 14px;
}
.radio {
  padding: 5px 0;
  font-size: 14px !important;
}
.red {
  color: #ff0000;
}
.empty {
  cursor: pointer;
  font-size: 12px;
  margin-left: 10px;
  line-height: 19px;
  color: var(--prev-color-text-primary);
  font-weight: 500;
}
</style>
