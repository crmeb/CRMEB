<template>
  <div class="notice-channel">
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="管理站内信、短信、公众号等通知渠道的启停与适用端，可发送测试消息验证配置。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px 20px' }">
      <el-table
        :data="channelList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="渠道名称" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.channel_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="渠道标识" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.channel_key }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :disabled="scope.row.channel_key === 'is_sms' && scope.row.status === 1"
              @change="changeStatus(scope.row)"
              size="large"
            />
          </template>
        </el-table-column>
        <el-table-column label="支持范围" min-width="150">
          <template #default="scope">
            <el-tag v-if="scope.row.support_user == 1" type="success">用户端</el-tag>
            <el-tag v-else type="info">用户端</el-tag>
            <el-tag v-if="scope.row.support_platform == 1" type="success">平台端</el-tag>
            <el-tag v-else type="info">平台端</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="描述" min-width="180">
          <template #default="scope">
            <span>{{ scope.row.description || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template #default="scope">
            <a class="setting btn" v-db-click @click="edit(scope.row)">配置</a>
            <el-divider direction="vertical"></el-divider>
            <a class="setting btn" v-db-click @click="test(scope.row)">测试</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog title="渠道配置" v-model="dialogVisible" width="600px" @close="dialogVisible = false">
      <el-form :model="formData" label-width="100px">
        <el-form-item label="渠道名称">
          <el-input v-model="formData.channel_name" readonly />
        </el-form-item>
        <el-form-item label="渠道标识">
          <el-input v-model="formData.channel_key" readonly />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            v-model="formData.status"
            :disabled="formData.channel_key === 'is_sms' && formData.status === 1"
          />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="formData.sort" :min="0" />
        </el-form-item>
        <el-form-item label="支持用户端">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            v-model="formData.support_user"
            :disabled="formData.channel_key === 'is_sms' && formData.support_user === 1"
          />
        </el-form-item>
        <el-form-item label="支持平台端">
          <el-switch :active-value="1" :inactive-value="0" v-model="formData.support_platform" />
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="formData.description" type="textarea" :rows="3" />
        </el-form-item>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="save">保存</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  noticeChannelList,
  noticeChannelDetail,
  noticeChannelUpdate,
  noticeChannelSetStatus,
  noticeChannelTest,
} from '@/api/notification.js';

const channelList = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const formData = ref({
  id: 0,
  channel_key: '',
  channel_name: '',
  status: 1,
  sort: 0,
  support_user: 1,
  support_platform: 1,
  description: '',
});

function getList() {
  loading.value = true;
  noticeChannelList()
    .then((res) => {
      channelList.value = res.data;
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}
function changeStatus(row) {
  noticeChannelSetStatus(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      row.status = row.status === 1 ? 0 : 1;
    });
}
function edit(row) {
  noticeChannelDetail(row.id).then((res) => {
    formData.value = res.data;
    dialogVisible.value = true;
  });
}
function save() {
  noticeChannelUpdate(formData.value.id, formData.value)
    .then((res) => {
      ElMessage.success(res.msg);
      dialogVisible.value = false;
      getList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function test(row) {
  const dialog =
    row.support_user == 1
      ? ElMessageBox.prompt('输入接收测试消息的用户UID', '测试渠道', {
          inputPattern: /^[1-9]\d*$/,
          inputErrorMessage: '请输入有效的用户UID',
        })
      : ElMessageBox.confirm('确定要向平台收件人发送测试消息吗？', '测试渠道', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        });
  dialog.then((answer) => {
    noticeChannelTest(row.id, { uid: row.support_user == 1 ? Number(answer.value) : 0 })
      .then((res) => {
        ElMessage.success(res.msg);
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  });
}

getList();
</script>

<style lang="scss" scoped>
.btn {
  padding: 6px 12px;
  cursor: pointer;
  font-size: 12px;
  border-radius: 3px;
}
</style>
