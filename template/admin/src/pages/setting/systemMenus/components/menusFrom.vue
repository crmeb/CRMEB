<template>
  <div>
    <el-dialog :visible.sync="modals" width="540px" :title="titleFrom" :close-on-click-modal="false" @closed="visible">
      <el-form ref="formValidate" :model="formValidate" label-width="80px" @submit.native.prevent>
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item :label="!authType ? '接口名称：' : '按钮名称：'" prop="menu_name">
              <div class="add">
                <el-input
                  v-model="formValidate.menu_name"
                  :placeholder="!authType ? '请输入接口名称' : '请输入按钮名称'"
                >
                </el-input>
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
          <el-col v-bind="grid" v-if="authType">
            <el-form-item label="图标：">
              <el-input v-model="formValidate.icon" placeholder="请选择图标，点击右面图标" icon="ios-appstore">
                <el-button slot="append" icon="el-icon-picture-outline" v-db-click @click="iconClick"></el-button>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType">
            <el-form-item label="排序：">
              <el-input type="number" v-model="formValidate.sort" placeholder="请输入排序" number></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="是否显示：">
              <el-radio-group v-model="formValidate.is_show_path">
                <el-radio :label="item.value" v-for="(item, i) in isShowPathRadio" :key="i">
                  <span>{{ item.label }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="handleReset">取 消</el-button>
        <el-button type="primary" v-db-click @click="handleSubmit('formValidate')" :disabled="valids">提 交</el-button>
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
    formValidate: {
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
        xl: 24,
        lg: 24,
        md: 24,
        sm: 24,
        xs: 24,
      },
      modals: false,
      modal12: false,
      FromData: [],
      valids: false,
      list2: [],
      list: icon,
      authType: true,
      search: icon,
      ruleModal: false,
      ruleList: [],
      searchData: [],
    };
  },
  watch: {
    'formValidate.header': function (n) {
      this.formValidate.is_header = n ? 1 : 0;
    },
    'formValidate.auth_type': function (n) {
      if (n === undefined) {
        n = 1;
      }
      this.authType = n === 1;
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
    isShowRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ('is_show' === item.field) {
          a = item.options;
        }
      });
      return a;
    },
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
    visible() {
      this.authType = true;
    },
    selectRule(data) {
      this.$emit('selectRule', data);
      this.$nextTick((e) => {
        this.ruleModal = false;
      });
    },
    changeRadio(n) {
      this.authType = n === 1 ? true : false;
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
      if (this.authType) {
        if (!this.formValidate.menu_name) {
          return this.$message.warning('请填写按钮名称');
        }
        if (!this.formValidate.menu_path) {
          return this.$message.warning('请填写路由地址');
        }
      } else {
        if (!this.formValidate.menu_name) {
          return this.$message.warning('请填写接口名称');
        }
        if (!this.formValidate.methods) {
          return this.$message.warning('请选择请求方式');
        }
        if (!this.formValidate.api_url) {
          return this.$message.warning('请选择接口地址');
        }
      }
      this.valids = true;
      addMenusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.modals = false;
          this.$emit('changeMenu', this.formValidate);
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
      this.authType = true;
      this.$refs['formValidate'].resetFields();
      this.$emit('clearFrom');
    },
  },
  created() {
    this.list = this.search;
    this.getAddFrom();
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
