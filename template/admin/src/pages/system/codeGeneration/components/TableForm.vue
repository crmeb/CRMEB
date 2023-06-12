<template>
  <div class="main">
    <Alert closable>
      crud生成说明
      <template #desc>
        <p>
          1、字段配置中表存在生成的字段为表内列的信息,并且主键、伪删除字段不允许设置为列，主键默认展示在列表中，伪删除字段不允许展示
        </p>
        <p>2、在字段配置中新建表时，主键不需要增加列，会自动增加一行主键id</p>
        <p>
          3、在字段配置中新建表时，字段类型为addTimestamps会自动创建create_time、update_time字段，字段类型为：timestamp
        </p>
        <p>
          4、在字段配置中新建表时，字段类型为addSoftDelete会字段创建delete_time字段，字段类型为：timestamp，作用是伪删除
        </p>
        <p>5、在字段配置中，表单类型为不生成时创建后不会生成对应的表单项</p>
        <p>6、添加字段id、create_time、update_time、delete_time为不可用字段</p>
      </template>
    </Alert>
    <div class="df">
      <Button class="mr20" type="primary" @click="addRow">添加一行</Button>
      <Checkbox class="mr10" v-model="isCreate" @on-change="addCreate">添加与修改时间</Checkbox>
      <Checkbox class="mr10" v-model="isDelete" @on-change="addDelete">伪删除</Checkbox>
    </div>
    <div>
      <Table
        ref="selection"
        :columns="columns"
        :data="tableField"
        no-data-text="暂无数据"
        highlight-row
        :loading="loading"
        max-height="600"
        size="small"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="field">
          <Input
            :disabled="disabledInput(index)"
            v-model="tableField[index].field"
            @on-blur="changeField(index)"
          ></Input>
        </template>
        <template slot-scope="{ row, index }" slot="field_type">
          <Select
            v-model="tableField[index].field_type"
            :disabled="disabledInput(index)"
            @on-change="changeItemField($event, index)"
          >
            <Option v-for="item in columnTypeList" :value="item" :key="item">{{ item }}</Option>
          </Select>
        </template>
        <template slot-scope="{ row, index }" slot="limit">
          <Input v-model="tableField[index].limit" :disabled="disabledInput(index)"></Input>
        </template>
        <template slot-scope="{ row, index }" slot="default">
          <Input v-model="tableField[index].default" :disabled="disabledInput(index)"></Input>
        </template>
        <template slot-scope="{ row, index }" slot="comment">
          <Input
            v-model="tableField[index].comment"
            :disabled="disabledInput(index)"
            @on-change="(e) => changeComment(e, index)"
          ></Input>
        </template>
        <template slot-scope="{ row, index }" slot="required">
          <Checkbox v-model="tableField[index].required" :disabled="disabledInput(index)"></Checkbox>
        </template>
        <template slot-scope="{ row, index }" slot="is_table">
          <Checkbox v-model="tableField[index].is_table" :disabled="disabledInput(index)"></Checkbox>
        </template>
        <template slot-scope="{ row, index }" slot="table_name">
          <Input v-model="tableField[index].table_name" :disabled="disabledInput(index)"></Input>
        </template>
        <template slot-scope="{ row, index }" slot="from_type">
          <Select v-model="tableField[index].from_type" :disabled="disabledInput(index)">
            <Option v-for="item in fromTypeList" :value="item.value" :key="item.value">{{ item.label }}</Option>
          </Select>
        </template>
        <template slot-scope="{ row, index }" slot="options">
          <div class="table-options" v-if="['select', 'radio', 'checkbox'].includes(tableField[index].from_type)">
            <Select>
              <Option v-for="item in tableField[index].options" :value="item.value" :key="item.value">{{
                item.label
              }}</Option>
            </Select>
            <Icon class="create" type="md-create" @click="eidtOptions(index)" />
          </div>
          <div v-else>--</div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a v-if="!tableField[index].primaryKey && !disabledInput(index)" @click="del(row, index)">删除</a>
          <span v-else>--</span>
        </template>
      </Table>
    </div>
    <Modal
      v-model="optionsModal"
      scrollable
      title="字典配置"
      closable
      :mask-closable="false"
      width="400px"
      @on-ok="addOptions"
      @on-cancel="optionsModal = false"
    >
      <div class="options-list">
        <div class="item" v-for="(item, index) in optionsList" :key="index">
          <Input class="mr10" v-model="item.label" placeholder="label" style="width: 150px" />
          <Input class="mr10" v-model="item.value" placeholder="value" style="width: 150px" />
          <Icon v-if="index == optionsList.length - 1" class="add" type="md-add-circle" @click="addOneOptions" />
          <Icon v-if="index > 0" class="add" type="md-remove-circle" @click="delOneOptions(index)" />
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import { crudMenus, crudColumnType, crudFilePath } from '@/api/systemCodeGeneration';

export default {
  name: '',
  props: {
    foundation: {
      type: Object,
      default: () => {
        return {};
      },
    },
    id: {
      type: String | Number,
    },
  },
  data() {
    return {
      foundationRules: {},
      menusList: [],
      columnTypeList: [],
      optionsModal: false,
      isCreate: false,
      isDelete: false,
      columns: [
        {
          title: '字段名称',
          slot: 'field',
          minWidth: 100,
        },
        {
          title: '字段类型',
          slot: 'field_type',
          minWidth: 100,
        },
        {
          title: '长度',
          slot: 'limit',
          minWidth: 100,
        },
        {
          title: '默认值',
          slot: 'default',
          minWidth: 100,
        },
        {
          title: '字段描述',
          slot: 'comment',
          minWidth: 100,
        },
        {
          title: '列表',
          slot: 'is_table',
          width: 70,
          align: 'center',
        },
        {
          title: '列表名',
          slot: 'table_name',
          minWidth: 120,
          align: 'center',
        },
        {
          title: '表单类型',
          slot: 'from_type',
          minWidth: 100,
        },
        {
          title: '字典配置',
          slot: 'options',
          minWidth: 100,
        },
        {
          title: '必填',
          slot: 'required',
          width: 70,
          align: 'center',
        },
        {
          title: '操作',
          slot: 'action',
          width: 70,
          align: 'center',
        },
      ],
      fromTypeList: [],
      loading: false,
      tableField: [],
      optionsList: [],
      index: 0,
      deleteField: [],
    };
  },
  created() {
    this.getCrudMenus();
  },
  mounted() {},
  methods: {
    disabledInput(index) {
      let fieldInfo = this.tableField[index];
      let res = ['addTimestamps', 'addSoftDelete'].includes(this.tableField[index].field_type);
      if (fieldInfo.primaryKey) {
        res = true;
      } else if (fieldInfo.field === 'delete_time' && fieldInfo.field_type === 'timestamp') {
        res = true;
      } else if (
        (fieldInfo.field === 'create_time' || fieldInfo.field === 'update_time') &&
        fieldInfo.field_type === 'timestamp'
      ) {
        res = true;
      }
      return res;
    },
    initfield() {
      this.tableField = [];
    },
    changeItemField(e, i) {
      if (e === 'addSoftDelete') {
        this.$set(this.tableField[i], 'comment', '伪删除');
      }
      if (e === 'addTimestamps') {
        this.$set(this.tableField[i], 'comment', '添加和修改时间');
      }
    },
    eidtOptions(i) {
      this.index = i;
      this.optionsList = this.tableField[i].options || [{ label: '', value: '' }];
      this.optionsModal = true;
    },
    addOptions() {
      this.$set(this.tableField[this.index], 'options', this.optionsList);
    },
    changeRadio(status) {
      this.tableField = [];
      if (status) {
        this.addRow();
      }
    },
    initTableName() {
      this.tableField = [];
    },
    addRow() {
      for (let i = 0; i < this.tableField.length; i++) {
        const el = this.tableField[i];
        if ((!el.field || !el.field_type) && !['addTimestamps', 'addSoftDelete'].includes(el.field_type)) {
          return this.$Message.warning('请先完善上一条数据');
        }
        if (
          el.is_table &&
          !el.table_name &&
          !Number(el.primaryKey) &&
          !['addTimestamps', 'addSoftDelete'].includes(el.field_type)
        ) {
          return this.$Message.warning('请输入列表名');
        }
      }
      let i = this.tableField.length;
      let spliceIndex = 0;
      this.tableField.map((e) => {
        if (e.field === 'create_time' || e.field === 'update_time') {
          spliceIndex++;
        }
        if (e.field === 'delete_time') {
          spliceIndex++;
        }
      });
      i = this.tableField.length - spliceIndex;
      this.tableField.splice(i, 0, {
        field: '',
        field_type: '',
        default: '',
        comment: '',
        required: false,
        is_table: true,
        table_name: '',
        limit: '',
        primaryKey: 0,
        from_type: '0',
      });
      // this.tableField.push();
    },
    addCreate(status) {
      if (status) {
        let haveCre = this.tableField.findIndex((e) => e.field === 'create_time');
        let haveUp = this.tableField.findIndex((e) => e.field === 'update_time');
        if (haveCre > 0 || haveUp > 0) {
          this.$nextTick((e) => {
            this.isCreate = false;
          });
          return this.$Message.warning('已存在 create_time或update_time');
        }
        let data = [
          {
            field: 'create_time',
            field_type: 'timestamp',
            default: '',
            comment: '添加时间',
            required: false,
            is_table: true,
            table_name: '添加时间',
            limit: '',
            primaryKey: 0,
            from_type: '0',
          },
          {
            field: 'update_time',
            field_type: 'timestamp',
            default: '',
            comment: '修改时间',
            required: false,
            is_table: true,
            table_name: '修改时间',
            limit: '',
            primaryKey: 0,
            from_type: '0',
          },
        ];
        this.tableField = [...this.tableField, ...data];
      } else {
        let i = this.tableField.findIndex((e) => e.field === 'create_time');
        this.tableField.splice(i, 2);
      }
    },
    addDelete(status) {
      if (status) {
        let haveDel = this.tableField.findIndex((e) => e.field === 'delete_time');
        if (haveDel > 0) {
          this.isDelete = false;
          return this.$Message.warning('已存在 delete_time');
        }
        let data = [
          {
            field: 'delete_time',
            field_type: 'timestamp',
            default: '',
            comment: '伪删除',
            required: false,
            is_table: false,
            table_name: '伪删除',
            limit: '',
            primaryKey: 0,
            from_type: '0',
          },
        ];
        this.tableField = [...this.tableField, ...data];
      } else {
        let i = this.tableField.findIndex((e) => e.field === 'delete_time');
        this.tableField.splice(i, 1);
      }
    },
    changeField(index) {
      console.log(this.tableField[index].field)
      if (this.tableField[index].field) {
        for (let i = 0; i < this.tableField.length; i++) {
          const e = this.tableField[i];
          if (['id', 'create_time', 'update_time', 'delete_time'].includes(this.tableField[index].field)) {
            this.$Message.warning('列表中已存在该字段名称');
            this.tableField[index].field = '';
            return;
          }
        }
      }
    },
    changeComment(e, index) {
      this.tableField[index].table_name = e.target.value;
    },
    getCrudMenus() {
      crudMenus().then((res) => {
        console.log(res);
        this.menusList = res.data;
      });
      crudColumnType().then((res) => {
        this.columnTypeList = res.data.types;
        this.fromTypeList = res.data.form;
      });
    },
    del(row, index) {
      this.tableField.splice(index, 1);
      if (this.id) {
        this.deleteField.push(row.field);
      }
    },
    addOneOptions() {
      this.optionsList.push({
        label: '',
        value: '',
      });
    },
    delOneOptions(i) {
      this.optionsList.splice(i, 1);
    },
  },
};
</script>
<style lang="stylus" scoped>
.form-width {
  width: 500px;
}
.item{
  display flex
  margin-bottom 10px
  .row{
    width 140px
    margin-right 10px
  }
}
.table-options{
  display flex
  align-items center
  .create{
    font-size: 16px;
    margin-left 10px
    cursor pointer
  }
}
.options-list{
  .item{
    display flex
    align-items center
    .add{
      font-size: 24px
      color #2d8cf0
    }
  }
}
</style>
