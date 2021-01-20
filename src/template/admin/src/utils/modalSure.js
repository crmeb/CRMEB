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
