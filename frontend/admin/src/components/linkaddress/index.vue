<template>
  <div>
    <el-dialog v-model="modals" title="选择链接" :close-on-click-modal="false" append-to-body width="1000px">
      <div class="table_box">
        <div class="left_box" v-if="fromType !== 'diyPage'">
          <el-tree
            :data="categoryData"
            node-key="id"
            default-expand-all
            :props="treeProps"
            highlight-current
            @node-click="handleCheckChange"
            :current-node-key="treeId"
          ></el-tree>
        </div>
        <div class="right_box" v-if="currenType == 'link'">
          <div v-if="tableList.length">
            <div class="cont">请选择链接</div>
            <div class="Box">
              <div
                class="cont_box"
                :class="currenId == item.id ? 'on' : ''"
                v-for="(item, index) in tableList"
                :key="index"
                v-db-click
                @click="getUrl(item)"
              >
                {{ item.name }}
              </div>
            </div>
          </div>
        </div>
        <div class="right_box" v-if="currenType == 'marketing_link' && coupon.length">
          <div>
            <div class="cont">优惠券</div>
            <div class="Box">
              <div
                class="cont_box"
                :class="currenId == item.id ? 'on' : ''"
                v-for="(item, index) in coupon"
                :key="index"
                v-db-click
                @click="getUrl(item)"
              >
                {{ item.name }}
              </div>
            </div>
          </div>
          <div>
            <div v-permission="'seckill'" v-if="basicsList.length">
              <div class="cont">秒杀</div>
              <div class="Box">
                <div
                  class="cont_box"
                  :class="currenId == item.id ? 'on' : ''"
                  v-for="(item, index) in basicsList"
                  :key="index"
                  v-db-click
                  @click="getUrl(item)"
                >
                  {{ item.name }}
                </div>
              </div>
            </div>
          </div>
          <div>
            <div v-permission="'bargain'" v-if="distributionList.length">
              <div class="cont">砍价</div>
              <div class="Box">
                <div
                  class="cont_box"
                  :class="currenId == item.id ? 'on' : ''"
                  v-for="(item, index) in distributionList"
                  :key="index"
                  v-db-click
                  @click="getUrl(item)"
                >
                  {{ item.name }}
                </div>
              </div>
            </div>
          </div>
          <div>
            <div v-permission="'combination'" v-if="userList.length">
              <div class="cont">拼团</div>
              <div class="Box">
                <div
                  class="cont_box"
                  :class="currenId == item.id ? 'on' : ''"
                  v-for="(item, index) in userList"
                  :key="index"
                  v-db-click
                  @click="getUrl(item)"
                >
                  {{ item.name }}
                </div>
              </div>
            </div>
          </div>
          <div v-if="integral.length">
            <div class="cont">积分</div>
            <div class="Box">
              <div
                class="cont_box"
                :class="currenId == item.id ? 'on' : ''"
                v-for="(item, index) in integral"
                :key="index"
                v-db-click
                @click="getUrl(item)"
              >
                {{ item.name }}
              </div>
            </div>
          </div>
          <div v-if="luckDraw.length">
            <div class="cont">抽奖</div>
            <div class="Box">
              <div
                class="cont_box"
                :class="currenId == item.id ? 'on' : ''"
                v-for="(item, index) in luckDraw"
                :key="index"
                v-db-click
                @click="getUrl(item)"
              >
                {{ item.name }}
              </div>
            </div>
          </div>
        </div>
        <div
          class="right_box"
          :class="fromType == 'diyPage' ? 'diy' : ''"
          v-if="
            currenType == 'special' ||
            currenType == 'product_category' ||
            currenType == 'product' ||
            currenType == 'seckill' ||
            currenType == 'bargain' ||
            currenType == 'combination' ||
            currenType == 'news' ||
            currenType == 'integral' ||
            currenType == 'lottery_list'
          "
        >
          <el-form :model="formValidate" class="tabform" v-if="currenType == 'product'">
            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item label="" label-for="pid">
                  <!-- <el-select v-model="formValidate.cate_id" style="width: 180px" clearable @change="userSearchs">
                    <el-option
                      v-for="item in treeSelect"
                      :value="item.id"
                      :key="item.id"
                      :label="item.html + item.cate_name"
                    >
                    </el-option>
                  </el-select> -->
                  <el-cascader
                    style="width: 180px"
                    v-model="formValidate.cate_id"
                    :options="treeSelect"
                    :props="{ multiple: true, checkStrictly: true, emitPath: false }"
                    filterable
                    clearable
                    @change="userSearchs"
                  ></el-cascader>
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="" label-for="store_name">
                  <el-input
                    search
                    enter-button
                    placeholder="请输入商品名称,关键字,编号"
                    v-model="formValidate.store_name"
                    style="width: 200px"
                    @change="userSearchs"
                  />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
          <el-table
            row-key="id"
            ref="table"
            empty-text="暂无数据"
            :data="tableList"
            v-loading="loading"
            :max-height="
              currenType == 'product_category'
                ? '460'
                : currenType == 'product' ||
                  currenType == 'seckill' ||
                  currenType == 'bargain' ||
                  currenType == 'combination' ||
                  currenType == 'news' ||
                  currenType == 'integral'
                ? '428'
                : ''
            "
          >
            <el-table-column
              :width="currenType != 'product_category' ? 50 : 80"
              v-if="
                [
                  'special',
                  'product',
                  'seckill',
                  'product_category',
                  'bargain',
                  'combination',
                  'integral',
                  'news',
                  'lottery_list',
                  'link',
                ].includes(currenType)
              "
            >
              <template #default="scope">
                <el-radio
                  v-model="templateRadio"
                  :label="scope.row.id"
                  :value="scope.row.id"
                  @change="getTemplateRow(scope.row)"
                  >&nbsp;</el-radio
                >
              </template>
            </el-table-column>
            <el-table-column
              :label="item.title"
              :width="item.width"
              :min-width="item.minWidth"
              v-for="(item, index) in currenType == 'special'
                ? columns
                : currenType == 'product_category'
                ? columns7
                : currenType == 'bargain' || currenType == 'combination' || currenType == 'integral'
                ? bargain
                : currenType == 'news'
                ? news
                : currenType == 'lottery_list'
                ? lottery
                : currenType == 'link'
                ? diyLink
                : columns8"
              :key="index"
            >
              <template #default="scope">
                <template v-if="item.key">
                  <div>
                    <span>{{ scope.row[item.key] }}</span>
                  </div>
                </template>
                <template v-else-if="item.slot === 'pic' && scope.row.hasOwnProperty('pic')">
                  <viewer>
                    <div class="tabBox_img">
                      <img v-lazy="scope.row.pic" />
                    </div>
                  </viewer>
                </template>
                <template v-else-if="item.slot === 'image' && scope.row.hasOwnProperty('image')">
                  <viewer>
                    <div class="tabBox_img">
                      <img v-lazy="scope.row.image" />
                    </div>
                  </viewer>
                </template>
                <template v-else-if="item.slot === 'image_input' && scope.row.hasOwnProperty('image_input')">
                  <viewer>
                    <div class="tabBox_img">
                      <img v-lazy="scope.row.image_input[0]" />
                    </div>
                  </viewer>
                </template>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="right_box" v-if="currenType == 'custom'">
          <!--<div v-if="!tableList.length || customNum==2">-->
          <!--<el-button type="primary" v-db-click @click="customList" v-if="tableList.length">自定义列表</el-button>-->
          <div style="width: 340px; margin: 150px 100px 0 120px">
            <el-form ref="customdateRef" :model="customdate" :rules="ruleValidate" :label-width="100">
              <!--<el-form-item label="链接名称：" prop="name">-->
              <!--<el-input v-model="customdate.name" placeholder="会员中心"></el-input>-->
              <!--</el-form-item>-->
              <!-- <el-form-item label="跳转路径：" prop="url">
                <el-input v-model="customdate.url" placeholder="请输入跳转路径"></el-input>
              </el-form-item> -->
              <div class="mb30 radioGroup">
                <el-radio-group v-model="customdate.status" @input="radioTap('customdate')">
                  <el-radio :label="2" :value="2">
                    <span>跳转其他小程序</span>
                  </el-radio>
                  <el-radio :label="1" :value="1">
                    <span>普通链接</span>
                  </el-radio>
                </el-radio-group>
              </div>
              <div v-if="customdate.status == 1">
                <el-form-item label="跳转路径：" prop="url" key="url">
                  <el-input v-model="customdate.url" placeholder="请输入正确跳转路径"></el-input>
                </el-form-item>
              </div>
              <div v-if="customdate.status == 2">
                <el-form-item label="APPID：" prop="appid" key="appid">
                  <el-input v-model="customdate.appid" placeholder="请输入正确APPID"></el-input>
                </el-form-item>
                <el-form-item label="小程序路径：" prop="mpUrl" key="mpUrl">
                  <el-input v-model="customdate.mpUrl" placeholder="请输入正确小程序路径"></el-input>
                </el-form-item>
              </div>
            </el-form>
          </div>
        </div>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="handleSubmit('customdate')" v-if="currenType == 'custom'"
            >确 定</el-button
          >
          <el-button type="primary" v-db-click @click="ok" v-else>确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { pageCategory, pageLink, saveLink } from '@diy/api';
import { modalSure } from '@/utils/public';
import { cascaderListApi, changeListApi } from '@/api/product';
import { seckillListApi, combinationListApi, bargainListApi, integralProductListApi } from '@/api/marketing';
import { cmsListApi } from '@/api/cms';
import { linkListApi } from '@/api/setting';

// 插件 api 模块动态发现：import.meta.glob 构建期收集实际存在的 plugins/{key}/api.js，
// 插件代码删除后自动从模块图消失，核心与插件 api 解耦（与 routers.js 发现插件 routes.js 同一机制）
const pluginApiModules = import.meta.glob('../../pages/plugin/*/api.js');
// 按约定加载插件 api 模块的具名导出；用路径结尾匹配，不依赖 glob key 的规范化形式；
// 插件未安装（模块不存在）时 resolve(null)，由调用方降级
function loadPluginApi(pluginKey, method) {
  const loader = Object.entries(pluginApiModules).find(([path]) => path.endsWith(`/plugin/${pluginKey}/api.js`))?.[1];
  if (!loader) return Promise.resolve(null);
  return loader()
    .then((mod) => mod[method] || null)
    .catch(() => null);
}

defineOptions({ name: 'linkaddress' });

const emit = defineEmits(['linkUrl']);

const props = defineProps({
  fromType: {
    type: String,
    default: '',
  },
});

const modals = ref(false);
const categoryData = ref([]);
const currenType = ref('link');
const treeProps = {
  label: 'name',
  children: 'children',
};
const templateRadio = ref(0);
const columns = [
  {
    title: 'ID',
    key: 'id',
    width: 60,
  },
  {
    title: '页面名称',
    key: 'title',
    width: 150,
  },
  {
    title: '页面链接',
    key: 'url',
  },
];
const columns7 = [
  {
    title: 'ID',
    key: 'id',
    width: 60,
  },
  {
    title: '分类名称',
    key: 'cate_name',
    tree: true,
  },
  {
    title: '分类图标',
    slot: 'pic',
  },
];
const columns8 = [
  {
    title: 'ID',
    key: 'id',
    width: 60,
  },
  {
    title: '商品图片',
    slot: 'image',
    width: 90,
  },
  {
    title: '商品名称',
    key: 'store_name',
  },
];
// 原文件未定义 diyLink（currenType == 'link' 走上方列表分支，不渲染表格列）
const diyLink = undefined;
const bargain = [
  {
    title: 'ID',
    key: 'id',
    width: 60,
  },
  {
    title: '商品图片',
    slot: 'image',
    width: 90,
  },
  {
    title: '商品名称',
    key: 'title',
  },
];
const news = [
  {
    title: 'ID',
    key: 'id',
    width: 60,
  },
  {
    title: '文章图片',
    slot: 'image_input',
    width: 90,
  },
  {
    title: '文章名称',
    key: 'title',
  },
];
const lottery = [
  {
    title: 'ID',
    key: 'id',
    width: 60,
  },
  {
    title: '名称',
    key: 'name',
  },
];
const formValidate = reactive({
  page: 1,
  limit: 1000,
  cate_id: '',
  store_name: '',
});
const basicsList = ref([]);
const userList = ref([]);
const distributionList = ref([]);
const coupon = ref([]);
const luckDraw = ref([]);
const integral = ref([]);
const currenId = ref('');
const currenUrl = ref('');
const loading = ref(false);
const tableList = ref([]);
const presentId = ref(0);
const categoryId = ref(''); //左侧分类id
const treeSelect = ref([]);
const customdate = reactive({
  url: '',
  appid: '',
  mpUrl: '',
  status: 2,
});
const customNum = ref(1);
const ruleValidate = {
  name: [{ required: true, message: '请输入链接名称', trigger: 'blur' }],
  url: [{ required: true, message: '请输入跳转路径', trigger: 'blur' }],
  appid: [{ required: true, message: '请输入APPID', trigger: 'blur' }],
};
const treeId = ref(0);
const customdateRef = ref(null);

// created
getSort();
goodsCategory();

function getTemplateRow(row) {
  presentId.value = row.id;
  currenUrl.value = row.url;
}
// 删除
function delLink(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `diy/del_link/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
      if (!tableList.value.length) {
        customNum.value = 2;
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg || '删除失败');
    });
}
function customLink() {
  customNum.value = 2;
}
function customList() {
  customNum.value = 1;
}
function getCustomList() {
  pageLink(categoryId.value)
    .then((res) => {
      if (!res.data.list.length) {
        customNum.value = 2;
      }
      tableList.value = res.data.list;
    })
    .catch((err) => {
      ElMessage.error(err.msg || '获取自定义列表失败');
    });
}
function getLotteryList() {}
function handleSubmit(name) {
  customdateRef.value.validate((valid) => {
    if (valid) {
      let url = customdate.url;
      if (customdate.status == 1) {
        url = customdate.url;
      } else {
        url = customdate.mpUrl + '@APPID=' + customdate.appid;
      }
      emit('linkUrl', url);
      modals.value = false;
      reset();
      // saveLink(customdate, categoryId.value).then(res=>{
      // 	getCustomList();
      // 	ElMessage.success(res.msg);
      // 	emit("linkUrl", customdate.url);
      // 	modals.value = false
      // 	reset();
      // }).catch(err=>{
      // 	ElMessage.error(err.msg);
      // })
    } else {
      ElMessage.error('请填写信息');
    }
  });
}
function handleReset(name) {
  refsResetFields(name);
}
function refsResetFields(name) {
  if (name === 'customdate') customdateRef.value.resetFields();
}
// 商品分类；
function goodsCategory() {
  cascaderListApi(1)
    .then((res) => {
      treeSelect.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg || '获取商品分类失败');
    });
}
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
function reset() {
  currenUrl.value = '';
  presentId.value = 0;
  currenId.value = '';
  // customdate.name="";
  customdate.url = '';
}
function getUrl(item) {
  currenId.value = item.id;
  currenUrl.value = item.url;
}
function getSort() {
  pageCategory()
    .then((res) => {
      if (res.data.length) {
        res.data[0].children[0].selected = true;
      }
      categoryData.value = res.data;
      if (props.fromType === 'diyPage' && res.data.length && res.data[0].children.length && res.data[0].children[2]) {
        handleCheckChange(res.data[0].children[2]);
      } else if (res.data.length && res.data[0].children.length && res.data[0].children[0].children.length) {
        handleCheckChange(res.data[0].children[0].children[0]);
      }
    })
    .catch((err) => {
      ElMessage.error(err.msg || '获取链接分类失败');
    });
}
function getList() {
  loading.value = true;
  formValidate.limit = 1000;
  if (currenType.value == 'product') {
    changeListApi(formValidate)
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/goods_details/index?id=${e.id}`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg || '获取列表失败');
      });
  } else if (currenType.value == 'seckill') {
    seckillListApi(formValidate)
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/activity/goods_seckill_details/index?id=${e.id}&status=1`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg || '获取秒杀列表失败');
      });
  } else if (currenType.value == 'bargain') {
    bargainListApi(formValidate)
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/activity/goods_bargain_details/index?id=${e.id}`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg || '获取砍价列表失败');
      });
  } else if (currenType.value == 'combination') {
    combinationListApi(formValidate)
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/activity/goods_combination_details/index?id=${e.id}`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg || '获取拼团列表失败');
      });
  } else if (currenType.value == 'news') {
    cmsListApi(formValidate)
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/extension/news_details/index?id=${e.id}`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg || '获取新闻列表失败');
      });
  } else if (currenType.value == 'lottery_list') {
    formValidate.cate_id = '';
    formValidate.store_name = '';
    Object.assign(formValidate, {
      page: 1,
      limit: 1000,
      factor: 1,
    });
    // 抽奖数据源由插件提供：按约定动态加载插件 api 模块（plugins/{key}/api.js 具名导出），
    // 核心不 import 插件文件、不硬编码插件接口；插件不存在时降级为空列表
    loadPluginApi('lottery', 'lotteryList')
      .then((apiFn) => (apiFn ? apiFn(formValidate) : null))
      .then(async (res) => {
        if (!res) {
          tableList.value = [];
          loading.value = false;
          return;
        }
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/goods/lottery/grids/index?type=1&lottery_id=${e.id}`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        if (res && res.msg) ElMessage.error(res.msg);
      });
  } else if (currenType.value == 'integral') {
    integralProductListApi(formValidate)
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((e) => {
          e.url = `/pages/points_mall/integral_goods_details?id=${e.id}`;
        });
        tableList.value = data.list;
        loading.value = false;
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg || '获取积分商品列表失败');
      });
  } else if (currenType.value == 'special') {
    linkListApi(formValidate)
      .then((res) => {
        loading.value = false;
        let data = res.data.list;
        let list = [];
        data.forEach((e) => {
          e.url = `/pages/annex/special/index?theme_id=${e.id}&name=${e.title}`;
          list.push(e);
        });
        tableList.value = list;
      })
      .catch((err) => {
        loading.value = false;
        ElMessage.error(err.msg || '获取链接列表失败');
      });
  }
}
function handleCheckChange(data) {
  reset();
  let id = '';
  treeId.value = data.id;
  if (data.pid) {
    id = data.id;
    categoryId.value = data.id;
  } else {
    return false;
  }
  loading.value = true;
  currenType.value = data.type;
  if (
    currenType.value == 'product' ||
    currenType.value == 'seckill' ||
    currenType.value == 'bargain' ||
    currenType.value == 'combination' ||
    currenType.value == 'news' ||
    currenType.value == 'integral' ||
    currenType.value == 'lottery_list'
  ) {
    getList();
  } else if (currenType.value == 'custom') {
    getCustomList();
  } else {
    formValidate.id = id;
    formValidate.page = 1;
    formValidate.limit = 1000;
    linkListApi(formValidate)
      .then((res) => {
        loading.value = false;
        let data = res.data.list;
        if (currenType.value == 'special') {
          let list = [];
          data.forEach((e) => {
            e.url = `/pages/annex/special/index?theme_id=${e.id}&name=${e.title}`;
            list.push(e);
          });
          tableList.value = list;
        } else {
          tableList.value = data;
        }
      })
      .catch((err) => {
        loading.value = false;
        ElMessage.error(err.msg || '获取链接列表失败');
      });
  }
}
function ok() {
  if (currenUrl.value == '') {
    return ElMessage.warning('请选择链接');
  } else {
    emit('linkUrl', currenUrl.value);
    modals.value = false;
    reset();
  }
}
function cancel() {
  modals.value = false;
  reset();
}

// 页面/组件通过 $refs.linkaddres.modals 驱动；c_tab_list 等还会调用 handleCheckChange 预选节点
defineExpose({ modals, handleCheckChange });
</script>

<style lang="scss" scoped>
:deep(.el-tree-node__content) {
  height: 30px;
}

:deep(.el-tree--highlight-current .el-tree-node.is-current > .el-tree-node__content) {
  background-color: var(--prev-bg-menu-hover-ba-color) !important;
  border-right: 2px solid var(--prev-color-primary);
}

:deep(.ivu-tree-title-selected),
:deep(.ivu-tree-title-selected:hover),
:deep(.ivu-tree-title:hover) {
  background-color: unset;
  color: var(--prev-color-primary);
}

:deep(.ivu-table-cell-tree) {
  border: 0;
  font-size: 15px;
  background-color: unset;
}

:deep(.el-table .cell) {
  display: flex;
  align-items: center;
}

:deep(.ivu-table-cell-tree .ivu-icon-ios-add:before) {
  content: '\F11F';
}

:deep(.ivu-table-cell-tree .ivu-icon-ios-remove:before) {
  content: '\F116';
}

.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

/* 定义滑块 内阴影+圆角 */
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px #ddd;
}

::-webkit-scrollbar {
  width: 4px !important;
  /* 对垂直流动条有效 */
}

.on {
  background-color: var(--prev-color-primary) !important;
  color: #fff !important;
}

.menu-item {
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;

  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }

  &:hover .icon-box {
    display: block;
  }

  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
  }
}

.radioGroup {
  :deep(.ivu-radio-wrapper) {
    margin-right: 30px;
  }
}

.table_box {
  display: flex;
  position: relative;

  .left_box {
    width: 171px;
    height: 470px;
    border-right: 1px solid var(--prev-border-color-lighter);
    overflow-x: hidden;
    overflow-y: auto;

    .left_cont {
      margin-bottom: 12px;
      cursor: pointer;
    }
  }

  .right_box {
    margin-left: 23px;
    font-size: 13px;
    font-family: PingFang SC;
    flex: 1;
    height: 470px;
    overflow-x: hidden;
    overflow-y: auto;

    .cont {
      font-weight: 500;
      color: #000000;
      font-weight: bold;
    }

    .Box {
      margin-top: 14px;
      display: flex;
      flex-wrap: wrap;

      .cont_box {
        font-weight: 400;
        color: var(--prev-color-text-regular);
        background: #fafafa;
        border-radius: 3px;
        text-align: center;
        padding: 7px 30px;
        margin-right: 10px;
        margin-bottom: 10px;
        cursor: pointer;

        &:hover {
          background-color: var(--prev-bg-menu-hover-ba-color);
          color: var(--prev-color-text-regular);
        }
      }

      .item {
        position: relative;

        .iconfont {
          display: none;
        }

        &:hover {
          .iconfont {
            display: block;
          }
        }
      }

      .iconfont {
        position: absolute;
        right: 9px;
        top: -8px;
        font-size: 18px;
        color: var(--prev-color-text-regular);
      }
    }
  }

  :deep(.el-table .cell) {
    padding-right: 0;
  }

  .Button {
    position: absolute;
    bottom: 15px;
    right: 15px;
    font-family: PingFangSC-Regular;
    text-align: center;

    .cancel {
      width: 70px;
      height: 32px;
      background: var(--prev-bg-white);
      border: 1px solid rgba(0, 0, 0, 0.14901960784313725);
      border-radius: 2px;
      font-size: 14px;
      color: #000000;
      line-height: 32px;
      float: left;
      margin-right: 10px;
      cursor: pointer;
    }

    .ok {
      width: 70px;
      height: 32px;
      background: var(--prev-color-primary);
      border-radius: 2px;
      font-size: 14px;
      color: #ffffff;
      line-height: 32px;
      float: left;
      cursor: pointer;
    }
  }
}
</style>
