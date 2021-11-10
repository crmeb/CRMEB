<template>
  <div>
    <Modal
      v-model="modals"
      width="850"
      scrollable
      footer-hide
      closable
      :title="titleFrom"
      :mask-closable="false"
      :z-index="1"
      @on-cancel="handleReset"
      @on-visible-change="visible"
    >
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="110"
        :rules="ruleValidate"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col span="24">
            <FormItem label="类型：">
              <RadioGroup
                v-model="formValidate.auth_type"
                @on-change="changeRadio"
              >
                <Radio
                  :label="item.value"
                  v-for="(item, i) in optionsRadio"
                  :key="i"
                >
                  <Icon type="social-apple"></Icon>
                  <span>{{ item.label }}</span>
                </Radio>
              </RadioGroup>
            </FormItem>
          </Col>
        </Row>
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="按钮名称：" prop="menu_name">
              <div class="add">
                <Input
                  v-model="formValidate.menu_name"
                  placeholder="请输入按钮名称"
                >
                </Input>
                <Button
                  class="ml10 df"
                  v-show="!authType"
                  @click="getRuleList()"
                  icon="ios-apps"
                >
                </Button>
              </div>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="父级分类：">
              <Cascader
                :data="menuList"
                change-on-select
                v-model="formValidate.path"
                filterable
              ></Cascader>
            </FormItem>
          </Col>
          <Col v-bind="grid" v-if="!authType">
            <FormItem label="请求方式：" prop="methods">
              <Select v-model="formValidate.methods">
                <Option value="">请求</Option>
                <Option value="GET">GET</Option>
                <Option value="POST">POST</Option>
                <Option value="PUT">PUT</Option>
                <Option value="DELETE">DELETE</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid" v-if="!authType">
            <FormItem label="接口地址：">
              <Input
                v-model="formValidate.api_url"
                placeholder="请输入接口地址"
                prop="api_url"
              ></Input>
            </FormItem>
          </Col>
          <Col v-bind="grid" v-if="authType">
            <FormItem label="接口参数：">
              <Input
                v-model="formValidate.params"
                placeholder="举例:a/123/b/234"
              ></Input>
            </FormItem>
          </Col>
          <Col v-bind="grid" v-if="authType">
            <FormItem label="路由名称：" prop="menu_path">
              <Input
                v-model="formValidate.menu_path"
                placeholder="请输入路由名称"
              ></Input>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="权限标识：" prop="unique_auth">
              <Input
                v-model="formValidate.unique_auth"
                placeholder="请输入权限标识"
              ></Input>
            </FormItem>
          </Col>
          <Col v-bind="grid" v-if="authType">
            <FormItem label="图标：">
              <Input
                v-model="formValidate.icon"
                placeholder="请选择图标，点击右面图标"
                icon="ios-appstore"
                @on-click="iconClick"
              ></Input>
            </FormItem>
          </Col>
          <!--<Col v-bind="grid" v-if="authType">-->
          <!--<FormItem label="顶部菜单：">-->
          <!--<Select v-model="formValidate.header" filterable allow-create @on-create="handleCreate1">-->
          <!--<Option v-for="(item,i) in headerOptionsList" :value="item.value" :key="i">{{ item.label-->
          <!--}}-->
          <!--</Option>-->
          <!--</Select>-->
          <!--</FormItem>-->
          <!--</Col>-->
          <Col v-bind="grid">
            <FormItem label="排序：">
              <Input
                type="number"
                v-model="formValidate.sort"
                placeholder="请输入排序"
                number
              ></Input>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="状态：">
              <RadioGroup v-model="formValidate.is_show">
                <Radio
                  :label="item.value"
                  v-for="(item, i) in isShowRadio"
                  :key="i"
                >
                  <Icon type="social-apple"></Icon>
                  <span>{{ item.label }}</span>
                </Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="是否为隐藏菜单：">
              <RadioGroup v-model="formValidate.is_show_path">
                <Radio
                  :label="item.value"
                  v-for="(item, i) in isShowPathRadio"
                  :key="i"
                >
                  <Icon type="social-apple"></Icon>
                  <span>{{ item.label }}</span>
                </Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col span="24">
            <Button
              type="primary"
              long
              @click="handleSubmit('formValidate')"
              :disabled="valids"
              >提交</Button
            >
          </Col>
        </Row>
      </Form>
    </Modal>
    <Modal
      v-model="modal12"
      scrollable
      width="600"
      title="图标选择"
      footer-hide
    >
      <Input
        v-model="iconVal"
        placeholder="输入关键词搜索,注意全是英文"
        clearable
        style="width: 300px"
        @on-change="upIcon(iconVal)"
        ref="search"
      />
      <div class="trees-coadd">
        <div class="scollhide">
          <div class="iconlist">
            <ul class="list-inline">
              <li
                class="icons-item"
                v-for="(item, i) in list"
                :key="i"
                :title="item.type"
              >
                <Icon
                  :type="item.type"
                  @click="iconChange(item.type)"
                  class="ivu-icon"
                />
              </li>
            </ul>
          </div>
        </div>
      </div>
    </Modal>
    <Modal
      v-model="ruleModal"
      scrollable
      width="1100"
      title="权限列表"
      footer-hide
      @on-visible-change="modalchange"
    >
      <div class="search-rule">
        <Input
          class="mr10"
          v-model="searchRule"
          placeholder="输入关键词搜索"
          clearable
          style="width: 300px"
          ref="search"
        />
        <Button class="mr10" type="primary" @click="searchRules">搜索</Button>
        <Button @click="init">重置</Button>
      </div>
      <div class="rule">
        <div
          class="rule-list"
          v-show="!arrs.length || arrs.includes(index)"
          :class="{ 'select-rule': arrs.includes(index) }"
          v-for="(item, index) in ruleList"
          :key="index"
          @click="selectRule(item)"
        >
          <div>按钮名称：{{ item.real_name }}</div>
          <div>请求方式：{{ item.method }}</div>
          <div>接口地址：{{ item.rule }}</div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import { addMenusApi, addMenus, getRuleList } from "@/api/systemMenus";
import icon from "@/utils/icon";
export default {
  name: "menusFrom",
  props: {
    formValidate: {
      type: Object,
      default: null,
    },
    titleFrom: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      arrs: [],
      searchRule: "",
      iconVal: "",
      grid: {
        xl: 12,
        lg: 12,
        md: 12,
        sm: 24,
        xs: 24,
      },
      modals: false,
      modal12: false,
      ruleValidate: {
        menu_name: [
          { required: true, message: "请输入按钮名称", trigger: "blur" },
        ],
        menu_path: [
          { required: true, message: "请输入路由名称", trigger: "blur" },
        ],
        methods: [
          { required: true, message: "请选择接口请求方式", trigger: "blur" },
        ],
        api_url: [
          { required: true, message: "请填写接口请求地址", trigger: "blur" },
        ],
      },
      FromData: [],
      valids: false,
      list2: [],
      list: icon,
      authType: true,
      search: icon,
      ruleModal: false,
      ruleList: [],
    };
  },
  watch: {
    "formValidate.header": function(n) {
      this.formValidate.is_header = n ? 1 : 0;
    },
    "formValidate.auth_type": function(n) {
      if (n === undefined) {
        n = 1;
      }
      this.authType = n === 1;
    },
    "formValidate.data": function(n) {},
  },
  computed: {
    /* eslint-disable */
    optionsList() {
      let a = [];
      this.FromData.map((item) => {
        if ("pid" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    headerOptionsList() {
      let a = [];
      this.FromData.map((item) => {
        if ("header" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    optionsListmodule() {
      let a = [];
      this.FromData.map((item) => {
        if ("module" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    optionsRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ("auth_type" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    isheaderRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ("is_header" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    isShowRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ("is_show" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    isShowPathRadio() {
      let a = [];
      this.FromData.map((item) => {
        if ("is_show_path" === item.field) {
          a = item.options;
        }
      });
      return a;
    },
    menuList() {
      let a = [];
      this.FromData.map((item) => {
        if ("menu_list" === item.field) {
          a = item.props.data;
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
    modalchange(type) {
      if (!type) {
        this.arrs = [];
        this.ruleModal = "";
        this.ruleModal = false;
      }
    },
    visible(type) {
      if (!type) {
        this.authType = true;
      }
    },
    selectRule(data) {
      this.$emit("selectRule", data);
      this.$nextTick((e) => {
        this.ruleModal = false;
      });
    },
    changeRadio(n) {
      this.authType = n === 1 ? true : false;
    },
    // 搜索
    upIcon(n) {
      let arrs = [];
      for (var i = 0; i < this.search.length; i++) {
        if (this.search[i].type.indexOf(n) !== -1) {
          arrs.push(this.search[i]);
          this.list = arrs;
        }
      }
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
      this.searchRule = "";
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
          this.$Message.error(res.msg);
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
        url: this.formValidate.id
          ? `/setting/menus/${this.formValidate.id}`
          : "/setting/menus",
        method: this.formValidate.id ? "put" : "post",
        datas: this.formValidate,
      };
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.valids = true;
          addMenusApi(data)
            .then(async (res) => {
              this.$Message.success(res.msg);
              this.modals = false;
              this.$emit("getList");
              this.getAddFrom();
              this.$store.dispatch("admin/menus/getMenusNavList");
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          if (!this.formValidate.menu_name)
            return this.$Message.error("请添加按钮名称！");
        }
      });
    },
    handleReset() {
      this.modals = false;
      this.authType = true;
      this.$refs["formValidate"].resetFields();
      this.$emit("clearFrom");
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
  background-color: #f2f2f2;
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
