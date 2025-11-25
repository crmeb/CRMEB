<template>
  <div>
    <el-dialog
      :visible.sync="modals"
      width="540px"
      :title="titleFrom"
      :close-on-click-modal="false"
      @closed="handleClose"
    >
      <el-form ref="formValidate" :model="formValidate" label-width="80px" @submit.native.prevent>
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item label="类型：">
              <el-radio-group v-model="formValidate.auth_type" @input="changeAuthType">
                <el-radio :label="item.value" v-for="(item, i) in optionsRadio" :key="i">
                  <span>{{ item.label }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item :label="!authType ? '接口名称：' : '按钮名称：'" prop="menu_name">
              <div class="add">
                <el-input
                  v-model="formValidate.menu_name"
                  :placeholder="!authType ? '请输入接口名称' : '请输入按钮名称'"
                >
                </el-input>
                <!-- <el-button class="ml10 df" v-show="!authType" v-db-click @click="getRuleList()" icon="ios-apps"></el-button> -->
              </div>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="父级分类：">
              <el-cascader
                :options="menuList"
                change-on-select
                v-model="formValidate.path"
                filterable
                style="width: 100%"
              ></el-cascader>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType != 2">
            <el-form-item label="页面地址：" prop="menu_path">
              <el-input v-model="formValidate.menu_path" placeholder="请输入页面地址" @change="changeUnique">
                <template #prepend>
                  <span>{{ $routeProStr }}</span>
                </template>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType == 2">
            <el-form-item label="请求方式：" prop="methods">
              <el-select v-model="formValidate.methods">
                <el-option value="GET" label="GET"></el-option>
                <el-option value="POST" label="POST"></el-option>
                <el-option value="PUT" label="PUT"></el-option>
                <el-option value="DELETE" label="DELETE"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType == 2">
            <el-form-item label="接口地址：" prop="api_url">
              <el-input v-model="formValidate.api_url" placeholder="请输入接口地址" @change="changeUnique"> </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="权限标识：" prop="unique_auth">
              <el-input v-model="formValidate.unique_auth" placeholder="请输入权限标识"></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType != 2">
            <el-form-item label="图标：">
              <el-input v-model="formValidate.icon" placeholder="请选择图标，点击右面图标">
                <el-button slot="append" icon="el-icon-picture-outline" v-db-click @click="iconClick"></el-button>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="备注：">
              <el-input v-model="formValidate.mark" placeholder="请输入备注" number></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="排序：">
              <el-input type="number" v-model="formValidate.sort" placeholder="请输入排序" number></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="状态：">
              <el-radio-group v-model="formValidate.is_show" @input="changeShow">
                <el-radio :label="item.value" v-for="(item, i) in isShowRadio" :key="i">
                  <span>{{ item.label }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="handleReset">取 消</el-button>
        <el-button type="primary" v-db-click @click="handleSubmit('formValidate')">提 交</el-button>
      </span>
    </el-dialog>
    <el-dialog :visible.sync="modal12" width="720px" title="图标选择">
      <el-input
        v-model="iconVal"
        placeholder="输入关键词搜索,注意全是英文"
        clearable
        style="width: 300px"
        @change="upIcon(iconVal)"
        ref="search"
      />
      <div class="trees-coadd">
        <div class="scollhide">
          <div class="iconlist">
            <ul class="list-inline">
              <li class="icons-item" v-for="(item, i) in iconVal ? searchData : list" :key="i" :title="item">
                <i :class="'el-icon-' + item" class="f-s-24" v-db-click @click="iconChange(item)"></i>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </el-dialog>
    <el-dialog :visible.sync="ruleModal" width="1100px" title="权限列表" @closed="modalchange">
      <div class="search-rule">
        <el-input
          class="mr10"
          v-model="searchRule"
          placeholder="输入关键词搜索"
          clearable
          style="width: 300px"
          ref="search"
        />
        <el-button type="primary" v-db-click @click="searchRules">搜索</el-button>
        <el-button v-db-click @click="init">重置</el-button>
      </div>
      <div class="rule">
        <div
          class="rule-list"
          v-show="!arrs.length || arrs.includes(index)"
          :class="{ 'select-rule': arrs.includes(index) }"
          v-for="(item, index) in ruleList"
          :key="index"
          v-db-click
          @click="selectRule(item)"
        >
          <div>接口名称：{{ item.real_name }}</div>
          <div>请求方式：{{ item.method }}</div>
          <div>接口地址：{{ item.rule }}</div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { addMenusApi, addMenus, getRuleList } from '@/api/systemMenus';
import icon from '@/utils/icon';

export default {
  name: 'menusFrom',
  props: {
    formVal: {
      type: Object,
      default: null,
    },
    titleFrom: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      arrs: [],
      searchRule: '',
      iconVal: '',
      grid: {
        xl: 22,
        lg: 22,
        md: 22,
        sm: 22,
        xs: 22,
      },
      modals: false,
      modal12: false,
      FromData: [],
      valids: false,
      list2: [],
      list: icon,
      search: icon,
      ruleModal: false,
      ruleList: [],
      authType: 1,
      formValidate: {},
      searchData: [],
      isShowRadio: [
        { value: 1, label: '开启' },
        { value: 0, label: '关闭' },
      ],
    };
  },
  watch: {
    formVal(val) {
      this.formValidate = val;
    },
    'formValidate.header': function (n) {
      this.formValidate.is_header = n ? 1 : 0;
    },
    'formValidate.auth_type': function (n) {
      if (n === undefined) {
        n = 1;
      }
      this.authType = n;
    },
    'formValidate.data': function (n) {},
  },
  computed: {
    /* eslint-disable */
    optionsList() {
      let a = [];
      this.FromData.map((item) => {
        if ('pid' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    headerOptionsList() {
      let a = [];
      this.FromData.map((item) => {
        if ('header' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    optionsListmodule() {
      let a = [];
      this.FromData.map((item) => {
        if ('module' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    optionsRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ('auth_type' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    isheaderRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ('is_header' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    // isShowRadio() {
    //   let a = [];
    //   this.FromData.map((item) => {
    //     if ('is_show' === item.field) {
    //       a = item.options;
    //     }
    //   });
    //   return a;
    // },
    isShowPathRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ('is_show_path' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    menuList() {
      let a = [];
      this.FromData.map((item) => {
        if ('menu_list' === item.field) {
          a = item.props.options;
        }
      });
      return a;
    },
  },
  methods: {
    handleClose() {
      this.formValidate = {};
    },
    // 获取权限列表
    getRuleList() {
      getRuleList().then((res) => {
        this.ruleList = res.data;
        this.ruleModal = true;
      });
    },
    modalchange() {
      this.arrs = [];
      this.ruleModal = '';
      this.ruleModal = false;
    },
    changeUnique(val) {
      let value = this.$routeProStr + val.target.value;
      if (value.slice(0, 1) === '/') value = value.replace('/', '');
      this.formValidate.unique_auth = value.replaceAll('/', '-');
    },
    changeAuthType(val) {
      this.authType = val;
    },
    changeShow(val) {
      this.formValidate.is_show = val;
    },
    selectRule(data) {
      this.$emit('selectRule', data);
      this.$nextTick((e) => {
        this.ruleModal = false;
      });
    },
    // 搜索
    upIcon(n) {
      this.searchData = this.list.filter((item) => item.indexOf(this.iconVal) > -1);
    },
    // 搜索规则
    searchRules() {
      if (this.searchRule.trim()) {
        this.arrs = [];
        for (var i = 0; i < this.ruleList.length; i++) {
          if (this.ruleList[i].real_name.indexOf(this.searchRule) !== -1) {
            this.arrs.push(i);
          }
        }
      } else {
        this.arrs = [];
      }
    },
    init() {
      this.searchRule = '';
      this.arrs = [];
    },
    handleCreate1(val) {
      this.headerOptionsList.push({
        value: val,
        label: val,
      });
    },
    // 获取新增表单
    getAddFrom() {
      addMenus()
        .then(async (res) => {
          this.FromData = res.data.rules;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    iconClick() {
      this.modal12 = true;
    },
    iconChange(n) {
      this.formValidate.icon = n;
      this.modal12 = false;
    },
    // 提交
    handleSubmit(name) {
      //判断是否选择父级分类
      if (this.formValidate.path) {
        let length = this.formValidate.path.length;
        this.formValidate.pid = this.formValidate.path[length - 1] || 0;
      }
      let data = {
        url: this.formValidate.id ? `/setting/menus/${this.formValidate.id}` : '/setting/menus',
        method: this.formValidate.id ? 'put' : 'post',
        datas: this.formValidate,
      };
      if (!this.formValidate.menu_name) {
        return this.$message.warning('请填写菜单/按钮/接口名称');
      }
      if (!this.formValidate.menu_path && this.authType != 2) {
        return this.$message.warning('请填写页面/按钮地址');
      }
      if (!this.formValidate.api_url && this.authType == 2) {
        return this.$message.warning('请填写接口地址');
      }
      this.valids = true;
      addMenusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.modals = false;
          this.$emit('changeMenu');
          this.getAddFrom();
          // this.$store.dispatch('menus/getMenusNavList');
        })
        .catch((res) => {
          this.valids = false;
          this.$message.error(res.msg);
        });
    },
    handleReset() {
      this.modals = false;
      this.$refs['formValidate'].resetFields();
      this.$emit('clearFrom');
    },
  },
  created() {
    this.list = this.search;
    // this.getAddFrom();
  },
};
</script>

<style scoped>
.trees-coadd {
  width: 100%;
  height: 500px;
  border-radius: 4px;
  overflow: hidden;
}

.scollhide {
  width: 100%;
  height: 100%;
  overflow: auto;
  margin-left: 18px;
  padding: 10px 0 10px 0;
  box-sizing: border-box;
}

.content {
  font-size: 12px;
}

.time {
  font-size: 12px;
  color: #2d8cf0;
}

.icons-item {
  float: left;
  margin: 6px 6px 6px 0;
  width: 53px;
  text-align: center;
  list-style: none;
  cursor: pointer;
  height: 50px;
  color: #5c6b77;
  transition: all 0.2s ease;
  position: relative;
  padding-top: 10px;
}

.icons-item .f-s-24 {
  font-size: 24px;
}

.search-rule {
  display: flex;
  align-items: center;
  padding: 10px;
  background-color: #f2f2f2;
}

.rule {
  display: flex;
  flex-wrap: wrap;
  max-height: 700px;
  overflow: scroll;
}

/*定义滚动条高宽及背景 高宽分别对应横竖滚动条的尺寸*/
.rule::-webkit-scrollbar {
  width: 10px;
  height: 10px;
  background-color: #f5f5f5;
}

/*定义滚动条轨道 内阴影+圆角*/
.rule::-webkit-scrollbar-track {
  border-radius: 4px;
  background-color: #f5f5f5;
}

/*定义滑块 内阴影+圆角*/
.rule::-webkit-scrollbar-thumb {
  border-radius: 4px;
  background-color: #555;
}

.rule-list {
  background-color: #f8f5f5;
  width: 32%;
  margin: 5px;
  border-radius: 3px;
  padding: 10px;
  color: #333;
  cursor: pointer;
  transition: all 0.1s;
}

.rule-list:hover {
  background-color: #c5d1dd;
}

.rule-list div {
  white-space: nowrap;
}

.select-rule {
  background-color: #c5d1dd;
}

.add {
  display: flex;
  align-items: center;
}

.df {
  display: flex;
  justify-content: center;
}
</style>
