// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

export function modelSure (formRequestPromise, Title) {
    return new Promise((resolve, reject) => {
        this.$Modal.confirm({
            title: Title,
            content: '<p>确定要删除吗？</p><p>删除后将无法恢复，请谨慎操作！</p>',
            onOk: () => {
                formRequestPromise.then(({ data }) => {
                }).catch(() => {
                    this.$Message.error('表单加载失败')
                })
            }
            // onCancel: () => {
            //     this.$Message.info('取消成功');
            // }
        })
    })
}
