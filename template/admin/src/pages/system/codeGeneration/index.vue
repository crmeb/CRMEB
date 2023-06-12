<template>
  <div class="code-wapper">
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <router-link :to="{ path: $routeProStr + '/system/code_generation_list' }"
          ><Button icon="ios-arrow-back" size="small" type="text">返回</Button></router-link
        >
        <Divider type="vertical" />
        <span class="ivu-page-header-title mr20" style="padding: 0">添加功能</span>
      </div>
    </div>
    <div class="message">
      <Card :bordered="false" dis-hover class="">
        <Steps :current="currentTab">
          <Step :title="item.label" v-for="(item, index) in headerList" :key="index"></Step>
        </Steps>
      </Card>
    </div>
    <div class="pt10 tab-1" v-show="currentTab == '0'">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <FoundationForm
          ref="Foundation"
          :foundation="formItem.foundation"
          :tableField="tableField"
          @storageData="storageData"
        />
      </Card>
    </div>
    <div class="pt10" v-show="currentTab == '1'">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <TableForm
          ref="TableForm"
          :foundation="formItem.foundation"
          :tableField="tableField"
          :id="id"
          @storageData="storageData"
        />
      </Card>
    </div>
    <div class="pt10" v-show="currentTab == '2'">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <StorageLoc :storage="formItem.storage" />
      </Card>
    </div>
    <Card :bordered="false" class="btn" dis-hover>
      <Button class="mr20" @click="beforeTab">上一步</Button>
      <Button type="primary" @click="nextTab">{{ currentTab == 2 ? '提交' : '下一步' }}</Button>
    </Card>
  </div>
</template>

<script>
import { codeCrud } from '@/api/setting';
import FoundationForm from './components/FoundationFor.vue';
import TableForm from './components/TableForm.vue';
import StorageLoc from './components/StorageLoc.vue';
import { getMenusUnique } from '@/api/systemMenus';
import { formatFlatteningRoutes } from '@/libs/system';
import { crudFilePath } from '@/api/systemCodeGeneration';
import { crudDet } from '@/api/systemCodeGeneration';

export default {
  name: 'system_code_generation',
  components: { FoundationForm, StorageLoc, TableForm },
  data() {
    return {
      currentTab: 0,
      headerList: [
        { label: '基础信息', value: 'foundation' },
        { label: '字段配置', value: 'table' },
        { label: '存放位置', value: 'storage' },
      ],
      formItem: {
        foundation: {
          pid: '',
          tableName: '',
          modelName: '',
          isTable: 1,
          menuName: '',
        },
        tableForm: {},
        storage: {},
        field: {},
        formItem: {},
      },
      ruleValidate: {
        foundation: {},
      },
      tableField: [],
      rowList: [],
      reqloading: false,
      id: '',
    };
  },
  created() {
    if (this.$route.query.id) {
      this.id = this.$route.query.id;
      this.getDetail(this.$route.query.id);
    }
  },
  mounted: function () {},
  methods: {
    getDetail(id) {
      crudDet(id).then((res) => {
        let data = res.data.crudInfo.field;
        this.formItem.foundation.pid = Number(data.pid);
        this.formItem.foundation.tableName = data.tableName;
        this.formItem.foundation.modelName = data.modelName;
        this.formItem.foundation.menuName = data.menuName;
        this.$refs.TableForm.tableField = data.tableField;
        this.formItem.storage = data.filePath;
        let i = 0;
        data.tableField.map((e) => {
          if (e.field === 'create_time' || e.field === 'update_time') {
            i++;
            if (i == 2) this.$refs.TableForm.isCreate = true;
          }
          if (e.field === 'delete_time') {
            this.$refs.TableForm.isDelete = true;
          }
        });
      });
    },
    storageData(data) {
      this.formItem.storage = data;
    },
    beforeTab() {
      this.currentTab--;
    },
    addRow() {
      let foundation = this.formItem.foundation;
      if (!foundation.tableName) return this.$Message.warning('请先填写表名');
      let data = {
        menuName: foundation.menuName,
        tableName: foundation.tableName,
        // isTable: foundation.isTable,
        fromField: [],
        columnField: [],
      };
      crudFilePath(data)
        .then((res) => {
          this.$refs.TableForm.tableField = res.data.tableField.length ? res.data.tableField : [];
          this.formItem.storage = res.data.makePath;
          if (!res.data.tableField.length) {
            this.$refs.TableForm.tableField.push({
              field: 'id',
              field_type: 'int',
              default: '',
              comment: '自增ID',
              required: false,
              is_table: true,
              table_name: '',
              limit: '10',
              primaryKey: 1,
              from_type: '0',
            });
          }
          this.currentTab++;
        })
        .catch((err) => {
          this.$Message.warning(err.msg);
        });
    },
    nextTab() {
      if (this.currentTab == 0) {
        // if (!this.formItem.foundation.pid) return this.$Message.warning('请选择菜单');
        if (!this.formItem.foundation.tableName) return this.$Message.warning('请输入表名');
        if (!this.formItem.foundation.modelName) return this.$Message.warning('请输入模块名');
        if (!this.formItem.foundation.isTable) {
          if (!this.$refs.TableForm.tableField.length) return this.$Message.warning('请先添加表数据');
          if (this.$refs.TableForm.tableField.length)
            for (let i = 0; i < this.$refs.TableForm.tableField.length; i++) {
              const el = this.$refs.TableForm.tableField[i];
              if (
                ['addSoftDelete', 'addTimestamps'].indexOf(el.field_type) === -1 &&
                (!el.field || !el.field_type || !el.comment)
              ) {
                return this.$Message.warning('请完善sql表数据');
              }
            }
        }
        if (this.id) {
          return this.currentTab++;
        }
        this.addRow();
      } else if (this.currentTab == 2) {
        if (this.reqloading) return;
        let data = {
          ...this.formItem.foundation,
          filePath: this.formItem.storage,
          tableField: this.$refs.TableForm.tableField,
          deleteField: this.id ? this.$refs.TableForm.deleteField : [],
        };
        if (this.id) {
          data.id = this.id;
          this.$Modal.confirm({
            title: '生成提醒',
            content:
              '重新提交会重新生成文件,<span style="color: red">删除、新增、修改</span>的字段将直接从改表中进行修改,请慎重操作！！',
            loading: true,
            onOk: () => {
              this.saveCodeCrud(data, true);
            },
          });
        } else {
          this.$Modal.confirm({
            title: '生成提醒',
            content:
              '生成后本地开发调试会直接加载生成的vue页面；如果是上线后进行生成,可以进行浏览，代码生成列表中的修改文件将不生效。需要重新打包上线！',
            loading: true,
            onOk: () => {
              this.saveCodeCrud(data, true);
            },
          });
        }
      } else {
        if (this.currentTab < 3) this.currentTab++;
      }
    },
    saveCodeCrud(data, loading) {
      this.reqloading = true;
      codeCrud(data)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getMenusUnique();
          this.reqloading = false;

          if (loading) {
            this.$Modal.remove();
          }
          this.$router.push({
            name: 'system_code_generation_list',
          });
        })
        .catch((err) => {
          this.reqloading = false;
          this.$Message.error(err.msg);
        });
    },
    getMenusUnique() {
      getMenusUnique().then((res) => {
        let data = res.data;
        this.$store.commit('userInfo/uniqueAuth', data.uniqueAuth);
        this.$store.commit('menus/getmenusNav', data.menus);
        this.$store.dispatch('routesList/setRoutesList', data.menus);
        let arr = formatFlatteningRoutes(this.$router.options.routes);
        this.formatTwoStageRoutes(arr);
        let routes = formatFlatteningRoutes(data.menus);
        this.$store.commit('menus/setOneLvRoute', routes);
        this.bus.$emit('routesListChange');
      });
    },
    formatTwoStageRoutes(arr) {
      if (arr.length <= 0) return false;
      const newArr = [];
      const cacheList = [];
      arr.forEach((v) => {
        if (v && v.meta && v.meta.keepAlive) {
          newArr.push({ ...v });
          cacheList.push(v.name);
          this.$store.dispatch('keepAliveNames/setCacheKeepAlive', cacheList);
        }
      });
      return newArr;
    },
  },
};
</script>
<style lang="scss" scoped>
.ivu-steps .ivu-steps-title {
  line-height: 26px;
}
.code-wapper {
  min-height: 800px;
  padding-bottom: 90px;
}
.btn {
  position: fixed;
  bottom: 10px;
  height: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 88.7%;
  background-color: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
}
.tab-1 {
  padding-bottom: 100px;
}
/deep/ .el-input__inner {
  padding-left: 7px;
}
/deep/ .ivu-form-item {
  margin-bottom: 17px;
}
/deep/ .ivu-form-item-error-tip {
  padding-top: 2px;
}
/deep/ .tip {
  color: #bbb;
  line-height: 16px;
  padding-top: 5px;
}
</style>
