<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <el-button class="btn-back" icon="el-icon-arrow-left" type="text" v-db-click @click="$router.go(-1)"
          >返回</el-button
        >
        <el-divider direction="vertical"></el-divider>
        <span class="ivu-page-header-title">{{ $route.query.lottery_id ? '编辑' : '新增' }}抽奖活动</span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px 20px' }">
      <el-row class="mt30 acea-row row-middle row-center">
        <el-col :span="24" v-loading="spinShow">
          <el-form
            class="form"
            ref="formRef"
            :rules="ruleValidate"
            :model="formValidate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.prevent
          >
            <el-row>
              <el-col :span="24">
                <el-form-item label="活动类型：" prop="name">
                  <el-radio-group v-model="formValidate.factor" @change="onClickTab">
                    <el-radio
                      v-for="(item, index) in tabs"
                      :label="item.type"
                      :value="item.type"
                      :disabled="!!lottery_id"
                      :key="index"
                      >{{ item.name }}</el-radio
                    >
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="活动名称：" prop="name">
                  <el-input
                    placeholder="请输入活动名称"
                    v-model="formValidate.name"
                    class="content_width"
                    maxlength="80"
                    show-word-limit
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="活动时间：">
                  <div class="acea-row row-middle">
                    <el-date-picker
                      v-model="formValidate.period"
                      :editable="false"
                      type="datetimerange"
                      format="YYYY-MM-DD"
                      value-format="YYYY-MM-DD"
                      range-separator="-"
                      start-placeholder="开始日期"
                      end-placeholder="结束日期"
                      @change="onchangeTime"
                      style="width: 460px"
                    ></el-date-picker>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="参与用户：" prop="attends_user">
                  <el-radio-group v-model="formValidate.attends_user" @change="changeUsers">
                    <el-radio :label="1" :value="1" class="radio">全部用户</el-radio>
                    <el-radio :label="2" :value="2">部分用户</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.attends_user == 2">
                <el-form-item label="" :prop="formValidate.attends_user == 2 ? 'user_level' : ''">
                  <div class="acea-row row-middle">
                    <el-select
                      multiple
                      v-model="formValidate.user_level"
                      class="content_width"
                      placeholder="请选择用户等级"
                    >
                      <el-option
                        v-for="item in userLevelList"
                        :value="item.id"
                        :key="item.id"
                        :label="item.name"
                      ></el-option>
                    </el-select>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.attends_user == 2">
                <el-form-item label="" :prop="formValidate.attends_user == 2 ? 'is_svip' : ''">
                  <div class="acea-row row-middle">
                    <el-select
                      v-model="formValidate.is_svip"
                      clearable
                      placeholder="请选择是否是付费会员"
                      class="content_width"
                    >
                      <el-option
                        v-for="item in templateList"
                        :value="item.id"
                        :key="item.id"
                        :label="item.name"
                      ></el-option>
                    </el-select>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.attends_user == 2">
                <el-form-item label="" :prop="formValidate.attends_user == 2 ? 'user_label' : ''">
                  <div class="acea-row row-middle">
                    <div class="labelInput acea-row row-between-wrapper" v-db-click @click="selectLabelShow = true">
                      <div class="">
                        <div v-if="selectDataLabel.length">
                          <el-tag
                            :closable="false"
                            v-for="(item, index) in selectDataLabel"
                            :key="index"
                            class="mr10"
                            >{{ item.label_name }}</el-tag
                          >
                        </div>
                        <span class="span" v-else>选择用户标签</span>
                      </div>
                      <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
                    </div>
                  </div>
                  <div class="tip ml100 grey">三个条件都设置后,必须这些条件都满足的用户才能参加抽奖</div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.factor == 5">
                <el-form-item label="抽奖次数：" :prop="formValidate.factor == 5 ? 'lottery_num_term' : ''">
                  <el-radio-group v-model="formValidate.lottery_num_term">
                    <el-radio :label="1" :value="1" class="radio">每天N次</el-radio>
                    <el-radio :label="2" :value="2">每人N次</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.factor == 5">
                <el-form-item label="邀请新用户最多可获得抽奖" :prop="formValidate.factor == 5 ? 'lottery_num' : ''">
                  <div class="acea-row row-middle">
                    <div class="mr10 grey"></div>
                    <el-input-number
                      :controls="false"
                      placeholder=""
                      :min="1"
                      :precision="0"
                      v-model="formValidate.lottery_num"
                      class="content_width"
                    />
                    <div class="ml10 grey">次</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.factor == 5">
                <el-form-item
                  label="邀请一位新用户关注公众号可获得抽奖"
                  :prop="formValidate.factor == 5 ? 'spread_num' : ''"
                >
                  <div class="acea-row row-middle">
                    <div class="mr10 grey"></div>
                    <el-input-number
                      :controls="false"
                      placeholder=""
                      :min="1"
                      :precision="0"
                      v-model="formValidate.spread_num"
                      class="content_width"
                    />
                    <div class="ml10 grey">次</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col
                :span="24"
                v-if="formValidate.factor == 1 || formValidate.factor == 3 || formValidate.factor == 4"
              >
                <el-form-item
                  :label="formValidate.factor == 1 ? '抽奖消耗积分：' : '抽奖次数：'"
                  :prop="
                    formValidate.factor == 1 || formValidate.factor == 3 || formValidate.factor == 4 ? 'factor_num' : ''
                  "
                >
                  <div class="acea-row row-middle">
                    <!-- <div class="mr10 grey"></div> -->
                    <el-input-number
                      :controls="false"
                      placeholder=""
                      :min="1"
                      :precision="0"
                      v-model="formValidate.factor_num"
                      class="content_width"
                    >
                    </el-input-number>
                    <!-- <div class="ml10 grey" v-if="formValidate.factor !== 1">次</div> -->
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row>
              <el-col :span="24">
                <el-form-item label="规格选择：" prop="prize">
                  <el-table ref="selectionRef" :data="specsData">
                    <el-table-column min-width="30">
                      <template #default="scope">
                        <div class="drag">
                          <img class="handle" src="../../../../assets/images/drag-icon.png" alt="" />
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column label="序号" type="index" width="50"> </el-table-column>
                    <el-table-column label="图片" min-width="80">
                      <template #default="scope">
                        <div
                          class="acea-row scope.row-middle scope.row-center-wrapper"
                          v-db-click
                          @click="modalPicTap('dan', 'goods', scope.$index)"
                        >
                          <div class="pictrue pictrueTab" v-if="scope.row.image">
                            <img v-lazy="scope.row.image" />
                          </div>
                          <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                            <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                          </div>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column label="名称" min-width="80">
                      <template #default="scope">
                        <div>{{ scope.row.name }}</div>
                      </template>
                    </el-table-column>
                    <el-table-column label="奖品" min-width="80">
                      <template #default="scope">
                        <div>{{ typeName(scope.row.type) }}</div>
                      </template>
                    </el-table-column>
                    <el-table-column label="提示语" min-width="80">
                      <template #default="scope">
                        <div>{{ scope.row.prompt }}</div>
                      </template>
                    </el-table-column>
                    <el-table-column label="数量" min-width="80">
                      <template #default="scope">
                        <el-input-number
                          :controls="false"
                          v-model="scope.row.total"
                          :max="9999999999"
                          :min="0"
                          :precision="0"
                          class="priceBox"
                        ></el-input-number>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="80">
                      <template #header>
                        <span :class="{ 'percent-warn': totalPercent != 100 }"
                          >奖品概率(%)
                          <template v-if="specsData.length">（合计：{{ totalPercent }}%）</template>
                        </span>
                      </template>
                      <template #default="scope">
                        <el-input-number
                          :controls="false"
                          v-model="scope.row.percent"
                          :max="100"
                          :min="0"
                          :precision="2"
                          class="priceBox"
                        ></el-input-number>
                      </template>
                    </el-table-column>
                    <el-table-column label="操作" fixed="right" width="80">
                      <template #default="scope">
                        <a class="submission mr15" v-db-click @click="editGoods(scope.$index)">编辑</a>
                      </template>
                    </el-table-column>
                  </el-table>
                  <el-button
                    v-if="specsData.length < 8"
                    type="primary"
                    class="submission mr15 mt20"
                    v-db-click
                    @click="openAddGoods"
                    >添加商品</el-button
                  >
                </el-form-item>
                <el-form-item>
                  <div class="pl60 grey">
                    奖品必须设置为8个，列表中拖拽可调整奖品在九宫中的位置
                    <el-tooltip effect="light" placement="bottom" width="380">
                      <a>查看位置示例图</a>
                      <template #content
                        ><div class="api">
                          <img src="../../../../assets/images/lotteryTest.png" alt="" /></div
                      ></template>
                    </el-tooltip>
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
            <div>
              <el-form-item
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                :prop="formValidate.factor != 3 && formValidate.factor != 4 ? 'image' : ''"
              >
                <template #label
                  ><div class="custom-label">
                    <div>
                      <div>活动背景图</div>
                      <div>(750*750)</div>
                    </div>
                    <div>：</div>
                  </div></template
                >
                <div class="acea-row">
                  <div class="pictrue" v-if="formValidate.image">
                    <img v-lazy="formValidate.image" />
                    <i class="el-icon-circle-close btndel" v-db-click @click="handleRemove()"></i>
                  </div>
                  <div
                    v-else
                    class="upLoad acea-row row-center-wrapper"
                    v-db-click
                    @click="modalPicTap('dan', 'danFrom')"
                  >
                    <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                  </div>
                </div>
              </el-form-item>
              <el-form-item
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                label="中奖名单："
                :prop="formValidate.factor != 3 && formValidate.factor != 4 ? 'is_all_record' : ''"
              >
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="formValidate.is_all_record"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </el-form-item>
              <el-form-item
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                label="个人中奖记录："
                :prop="formValidate.factor != 3 && formValidate.factor != 4 ? 'is_personal_record' : ''"
              >
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="formValidate.is_personal_record"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </el-form-item>
              <el-form-item
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                label="活动规则："
                prop="is_content"
              >
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="formValidate.is_content"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </el-form-item>
              <el-form-item
                label=""
                :prop="
                  formValidate.factor != 3 && formValidate.factor != 4 && formValidate.is_content == 1 ? 'content' : ''
                "
                v-show="formValidate.factor != 3 && formValidate.factor != 4 && formValidate.is_content == 1"
              >
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.content"
                  @editorContent="getEditorContent"
                  :ai-default-prompt="ruleAiPrompt"
                  :ai-default-system-prompt="RULE_AI_SYSTEM_PROMPT"
                ></WangEditor>
              </el-form-item>
              <el-form-item label="活动状态：" prop="status">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="formValidate.status"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </el-form-item>
            </div>
            <el-form-item>
              <el-button type="primary" :loading="submitOpen" v-db-click @click="next()">提交</el-button>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-card>

    <!-- 上传图片-->
    <el-dialog v-model="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
      <uploadPictures :isChoice="isChoice" @getPic="getPic" v-if="modalPic"></uploadPictures>
    </el-dialog>
    <!-- 上传图片-->
    <el-dialog v-model="addGoodsModel" width="720px" :title="title" :close-on-click-modal="false">
      <addGoods ref="addGoodsForm" v-if="addGoodsModel" @addGoodsData="addGoodsData" :editData="editData"></addGoods>
      <div class="acea-row row-right mt20">
        <el-button v-db-click @click="addGoodsModel = false">取消</el-button>
        <el-button type="primary" v-db-click @click="submitAddGoods">提交</el-button>
      </div>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog v-model="selectLabelShow" scrollable title="请选择用户标签" :closable="false" width="540px">
      <userLabel
        v-if="selectLabelShow"
        :uid="0"
        ref="userLabelRef"
        :only_get="true"
        :selectDataLabel="selectDataLabel"
        @activeData="activeSelectData"
        @close="labelClose"
      ></userLabel>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';
import userLabel from '@/components/userLabel';
import addGoods from './addGoods';
import { lotteryDetailApi, lotteryCreateApi, lotteryEditApi } from '../api'; //详情 创建 编辑
import Setting from '@/setting';
import { lotteryFrom } from './formRule/lotteryFrom';
import { labelListApi } from '@/api/product';
import { levelListApi } from '@/api/user';
import WangEditor from '@/components/wangEditor/index.vue';

import { formatDate } from '@/utils/validate';
import { formatRichText } from '@/utils/editorImg';
import Sortable from 'sortablejs';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'lotteryCreate' });

const route = useRoute();
const router = useRouter();
const mediaStore = useMediaStore();

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '120px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const formRef = ref(null);
const selectionRef = ref(null);
const addGoodsForm = ref(null);

const selectDataLabel = ref([]);
const selectLabelShow = ref(false);
const content = ref('');
const tabs = [
  { name: '积分抽取', type: '1' },
  { name: '订单支付', type: '3' },
  { name: '订单评价', type: '4' },
];
const title = ref('添加商品');
const userLabelList = ref([]); //用户标签列表
const userLevelList = ref([]); //用户等级列表
const submitOpen = ref(false);
const spinShow = ref(false);
const addGoodsModel = ref(false);
const editData = ref({});
const isChoice = ref('单选');
const modalPic = ref(false);
const templateList = [
  { id: -1, name: '不限制会员类型' },
  { id: 0, name: '非付费会员' },
  { id: 1, name: '付费会员' },
];
const picTit = ref('');
const tableIndex = ref(0);
const copy = ref(0);
const editIndex = ref(null);
const lottery_id = ref(0);
let sortable = null;

// 后端站点根（apiBaseURL 以 /adminapi 结尾，去掉后即站点地址），用于拼接系统内置静态资源
const backendHost = Setting.apiBaseURL.replace(/\/adminapi$/, '');
// 「未中奖」默认奖品图（backend/public/statics/images/lottery-no-win.png）
const noWinImage = `${backendHost}/statics/images/lottery-no-win.png`;

// 默认 8 个「未中奖」奖品占位，概率平均分（8 × 12.5 = 100%）
function defaultPrizeList() {
  return Array.from({ length: 8 }, () => ({
    type: 1, //类型 1：未中奖 2：积分  3:余额  4：红包 5:优惠券 6：站内商品
    name: '未中奖',
    num: 10,
    image: noWinImage,
    chance: 1, //中奖权重
    total: 0,
    percent: 12.5, //中奖概率
    min_try_num: 0,
    prompt: '',
  }));
}

const specsData = ref(defaultPrizeList());
// 当前奖品概率合计（保留两位小数，消除浮点误差）
const totalPercent = computed(() => {
  const sum = specsData.value.reduce((sum, item) => sum + Number(item.percent || 0), 0);
  return Math.round(sum * 100) / 100;
});
const formValidate = ref({
  images: [],
  name: '', //活动名称
  desc: '', //活动描述
  image: '', //活动背景图
  factor: '1', //抽奖类型：1:积分 2:余额 3：下单支付成功 4:订单评价',5:关注
  factor_num: 1, //获取一次抽奖的条件数量
  attends_user: 1, //参与用户1：所有  2：部分
  user_level: [], //参与用户等级
  user_label: [], //参与用户标签
  is_svip: '', //参与用户是否付费会员
  prize_num: 0, //奖品数量
  period: [], //活动时间
  prize: [], //奖品数组
  lottery_num_term: 1, //抽奖次数限制：1：每天2：每人
  lottery_num: 1, //抽奖次数
  spread_num: 1, //关注推广获取抽奖次数
  is_all_record: 0, //中奖纪录展示
  is_personal_record: 0, //个人中奖纪录展示
  is_content: 0, //活动规格是否展示
  content: '', //富文本内容
  status: 0, //状态
});
const ruleValidate = lotteryFrom;

//奖品类型文案（原 filters.typeName）
function typeName(type) {
  const map = { 1: '未中奖', 2: '积分', 3: '余额', 4: '红包', 5: '优惠券', 6: '商品' };
  return map[type] || '';
}

// ── 活动规则 AI 写作提示词（经 WangEditor 传入 AiModal） ─────────────────────────────
// 系统提示词：定义 AI 角色与输出要求（AiModal 按行拆分后作为 prompts 传给后端，每行一条）
const RULE_AI_SYSTEM_PROMPT = [
  '你是电商平台抽奖活动的资深运营，负责撰写展示给顾客的抽奖活动规则说明。',
  '输出要求：使用 Markdown 格式，章节标题用 ##，条目用 - 列表，重点内容用 **加粗**；直接输出规则正文，不要输出任何解释说明或与正文无关的内容。',
  '内容需覆盖：活动时间、参与条件、抽奖方式与消耗、奖品与中奖概率说明、奖品发放与使用、注意事项；提示词中未提及的具体信息（如时间、数量）用"以活动页面展示为准"表述，不得自行编造数值。',
  '合规要求：语言严谨无歧义、条理清晰，不夸大中奖可能，不使用"必中""稳赚""最高可中"等绝对化用语，符合广告法与平台内容规范。',
].join('\n');
// 默认提示词：结合当前活动配置动态生成摘要，弹窗每次打开读取最新值（未被用户修改时跟随刷新）
const ruleAiPrompt = computed(() => {
  const form = formValidate.value;
  const factorMap = { 1: '积分抽取', 3: '订单支付抽取', 4: '订单评价抽取' };
  const lines = ['请根据以下活动信息，撰写一份完整的抽奖活动规则说明：'];
  lines.push(`1. 活动名称：${form.name || '（未填写）'}`);
  lines.push(
    `2. 活动时间：${Array.isArray(form.period) && form.period.length ? form.period.join(' 至 ') : '（未设置）'}`,
  );
  lines.push(
    `3. 抽奖方式：${factorMap[form.factor] || ''}${form.factor == 1 ? `，每次消耗 ${form.factor_num} 积分` : ''}`,
  );
  if (form.attends_user == 2) {
    const svipMap = { '-1': '不限会员类型', 0: '非付费会员', 1: '付费会员' };
    lines.push(`4. 参与用户：指定用户（${svipMap[String(form.is_svip)] || '不限会员类型'}，按等级/标签筛选）`);
  } else {
    lines.push('4. 参与用户：全部用户');
  }
  // 奖品按九宫格位置逐个列出（含未中奖占位）；概率不写入提示词，由规则中"以活动页面展示为准"表述
  const prizeDesc = specsData.value
    .map((item, index) => `位置${index + 1}：${item.name}（${typeName(item.type)}）`)
    .join('；');
  lines.push(`5. 奖品设置：${prizeDesc}`);
  return lines.join('\n');
});

onMounted(() => {
  getUserLabelList();
  getUserLevelList();
  if (route.query.type) {
    formValidate.value.factor = route.query.type;
  }
  if (route.query.lottery_id) {
    lottery_id.value = route.query.lottery_id;
    getInfo();
  }
  nextTick(() => {
    setSort();
  });
});

function submitAddGoods() {
  addGoodsForm.value.handleSubmit();
}
function changeUsers(e) {
  if (e == 1) {
    formValidate.value.user_level = []; //参与用户等级
    formValidate.value.user_label = []; //参与用户标签
    formValidate.value.is_svip = '-1'; //参与用户是否付费会员
    selectDataLabel.value = []; //参与用户是否付费会员
  }
}
// 标签弹窗关闭
function labelClose() {
  selectLabelShow.value = false;
}
function activeSelectData(data) {
  selectLabelShow.value = false;
  selectDataLabel.value = data;
}
function onClickTab(e) {
  if (lottery_id.value) getInfo(e);
}
function getEditorContent(data) {
  content.value = data;
}
//用户标签列表
function getUserLabelList() {
  labelListApi().then((res) => {
    userLabelList.value = res.data.list;
  });
}
//用户等级列表
function getUserLevelList() {
  levelListApi().then((res) => {
    userLevelList.value = res.data.list;
  });
}
// 具体日期
function onchangeTime(e) {
  formValidate.value['period'] = e;
}
// 详情
function getInfo(e) {
  spinShow.value = true;
  lotteryDetailApi(lottery_id.value)
    .then((res) => {
      if (res.status == 200 && !Array.isArray(res.data)) {
        formValidate.value = res.data;
        formValidate.value.user_level = res.data.user_level || [];
        selectDataLabel.value = res.data.user_label || [];
        formValidate.value.is_svip = res.data.is_svip;
        content.value = res.data.is_content ? res.data.content : '';
        formValidate.value.factor = res.data.factor.toString();
        formValidate.value['period'] = [
          formatDateTime(res.data.start_time) || '',
          formatDateTime(res.data.end_time) || '',
        ];
        specsData.value = res.data.prize;
        getProbability();
      } else {
        formValidate.value = {
          images: [],
          name: '', //活动名称
          desc: '', //活动描述
          image: '', //活动背景图
          factor: e ? e.toString() : formValidate.value.factor, //抽奖类型：1:积分 2:余额 3：下单支付成功 4:订单评价',5:关注
          factor_num: 1, //获取一次抽奖的条件数量
          attends_user: 1, //参与用户1：所有  2：部分
          user_level: [], //参与用户等级
          user_label: [], //参与用户标签
          is_svip: '-1', //参与用户是否付费会员
          prize_num: 0, //奖品数量
          period: [], //活动时间
          prize: [], //奖品数组
          lottery_num_term: 1, //抽奖次数限制：1：每天2：每人
          lottery_num: 1, //抽奖次数
          spread_num: 1, //关注推广获取抽奖次数
          is_all_record: 0, //中奖纪录展示
          is_personal_record: 0, //个人中奖纪录展示
          is_content: 0, //活动规格是否展示
          content: '', //富文本内容
          status: 0, //状态
        };
        specsData.value = defaultPrizeList();
      }
      nextTick(() => {
        spinShow.value = false;
      });
    })
    .catch(() => {
      spinShow.value = false;
    });
}
// 提交
function next() {
  formValidate.value.prize = specsData.value;
  if (formValidate.value.is_content) {
    formValidate.value.content = formatRichText(content.value);
  }
  if (formValidate.value.attends_user == 2) {
    if (selectDataLabel.value.length) {
      formValidate.value.user_label = selectDataLabel.value.map((item) => item.id);
    }
  }
  if (submitOpen.value) return false;
  formRef.value.validate((valid) => {
    if (!valid) return false;
    submitOpen.value = true;
    const request =
      formValidate.value.id && !copy.value
        ? lotteryEditApi(formValidate.value.id, formValidate.value)
        : lotteryCreateApi(formValidate.value);
    request
      .then(async (res) => {
        ElMessage.success(res.msg);
        submitOpen.value = false;
        setTimeout(() => {
          router.push({
            path: '/admin/marketing/lottery/list',
          });
        }, 500);
      })
      .catch((res) => {
        submitOpen.value = false;
        ElMessage.error(res.msg);
      });
  });
}
// 点击商品图
function modalPicTap(tit, picTitVal, index) {
  modalPic.value = true;
  isChoice.value = tit === 'dan' ? '单选' : '多选';
  picTit.value = picTitVal || '';
  tableIndex.value = index;
}
// 获取单张图片信息
function getPic(pc) {
  switch (picTit.value) {
    case 'danFrom':
      formValidate.value.image = pc.att_dir;
      break;
    default:
      specsData.value[tableIndex.value].image = pc.att_dir;
  }
  modalPic.value = false;
}
function handleRemove() {
  formValidate.value.image = '';
}
//新增商品（函数名避免与 addGoods 组件冲突）
function openAddGoods() {
  addGoodsModel.value = true;
  title.value = '添加商品';
  editData.value = {};
}
//编辑商品
function editGoods(index) {
  addGoodsModel.value = true;
  title.value = '添加奖品';
  editData.value = specsData.value[index];
  editIndex.value = index;
}
//删除商品
function deleteGoods(index) {
  specsData.value.splice(index, 1);
}
//获取数组中某个字段之和
function sumArr(arr, name) {
  return arr.reduce((sum, item) => sum + Number(item[name] || 0), 0);
}
function addGoodsData(data) {
  if (editIndex.value != null) {
    specsData.value[editIndex.value] = data;
  } else if (specsData.value.length < 8) {
    specsData.value.push(data);
  } else {
    ElMessage.warning('最多添加8个奖品');
  }
  getProbability();
  addGoodsModel.value = false;
  editIndex.value = null;
}
//获取商品中奖概率
function getProbability() {
  let sum = sumArr(specsData.value, 'chance');
  for (let j = 0; j < specsData.value.length; j++) {
    if (sum == 0) {
      specsData.value[j]['probability'] = '0%';
    } else {
      specsData.value[j]['probability'] = ((specsData.value[j].chance / sum) * 100).toFixed(2) + '%';
    }
  }
}
//拖拽排序
function setSort() {
  // ref一定跟table上面的ref一致
  const tableEl = selectionRef.value && selectionRef.value.$el;
  if (!tableEl) return;
  // Element Plus 表格 body 在 scrollbar 层级之下，不能用 `> table > tbody` 直连
  const el = tableEl.querySelectorAll('.el-table__body-wrapper tbody')[0];
  if (!el) return;
  sortable = Sortable.create(el, {
    ghostClass: 'sortable-ghost',
    handle: '.handle',
    setData: function (dataTransfer) {
      dataTransfer.setData('Text', '');
    },
    // 监听拖拽事件结束时触发
    onEnd: (evt) => {
      elChangeExForArray(evt.oldIndex, evt.newIndex);
    },
  });
}
function elChangeExForArray(index1, index2) {
  const arr = specsData.value;
  const temp = arr[index1];
  arr[index1] = arr[index2];
  arr[index2] = temp;
  // 先清空再回填，强制表格整体重绘，避免 Sortable 移动过的 DOM 与数据错位
  specsData.value = [];
  nextTick(() => {
    specsData.value = arr;
  });
}
//时间格式转换
function formatDateTime(time) {
  if (time) {
    let date = new Date(time * 1000);
    return formatDate(date, 'yyyy-MM-dd hh:mm');
  } else {
    return '';
  }
}
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}
.custom-label {
  display: inline-flex;
  line-height: 1.5;
}
.grey {
  color: #999;
}
.percent-warn {
  color: #f56c6c;
}
.maxW :deep(.ivu-select-dropdown) {
  max-width: 600px;
}
.ivu-table-wrapper {
  border-left: 1px solid #dcdee2;
  border-top: 1px solid #dcdee2;
}
.tabBox_img {
  width: 50px;
  height: 50px;
}
.tabBox_img img {
  width: 100%;
  height: 100%;
}
.priceBox {
  width: 100%;
}
.form {
  .picBox {
    display: inline-block;
    cursor: pointer;
  }
  .pictrue {
    width: 58px;
    height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 0px;
    display: inline-block;
    position: relative;
    cursor: pointer;

    img {
      width: 100%;
      height: 100%;
    }
    .btndel {
      position: absolute;
      z-index: 9;
      font-size: 20px;
      left: 46px;
      top: -4px;
    }
  }
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
    cursor: pointer;
  }
}
.labelInput {
  border: 1px solid #dcdee2;
  padding: 0 15px;
  width: 460px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  .span {
    font-size: 12px;
    color: #c5c8ce;
  }
  .ivu-icon-ios-arrow-down {
    font-size: 14px;
    color: #808695;
  }
}
</style>
