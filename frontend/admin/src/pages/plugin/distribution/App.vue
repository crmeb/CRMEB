<template>
  <div class="agent-app">
    <div class="section-content">
      <component :is="sectionComponent" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import AgentManage from './agentManage.vue';
import SpreadApply from './spread/apply.vue';
import DivisionList from './division/list/index.vue';
import AgentIndex from './division/agent/index.vue';
import AgentStatistics from './division/agent/statistics.vue';
import ApplyList from './division/agent/applyList.vue';
import Agreement from './division/agent/agreement.vue';

defineOptions({ name: 'AgentApp' });

const props = defineProps({
  section: { type: String, default: 'distribution' },
  action: { type: String, default: 'manage' },
  params: { type: Object, default: () => ({}) },
});

const actionComponents = {
  manage: AgentManage,
  apply: SpreadApply,
  list: DivisionList,
  agent: AgentIndex,
  statistics: AgentStatistics,
  applyList: ApplyList,
  agreement: Agreement,
};

const sectionComponent = computed(() => actionComponents[props.action] || AgentManage);
</script>

<style scoped>
.agent-app {
  height: 100%;
}
</style>
