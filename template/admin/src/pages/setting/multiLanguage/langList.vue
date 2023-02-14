<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        @submit.native.prevent
      >
        <Row :gutter="24" type="flex">
          <Col span="24">
            <FormItem label="语言分类：">
              <RadioGroup type="button" v-model="formValidate.is_admin" class="mr15" @on-change="selChange">
                <Radio :label="item.value" v-for="(item, index) in langType.isAdmin" :key="index"
                  >{{ item.title }}
                </Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="语言类型：">
              <RadioGroup type="button" v-model="formValidate.type_id" class="mr15" @on-change="selChange">
                <Radio :label="item.value" v-for="(item, index) in langType.langType" :key="index"
                  >{{ item.title }}
                </Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="搜索：">
              <div class="acea-row row-middle">
                <Input
                  search
                  enter-button
                  @on-search="selChange"
                  placeholder="请输入语言备注"
                  element-id="name"
                  v-model="formValidate.remarks"
                  style="width: 30%"
                />
              </div>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Card>
    <Alert class="mt10">
      使用说明
      <template slot="desc"
        >添加用户端页面语言，添加完成之后状态码为中文文字，前端页面使用 $t(`xxxx`)，js文件中使用 this.t(`xxxx`) 或者使用
        that.t(`xxxx`)<br />添加后端接口语言，添加完成之后状态码为6位数字，后台抛错或者控制器返回文字的时候直接填写状态码数字
      </template>
    </Alert>
    <Card :bordered="false" dis-hover>
      <Row type="flex">
        <Col>
          <Button type="primary" icon="md-add" @click="add">添加语言</Button>
        </Col>
      </Row>
      <Table
        ref="table"
        :columns="columns"
        :data="tabList"
        class="ivu-mt"
        :loading="loading"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除语言', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <Modal
      v-model="addlangModal"
      width="750"
      title="添加语言"
      :loading="FormLoading"
      @on-ok="ok"
      @on-cancel="addlangModal = false"
      @on-visible-change="modalChange"
    >
      <Form ref="langFormData" :model="langFormData" :rules="ruleValidate" :label-width="120">
        <FormItem label="语言分类：" class="mb20">
          <RadioGroup type="button" v-model="langFormData.is_admin" class="mr15">
            <Radio :label="item.value" v-for="(item, index) in langType.isAdmin" :key="index">{{ item.title }}</Radio>
          </RadioGroup>
        </FormItem>
        <Input v-model="langFormData.edit" v-show="false"></Input>
        <FormItem label="语言说明：" prop="remarks" class="mb20">
          <Input
            v-model="langFormData.remarks"
            placeholder="请输入语言说明"
            style="width: 330px"
            search
            enter-button="翻译"
            @on-search="translate"
          ></Input>
        </FormItem>
        <FormItem label="对应语言：" prop="remark" class="mb20">
          <Table
            ref="langTable"
            :loading="traTabLoading"
            :columns="langColumns"
            :data="langFormData.list"
            no-data-text="暂无数据"
            no-filtered-data-text="暂无筛选结果"
          >
            <template slot-scope="{ row, index }" slot="lang_explain">
              <Input v-model="langFormData.list[index].lang_explain" class="priceBox"></Input>
            </template>
          </Table>
        </FormItem>
      </Form>
    </Modal>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import { langCodeList, langCodeInfo, langCodeSettingSave, langCodeTranslate } from '@/api/setting';

export default {
  data() {
    return {
      addlangModal: false,
      traTabLoading: false,
      langType: {},
      formValidate: {
        is_admin: 0,
        type_id: 1,
        remarks: '',
        page: 1,
        limit: 20,
      },
      total: 0,
      FormLoading: true,
      loading: false,
      ruleValidate: {
        code: [{ required: true, message: '请输入状态码/文字', trigger: 'blur' }],
        remarks: [{ required: true, message: '请输入文字', trigger: 'blur' }],
      },
      columns: [
        {
          title: '编号',
          key: 'id',
          width: 80,
        },
        {
          title: '所属语言',
          key: 'language_name',
          minWidth: 180,
        },
        {
          title: '状态码/文字',
          key: 'code',
          minWidth: 300,
        },
        {
          title: '备注说明',
          key: 'remarks',
          minWidth: 300,
        },
        {
          title: '对应语言',
          key: 'lang_explain',
          minWidth: 150,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          width: 100,
        },
      ],
      langColumns: [
        {
          title: '所属语言',
          key: 'language_name',
          width: 120,
        },
        {
          title: '对应语言',
          slot: 'lang_explain',
          minWidth: 250,
        },
      ],
      langData: [],
      langFormData: {
        is_admin: 0,
        code: '',
        remarks: '',
        edit: 0,
        list: [],
      },
      tabList: [],
      FromData: null,
      extractId: 0,
      code: null,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    translate() {
      if (!this.langFormData.remarks.trim()) {
        return this.$Message.warning('请先输入翻译内容');
      }
      this.traTabLoading = true;
      langCodeTranslate({
        text: this.langFormData.remarks,
      })
        .then((res) => {
          this.langFormData.list.map((e) => {
            e.lang_explain = res.data[e.type_id];
          });
          this.traTabLoading = false;
        })
        .catch((err) => {
          this.traTabLoading = false;
          this.$Message.error(err.msg);
        });
    },
    add() {
      this.langFormData.list = this.langType.langType.map((e) => {
        return {
          language_name: e.title,
          lang_explain: '',
          remarks: '',
          type_id: e.value,
        };
      });
      this.addlangModal = true;
    },
    ok() {
      if (!this.langFormData.remarks.trim()) {
        this.FormLoading = false;
        this.$nextTick(() => {
          this.FormLoading = true;
        });
        return this.$Message.error('请先输入语言说明');
      }
      langCodeSettingSave(this.langFormData)
        .then((res) => {
          this.addlangModal = false;
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.FormLoading = false;
          this.$nextTick(() => {
            this.FormLoading = true;
          });
          this.$Message.error(err.msg);
        });
    },
    edit(row) {
      this.langFormData.is_admin = this.formValidate.is_admin;
      this.code = row.code;
      langCodeInfo({ code: row.code })
        .then((res) => {
          this.langFormData.list = res.data.list;
          this.langFormData.code = res.data.code;
          this.langFormData.remarks = res.data.remarks;
          this.langFormData.edit = 1;
          this.addlangModal = true;
        })
        .catch((err) => {
          this.loading = false;
          this.$Message.error(err.msg);
        });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/lang_code/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tabList.splice(num, 1);
          // this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    modalChange(status) {
      if (!status) {
        this.langFormData = {
          is_admin: 0,
          name: '',
          code: '',
          list: [],
        };
        this.code = null;
      }
    },
    // 选择
    selChange() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      langCodeList(this.formValidate)
        .then(async (res) => {
          this.loading = false;
          this.tabList = res.data.list;
          this.total = res.data.count;
          this.langType = res.data.langType;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
  },
};
</script>
<style scoped lang="stylus">
.ivu-mt .type .item {
  margin: 3px 0;
}

.tabform {
  margin-bottom: 10px;
}

.Refresh {
  font-size: 12px;
  color: #1890FF;
  cursor: pointer;
}

.ivu-form-item {
  margin-bottom: 10px;
}

.status >>> .item ~ .item {
  margin-left: 6px;
}

.status >>> .statusVal {
  margin-bottom: 7px;
}

/* .ivu-mt >>> .ivu-table-header */
/* border-top:1px dashed #ddd!important */
.type {
  padding: 3px 0;
  box-sizing: border-box;
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

.mb20 /deep/ .ivu-table-wrapper > .ivu-spin-fix {
  border: none;
}
</style>
