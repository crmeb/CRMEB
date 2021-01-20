export default {
    shortcuts: [
        {
            text: '今天',
            value () {
                const end = new Date()
                const start = new Date()
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()))
                return [start, end]
            }
        },
        {
            text: '昨天',
            value () {
                const end = new Date()
                const start = new Date()
                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)))
                end.setTime(end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() -1 )))
                return [start, end]
            }
        },
        {
            text: '最近7天',
            value () {
                const end = new Date()
                const start = new Date()
                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)))
                return [start, end]
            }
        },
        {
            text: '最近30天',
            value () {
                const end = new Date()
                const start = new Date()
                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)))
                return [start, end]
            }
        },
        {
            text: '本月',
            value () {
                const end = new Date()
                const start = new Date()
                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)))
                return [start, end]
            }
        },
        {
            text: '本年',
            value () {
                const end = new Date()
                const start = new Date()
                start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)))
                return [start, end]
            }
        }
    ]
}