<template>
  <div>
    <div class="tabs">
      <el-tabs v-model="apiType">
        <el-tab-pane label="管理端接口" name="adminapi"></el-tab-pane>
        <el-tab-pane label="用户端接口" name="api"></el-tab-pane>
      </el-tabs>
    </div>
    <div class="main" v-loading="winLoading">
      <div class="ivu-mt card-tree b-r-1">
        <div class="tree">
          <div class="main-btn">
            <!-- <el-button class="mb5" style="flex: 1" type="primary" v-db-click @click="clickMenu(4)" long
              >新增分类</el-button
            > -->
            <el-button class="mb5 mr10" style="flex: 1" type="success" v-db-click @click="syncRouteConfirm()"
              >同步</el-button
            >
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
                  <template #dropdown>
                    <el-dropdown-menu>
                      <!-- <el-dropdown-item command="1" v-if="!slotProps.model.method">新增接口</el-dropdown-item>
                      <el-dropdown-item command="2" v-if="!slotProps.model.method">编辑分类名</el-dropdown-item> -->
                      <el-dropdown-item command="3">删除</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
            </template>
            <!-- 新建文件夹 -->

            <template #addTreeNodeIcon><span class="icon"></span></template>
            <template #addLeafNodeIcon><span class="icon"></span></template>
            <template #editNodeIcon><span class="icon"></span></template>
            <template #delNodeIcon><span class="icon"></span></template>
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
              <el-button class="submission" v-db-click @click="debuggingTap()">调试</el-button>
              <el-button
                v-if="formValidate.id"
                type="primary"
                class="submission"
                v-db-click
                @click="isEdit = !isEdit"
                >{{ isEdit ? '取消' : '编辑' }}</el-button
              >
              <el-button v-if="isEdit" type="primary" class="submission" v-db-click @click="handleSubmit()"
                >保存</el-button
              >
            </div>
          </div>
          <el-form
            class="formValidate mt20"
            ref="formValidateRef"
            :rules="ruleValidate"
            :model="formValidate"
            label-width="120px"
            :label-position="labelPosition"
            @submit.prevent
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
                    show-overflow
                    keep-source
                    ref="headTable"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.header"
                    :row-config="{ keyField: 'id' }"
                    :column-config="{ resizable: true }"
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
                    show-overflow
                    keep-source
                    ref="xTable"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.query"
                    :row-config="{ keyField: 'id' }"
                    :column-config="{ resizable: true }"
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
                    show-overflow
                    keep-source
                    ref="bodyTable"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.request"
                    :row-config="{ keyField: 'id' }"
                    :column-config="{ resizable: true }"
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
                    show-overflow
                    keep-source
                    ref="resTable"
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.response"
                    :row-config="{ keyField: 'id' }"
                    :column-config="{ resizable: true }"
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
                      <template #title>
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
                    show-overflow
                    keep-source
                    ref="codeTable"
                    is-tree-view
                    :print-config="{}"
                    :export-config="{}"
                    :loading="loading"
                    :tree-config="{ rowField: 'id', parentField: 'parentId' }"
                    :data="formValidate.error_code"
                    :row-config="{ keyField: 'id' }"
                    :column-config="{ resizable: true }"
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
                  <el-button type="primary" class="submission" v-db-click @click="handleSubmit()">保存</el-button>
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
    <el-dialog v-model="nameModal" width="470px" title="分组名称">
      <label>分组名称：</label>
      <el-input v-model="value" placeholder="请输入分组名称" style="width: 85%" />
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="nameModal = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="asyncOK">确 定</el-button>
        </span></template
      >
    </el-dialog>
    <el-drawer
      v-model="debuggingModal"
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

<script setup>
import { ref, computed, watch, nextTick, resolveComponent } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
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
import 'vue-tree-list/dist/vue-tree-list.css';
import { VueTreeList, Tree, TreeNode } from 'vue-tree-list';
import debugging from './debugging.vue';
import modalForm from '@/utils/modalForm';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'systemOutInterface' });

const mediaStore = useMediaStore();

const treeList = ref(null);
const headTable = ref(null);
const xTable = ref(null);
const bodyTable = ref(null);
const resTable = ref(null);
const codeTable = ref(null);
const formValidateRef = ref(null);
const tableRefs = { headTable, xTable, bodyTable, resTable, codeTable };

const value = ref('');
const isEdit = ref(false);
const nameModal = ref(false);
const debuggingModal = ref(false);
const formValidate = ref({});
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const ruleValidate = {
  title: [{ message: '请输入正确的描述 (不能多于200位数)', trigger: 'blur', max: 200 }],
};
const loading = ref(false);
const intTypeList = [
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
];
const typeList = [
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
];
const requestTypeList = [
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
];
const contextData = ref(null); //左侧导航右键点击是产生的数据对象
// vue-tree-list v2 的 model 需传根节点（new Tree(data).root），且不可为 undefined
// （组件 setup 会直接读取 model.name，传 undefined 会抛错导致整页白屏）
const treeData = ref(new Tree([]).root);
const buttonProps = {
  type: 'default',
};
const methodColor = ref('#fff');
const apiType = ref('adminapi');
const paramsId = ref(0);
const winLoading = ref(false);
let navItem = null;
let pathname = '';
let newTree = null;
let data = null;

watch(() => formValidate.value.method, {
  deep: true,
  handler(newVal, oldVal) {
    if (newVal) {
      let method = newVal.toUpperCase();
      if (method == 'GET') {
        methodColor.value = '#61affe';
      } else if (method == 'POST') {
        methodColor.value = '#49cc90';
      } else if (method == 'PUT') {
        methodColor.value = '#fca130';
      } else if (method == 'DEL' || method == 'DELETE') {
        methodColor.value = '#f93e3e';
      }
    }
  },
});
watch(apiType, (newVal) => {
  if (newVal) {
    winLoading.value = true;
    getInterfaceList('one');
  }
});
watch(isEdit, (newVal) => {
  if (newVal) {
    formValidate.value.response_example.map((e) => {
      e.data = JSON.stringify(e.data);
    });
  } else {
    formValidate.value.response_example.map((e) => {
      e.data = JSON.parse(e.data);
    });
  }
});

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '50px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

function syncRouteConfirm() {
  ElMessageBox({
    title: '立即同步',
    message: '同步之后，路由文件中新增的接口添加到接口列表中，路由文件中删除的路由会同步的在接口列表中删除',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
  })
    .then(() => {
      syncRoute(apiType.value).then((res) => {
        getInterfaceList('one');
        ElMessage.success(res.msg);
      });
    })
    .catch(() => {});
}
function debuggingTap() {
  debuggingModal.value = true;
}
function onClicksss(e) {}
function methodsColor(newVal) {
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
}
function insertBefore(params) {}
function insertAfter(params) {}
function moveInto(params) {}
async function addTableData() {
  const { row: data } = await $table.insertAt(newRow, -1);
  await $table.setActiveCell(data, 'name');
}
function getInterfaceList(disk_type) {
  try {
    routeList(apiType.value)
      .then((res) => {
        if (res.data.length) {
          res.data[0].expand = false;
          treeData.value = new Tree(res.data).root;
          let i;
          nextTick((e) => {
            if (disk_type) {
              if (res.data[0].children && res.data[0].children[0].children && res.data[0].children[0].children.length) {
                document.querySelectorAll('.vtl-icon-caret-right')[0].click();
                document.querySelectorAll('.vtl-icon-caret-right')[1].click();
                i = res.data[0].children[0].children[0];
              } else {
                document.querySelectorAll('.vtl-icon-caret-right')[0].click();
                i = res.data[0].children[0];
              }
              onClick(i);
            }
          });
        } else {
          // treeList.value.clear();
          treeData.value = new Tree({}).root;
          formValidate.value = {};
        }
        winLoading.value = false;
      })
      .catch((err) => {
        winLoading.value = false;
        ElMessage.error(err.msg);
      });
  } catch (error) {
    console.log(error);
  }
}
function onClick(params) {
  try {
    if (params.method) {
      isEdit.value = false;
      paramsId.value = params.id;
      getRoteData(params.id);
    }
  } catch (error) {}
}
function getRoteData(id) {
  routeDet(id)
    .then((res) => {
      formValidate.value = res.data;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
async function handleSubmit() {
  if (!formValidate.value.name) {
    return ElMessage.warning('请输入接口名称');
  } else if (!formValidate.value.method) {
    return ElMessage.warning('请选择请求类型');
  } else if (!formValidate.value.path) {
    return ElMessage.warning('请输入路由地址');
  }
  formValidate.value.request = await bodyTable.value.getTableData().tableData;
  formValidate.value.response = await resTable.value.getTableData().tableData;
  formValidate.value.error_code = await codeTable.value.getTableData().tableData;
  formValidate.value.header = await headTable.value.getTableData().tableData;
  formValidate.value.query = await xTable.value.getTableData().tableData;
  formValidate.value.apiType = apiType.value;
  formValidate.value.response_example.map((e) => {
    e.data = JSON.parse(e.data);
  });
  await routeSave(formValidate.value)
    .then((res) => {
      ElMessage.success(res.msg);
      getRoteData(paramsId.value);
      isEdit.value = false;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
async function insertEvent(type) {
  const $table = tableRefs[type].value;
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
}
async function insertRow(currRow, type) {
  const $table = tableRefs[type].value;
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
}
async function removeRow(row, type) {
  const $table = tableRefs[type].value;
  await $table.remove(row);
}
// 修改名字
function add() {
  value.value = '';
  formValidate.value.id = 0;
  nameModal.value = true;
}
// 点击菜单
function clickMenu(name, params) {
  if (name == 1) {
    formValidate.value = {};
    formValidate.value.cate_id = params ? params.id : 0;
    formValidate.value.id = 0;
    isEdit.value = true;
  } else if (name == 2) {
    // value.value = params.name || '';
    // formValidate.value.cate_id = params ? params.id : 0;
    // nameModal.value = true;
    // onEdit(params);
    modalForm(routeEdit(params.id, apiType.value)).then(() => getInterfaceList());
  } else if (name == 3) {
    onDel(params);
  } else if (name == 4) {
    // add();
    modalForm(routeCate(apiType.value)).then(() => getInterfaceList());
  }
}

function addFac(params) {
  formValidate.value = {
    id: params ? params.id : 0,
  };
  isEdit.value = true;
}
function asyncOK() {
  let data = {
    id: formValidate.value.id || 0,
    type: 0,
    name: value.value,
  };
  routeSave(data)
    .then((res) => {
      ElMessage.success(res.msg);
      getInterfaceList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
//侧边栏右键点击事件
function handleContextMenu(data, event, position) {
  position.left = Number(position.left.slice(0, -2)) + 75 + 'px';
  contextData.value = data;
}
function handleContextCreateFolder() {}
function handleContextCreateFile() {}
// 自定义显示
function renderContent(h, { root, node, data }) {
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
            ...buttonProps,
            icon: 'ios-add',
            style: {
              marginRight: '8px',
            },
            onClick: () => {
              append(data);
            },
          }),
          h(resolveComponent('Button'), {
            ...buttonProps,
            icon: 'ios-remove',
            onClick: () => {
              remove(root, node, data);
            },
          }),
        ],
      ),
    ],
  );
}
/**
 * 侧边栏点击事件
 * @param {Object} data
 */
function clickDir(item, root, node) {
  navItem = item;
  pathname = item.pathname;
}
function append(item) {
  const children = item.children || [];
  children.push({
    title: 'appended node',
    expand: true,
  });
  item['children'] = children;
}
function remove(root, node, item) {
  const parentKey = root.find((el) => el === node).parent;
  const parent = root.find((el) => el.nodeKey === parentKey).node;
  const index = parent.children.indexOf(item);
  parent.children.splice(index, 1);
}
function onMouseOver(root, node, item) {
  console.log(root, node, item);
}
//
function onDel(node) {
  let method = node.cate_id ? routeDel : routeCateDel;
  ElMessageBox({
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
          ElMessage.success(res.msg);
          node.remove();
        })
        .catch((err) => {
          ElMessage.error(err.msg);
        });
    })
    .catch(() => {});
}

function onChangeName(params) {
  if (params.eventType == 'blur') {
    let data = {
      name: params.newName,
      id: params.id,
    };
    interfaceEditName(data)
      .then((res) => {
        ElMessage.success(res.msg);
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  }
}

function onAddNode(params) {
  // router.push({
  //   path: '/admin/setting/system_out_interface/add',
  //   query: {
  //     pid: params.pid,
  //   },
  // });
}

function addNode() {
  var node = new TreeNode({ name: 'new node', isLeaf: false });
  if (!data.children) data.children = [];
  data.addChildren(node);
}

function getNewTree() {
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

  newTree = _dfs(data);
}

getInterfaceList('one');
</script>

<style lang="scss" scoped>
.reset {
  margin-left: 10px;
}
.b-r-1 {
  border-right: 1px solid #f2f2f2;
}
.card-tree {
  background: var(--prev-bg-white);
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
.tabs {
  background: var(--prev-bg-white);
  padding-left: 20px;
  border-radius: 5px 5px 0 0;
}
.main {
  width: 100%;
  display: flex;
  padding-bottom: 16px;
  background: var(--prev-bg-white);
  border-radius: 6px;
  .main-btn {
    display: flex;
    position: sticky;
    padding: 0px 5px 0 15px;
    width: 100%;
    top: 0px;
    background-color: color-mix(in srgb, var(--prev-bg-white) 60%, transparent);
    backdrop-filter: blur(4px);
    z-index: 99;
  }
  .card-tree {
    width: 290px;
    height: calc(100vh - 205px);
    overflow-y: scroll;
  }
  :deep(.tree) {
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
      color: var(--prev-color-text-regular);
    }
  }
  :deep(.vtl-node-main .vtl-operation) {
    position: absolute;
    right: 20px;
  }
  :deep(.vtl-node-content) {
    width: 100%;
  }
  .pop-menu {
    display: flex;
    justify-content: space-between;
  }
  :deep(.vtl-node-content .add) {
    display: none;
    margin-right: 10px;
  }
  :deep(.vtl-node-content:hover .add) {
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    width: 18px;
    height: 18px;
  }
  :deep(.vtl-node-content:hover .add:hover) {
    background-color: var(--prev-bg-white);
    .pop-menu {
      font-size: 16px;
    }
  }
  :deep(.vtl-node-main) {
    padding: 3px 0;
  }
  :deep(.line1) {
    display: table-caption;
    white-space: nowrap;
    width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  :deep(.ivu-form-item) {
    margin-bottom: 10px;
  }
  .right-card {
    flex: 1;
    :deep(.el-card__body) {
      max-height: calc(100vh - 205px);
      overflow-y: scroll;
      padding-bottom: 16px;
    }
    :deep(.el-form-item--small.el-form-item) {
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
  :deep(.ivu-tree-title) {
    width: 100% !important;
  }
  :deep(.vtl-tree-margin) {
    margin-left: 15px;
  }
  :deep(.ivu-btn-icon-only.ivu-btn-small) {
    width: 28px;
  }
  :deep(.tree-node > span) {
    font-size: 14px;
  }
  :deep(.tree-node.node > span) {
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
