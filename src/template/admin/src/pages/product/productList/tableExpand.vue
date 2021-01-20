<template>
    <div>
        <Row class="expand-row">
            <Col span="8">
                <span class="expand-key">商品分类：</span>
                <span class="expand-value">{{ row.cate_name }}</span>
            </Col>
            <Col span="8">
                <span class="expand-key">商品市场价格：</span>
                <span class="expand-value">{{ row.ot_price }}</span>
            </Col>
            <Col span="8">
                <span class="expand-key">成本价：</span>
                <span class="expand-value">{{ row.cost }}</span>
            </Col>
        </Row>
        <Row class="expand-row">
            <Col span="8">
                <span class="expand-key">收藏：</span>
                <span class="expand-value">{{ row.collect }}</span>
            </Col>
            <Col span="8">
                <span class="expand-key">虚拟销量：</span>
                <span class="expand-value">{{ row.ficti }}</span>
            </Col>
        </Row>
    </div>
</template>
<style scoped>
    .expand-row{
        margin-bottom: 16px;
    }
</style>
<script>
export default {
  props: {
    row: Object
  }
}
</script>
