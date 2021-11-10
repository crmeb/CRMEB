<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="acea-row row-between-wrapper">
        <Row type="flex">
          <Col v-bind="grid">
            <div class="button acea-row row-middle">
              <Button class="mr20" type="primary" icon="md-add" @click="add(0)"
                >添加省份</Button
              >
              <Button type="primary" @click="cleanCache">清除缓存</Button>
            </div>
          </Col>
        </Row>
      </div>
      <vxe-table
        class="mt25"
        highlight-hover-row
        :loading="loading"
        header-row-class-name="false"
        :tree-config="{ children: 'children' }"
        :data="cityLists"
      >
        <vxe-table-column
          field="id"
          title="ID"
          tooltip
          width="80"
        ></vxe-table-column>
        <vxe-table-column
          field="name"
          tree-node
          title="地区名称"
          min-width="100"
        >
        </vxe-table-column>
        <vxe-table-column
          field="parent_id"
          title="上级名称"
          min-width="250"
        ></vxe-table-column>
        <vxe-table-column
          field="date"
          title="操作"
          width="250"
          fixed="right"
          align="center"
        >
          <template v-slot="{ row, index }">
            <a v-if="row.level < 2" @click="add(row.city_id)">添加下级</a>
            <Divider v-if="row.level < 2" type="vertical" />
            <a @click="edit(row.id)">编辑</a>
            <Divider type="vertical" />
            <a @click="del(row, '删除城市', index)">删除</a>
          </template>
        </vxe-table-column>
      </vxe-table>
      <!-- <Table :columns="columns1" :data="cityLists" ref="table" class="mt25"
                   :loading="loading" highlight-row
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="icons">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="row.icon">
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="region">
                    <div class="font-blue" @click="lower(row.city_id)">{{row.name}}</div>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row.id)">编辑</a>
                    <Divider type="vertical" />
                    <a @click="del(row,'删除城市',index)">删除</a>
                </template>
            </Table> -->
    </Card>
  </div>
</template>

<script>
import { mapState } from "vuex";
import {
  cityListApi,
  cityAddApi,
  cityApi,
  cityCleanCacheApi,
} from "@/api/setting";
export default {
  name: "setting_dada",
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      columns1: [
        {
          title: "编号",
          key: "id",
          width: 80,
        },
        {
          title: "上级名称",
          key: "parent_id",
          minWidth: 300,
        },
        {
          title: "地区名称",
          slot: "region",
          minWidth: 300,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 120,
        },
      ],
      cityLists: [],
      cityId: 0, // 城市id
    };
  },
  computed: {
    ...mapState("media", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? "top" : "left";
    },
  },
  created() {
    this.getList(0);
  },
  methods: {
    // 清除缓存；
    cleanCache() {
      cityCleanCacheApi()
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.success(res.msg);
        });
    },
    // 添加
    add(cityId) {
      this.$modalForm(cityAddApi(cityId)).then(() => this.getList(0));
    },
    // 添加下级；
    lower(cityId) {
      this.cityId = cityId;
      this.getList(cityId);
    },
    // 城市列表
    getList(parentId) {
      let that = this;
      that.loading = true;
      cityListApi(parentId)
        .then(async (res) => {
          that.cityLists = res.data;
          that.loading = false;
        })
        .catch((res) => {
          that.loading = false;
          that.$Message.error(res.msg);
        });
    },
    // 返回
    goBack() {
      this.cityId = 0;
      this.getList(0);
    },
    // 修改
    edit(id) {
      this.$modalForm(cityApi(id)).then(() => this.getList(this.cityId));
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/city/del/${row.city_id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.cityLists.splice(num, 1);
          this.getList(this.cityId);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.button
   width 300px;
</style>
