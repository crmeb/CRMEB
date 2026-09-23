<template>
  <div class="article-manager">
    <!-- <div class="i-layout-page-header header-title">
      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
    </div> -->
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      description="可视化编辑公众号自定义菜单，手机模拟器实时预览，发布后生效。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <!-- 公众号设置 -->
      <el-row :gutter="24">
        <el-col :span="24" class="ml40">
          <!-- 预览功能 -->
          <el-col :span="24">
            <el-col :xl="7" :lg="7" :md="22" :sm="22" :xs="22" class="left mb15">
              <img class="top" src="../../../../../assets/images/mobilehead.png" />
              <img class="bottom" src="../../../../../assets/images/mobilefoot.png" />
              <div style="background: #f4f5f9; min-height: 438px; position: absolute; top: 63px; width: 320px"></div>
              <div class="textbot">
                <div class="li" v-for="(item, indx) in list" :key="indx" :class="{ active: item === formValidate }">
                  <div>
                    <div class="add" v-db-click @click="add(item, indx)">
                      <i class="el-icon-plus"></i>
                      <div class="arrow"></div>
                    </div>
                    <div class="tianjia">
                      <div
                        class="addadd"
                        v-for="(j, index) in item.sub_button"
                        :key="index"
                        :class="{ active: j === formValidate }"
                        v-db-click
                        @click="gettem(j, index, indx)"
                      >
                        {{ j.name || '二级菜单' }}
                      </div>
                    </div>
                  </div>
                  <div class="text" v-db-click @click="gettem(item, indx, null)">{{ item.name || '一级菜单' }}</div>
                </div>
                <div class="li" v-show="list.length < 3">
                  <div class="text" v-db-click @click="addtext"><i class="el-icon-plus"></i></div>
                </div>
              </div>
            </el-col>
            <el-col :xl="11" :lg="12" :md="22" :sm="22" :xs="22">
              <el-tabs value="name1" v-if="checkedMenuId !== null">
                <el-tab-pane label="菜单信息" name="name1">
                  <el-col :span="24" class="userAlert">
                    <div class="box-card right">
                      <el-alert type="info" show-icon closable title="已添加子菜单，仅可设置菜单名称"></el-alert>
                      <el-form
                        ref="formValidateRef"
                        :model="formValidate"
                        :rules="ruleValidate"
                        label-width="100px"
                        class="mt20"
                      >
                        <el-form-item label="菜单名称" prop="name">
                          <el-input v-model="formValidate.name" placeholder="请填写菜单名称" class="spwidth"></el-input>
                        </el-form-item>
                        <el-form-item label="规则状态" prop="type">
                          <el-select v-model="formValidate.type" placeholder="请选择规则状态" class="spwidth">
                            <el-option value="click" label="关键字"></el-option>
                            <el-option value="view" label="跳转网页"></el-option>
                            <el-option value="miniprogram" label="小程序"></el-option>
                          </el-select>
                        </el-form-item>
                        <div v-if="formValidate.type === 'click'">
                          <el-form-item label="关键字" prop="key">
                            <el-input v-model="formValidate.key" placeholder="请填写关键字" class="spwidth"></el-input>
                          </el-form-item>
                        </div>
                        <div v-if="formValidate.type === 'miniprogram'">
                          <el-form-item label="appid" prop="appid">
                            <el-input v-model="formValidate.appid" placeholder="请填写appid" class="spwidth"></el-input>
                          </el-form-item>
                          <el-form-item label="小程序路径" prop="pagepath">
                            <el-input
                              v-model="formValidate.pagepath"
                              placeholder="请填写小程序路径"
                              class="spwidth"
                            ></el-input>
                          </el-form-item>
                          <el-form-item label="备用网页" prop="url">
                            <el-input
                              v-model="formValidate.url"
                              placeholder="请填写备用网页"
                              class="spwidth"
                            ></el-input>
                          </el-form-item>
                        </div>
                        <div v-if="formValidate.type === 'view'">
                          <el-form-item label="跳转地址" prop="url">
                            <el-input
                              v-model="formValidate.url"
                              placeholder="请填写跳转地址"
                              class="spwidth"
                            ></el-input>
                          </el-form-item>
                        </div>
                      </el-form>
                    </div>
                  </el-col>
                </el-tab-pane>
              </el-tabs>
              <el-col :span="24" v-if="isTrue">
                <el-button type="danger" v-db-click @click="deltMenus">删除</el-button>
                <el-button type="primary" v-db-click @click="submenus('formValidate')">保存并发布</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { wechatMenuApi, MenuApi } from '../../api';

defineOptions({ name: 'wechatMenus' });

const modal2 = ref(false);
const formValidate = ref({
  name: '',
  type: 'click',
  appid: '',
  url: '',
  key: '',
  pagepath: '',
  id: 0,
});
const ruleValidate = ref({
  name: [
    { required: true, message: '请填写菜单名称', trigger: 'blur' },
    { min: 1, max: 14, message: '长度在 1 到 14 个字符', trigger: 'blur' },
  ],
  key: [{ required: true, message: '请填写关键字', trigger: 'blur' }],
  appid: [{ required: true, message: '请填写appid', trigger: 'blur' }],
  pagepath: [{ required: true, message: '请填写备用网页', trigger: 'blur' }],
  url: [{ required: true, message: '请填写跳转地址', trigger: 'blur' }],
  type: [{ required: true, message: '请选择规则状态', trigger: 'change' }],
});
const parentMenuId = ref(null);
const list = ref([]);
const checkedMenuId = ref(null);
const isTrue = ref(false);

const formValidateRef = ref(null);
const pageHeader = ref(null);

// 添加一级字段函数
function defaultMenusData() {
  return {
    type: 'click',
    name: '',
    sub_button: [],
  };
}
// 添加二级字段函数
function defaultChildData() {
  return {
    type: 'click',
    name: '',
  };
}
// 获取 菜单
function getMenus() {
  wechatMenuApi()
    .then(async (res) => {
      let data = res.data;
      list.value = data.menus;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 点击保存提交
function submenus(name) {
  if (isTrue.value && !checkedMenuId.value && checkedMenuId.value !== 0) {
    putData();
  } else {
    formValidateRef.value.validate((valid) => {
      if (valid) {
        putData();
      } else {
        if (!check()) return false;
      }
    });
  }
}
// 新增data
function putData() {
  let data = {
    button: list.value,
  };
  MenuApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      checkedMenuId.value = null;
      formValidate.value = {};
      isTrue.value = false;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 点击元素
function gettem(item, index, pid) {
  checkedMenuId.value = index;
  formValidate.value = item;
  parentMenuId.value = pid;
  isTrue.value = true;
}
// 增加二级
function add(item, index) {
  if (!check()) return false;
  if (item.sub_button.length < 5) {
    let data = defaultChildData();
    let id = item.sub_button.length;
    item.sub_button.push(data);
    formValidate.value = data;
    checkedMenuId.value = id;
    parentMenuId.value = index;
    isTrue.value = true;
  } else {
    ElMessage.warning('二级菜单最多只能添加5个!');
    return false;
  }
}
// 增加一级
function addtext() {
  if (!check()) return false;
  let data = defaultMenusData();
  let id = list.value.length;
  list.value.push(data);
  formValidate.value = data;
  checkedMenuId.value = id;
  parentMenuId.value = null;
  isTrue.value = true;
}
// 判断函数
function check() {
  let reg = /[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/;
  if (checkedMenuId.value === null) return true;
  if (!isTrue.value) return true;
  if (!formValidate.value.name) {
    ElMessage.warning('请输入按钮名称!');
    return false;
  }
  if (formValidate.value.type === 'click' && !formValidate.value.key) {
    ElMessage.warning('请输入关键字!');
    return false;
  }
  if (formValidate.value.type === 'view' && !reg.test(formValidate.value.url)) {
    ElMessage.warning('请输入正确的跳转地址!');
    return false;
  }
  if (
    formValidate.value.type === 'miniprogram' &&
    (!formValidate.value.appid || !formValidate.value.pagepath || !formValidate.value.url)
  ) {
    ElMessage.warning('请填写完整小程序配置!');
    return false;
  }
  return true;
}
// 删除
function deltMenus() {
  if (isTrue.value) {
    ElMessageBox.confirm('确认删除此菜单吗?', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
      beforeClose(action, instance, done) {
        if (action == 'confirm') {
          const confirmBtn = instance.$refs && instance.$refs.confirm;
          if (confirmBtn && confirmBtn.$el) {
            confirmBtn.$el.onclick = a();
          } else {
            done();
          }
          function a(e) {
            e = e || window.event;
            if (e.detail != 0) {
              done();
            }
          }
        } else {
          done();
        }
      },
    })
      .then(() => {
        del();
      })
      .catch(() => {});
  } else {
    ElMessage.warning('请选择菜单!');
  }
}
// 确认删除
function del() {
  parentMenuId.value === null
    ? list.value.splice(checkedMenuId.value, 1)
    : list.value[parentMenuId.value].sub_button.splice(checkedMenuId.value, 1);
  parentMenuId.value = null;
  formValidate.value = {
    name: '',
    type: 'click',
    appid: '',
    url: '',
    key: '',
    pagepath: '',
    id: 0,
  };
  isTrue.value = true;
  modal2.value = false;
  checkedMenuId.value = null;
  formValidateRef.value.resetFields();
}

onMounted(() => {
  getMenus();
});
</script>
<style scoped lang="scss">
* {
  -moz-user-select: none; /*火狐*/
  -webkit-user-select: none; /*webkit浏览器*/
  -ms-user-select: none; /*IE10*/
  -khtml-user-select: none; /*早期浏览器*/
  user-select: none;
}

.left {
  min-width: 390px;
  min-height: 550px;
  position: relative;
  padding-left: 40px;
}

.top {
  position: absolute;
  top: 0px;
}

.bottom {
  position: absolute;
  bottom: 0px;
}

.textbot {
  position: absolute;
  bottom: 0px;
  left: 55px;
  width: 100%;
}
.active {
  border: 1px solid var(--prev-color-primary) !important;
  color: var(--prev-color-primary) !important;
}
.li {
  float: left;
  width: 92px;
  height: 48px;
  line-height: 48px;
  border-left: 1px solid #e7e7eb;
  background: #fafafa;
  text-align: center;
  cursor: pointer;
  color: #999;
  position: relative;
}
.text {
  height: 50px;
  white-space: nowrap;
  width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  padding: 0 5px;
}
.text:hover {
  color: #000;
}

.add {
  position: absolute;
  bottom: 65px;
  width: 100%;
  line-height: 40px;
  // border: 1px solid #e7e7eb;
  background: #fafafa;
}
.arrow {
  position: absolute;
  bottom: -16px;
  left: 36px;
  /* 圆角的位置需要细心调试哦 */
  width: 0;
  height: 0;
  font-size: 0;
  border: solid 8px;
  border-color: #fafafa #f4f5f9 #f4f5f9 #f4f5f9;
}
.tianjia {
  position: absolute;
  bottom: 107px;
  width: 100%;
  line-height: 48px;
  background: #fafafa;
  :first-child {
    border: none;
  }
}
.addadd {
  width: 100%;
  line-height: 40px;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
  height: 40px;
}
.right {
  background: #fff;
  min-height: 400px;
}
.spwidth {
  width: 100%;
}
.userAlert {
  margin-top: 16px !important;
}
</style>
