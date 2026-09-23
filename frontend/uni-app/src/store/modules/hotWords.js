// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不可去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { defineStore } from 'pinia';
import { ref } from 'vue';

/** 热搜词 store（原 Vuex namespaced module hotWords） */
export const useHotWordsStore = defineStore('hotWords', () => {
	// 搜索关键字
	const hotWord = ref([]);

	function setHotWord(fastsearchforhotwords) {
		hotWord.value = fastsearchforhotwords;
	}

	return {
		hotWord,
		setHotWord
	};
});
