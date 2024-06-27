<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <!-- <el-button type="primary" v-db-click @click="addType">添加类型</el-button> -->
      <el-table
        class="mt14"
        :data="tbody"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="会员名" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="有限期（天）" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.vip_day === -1 ? '永久' : scope.row.vip_day }}</span>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠价" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.pre_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否开启" min-width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="0"
              :inactive-value="1"
              v-model="scope.row.is_del"
              :value="scope.row.is_del"
              @change="onchangeIsShow(scope.row)"
              size="large"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a href="javascript:" v-db-click @click="editType(scope.row)">编辑</a>
            <!-- <el-divider direction="vertical" v-if="scope.row.type !== 'free' && scope.row.type !== 'ever'" />
            <a
              v-if="scope.row.type !== 'free' && scope.row.type !== 'ever'"
              href="javascript:"
              v-db-click @click="del(scope.row, '删除类型', scope.$index)"
              >删除</a
            > -->
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog
      :visible.sync="modal"
      :title="`${rowModelType}${rowEdit && rowEdit.title}会员`"
      width="540px"
      @closed="cancel"
    >
      <form-create v-if="modal" v-model="fapi" :rule="rule" :option="options" @submit="onSubmit"></form-create>
    </el-dialog>
  </div>
</template>

<script>
import { userMemberShip, memberShipSave, memberCard, deleteCard } from '@/api/user';

export default {
  name: 'list',
  data() {
    return {
      tbody: [],
      loading: false,
      modal: false,
      rowEdit: {},
      rowModelType: '编辑',
      options: {
        form: {
          labelWidth: '100px',
        },
      },
      rule: [
        {
          type: 'hidden',
          field: 'id',
          value: '',
        },
        {
          type: 'hidden',
          field: 'type',
          value: '',
        },
        {
          type: 'input',
          field: 'title',
          title: '会员名',
          value: '',
          props: {
            disabled: false,
            placeholder: '输入会员名',
          },
          validate: [
            {
              type: 'string',
              max: 10,
              min: 1,
              message: '请输入长度为1-10的名称',
              requred: true,
            },
          ],
        },
        {
          type: 'InputNumber',
          field: 'vip_day',
          title: '有限期（天）',
          value: null,
          props: {
            precision: 0,
            disabled: false,
            type: 'text',
            placeholder: '输入有限期',
            controls: false,
          },
          style: {
            width: '100%',
          },
          validate: [
            {
              type: 'number',
              max: 1000000,
              min: 0,
              message: '最大只能输入1000000,最小为0',
              requred: true,
            },
          ],
        },
        {
          type: 'InputNumber',
          field: 'price',
          title: '原价',
          value: null,
          props: {
            min: 0,
            disabled: false,
            placeholder: '输入原价',
            controls: false,
          },
          style: {
            width: '100%',
          },
          validate: [
            {
              type: 'number',
              max: 1000000,
              min: 0,
              message: '最大只能输入1000000,最小为0',
              requred: true,
            },
          ],
        },
        {
          type: 'InputNumber',
          field: 'pre_price',
          title: '优惠价',
          value: null,
          props: {
            min: 0,
            disabled: false,
            placeholder: '输入优惠价',
            controls: false,
          },
          style: {
            width: '100%',
          },
          validate: [
            {
              type: 'number',
              max: 1000000,
              min: 0,
              message: '最大只能输入1000000,最小为0',
              requred: true,
            },
          ],
        },
        {
          type: 'InputNumber',
          field: 'sort',
          title: '排序',
          value: 0,
          props: {
            min: 1,
            max: 1000000,
            disabled: false,
            placeholder: '请输入排序',
            controls: false,
          },
          style: {
            width: '100%',
          },
          validate: [
            {
              type: 'number',
              max: 1000000,
              min: 0,
              message: '最大只能输入1000000,最小为0',
              requred: true,
            },
          ],
        },
      ],
      fapi: {
        id: '',
        pre_price: null,
        price: null,
        sort: null,
        title: '',
        type: 'owner',
        vip_day: null,
      },
    };
  },
  created() {
    this.getMemberShip();
  },
  mounted() {},
  methods: {
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        is_del: row.is_del,
      };
      memberCard(data)
        .then((res) => {
          this.$message.success(res.msg);
          this.getMemberShip();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    cancel() {
      this.fapi = {
        id: '',
        pre_price: null,
        price: null,
        sort: null,
        title: '',
        type: 'owner',
        vip_day: null,
      };
      this.rule.forEach((e) => {
        e.value = null;
      });
    },
    getMemberShip() {
      this.loading = true;
      userMemberShip()
        .then((res) => {
          this.loading = false;
          const { count, list } = res.data;
          this.total = count;
          this.tbody = list;
        })
        .catch((err) => {
          this.loading = false;
          this.$message.error(err.msg);
        });
    },
    addType() {
      this.rowEdit.id = 0;
      this.rowModelType = '新增';
      this.rule[1].value = 'owner';
      this.rule[3].props.disabled = false;
      this.rule[5].props.disabled = false;
      this.rowEdit.title = '';
      this.modal = true;
    },
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `user/member_ship/delete/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getMemberShip();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    editType(row) {
      this.rule.forEach((item) => {
        for (const key in row) {
          if (row.hasOwnProperty(key)) {
            if (item.field === key) {
              if (key === 'vip_day') {
                if (row[key] === -1 || row[key] == '永久') {
                  item.type = 'input';
                  item.props.disabled = true;
                  row[key] = '永久';
                  item.validate = [{ type: 'string', message: '', requred: true }];
                } else {
                  item.props.disabled = false;
                  item.props.min = 1;
                  item.validate = [
                    {
                      type: 'number',
                      max: 1000000,
                      min: 0,
                      message: '最大只能输入1000000,最小为0',
                      requred: true,
                    },
                  ];
                }
              }
              if (['price'].includes(key)) {
                row[key] = parseFloat(row[key]);
              }
              if (['pre_price'].includes(key)) {
                row[key] = parseFloat(row[key]);
                if (row[key]) {
                  item.props.disabled = false;
                } else {
                  item.props.disabled = true;
                }
              }
              item.value = row[key];
            }
          }
        }
      });
      this.rowModelType = '编辑';
      this.rowEdit = JSON.parse(JSON.stringify(row));
      this.modal = true;
    },
    onSubmit(formData) {
      memberShipSave(this.rowEdit.id, formData)
        .then((res) => {
          this.modal = false;
          this.$message.success(res.msg);
          this.getMemberShip();
          this.cancel();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>
<style scoped lang="stylus"></style>
