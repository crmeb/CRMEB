<template>
  <div>
    <div class="tabs">
      <el-tabs v-model="apiType">
        <el-tab-pane label="管理端接口" name="adminapi"></el-tab-pane>
        <el-tab-pane label="用户端接口" name="api"></el-tab-pane>
        <el-tab-pane label="客服端接口" name="kefuapi"></el-tab-pane>
        <el-tab-pane label="对外接口" name="outapi"></el-tab-pane>
      </el-tabs>
    </div>
    <div class="main" v-loading="winLoading">
      <div class="ivu-mt card-tree b-r-1">
        <div class="tree">
          <div class="main-btn">
            <el-button class="mb5" style="flex: 1" type="primary" v-db-click @click="clickMenu(4)" long
              >新增分类</el-button
            >
            <el-button class="mb5 mr10" type="success" v-db-click @click="syncRoute()">同步</el-button>
          </div>

          <vue-tree-list
            class="tree-list"
            ref="treeList"
            @change-name="onChangeName"
            @delete-node="onDel"
            :model="treeData"
            default-tree-node-name="默认文件夹"
            default-leaf-node-name="默认接口名"
            v-bind:default-expanded="false"
            :expand-only-one="true"
          >
            <template v-slot:leafNameDisplay="slotProps">
              <div></div>
              <div
                class="tree-node"
                :class="{
                  node: slotProps.model.method,
                  open: formValidate.path == slotProps.model.path && formValidate.method == slotProps.model.method,
                }"
                v-db-click
                @click.stop="onClick(slotProps.model)"
              >
                <span
                  class=""
                  :class="{
                    open: formValidate.path == slotProps.model.path && formValidate.method == slotProps.model.method,
                  }"
                  >{{ slotProps.model.name }}</span
                >
                <el-dropdown
                  size="small"
                  transfer
                  @command="
                    (name) => {
                      clickMenu(name, slotProps.model);
                    }
                  "
                >
                  <span class="el-dropdown-link">
                    <i class="el-icon-more"></i>
                  </span>
                  <template slot="dropdown">
                    <el-dropdown-menu>
                      <el-dropdown-item command="1" v-if="!slotProps.model.method">新增接口</el-dropdown-item>
                      <el-dropdown-item command="2" v-if="!slotProps.model.method">编辑分类名</el-dropdown-item>
                      <el-dropdown-item command="3">删除</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
            </template>
            <!-- 新建文件夹 -->

            <span class="icon" slot="addTreeNodeIcon"></span>
            <span class="icon" slot="addLeafNodeIcon"></span>
            <span class="icon" slot="editNodeIcon"></span>
            <span class="icon" slot="delNodeIcon"></span>
            <template v-slot:treeNodeIcon="slotProps">
              <span
                v-if="slotProps.model.method"
                class="req-method"
                :style="{
                  color: methodsColor(slotProps.model.method),
                  'font-weight': slotProps.model.pid == formValidate.pid ? '500' : '500',
                }"
                >{{ slotProps.model.method }}</span
              >

              <!-- <span v-if="slotProps.model.method"></span> -->
            </template>
          </vue-tree-list>
        </div>
      </div>
      <el-card :bordered="false" shadow="never" class="ivu-mt right-card">
        <div class="data">
          <div class="eidt-sub">
            <div class="name">
              {{ formValidate.name }}
            </div>
            <div>
              <el-button class="submission" v-db-click @click="debugging()">调试</el-button>
              <el-button
                v-if="formValidate.id"
                type="primary"
                class="submission"
                v-db-click
                @click="isEdit = !isEdit"
                >{{ isEdit ? '取消' : '编辑' }}</el-button
              >
              <el-button
                v-if="isEdit"
                type="primary"
                class="submission"
                v-db-click
                @click="handleSubmit('formValidate')"
                >保存</el-button
              >
            </div>
          </div>
          <el-form
            class="formValidate mt20"
            ref="formValidate"
            :rules="ruleValidate"
            :model="formValidate"
            label-width="120px"
            :label-position="labelPosition"
            @submit.native.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24">
                <div class="title">接口信息</div>
                <el-form-item label="接口名称：" prop="name">
                  <el-input
                    v-if="isEdit"
                    class="perW20"
                    type="text"
                    :rows="4"
                    v-model.trim="formValidate.name"
                    placeholder="请输入"
                  />
                  <span v-else>{{ formValidate.name || '' }}</span>
                </el-form-item>
                <el-form-item label="请求类型：" prop="name">
                  <el-select v-if="isEdit" v-model="formValidate.method" style="width: 120px">
                    <el-option
                      v-for="(item, index) in requestTypeList"
                      :key="index"
                      :value="item.value"
                      :label="item.label"
                    ></el-option>
                  </el-select>
                  <span v-else class="req-method" :style="'background-color:' + methodColor">{{
                    formValidate.method || ''
                  }}</span>
                </el-form-item>
                <el-form-item label="功能描述：" prop="name">
                  <el-input
                    v-if="isEdit"
                    class="perW20"
                    type="textarea"
                    :rows="4"
                    v-model.trim="formValidate.describe"
                    placeholder="请输入"
                  />
                  <span v-else class="text-area">{{ formValidate.describe || '--' }}</span>
                </el-form-item>
                <el-form-item label="所属分类：" prop="name" v-if="isEdit">
                  <el-cascader
                    v-model="formValidate.cate_id"
                    size="small"
                    :options="formValidate.cate_tree"
                    :props="{ checkStrictly: true, multiple: false, emitPath: false, value: 'id', label: 'name' }"
                    clearable
                  ></el-cascader>
                </el-form-item>
                <el-form-item label="是否公共：" prop="name">
                  <el-switch v-if="isEdit" v-model="formValidate.type" :active-value="1" :inactive-value="0">
                  </el-switch>
                  <span v-else class="text-area">{{ formValidate.type ? '是' : '否' }}</span>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="24">
              <el-col :span="24">
                <div class="title">调用方式</div>
                <el-form-item label="路由地址：" prop="path">
                  <span>{{ formValidate.path || '' }}</span>
                </el-form-item>
                <el-form-item label="文件地址：" prop="path">
                  <span>{{ formValidate.file_path || '' }}</span>
                </el-form-item>
                <el-form-item label="方法名：" prop="path">
                  <span>{{ formValidate.action || '' }}</span>
                </el-form-item>
                <el-form-item label="header参数：">
                  <vxe-table
                    resizable
                    show-overflow
                    keep-source
                    ref="headTable"
                    row-id="id"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.header"
                  >
                    <!-- <vxe-column type="checkbox" width="60"></vxe-column> -->
                    <vxe-column field="attribute" width="300" title="属性" tree-node :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.attribute" type="text"></vxe-input>
                        <span v-else>{{ row.attribute || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="type" title="类型" width="200" :edit-render="{}">
                      <template #default="{ row }">
                        <!-- <vxe-select v-if="isEdit" v-model="row.type" type="text" :optionGroups="typeList"></vxe-select> -->
                        <vxe-select v-if="isEdit" v-model="row.type" transfer>
                          <vxe-option
                            v-for="item in typeList"
                            :key="item.value"
                            :value="item.value"
                            :label="item.label"
                          ></vxe-option>
                        </vxe-select>
                        <span v-else>{{ row.type || '' }}</span>

                        <!-- <vxe-select v-model="row.type">
									    <vxe-option v-for="num in 12" :key="num" :value="num" :label="num"></vxe-option>
									  </vxe-select> -->
                      </template>
                    </vxe-column>
                    <vxe-column field="must" title="必填" width="100" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-checkbox
                          v-if="isEdit"
                          v-model="row.must"
                          :unchecked-value="'0'"
                          :checked-value="'1'"
                        ></vxe-checkbox>
                        <span v-else>{{ row.must == '1' ? '是' : '否' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="trip" title="说明" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.trip" type="text"></vxe-input>
                        <span v-else>{{ row.trip || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column title="操作" width="200" v-if="isEdit">
                      <template #default="{ row }">
                        <vxe-button
                          type="text"
                          v-if="row.type === 'array' || row.type === 'object'"
                          status="primary"
                          v-db-click
                          @click="insertRow(row, 'headTable')"
                          >插入</vxe-button
                        >
                        <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'headTable')"
                          >删除</vxe-button
                        >
                      </template>
                    </vxe-column>
                  </vxe-table>

                  <el-button class="mt10" v-if="isEdit" type="primary" v-db-click @click="insertEvent('headTable')"
                    >添加参数</el-button
                  >
                </el-form-item>
                <el-form-item label="query参数：">
                  <vxe-table
                    resizable
                    show-overflow
                    keep-source
                    ref="xTable"
                    row-id="id"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.query"
                  >
                    <vxe-column field="attribute" width="300" title="属性" tree-node :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.attribute" type="text"></vxe-input>
                        <span v-else>{{ row.attribute || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="type" title="类型" width="200" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-select v-if="isEdit" v-model="row.type" transfer>
                          <vxe-option
                            v-for="item in typeList"
                            :key="item.value"
                            :value="item.value"
                            :label="item.label"
                          ></vxe-option>
                        </vxe-select>
                        <span v-else>{{ row.type || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="must" title="必填" width="100" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-checkbox
                          v-if="isEdit"
                          v-model="row.must"
                          :unchecked-value="'0'"
                          :checked-value="'1'"
                        ></vxe-checkbox>
                        <span v-else>{{ row.must == '1' ? '是' : '否' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="trip" title="说明" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.trip" type="text"></vxe-input>
                        <span v-else>{{ row.trip || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column title="操作" width="200" v-if="isEdit">
                      <template #default="{ row }">
                        <vxe-button
                          type="text"
                          v-if="row.type === 'array' || row.type === 'object'"
                          status="primary"
                          v-db-click
                          @click="insertRow(row, 'xTable')"
                          >插入</vxe-button
                        >
                        <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'xTable')"
                          >删除</vxe-button
                        >
                      </template>
                    </vxe-column>
                  </vxe-table>
                  <el-button class="mt10" v-if="isEdit" type="primary" v-db-click @click="insertEvent('xTable')"
                    >添加参数</el-button
                  >
                </el-form-item>
                <el-form-item label="body参数：">
                  <vxe-table
                    resizable
                    show-overflow
                    keep-source
                    ref="bodyTable"
                    row-id="id"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.request"
                  >
                    <!-- <vxe-column type="checkbox" width="60"></vxe-column> -->
                    <vxe-column field="attribute" width="300" title="属性" tree-node :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.attribute" type="text"></vxe-input>
                        <span v-else>{{ row.attribute || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="type" title="类型" width="200" :edit-render="{}">
                      <template #default="{ row }">
                        <!-- <vxe-select v-if="isEdit" v-model="row.type" type="text" :optionGroups="typeList"></vxe-select> -->
                        <vxe-select v-if="isEdit" v-model="row.type" transfer>
                          <vxe-option
                            v-for="item in typeList"
                            :key="item.value"
                            :value="item.value"
                            :label="item.label"
                          ></vxe-option>
                        </vxe-select>
                        <span v-else>{{ row.type || '' }}</span>

                        <!-- <vxe-select v-model="row.type">
                      <vxe-option v-for="num in 12" :key="num" :value="num" :label="num"></vxe-option>
                    </vxe-select> -->
                      </template>
                    </vxe-column>
                    <vxe-column field="must" title="必填" width="100" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-checkbox
                          v-if="isEdit"
                          v-model="row.must"
                          :unchecked-value="'0'"
                          :checked-value="'1'"
                        ></vxe-checkbox>
                        <span v-else>{{ row.must == '1' ? '是' : '否' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="trip" title="说明" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.trip" type="text"></vxe-input>
                        <span v-else>{{ row.trip || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column title="操作" width="200" v-if="isEdit">
                      <template #default="{ row }">
                        <vxe-button
                          type="text"
                          v-if="row.type === 'array' || row.type === 'object'"
                          status="primary"
                          v-db-click
                          @click="insertRow(row, 'bodyTable')"
                          >插入</vxe-button
                        >
                        <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'bodyTable')"
                          >删除</vxe-button
                        >
                      </template>
                    </vxe-column>
                  </vxe-table>

                  <el-button class="mt10" v-if="isEdit" type="primary" v-db-click @click="insertEvent('bodyTable')"
                    >添加参数</el-button
                  >
                </el-form-item>
                <el-form-item label="返回参数：">
                  <vxe-table
                    resizable
                    show-overflow
                    keep-source
                    ref="resTable"
                    row-id="id"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.response"
                  >
                    <!-- <vxe-column type="checkbox" width="60"></vxe-column> -->
                    <vxe-column field="attribute" title="属性" width="300" tree-node :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.attribute" type="text"></vxe-input>
                        <span v-else>{{ row.attribute || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="type" title="类型" width="200" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-select v-if="isEdit" v-model="row.type" transfer>
                          <vxe-option
                            v-for="item in typeList"
                            :key="item.value"
                            :value="item.value"
                            :label="item.label"
                          ></vxe-option>
                        </vxe-select>
                        <span v-else>{{ row.type || '' }}</span>
                      </template>
                    </vxe-column>
                    <!-- <vxe-column field="type" title="必填" :edit-render="{}">
                  <template #default="{ row }">
                    <vxe-checkbox v-model="row.must" :unchecked-value="0" :checked-value="1"></vxe-checkbox
                    >{{ row.must }}
                  </template>
                </vxe-column> -->
                    <vxe-column field="trip" title="说明" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.trip" type="text"></vxe-input>
                        <span v-else>{{ row.trip || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column title="操作" width="200" v-if="isEdit">
                      <template #default="{ row }">
                        <vxe-button
                          type="text"
                          v-if="row.type === 'array' || row.type === 'object'"
                          status="primary"
                          v-db-click
                          @click="insertRow(row, 'resTable')"
                          >插入</vxe-button
                        >
                        <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'resTable')"
                          >删除</vxe-button
                        >
                      </template>
                    </vxe-column>
                  </vxe-table>
                  <el-button class="mt10" v-if="isEdit" type="primary" v-db-click @click="insertEvent('resTable')"
                    >添加参数</el-button
                  >
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="24">
              <el-col :span="24">
                <div class="title">调用示例</div>
                <!-- <el-form-item label="请求数据示例：" prop="request_example">
                    <el-input
                      v-if="isEdit"
                      class="perW20"
                      type="textarea"
                      :rows="4"
                      v-model.trim="formValidate.request_example"
                      placeholder="请输入"
                    />
                    <span v-else class="text-area">{{ formValidate.request_example || '' }}</span>
                  </el-form-item> -->
                <el-form-item v-if="formValidate.response_example" label="返回数据示例：" prop="response_example">
                  <el-collapse v-for="(item, index) in formValidate.response_example" accordion :key="index">
                    <el-collapse-item>
                      <template slot="title">
                        {{ item.name || '' }}
                      </template>
                      <el-input
                        v-if="isEdit"
                        class="perW20"
                        type="textarea"
                        :rows="4"
                        v-model.trim="item.data"
                        placeholder="请输入"
                      />
                      <span v-else class="text-area">{{ item.data || '' }}</span>
                    </el-collapse-item>
                  </el-collapse>
                </el-form-item>
                <el-form-item label="错误码：">
                  <vxe-table
                    resizable
                    show-overflow
                    keep-source
                    ref="codeTable"
                    row-id="id"
                    is-tree-view
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.error_code"
                  >
                    <!-- <vxe-column type="checkbox" width="60"></vxe-column> -->
                    <vxe-column field="code" title="错误码" tree-node :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.code" type="text"></vxe-input>
                        <span v-else>{{ row.code || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="value" title="错误码取值" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.value" type="text"></vxe-input>
                        <span v-else>{{ row.value || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column field="solution" title="解决方案" :edit-render="{}">
                      <template #default="{ row }">
                        <vxe-input v-if="isEdit" v-model="row.solution" type="text"></vxe-input>
                        <span v-else>{{ row.solution || '' }}</span>
                      </template>
                    </vxe-column>
                    <vxe-column title="操作" v-if="isEdit">
                      <template #default="{ row }">
                        <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'codeTable')"
                          >删除</vxe-button
                        >
                      </template>
                    </vxe-column>
                  </vxe-table>
                  <el-button class="mt10" v-if="isEdit" type="primary" v-db-click @click="insertEvent('codeTable')"
                    >添加参数</el-button
                  >
                </el-form-item>
              </el-col>
            </el-row>
            <!-- <el-row :gutter="24" >
              <el-col :span="24">
                <el-form-item>
                  <el-button type="primary" class="submission" v-db-click @click="handleSubmit('formValidate')">保存</el-button>
                </el-form-item>
              </el-col>
            </el-row> -->
          </el-form>
        </div>
        <!-- <div v-else class="nothing">
          <div class="box" v-db-click @click="clickMenu(4)">
            <div class="icon">
              <Icon type="ios-folder" />
            </div>
            <div class="text">新建文件</div>
          </div>
          <div class="box" v-db-click @click="clickMenu(1)">
            <div class="icon">
              <Icon type="logo-linkedin" />
            </div>
            <div class="text">新建接口</div>
          </div>
        </div> -->
      </el-card>
    </div>
    <el-dialog :visible.sync="nameModal" width="470px" title="分组名称">
      <label>分组名称：</label>
      <el-input v-model="value" placeholder="请输入分组名称" style="width: 85%" />
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="nameModal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="asyncOK">确 定</el-button>
      </span>
    </el-dialog>
    <el-drawer
      :visible.sync="debuggingModal"
      :title="formValidate.name"
      size="70%"
      :wrapperClosable="false"
      :loading="loading"
    >
      <debugging
        v-if="debuggingModal"
        :formValidate="formValidate"
        :typeList="intTypeList"
        :requestTypeList="requestTypeList"
        :apiType="apiType"
      />
    </el-drawer>
  </div>
</template>

<script>
import {
  routeCate,
  syncRoute,
  routeList,
  routeDet,
  routeSave,
  interfaceEditName,
  routeDel,
  routeEdit,
  routeCateDel,
} from '@/api/systemBackendRouting';
import { VueTreeList, Tree, TreeNode } from 'vue-tree-list';
import debugging from './debugging.vue';

import { mapState } from 'vuex';
export default {
  name: 'systemOutInterface',
  components: {
    VueTreeList,
    debugging,
  },
  data() {
    return {
      value: '',
      isEdit: false,
      nameModal: false,
      debuggingModal: false,
      formValidate: {},
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      ruleValidate: {
        title: [{ message: '请输入正确的描述 (不能多于200位数)', trigger: 'blur', max: 200 }],
      },
      loading: false,
      intTypeList: [
        {
          value: 'string',
          label: 'String',
        },
        // {
        //   value: 'array',
        //   label: 'Array',
        // },
        // {
        //   value: 'object',
        //   label: 'Object',
        // },
        {
          value: 'number',
          label: 'Number',
        },
        {
          value: 'boolean',
          label: 'Boolean',
        },
        {
          value: 'null',
          label: 'Null',
        },
        {
          value: 'any',
          label: 'Any',
        },
      ],
      typeList: [
        {
          value: 'string',
          label: 'String',
        },
        {
          value: 'array',
          label: 'Array',
        },
        {
          value: 'object',
          label: 'Object',
        },
        {
          value: 'number',
          label: 'Number',
        },
        {
          value: 'boolean',
          label: 'Boolean',
        },
        {
          value: 'null',
          label: 'Null',
        },
        {
          value: 'any',
          label: 'Any',
        },
      ],
      requestTypeList: [
        {
          value: 'GET',
          label: 'GET',
        },
        {
          value: 'POST',
          label: 'POST',
        },
        {
          value: 'DELETE',
          label: 'DELETE',
        },
        {
          value: 'PUT',
          label: 'PUT',
        },
      ],
      contextData: null, //左侧导航右键点击是产生的数据对象
      treeData: undefined,
      buttonProps: {
        type: 'default',
        size: 'small',
      },
      methodColor: '#fff',
      apiType: 'adminapi',
      paramsId: 0,
      winLoading: false,
    };
  },
  watch: {
    ['formValidate.method']: {
      deep: true,
      handler(newVal, oldVal) {
        if (newVal) {
          let method = newVal.toUpperCase();
          if (method == 'GET') {
            this.methodColor = '#61affe';
          } else if (method == 'POST') {
            this.methodColor = '#49cc90';
          } else if (method == 'PUT') {
            this.methodColor = '#fca130';
          } else if (method == 'DEL' || method == 'DELETE') {
            this.methodColor = '#f93e3e';
          }
        }
      },
    },
    apiType(newVal) {
      if (newVal) {
        this.winLoading = true;
        this.getInterfaceList('one');
      }
    },
    isEdit(newVal) {
      if (newVal) {
        this.formValidate.response_example.map((e) => {
          e.data = JSON.stringify(e.data);
        });
      } else {
        this.formValidate.response_example.map((e) => {
          e.data = JSON.parse(e.data);
        });
      }
    },
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '50px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getInterfaceList('one');
  },
  methods: {
    syncRoute() {
      this.$msgbox({
        title: '立即同步',
        message: '同步之后，路由文件中新增的接口添加到接口列表中，路由文件中删除的路由会同步的在接口列表中删除',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          syncRoute(this.apiType).then((res) => {
            this.getInterfaceList('one');
            this.$message.success(res.msg);
          });
        })
        .catch(() => {});
    },
    debugging() {
      this.debuggingModal = true;
    },
    onClicksss(e) {},
    methodsColor(newVal) {
      let method = newVal.toUpperCase();
      if (method == 'GET') {
        return '#61affe';
      } else if (method == 'POST') {
        return '#49cc90';
      } else if (method == 'PUT') {
        return '#fca130';
      } else if (method == 'DEL' || method == 'DELETE') {
        return '#f93e3e';
      }
    },
    insertBefore(params) {},
    insertAfter(params) {},
    moveInto(params) {},
    async addTableData() {
      const { row: data } = await $table.insertAt(newRow, -1);
      await $table.setActiveCell(data, 'name');
    },
    getInterfaceList(disk_type) {
      try {
        routeList(this.apiType)
          .then((res) => {
            if (res.data.length) {
              res.data[0].expand = false;
              this.treeData = new Tree(res.data);
              let i;
              this.$nextTick((e) => {
                if (disk_type) {
                  if (
                    res.data[0].children &&
                    res.data[0].children[0].children &&
                    res.data[0].children[0].children.length
                  ) {
                    document.querySelectorAll('.vtl-icon-caret-right')[0].click();
                    document.querySelectorAll('.vtl-icon-caret-right')[1].click();
                    i = res.data[0].children[0].children[0];
                  } else {
                    document.querySelectorAll('.vtl-icon-caret-right')[0].click();
                    i = res.data[0].children[0];
                  }
                  this.onClick(i);
                }
              });
            } else {
              // this.$refs.treeList.clear();
              this.treeData = new Tree({});
              this.formValidate = {};
            }
            this.winLoading = false;
          })
          .catch((err) => {
            this.winLoading = false;
            this.$message.error(err.msg);
          });
      } catch (error) {
        console.log(error);
      }
    },
    onClick(params) {
      try {
        if (params.method) {
          this.isEdit = false;
          this.paramsId = params.id;
          this.getRoteData(params.id);
        }
      } catch (error) {}
    },
    getRoteData(id) {
      routeDet(id)
        .then((res) => {
          this.formValidate = res.data;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    async handleSubmit() {
      if (!this.formValidate.name) {
        return this.$message.warning('请输入接口名称');
      } else if (!this.formValidate.method) {
        return this.$message.warning('请选择请求类型');
      } else if (!this.formValidate.path) {
        return this.$message.warning('请输入路由地址');
      }
      this.formValidate.request = await this.$refs.bodyTable.getTableData().tableData;
      this.formValidate.response = await this.$refs.resTable.getTableData().tableData;
      this.formValidate.error_code = await this.$refs.codeTable.getTableData().tableData;
      this.formValidate.header = await this.$refs.headTable.getTableData().tableData;
      this.formValidate.query = await this.$refs.xTable.getTableData().tableData;
      this.formValidate.apiType = this.apiType;
      this.formValidate.response_example.map((e) => {
        e.data = JSON.parse(e.data);
      });
      await routeSave(this.formValidate)
        .then((res) => {
          this.$message.success(res.msg);
          this.getRoteData(this.paramsId);
          this.isEdit = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    async insertEvent(type) {
      const $table = this.$refs[type];
      let newRow;
      if (type == 'xTable') {
        newRow = {
          attribute: '',
          type: '',
          must: 0,
          trip: '',
        };
      } else if (type == 'resTable') {
        newRow = {
          attribute: '',
          type: '',
          trip: '',
        };
      } else {
        newRow = {
          code: '',
          value: '',
          solution: '',
        };
      }
      // $table.insert(newRow).then(({ row }) => $table.setEditRow(row, -1));
      const { row: data } = await $table.insertAt(newRow, -1);
      await $table.setActiveCell(data, 'name');
    },
    async insertRow(currRow, type) {
      const $table = this.$refs[type];
      // 如果 null 则插入到目标节点顶部
      // 如果 -1 则插入到目标节点底部
      // 如果 row 则有插入到效的目标节点该行的位置
      let record;
      if (type == 'xTable') {
        record = {
          attribute: '',
          type: '',
          must: 0,
          trip: '',
          id: Date.now(),
          parentId: currRow.id, // 需要指定父节点，自动插入该节点中
        };
      } else if (type == 'resTable') {
        record = {
          attribute: '',
          type: '',
          trip: '',
          id: Date.now(),
          parentId: currRow.id, // 需要指定父节点，自动插入该节点中
        };
      } else {
        record = {
          code: '',
          value: '',
          solution: '',
          id: Date.now(),
          parentId: currRow.id, // 需要指定父节点，自动插入该节点中
        };
      }
      const { row: newRow } = await $table.insertAt(record, -1);
      await $table.setTreeExpand(currRow, true); // 将父节点展开
      await $table.setActiveRow(newRow); // 插入子节点
    },
    async removeRow(row, type) {
      const $table = this.$refs[type];
      await $table.remove(row);
    },
    // 修改名字
    add() {
      this.value = '';
      this.formValidate.id = 0;
      this.nameModal = true;
    },
    // 点击菜单
    clickMenu(name, params) {
      if (name == 1) {
        this.formValidate = {};
        this.formValidate.cate_id = params ? params.id : 0;
        this.formValidate.id = 0;
        this.isEdit = true;
      } else if (name == 2) {
        // this.value = params.name || '';
        // this.formValidate.cate_id = params ? params.id : 0;
        // this.nameModal = true;
        // this.onEdit(params);
        this.$modalForm(routeEdit(params.id, this.apiType)).then(() => this.getInterfaceList());
      } else if (name == 3) {
        this.onDel(params);
      } else if (name == 4) {
        // this.add();
        this.$modalForm(routeCate(this.apiType)).then(() => this.getInterfaceList());
      }
    },

    addFac(params) {
      this.formValidate = {
        id: params ? params.id : 0,
      };
      this.isEdit = true;
    },
    asyncOK() {
      let data = {
        id: this.formValidate.id || 0,
        type: 0,
        name: this.value,
      };
      routeSave(data)
        .then((res) => {
          this.$message.success(res.msg);
          this.getInterfaceList();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    //侧边栏右键点击事件
    handleContextMenu(data, event, position) {
      position.left = Number(position.left.slice(0, -2)) + 75 + 'px';
      this.contextData = data;
    },
    handleContextCreateFolder() {},
    handleContextCreateFile() {},
    // 自定义显示
    renderContent(h, { root, node, data }) {
      let that = this;
      return h(
        'span',
        {
          style: {
            display: 'inline-block',
            width: '100%',
          },
        },
        [
          h('span', [
            h(resolveComponent('Icon'), {
              type: 'ios-paper-outline',
              style: {
                marginRight: '8px',
              },
            }),
            h('span', data.title),
          ]),
          h(
            'span',
            {
              style: {
                display: 'inline-block',
                float: 'right',
                marginRight: '32px',
              },
            },
            [
              h(resolveComponent('Button'), {
                ...this.buttonProps,
                icon: 'ios-add',
                style: {
                  marginRight: '8px',
                },
                onClick: () => {
                  this.append(data);
                },
              }),
              h(resolveComponent('Button'), {
                ...this.buttonProps,
                icon: 'ios-remove',
                onClick: () => {
                  this.remove(root, node, data);
                },
              }),
            ],
          ),
        ],
      );
    },
    /**
     * 侧边栏点击事件
     * @param {Object} data
     */
    clickDir(data, root, node) {
      let that = this;
      that.navItem = data;
      that.pathname = data.pathname;
    },
    append(data) {
      const children = data.children || [];
      children.push({
        title: 'appended node',
        expand: true,
      });
      this.$set(data, 'children', children);
    },
    remove(root, node, data) {
      const parentKey = root.find((el) => el === node).parent;
      const parent = root.find((el) => el.nodeKey === parentKey).node;
      const index = parent.children.indexOf(data);
      parent.children.splice(index, 1);
    },
    onMouseOver(root, node, data, e, d) {
      console.log(root, node, data);
    },
    //
    onDel(node) {
      let method = node.cate_id ? routeDel : routeCateDel;
      this.$msgbox({
        title: '提示',
        message: '删除后无法恢复，请确认后删除！',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          method(node.id)
            .then((res) => {
              this.$message.success(res.msg);
              node.remove();
            })
            .catch((err) => {
              this.$message.error(err.msg);
            });
        })
        .catch(() => {});
    },

    onChangeName(params) {
      if (params.eventType == 'blur') {
        let data = {
          name: params.newName,
          id: params.id,
        };
        interfaceEditName(data)
          .then((res) => {
            this.$message.success(res.msg);
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      }
    },

    onAddNode(params) {
      // this.$router.push({
      //   path: '/admin/setting/system_out_interface/add',
      //   query: {
      //     pid: params.pid,
      //   },
      // });
    },

    addNode() {
      var node = new TreeNode({ name: 'new node', isLeaf: false });
      if (!this.data.children) this.data.children = [];
      this.data.addChildren(node);
    },

    getNewTree() {
      var vm = this;
      function _dfs(oldNode) {
        var newNode = {};

        for (var k in oldNode) {
          if (k !== 'children' && k !== 'parent') {
            newNode[k] = oldNode[k];
          }
        }

        if (oldNode.children && oldNode.children.length > 0) {
          newNode.children = [];
          for (var i = 0, len = oldNode.children.length; i < len; i++) {
            newNode.children.push(_dfs(oldNode.children[i]));
          }
        }
        return newNode;
      }

      vm.newTree = _dfs(vm.data);
    },
  },
};
</script>

<style lang="scss" scoped>
.reset {
  margin-left: 10px;
}
.b-r-1 {
  border-right: 1px solid #f2f2f2;
}
.card-tree {
  background: #fff;
  height: 72px;
  box-sizing: border-box;
  overflow-x: scroll; /* 设置溢出滚动 */
  white-space: nowrap;
  overflow-y: hidden;
  /* 隐藏滚动条 */
  border-radius: 4px;
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
}
.card-tree::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.tabs {
  background: #fff;
  padding-left: 20px;
  border-radius: 5px 5px 0 0;
}
.main {
  width: 100%;
  display: flex;
  padding-bottom: 16px;
  background: #fff;
  border-radius: 6px;
  .main-btn {
    display: flex;
    position: sticky;
    padding: 0px 5px 0 15px;
    width: 100%;
    background: #fff;
    top: 0px;
    background-color: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(4px);
    z-index: 99;
  }
  .card-tree {
    width: 290px;
    height: calc(100vh - 205px);
    overflow-y: scroll;
  }
  ::v-deep .tree {
    .tree-list {
      margin-left: 10px;
      padding: 0 15px;
      margin-top: 10px;
    }
    .vtl-caret {
      padding-right: 2px;
    }
    .req-method {
      display: block;
      padding: 0px 2px;
      font-size: 13px;
      line-height: 13px;
      margin-right: 5px;
      border-radius: 4px;

      text-transform: uppercase;
    }
    .tree-node {
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;

      padding: 3px 7px 3px 0;
    }
    .node {
    }
    .open {
      font-weight: 500;
      color: #333;
    }
  }
  ::v-deep .vtl-node-main .vtl-operation {
    position: absolute;
    right: 20px;
  }
  ::v-deep .vtl-node-content {
    width: 100%;
  }
  .pop-menu {
    display: flex;
    justify-content: space-between;
  }
  ::v-deep .vtl-node-content .add {
    display: none;
    margin-right: 10px;
  }
  ::v-deep .vtl-node-content:hover .add {
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    width: 18px;
    height: 18px;
  }
  ::v-deep .vtl-node-content:hover .add:hover {
    background-color: #fff;
    .pop-menu {
      font-size: 16px;
    }
  }
  ::v-deep .vtl-node-main {
    padding: 3px 0;
  }
  ::v-deep .line1 {
    display: table-caption;
    white-space: nowrap;
    width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  ::v-deep .ivu-form-item {
    margin-bottom: 10px;
  }
  .right-card {
    flex: 1;
    ::v-deep .el-card__body {
      max-height: calc(100vh - 205px);
      overflow-y: scroll;
      padding-bottom: 16px;
    }
    ::v-deep .el-form-item--small.el-form-item {
      margin-bottom: 6px;
    }
  }
  .data {
    flex: 1;
    .req-method {
      text-transform: uppercase;
      border-radius: 4px;
      color: #fff;
      padding: 3px 7px;
    }
    .eidt-sub {
      display: flex;
      justify-content: space-between;
      .name {
        font-size: 20px;
        font-weight: 500;
      }
    }
    .title {
      font-size: 16px;
      font-weight: 500;
      margin-bottom: 15px;
    }
    .perW20 {
      width: 500px;
    }
    .text-area {
      white-space: pre-wrap;
      word-break: break-word;
    }
  }
  ::v-deep .ivu-tree-title {
    width: 100% !important;
  }
  ::v-deep .vtl-tree-margin {
    margin-left: 15px;
  }
  ::v-deep .ivu-btn-icon-only.ivu-btn-small {
    width: 28px;
  }
  ::v-deep .tree-node > span {
    font-size: 14px;
  }
  ::v-deep .tree-node.node > span {
    font-size: 13px;
  }
  .nothing {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 800px;
    .box:hover {
      border: 1px solid pink;
    }
    .box {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      width: 150px;
      height: 200px;
      margin: 0 20px;
      border-radius: 10px;
      cursor: pointer;
      overflow: hidden;
      border: 1px solid #fff;
      .icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 150px;
        font-size: 40px;
        color: #2d8cf0;
        background: #f1f1f1;
      }
      .text {
        width: 100%;
        height: 50px;
        background: #ddd;
        text-align: center;
        line-height: 50px;
        font-size: 14px;
        font-weight: 500;
      }
    }
  }
}
</style>
