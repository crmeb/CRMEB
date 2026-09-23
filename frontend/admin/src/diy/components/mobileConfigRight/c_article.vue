<template>
  <div class="article-box" v-if="defaults[configNme]">
    <div class="title-bar">文章列表</div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="defaults[configNme].list"
        group="peoples"
        handle=".move-icon"
        :item-key="articleItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item">
            <div class="move-icon">
              <span class="iconfont iconxingzhuangjiehe"></span>
            </div>
            <div class="img-box">
              <img :src="item.image_input[0]" alt="" v-if="item.image_input && item.image_input.length" />
              <div class="empty-img" v-else>
                <i class="el-icon-picture"></i>
              </div>
            </div>
            <div class="info">
              <div class="name line1">{{ item.title }}</div>
            </div>
            <span class="iconfont iconshanchu3" @click.stop="bindDelete(index)"></span>
          </div>
        </template>
      </draggable>
      <div class="add-btn" @click="modals = true">
        <el-button class="btn"><span class="iconfont iconaddto"></span>添加</el-button>
      </div>
    </div>

    <el-dialog
      v-model="modals"
      title="文章列表"
      class="paymentFooter"
      width="900px"
      :destroy-on-close="true"
      :close-on-click-modal="false"
    >
      <div class="article-manager">
        <div class="padding-add">
          <el-form :model="artFrom" label-width="80px" label-position="right" @submit.prevent inline>
            <el-form-item label="文章分类：" label-for="pid">
              <el-cascader
                v-model="artFrom.pid"
                placeholder="请选择"
                class="treeSel"
                @change="handleCheckChange"
                :options="treeData"
                :props="cascaderProps"
                style="width: 250px"
                clearable
              >
              </el-cascader>
            </el-form-item>
            <el-form-item label="文章搜索：" label-for="title">
              <el-input clearable placeholder="请输入" v-model="artFrom.title" class="form_content_width" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="userSearchs">查询</el-button>
            </el-form-item>
          </el-form>
        </div>
        <el-table
          :data="cmsList"
          ref="table"
          class="mt14"
          v-loading="loading"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
          height="400"
          @selection-change="handleSelectionChange"
        >
          <el-table-column type="selection" width="55"> </el-table-column>
          <el-table-column label="ID" width="80" prop="id"> </el-table-column>
          <el-table-column label="文章图片" min-width="90">
            <template #default="scope">
              <div class="tabBox_img" v-if="scope.row.image_input && scope.row.image_input.length">
                <img :src="scope.row.image_input[0]" />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="文章名称" min-width="130" prop="title"> </el-table-column>
          <el-table-column label="分类" min-width="130" prop="catename"> </el-table-column>
          <el-table-column label="时间" min-width="130">
            <template #default="scope">
              <span>{{ formatDate(scope.row.add_time) }}</span>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page" style="margin-top: 20px">
          <el-pagination
            v-if="total"
            :total="total"
            v-model:current-page="artFrom.page"
            v-model:page-size="artFrom.limit"
            layout="prev, pager, next"
            @current-change="pageChange"
          />
        </div>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button @click="modals = false">取 消</el-button>
          <el-button type="primary" @click="addSelectedArticles">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import draggable from 'vuedraggable';
import { cmsListApi, categoryListApi } from '@/api/cms';
import { formatDate as formatDateUtil } from '@/utils/validate';

defineOptions({ name: 'c_article' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const modals = ref(false);
const defaults = ref({});
const loading = ref(false);
const artFrom = ref({
  pid: 0,
  title: '',
  page: 1,
  limit: 10,
});
const total = ref(0);
const cmsList = ref([]);
const treeData = ref([]);
// el-cascader 的 props 配置（原 data 里名为 props，与组件 props 重名，故改名）
const cascaderProps = {
  value: 'id',
  label: 'title',
  emitPath: false,
  checkStrictly: true,
};
const multipleSelection = ref([]);

// 原 filters.formatDate，转为本地函数（模板直接调用）
function formatDate(time) {
  if (time !== 0) {
    let date = new Date(time * 1000);
    return formatDateUtil(date, 'yyyy-MM-dd hh:mm');
  }
}

// vuedraggable4 item-key（数据无唯一 id，用索引）
function articleItemKey(element) {
  return defaults.value[props.configNme].list.indexOf(element);
}

watch(
  () => props.configObj,
  (nVal, oVal) => {
    defaults.value = nVal;
  },
  { immediate: true, deep: true },
);

// created
defaults.value = props.configObj;
getClass();

watch(modals, (val) => {
  if (val) {
    getList();
  }
});

function handleSelectionChange(val) {
  multipleSelection.value = val;
}
function addSelectedArticles() {
  if (multipleSelection.value.length === 0) {
    return ElMessage.warning('请至少选择一篇文章');
  }
  let list = defaults.value[props.configNme].list;
  let newItems = [];
  multipleSelection.value.forEach((item) => {
    let exists = list.some((i) => i.id === item.id);
    if (!exists) {
      newItems.push(item);
    }
  });
  if (newItems.length === 0) {
    return ElMessage.warning('您选择的文章已存在');
  }
  defaults.value[props.configNme].list = list.concat(newItems);
  ElMessage.success('添加成功');
  modals.value = false;
}
// 获取文章列表
function getList() {
  loading.value = true;
  cmsListApi(artFrom.value)
    .then((res) => {
      cmsList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 获取分类
function getClass() {
  categoryListApi({ status: 1, type: 1 })
    .then((res) => {
      treeData.value = formatCategory(res.data);
      treeData.value.unshift({ id: 0, title: '全部' });
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function formatCategory(list) {
  return list.map((item) => {
    return {
      id: item.id,
      title: item.title,
      children: item.children ? formatCategory(item.children) : null,
    };
  });
}
// 选择分类
function handleCheckChange(data) {
  artFrom.value.pid = data ? data : 0;
  artFrom.value.page = 1;
  getList();
}
// 搜索
function userSearchs() {
  artFrom.value.page = 1;
  getList();
}
// 分页
function pageChange(e) {
  artFrom.value.page = e;
  getList();
}
// 选择文章
function selectArticle(row) {
  // 检查是否已存在
  let list = defaults.value[props.configNme].list;
  let exists = list.some((item) => item.id === row.id);
  if (exists) {
    ElMessage.warning('该文章已添加');
    return;
  }
  defaults.value[props.configNme].list.push(row);
  ElMessage.success('添加成功');
}
// 删除文章
function bindDelete(index) {
  defaults.value[props.configNme].list.splice(index, 1);
}
</script>

<style scoped lang="scss">
:deep(.el-checkbox) {
  margin-bottom: 0 !important;
}
.article-box {
  display: flex;
  padding: 0 15px 20px 15px;
  .title-bar {
    color: #fff;
    font-size: 12px;
    margin-right: 30px;
  }
  .list-box {
    flex: 1;
    .item {
      position: relative;
      display: flex;
      align-items: center;
      width: 100%;
      height: 40px;
      margin-bottom: 4px;
      background: #fff;
      border-radius: 4px;
      padding: 0 10px;
      box-sizing: border-box;
      &.sortable-chosen,
      &.sortable-drag,
      &.sortable-ghost {
        background: #f7f7f7 !important;
      }
      .move-icon {
        cursor: move;
        margin-right: 10px;
        .iconfont {
          color: #ddd;
          font-size: 16px;
        }
      }
      .img-box {
        width: 30px;
        height: 30px;
        margin-right: 10px;
        position: relative;
        img {
          width: 100%;
          height: 100%;
          border-radius: 4px;
        }
        .empty-img {
          width: 100%;
          height: 100%;
          background: #f5f5f5;
          border-radius: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #ccc;
          font-size: 14px;
        }
      }
      .info {
        flex: 1;
        overflow: hidden;
        max-width: 170px;
        .name {
          font-size: 12px;
          color: #333;
        }
      }
      .iconshanchu3 {
        display: none;
        color: #999999;
        font-size: 16px;
        cursor: pointer;
        margin-left: 10px;
      }
      &:hover {
        .iconshanchu3 {
          display: block;
        }
      }
    }
  }
  .add-btn {
    margin-left: 15px;
    .iconaddto {
      font-size: 12px;
      color: #999;
      margin-right: 5px;
    }
    .btn {
      width: 100%;
    }
  }
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
</style>
