<template>
  <div>
    <Row class="expand-row">
      <Col span="8">
        <span class="expand-key">下单时间：</span>
        <span class="expand-value" v-text="row.add_time"></span>
      </Col>
      <Col span="8">
        <span class="expand-key">用户备注：</span>
        <span class="expand-value" v-text="row.mark ? row.mark : '无'"></span>
      </Col>
      <Col span="8">
        <span class="expand-key">商家备注：</span>
        <span class="expand-value" v-text="row.remark ? row.remark : '无'"></span>
      </Col>
    </Row>
  </div>
</template>

<script>
export default {
  name: 'table-expand',
  props: {
    row: Object,
  },
};
</script>

<style scoped>
.expand-row {
  margin-bottom: 16px;
}
</style>
