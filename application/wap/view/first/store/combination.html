{extend name="public/container"}
{block name="title"}拼团列表{/block}
{extend name="public/container"}
{block name="content"}
<div class="buyspell-list product-sort" id="product-list">
    <div class="search-wrapper" style=" padding: 0.12rem 0.2rem;">
        <form action="" method="post" @submit.prevent="search">
            <input type="text" placeholder="商品搜索: 请输入商品关键词" ref='search'>
        </form>
    </div>
    <div class="price-select flex">
        <div class="item" @click='order("default")' :class='{on:where.on=="default"}'>默认</div>
        <div class="item"
             @click='order("price")'
             :class='{"selected-up":where.on=="price" && where.price==1,"selected-down":where.on=="price" && where.price==2,"on":where.on=="price"}'
        >价格<i class="icon"></i></div>
        <div class="item"
             @click='order("sales")'
             :class='{"selected-up":where.on=="sales" && where.sales==1,"selected-down":where.on=="sales" && where.sales==2,"on":where.on=="sales"}'
        >销量<i class="icon"></i></div>
        <div class="item" @click='order("people")' :class="{on:where.on=='people'}">人气</div>
    </div>
    <div class="bslist-box" v-cloak="">
        <div class="bs-item flex" v-for='item in list.data'>
            <div class="picture">
                <img :src="item.image" :alt="item.title">
            </div>
            <div class="bs-item-info flex">
                <div class="info-title">{{item.title}}</div>
                <div class="count-wrapper">
                    <span class="price">￥{{item.price}}</span>
                    <span class="old-price">￥{{item.product_price}}</span>
                    <span class="count">已拼{{item.sales}}单</span>
                </div>
                <a class="people-num" :href="'combination_detail/id/'+item.id">
                    <span class="numbers">{{item.people}}人团</span>
                    <span class="peo-txt">去开团</span>
                    <i class="index-icon people-icon"></i>
                </a>
            </div>
        </div>
        <p class="loading-line" v-show="load == false && list.loading==true"><i></i>正在加载中<i></i></p>
        <p class="loading-line" v-show='load == true' @click="getList"><i></i>点击加载<i></i></p>
        <p class="loading-line" v-show='load == false && list.loadEnd==true'><i></i>没有更多了<i></i></p>
    </div>
</div>
<script type="text/javascript">
    var product_list =<?php echo json_encode($list);?>;
    requirejs(['vue', 'store', 'helper'], function (Vue, storeApi, $h) {
        new Vue({
            el: "#product-list",
            data: {
                load: true,
                list: {
                    loading: false,
                    loadEnd: false,
                    data: product_list
                },
                where: {
                    page: 1,
                    search: '',
                    people: 0,
                    sales: 0,
                    price: 0,
                    default: 0,
                    on: 'default',
                    key: false
                },
                keyorder: ''
            },
            methods: {
                search: function (e) {
                    e.preventDefault();
                    this.list.loadEnd = false;
                    var search = this.$refs.search.value;
                    if (this.$refs.search.value == '') {
                        this.order('default');
                        return;
                    } else if (this.$refs.search.value == this.keyorder) {
                        this.list.loadEnd = true;
                        return;
                    } else {
                        this.keyorder = search;
                    }
                    if (search != '') {
                        this.where.search = search;
                    }
                    this.where.on = 'search';
                    this.where.sales = 0;
                    this.where.price = 0;
                    this.where.page = 0;
                    this.where.default = 0;
                    this.where.people = 0;
                    this.where.key = true;
                    this.getList();
                },
                order: function (info) {
                    this.list.loadEnd = false;
                    if (info == 'people') {
                        if (this.where.people == 1) {
                            if (this.list.data.length < 4) {
                                this.list.loadEnd = true;
                            }
                            return;
                        }
                        this.where.on = info;
                        this.where.page = 0;
                        if (this.where.people == 0) {
                            this.where.people = 1;
                        } else {
                            this.where.people = 0;
                        }

                        this.where.sales = 0;
                        this.where.price = 0;
                        this.where.default = 0;
                    } else if (info == 'sales') {
                        this.where.on = info;
                        this.where.page = 0;
                        if (this.where.sales == 0 || this.where.sales == 2) {
                            this.where.sales = 1;
                        } else {
                            this.where.sales = 2;
                        }

                        this.where.people = 0;
                        this.where.price = 0;
                        this.where.default = 0;
                    } else if (info == 'price') {
                        this.where.on = info;
                        this.where.page = 0;
                        if (this.where.price == 0 || this.where.price == 2) {
                            this.where.price = 1;
                        } else {
                            this.where.price = 2;
                        }

                        this.where.people = 0;
                        this.where.sales = 0;
                        this.where.default = 0;
                    } else if (info == 'default') {
                        if (this.where.default == 1) {
                            if (this.list.data.length < 4) {
                                this.list.loadEnd = true;
                            }
                            return false;
                        }
                        this.$refs.search.value = '';
                        this.where.search = '';
                        this.keyorder = '';
                        this.where.on = info;
                        this.where.page = 0;
                        this.where.default = 1;
                        this.where.search = '';
                        this.where.people = 0;
                        this.where.sales = 0;
                    }

                    this.where.key = true;
                    this.getList();
                },
                getList: function () {
                    var this_ = this;
                    this_.list.loading = true;
                    this_.load = false;
                    storeApi.basePost('{:url(\'wap/store/get_list\')}',
                        {
                            'where': this_.where
                        },
                        function (msg) {
                            this_.list.loading = false;
                            var _length = msg.data.data.list.length;
                            if (_length == 0) {
                                if (this_.keyorder != '' && this_.where.key == true) {
                                    this_.list.data = [];
                                } else {
                                    this_.list.loadEnd = true;
                                }
                            } else {
                                if (this_.where.key == true && this_.where.on != '' && this_.where.page == 0) {
                                    this_.list.data = msg.data.data.list;
                                } else {
                                    for (var i = 0; i < _length; i++) {
                                        this_.list.data.push(msg.data.data.list[i]);
                                    }
                                }
                                this_.load = true;
                            }
                            if (_length < 4) {
                                this_.load = false;
                                this_.list.loadEnd = true;
                            }
                            this_.where.page = msg.data.data.page;
                        },
                        function (error) {
                            this_.list.loading = false;
                            $h.pushMsg('网络异常!');
                        });
                }
            },
            mounted: function () {
                if (this.list.data.length < 4) {
                    this.load = false;
                    this.list.loadEnd = true;
                }
            }
        })
    })

</script>
{/block}