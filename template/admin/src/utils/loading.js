import Vue from 'vue';
import loadingCss from '@/theme/loading.scss';

// 定义方法
export const PrevLoading = {
  // 载入 css
  setCss: () => {
    let link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = loadingCss;
    link.crossOrigin = 'anonymous';
    document.getElementsByTagName('head')[0].appendChild(link);
  },
  // 创建 loading
  start: () => {
    const bodys = document.body;
    const div = document.createElement('div');
    div.setAttribute('class', 'loading-prev');
    const htmls = `
			<div class="loading-prev-box">
			<div class="loading-prev-box-warp">
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
				<div class="loading-prev-box-item"></div>
			</div>
		</div>
		`;
    div.innerHTML = htmls;
    bodys.insertBefore(div, bodys.childNodes[0]);
  },
  // 移除 loading
  done: () => {
    Vue.nextTick(() => {
      setTimeout(() => {
        const el = document.querySelector('.loading-prev');
        el && el.parentNode?.removeChild(el);
      }, 1000);
    });
  },
};
