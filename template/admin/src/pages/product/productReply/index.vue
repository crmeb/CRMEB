<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          inline
          label-width="80px"
          label-position="right"
          @submit.native.prevent
        >
          <el-form-item label="评论时间：">
            <el-date-picker
              clearable
              v-model="timeVal"
              type="daterange"
              @change="onchangeTime"
              format="yyyy/MM/dd"
              value-format="yyyy/MM/dd"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              :picker-options="pickerOptions"
            ></el-date-picker>
          </el-form-item>
          <el-form-item label="评价状态：">
            <el-select
              v-model="formValidate.is_reply"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="已回复"></el-option>
              <el-option value="0" label="未回复"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="审核状态：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="0" label="未审核"></el-option>
              <el-option value="1" label="已通过"></el-option>
              <el-option value="2" label="已拒绝"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品信息：" label-for="store_name">
            <el-input
              placeholder="请输入商品信息"
              clearable
              v-model="formValidate.store_name"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item label="用户名称：">
            <el-input
              enter-button
              placeholder="请输入"
              clearable
              v-model="formValidate.account"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-row>
        <el-col v-bind="grid">
          <el-button v-auth="['product-reply-save_fictitious_reply']" type="primary" v-db-click @click="addRep"
            >添加自评</el-button
          >
          <el-button v-auth="['product-reply-save_fictitious_reply']" v-db-click @click="openBatchModal"
            >批量审核</el-button
          >
        </el-col>
      </el-row>
      <el-table
        ref="table"
        :data="tableList"
        class="ivu-mt mt14"
        v-loading="loading"
        @on-sort-change="sortMethod"
        @selection-change="handleSelectRow"
        empty-text="暂无数据"
      >
        <el-table-column type="selection" width="60"> </el-table-column>
        <el-table-column label="评论ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品信息" min-width="130">
          <template slot-scope="scope">
            <div class="imgPic acea-row row-middle">
              <div class="pictrue" v-viewer><img v-lazy="scope.row.image" /></div>
              <div class="info line2">{{ scope.row.store_name }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="规格" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.suk }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="评分" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.score }}</span>
          </template>
        </el-table-column>
        <el-table-column label="评价内容" min-width="130">
          <template slot-scope="scope">
            <div class="mb5 content_font">{{ scope.row.comment }}</div>
            <div v-viewer class="pictrue mr10" v-for="(item, index) in scope.row.pics || []" :key="index">
              <img v-lazy="item" :src="item" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="回复内容" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.merchant_reply_content }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="80">
          <template slot-scope="scope">
            <el-tag effect="dark" v-if="scope.row.status == 1"> 通过 </el-tag>
            <el-tag effect="dark" type="warning" v-if="scope.row.status == 0"> 待审核 </el-tag>
            <el-tag effect="dark" type="danger" v-if="scope.row.status == 2"> 已拒绝 </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="评价时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <template v-if="scope.row.status == 0">
              <a class="item" v-db-click @click="adopt(scope.row, '审核通过', 1)">通过</a>
              <el-divider direction="vertical"></el-divider>
              <a class="item" v-db-click @click="adopt(scope.row, '拒绝', 2)">驳回</a>
              <el-divider direction="vertical"></el-divider>
            </template>
            <a v-if="scope.row.status != 2" v-db-click @click="reply(scope.row)">回复</a>
            <el-divider v-if="scope.row.status != 2" direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除评论', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <el-dialog :visible.sync="modals" scrollable title="回复内容" width="720px">
      <el-form ref="contents" :model="contents" :rules="ruleInline" label-position="right" @submit.native.prevent>
        <el-form-item prop="content">
          <el-input v-model="contents.content" type="textarea" :rows="4" placeholder="请输入回复内容" />
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button @click="cancels">取消</el-button>
        <el-button type="primary" v-db-click @click="oks">确定</el-button>
      </div>
    </el-dialog>
    <addReply
      @close="close"
      :visible="replyModal"
      :goods="goodsData"
      :attr="attrData"
      :avatar="avatarData"
      :picture="pictureData"
      @callGoods="callGoods"
      @callAttr="callAttr"
      @callPicture="callPicture"
      @removePicture="removePicture"
    ></addReply>
    <el-dialog :visible.sync="goodsModal" title="选择商品" width="1000px">
      <goodsList v-if="replyModal" @getProductId="getProductId"></goodsList>
    </el-dialog>
    <el-dialog :visible.sync="attrModal" title="选择商品规格" width="1000px">
      <el-table ref="table" :row-key="getRowKey" :data="goodsData.attrs" height="500">
        <el-table-column label="" width="70">
          <template slot-scope="scope">
            <el-radio v-model="templateRadio" :label="scope.row.unique" @change.native="getTemplateRow(scope.row)"
              >&nbsp;</el-radio
            >
          </template>
        </el-table-column>
        <el-table-column label="图片" width="120">
          <template slot-scope="scope">
            <div class="product-data">
              <img class="image" :src="scope.row.pic" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="规格" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.suk }}</span>
          </template>
        </el-table-column>
        <el-table-column label="售价" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.ot_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠价" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
    <el-dialog :visible.sync="pictureModal" width="950px" title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        @getPicD="getPicD"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="pictureModal"
      ></uploadPictures>
    </el-dialog>
    <el-dialog
      :visible.sync="batchModal"
      class="batch-box"
      title="审核批量设置"
      :show-close="true"
      :close-on-click-modal="false"
      width="540px"
    >
      <el-form
        ref="batchFormData"
        :model="batchFormData"
        label-width="90px"
        label-position="right"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col :span="24">
            <el-form-item label="批量设置：" prop="status">
              <el-radio-group v-model="batchFormData.status">
                <el-radio :label="1">通过</el-radio>
                <el-radio :label="2">拒绝</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="batchModal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="batchSub">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { replyListApi, setReplyApi, replyBatchStatus } from '@/api/product';
import addReply from '../components/addReply.vue';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';

export default {
  name: 'product_productEvaluate',
  components: {
    addReply,
    goodsList,
    uploadPictures,
  },
  data() {
    return {
      templateRadio: 0,
      modals: false,
      replyModal: false,
      pictureModal: false,
      goodsModal: false,
      batchModal: false,
      attrModal: false, // 选择商品规格
      batchFormData: {
        status: 1,
      },
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 12,
        xs: 24,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      formValidate: {
        is_reply: '',
        data: '',
        store_name: '',
        key: '',
        order: '',
        account: '',
        status: '',
        product_id: this.$route.params.id === undefined ? 0 : this.$route.params.id,
        page: 1,
        limit: 15,
      },
      pickerOptions: this.$timeOptions,
      value: '45',
      tableList: [],
      goodsAddType: '',
      goodsData: {},
      attrData: {},
      avatarData: {},
      pictureData: [],
      selectProductAttrList: [],
      isChoice: '',
      picTit: '',
      tableIndex: 0,
      total: 0,
      loading: false,
      timeVal: [],
      contents: {
        content: '',
      },
      ruleInline: {
        content: [{ required: true, message: '请输入回复内容', trigger: 'blur' }],
      },
      rows: {},
      ids: [],
    };
  },
  computed: {},
  created() {
    if (this.$route.query.is_reply == 0) this.formValidate.is_reply = this.$route.query.is_reply;
    this.getList();
  },
  watch: {
    '$route.params.id'(to, from) {
      this.formValidate.product_id = 0;
      this.getList();
    },
    replyModal(value) {
      if (!value) {
        this.goodsData = {};
        this.attrData = {};
        this.avatarData = {};
        this.pictureData = [];
        this.getList();
      }
    },
  },
  methods: {
    // 通过/驳回
    adopt(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/reply/set_status/${row.id}/${num}`,
        method: 'put',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 添加虚拟评论；
    addRep() {
      // this.$modalForm(fictitiousReply(this.formValidate.product_id)).then(() => this.getList());
      this.replyModal = true;
    },
    getRowKey(row) {
      return row.unique;
    },
    getTemplateRow(row) {
      this.attrData = row;
      this.attrModal = false;
    },
    oks() {
      this.modals = true;
      this.$refs['contents'].validate((valid) => {
        if (valid) {
          setReplyApi(this.contents, this.rows.id)
            .then(async (res) => {
              this.$message.success(res.msg);
              this.modals = false;
              this.$refs['contents'].resetFields();
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    handleSelectRow(selection) {
      const ids = [];
      for (let i = 0; i < selection.length; i++) {
        const item = selection[i];
        if (!ids.includes(item.id)) {
          ids.push(item.id);
        }
      }
      this.ids = ids;
      console.log(this.ids);
    },
    openBatchModal() {
      if (!this.ids.length) return this.$message.warning('请先选择评论');
      this.batchModal = true;
    },
    batchSub() {
      let delfromData = {
        ids: this.ids,
        status: this.batchFormData.status,
      };
      replyBatchStatus(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.batchModal = false;
          this.ids = [];
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    cancels() {
      this.modals = false;
      this.$refs['contents'].resetFields();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/reply/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tableList.splice(num, 1);
          this.total = this.total - 1;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 回复
    reply(row) {
      this.modals = true;
      this.rows = row;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      this.formValidate.data = this.timeVal[0] ? (this.timeVal ? this.timeVal.join('-') : '') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    sortMethod(a) {
      if (a.order === 'normal') {
        this.formValidate.key = '';
        this.formValidate.order = '';
      } else {
        this.formValidate.key = a.key;
        this.formValidate.order = a.order;
      }
      this.getList();
    },
    close(e) {
      this.replyModal = e;
      this.attrData = {};
      this.templateRadio = '';
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.is_reply = this.formValidate.is_reply || '';
      this.formValidate.store_name = this.formValidate.store_name || '';
      replyListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    search() {},
    callGoods() {
      this.goodsModal = true;
    },
    callAttr() {
      this.attrModal = true;
    },
    getProductId(goods) {
      this.goodsData = goods;
      this.goodsModal = false;
      this.attrData.unique = '';
      this.templateRadio = '';
      this.attrData = {};
    },
    getPic(pc) {
      this.avatarData = pc;
      this.pictureModal = false;
    },
    getPicD(pc) {
      let pictureData = [...this.pictureData];
      pictureData = pictureData.concat(pc);
      pictureData.sort((a, b) => a.att_id - b.att_id);
      let picture = [];
      for (let i = 0; i < pictureData.length; i++) {
        if (pictureData[i + 1] && pictureData[i].att_id != pictureData[i + 1].att_id) {
          picture.push(pictureData[i]);
        }
        if (!pictureData[i + 1]) {
          picture.push(pictureData[i]);
        }
      }
      this.pictureData = picture;
      this.pictureModal = false;
    },
    callPicture(type) {
      this.isChoice = type;
      this.pictureModal = true;
    },
    removePicture(att_id) {
      let index = this.pictureData.findIndex((item) => item.att_id === att_id);
      this.pictureData.splice(index, 1);
    },
  },
};
</script>
<style lang="scss" scoped>
.content_font {
  color: #2b85e4;
}
.search {
  ::v-deep .ivu-form-item-content {
    margin-left: 0 !important;
  }
}
.ivu-mt .Button .bnt {
  margin-right: 6px;
}
.ivu-mt .ivu-table-row {
  font-size: 12px;
  color: rgba(0, 0, 0, 0.65);
}
.ivu-mt ::v-deep .ivu-table-cell {
  padding: 10px 0 !important;
}
.pictrue {
  width: 36px;
  height: 36px;
  display: inline-block;
  cursor: pointer;
}
.pictrue img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}
.ivu-mt .imgPic .info {
  flex: 1;
  margin-left: 10px;
}
.ivu-mt .picList .pictrue {
  height: 36px;
  margin: 7px 3px 0 3px;
}
.ivu-mt .picList .pictrue img {
  height: 100%;
  display: block;
}
.product-data {
  display: flex;
  align-items: center;
  .image {
    width: 50px !important;
    height: 50px !important;
    margin-right: 10px;
  }
}
</style>
