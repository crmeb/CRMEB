import CommonIcon from '_c/common-icon'
import { showTitle } from '@/libs/util'
export default {
    components: {
        CommonIcon
    },
    methods: {
        showTitle (item) {
            return showTitle(item, this)
        },
        showChildren (item) {
            return item.children && item.children.length
        },
        getNameOrHref (item, children0) {
            return item.href ? `isTurnByHref_${item.href}` : (children0 ? item.children[0].path : item.path)
        }
    }
}
