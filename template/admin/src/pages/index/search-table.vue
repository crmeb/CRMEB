<template>
    <div class="i-table-no-border">
        <Table
            :columns="column"
            :data="dataWithPage"
            :loading="loading"
            size="small"
            @on-sort-change="handleSortChange"
        >
            <template slot-scope="{ row }" slot="keyword">
                <a href="/" target="_blank">{{ row.keyword }}</a>
            </template>
            <template slot-scope="{ row }" slot="range">
                {{ row.range }}%
                <Trend :flag="row.status ? 'up' : 'down'" />
            </template>
        </Table>
        <div class="ivu-text-right ivu-mt-8">
            <Page size="small" :page-size="5" :total="limitData.length" :current.sync="current" @on-change="getData" />
        </div>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                current: 1,
                size: 5,
                loading: false,
                sortType: 'normal', // 当前排序类型，可选值为：normal（默认） || asc（升序）|| desc（降序）
                sortKey: '',
                column: [
                    {
                        title: '排名',
                        key: 'index',
                        width: 80
                    },
                    {
                        title: '搜索关键词',
                        key: 'keyword',
                        slot: 'keyword',
                        minWidth: 120
                    },
                    {
                        title: '用户数',
                        key: 'count',
                        sortable: 'custom',
                        minWidth: 100
                    },
                    {
                        title: '周涨幅',
                        key: 'range',
                        slot: 'range',
                        width: 100,
                        align: 'right',
                        sortable: 'custom'
                    }
                ],
                data: []
            }
        },
        computed: {
            limitData () {
                let data = [...this.data]

                // 动态计算排序类型
                if (this.sortType === 'asc') {
                    data = data.sort((a, b) => {
                        return a[this.sortKey] > b[this.sortKey] ? 1 : -1
                    })
                }
                if (this.sortType === 'desc') {
                    data = data.sort((a, b) => {
                        return a[this.sortKey] < b[this.sortKey] ? 1 : -1
                    })
                }

                return data
            },
            // 因为要动态计算总页数，所以还需要一个计算属性来返回最终给 Table 的 data
            dataWithPage () {
                const data = this.limitData
                const start = this.current * this.size - this.size
                const end = start + this.size
                return [...data].slice(start, end)
            }
        },
        methods: {
            // 点击排序按钮时触发
            handleSortChange ({ key, order }) {
                // 将排序保存到数据
                this.sortKey = key
                this.sortType = order
                this.current = 1
            },
            getData () {
                const data = [
                    {
                        'index': 1,
                        'keyword': '搜索关键词-0',
                        'count': 257,
                        'range': 69,
                        'status': 0
                    }, {
                        'index': 2,
                        'keyword': '搜索关键词-1',
                        'count': 711,
                        'range': 57,
                        'status': 0
                    }, {
                        'index': 3,
                        'keyword': '搜索关键词-2',
                        'count': 766,
                        'range': 24,
                        'status': 1
                    }, {
                        'index': 4,
                        'keyword': '搜索关键词-3',
                        'count': 597,
                        'range': 31,
                        'status': 1
                    }, {
                        'index': 5,
                        'keyword': '搜索关键词-4',
                        'count': 133,
                        'range': 30,
                        'status': 0
                    }, {
                        'index': 6,
                        'keyword': '搜索关键词-5',
                        'count': 845,
                        'range': 16,
                        'status': 0
                    }, {
                        'index': 7,
                        'keyword': '搜索关键词-6',
                        'count': 454,
                        'range': 35,
                        'status': 1
                    }, {
                        'index': 8,
                        'keyword': '搜索关键词-7',
                        'count': 884,
                        'range': 96,
                        'status': 1
                    }, {
                        'index': 9,
                        'keyword': '搜索关键词-8',
                        'count': 725,
                        'range': 30,
                        'status': 1
                    }, {
                        'index': 10,
                        'keyword': '搜索关键词-9',
                        'count': 778,
                        'range': 46,
                        'status': 0
                    }, {
                        'index': 11,
                        'keyword': '搜索关键词-10',
                        'count': 414,
                        'range': 69,
                        'status': 1
                    }, {
                        'index': 12,
                        'keyword': '搜索关键词-11',
                        'count': 927,
                        'range': 41,
                        'status': 0
                    }, {
                        'index': 13,
                        'keyword': '搜索关键词-12',
                        'count': 128,
                        'range': 54,
                        'status': 0
                    }, {
                        'index': 14,
                        'keyword': '搜索关键词-13',
                        'count': 169,
                        'range': 77,
                        'status': 0
                    }, {
                        'index': 15,
                        'keyword': '搜索关键词-14',
                        'count': 764,
                        'range': 8,
                        'status': 0
                    }, {
                        'index': 16,
                        'keyword': '搜索关键词-15',
                        'count': 178,
                        'range': 23,
                        'status': 1
                    }, {
                        'index': 17,
                        'keyword': '搜索关键词-16',
                        'count': 32,
                        'range': 94,
                        'status': 0
                    }, {
                        'index': 18,
                        'keyword': '搜索关键词-17',
                        'count': 183,
                        'range': 34,
                        'status': 1
                    }, {
                        'index': 19,
                        'keyword': '搜索关键词-18',
                        'count': 988,
                        'range': 5,
                        'status': 0
                    }, {
                        'index': 20,
                        'keyword': '搜索关键词-19',
                        'count': 324,
                        'range': 15,
                        'status': 0
                    }, {
                        'index': 21,
                        'keyword': '搜索关键词-20',
                        'count': 832,
                        'range': 42,
                        'status': 0
                    }, {
                        'index': 22,
                        'keyword': '搜索关键词-21',
                        'count': 336,
                        'range': 99,
                        'status': 0
                    }, {
                        'index': 23,
                        'keyword': '搜索关键词-22',
                        'count': 23,
                        'range': 1,
                        'status': 1
                    }, {
                        'index': 24,
                        'keyword': '搜索关键词-23',
                        'count': 557,
                        'range': 84,
                        'status': 0
                    }, {
                        'index': 25,
                        'keyword': '搜索关键词-24',
                        'count': 894,
                        'range': 62,
                        'status': 1
                    }, {
                        'index': 26,
                        'keyword': '搜索关键词-25',
                        'count': 610,
                        'range': 73,
                        'status': 1
                    }, {
                        'index': 27,
                        'keyword': '搜索关键词-26',
                        'count': 810,
                        'range': 1,
                        'status': 1
                    }, {
                        'index': 28,
                        'keyword': '搜索关键词-27',
                        'count': 83,
                        'range': 13,
                        'status': 1
                    }, {
                        'index': 29,
                        'keyword': '搜索关键词-28',
                        'count': 734,
                        'range': 11,
                        'status': 1
                    }, {
                        'index': 30,
                        'keyword': '搜索关键词-29',
                        'count': 6,
                        'range': 97,
                        'status': 0
                    }, {
                        'index': 31,
                        'keyword': '搜索关键词-30',
                        'count': 112,
                        'range': 88,
                        'status': 0
                    }, {
                        'index': 32,
                        'keyword': '搜索关键词-31',
                        'count': 978,
                        'range': 92,
                        'status': 0
                    }, {
                        'index': 33,
                        'keyword': '搜索关键词-32',
                        'count': 674,
                        'range': 86,
                        'status': 0
                    }, {
                        'index': 34,
                        'keyword': '搜索关键词-33',
                        'count': 632,
                        'range': 42,
                        'status': 1
                    }, {
                        'index': 35,
                        'keyword': '搜索关键词-34',
                        'count': 0,
                        'range': 23,
                        'status': 1
                    }, {
                        'index': 36,
                        'keyword': '搜索关键词-35',
                        'count': 965,
                        'range': 94,
                        'status': 0
                    }, {
                        'index': 37,
                        'keyword': '搜索关键词-36',
                        'count': 758,
                        'range': 86,
                        'status': 0
                    }, {
                        'index': 38,
                        'keyword': '搜索关键词-37',
                        'count': 857,
                        'range': 34,
                        'status': 0
                    }, {
                        'index': 39,
                        'keyword': '搜索关键词-38',
                        'count': 72,
                        'range': 95,
                        'status': 0
                    }, {
                        'index': 40,
                        'keyword': '搜索关键词-39',
                        'count': 69,
                        'range': 74,
                        'status': 1
                    }, {
                        'index': 41,
                        'keyword': '搜索关键词-40',
                        'count': 553,
                        'range': 21,
                        'status': 1
                    }, {
                        'index': 42,
                        'keyword': '搜索关键词-41',
                        'count': 430,
                        'range': 2,
                        'status': 1
                    }, {
                        'index': 43,
                        'keyword': '搜索关键词-42',
                        'count': 86,
                        'range': 23,
                        'status': 1
                    }, {
                        'index': 44,
                        'keyword': '搜索关键词-43',
                        'count': 626,
                        'range': 2,
                        'status': 1
                    }, {
                        'index': 45,
                        'keyword': '搜索关键词-44',
                        'count': 266,
                        'range': 8,
                        'status': 1
                    }, {
                        'index': 46,
                        'keyword': '搜索关键词-45',
                        'count': 943,
                        'range': 61,
                        'status': 0
                    }, {
                        'index': 47,
                        'keyword': '搜索关键词-46',
                        'count': 417,
                        'range': 17,
                        'status': 1
                    }, {
                        'index': 48,
                        'keyword': '搜索关键词-47',
                        'count': 151,
                        'range': 92,
                        'status': 1
                    }, {
                        'index': 49,
                        'keyword': '搜索关键词-48',
                        'count': 197,
                        'range': 63,
                        'status': 0
                    }, {
                        'index': 50,
                        'keyword': '搜索关键词-49',
                        'count': 578,
                        'range': 18,
                        'status': 1
                    }
                ]
                this.total = data.length
                this.data = data
            }
        },
        mounted () {
            this.getData()
        }
    }
</script>
