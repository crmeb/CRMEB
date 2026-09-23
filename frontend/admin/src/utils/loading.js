import { nextTick } from 'vue';
import loadingCss from '@/theme/loading.scss?inline';

// 定义方法
export const PrevLoading = {
  // 载入 css
  setCss: () => {
    let style = document.createElement('style');
    style.textContent = loadingCss;
    document.getElementsByTagName('head')[0].appendChild(style);
  },
  // 创建 loading
  start: (type = 'box') => {
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
    const htmls_1 = `
    <div class="loading-prev-box">
			<div class="loading-prev-box-warp">
        <svg class="pl" width="240" height="240" viewBox="0 0 240 240">
          <circle class="pl__ring pl__ring--a" cx="120" cy="120" r="105" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 660" stroke-dashoffset="-330" stroke-linecap="round"></circle>
          <circle class="pl__ring pl__ring--b" cx="120" cy="120" r="35" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 220" stroke-dashoffset="-110" stroke-linecap="round"></circle>
          <circle class="pl__ring pl__ring--c" cx="85" cy="120" r="70" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
          <circle class="pl__ring pl__ring--d" cx="155" cy="120" r="70" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
        </svg>
      </div>
		</div>`;
    div.innerHTML = type == 'box' ? htmls : htmls_1;
    bodys.insertBefore(div, bodys.childNodes[0]);
  },
  // 移除 loading
  done: () => {
    nextTick(() => {
      setTimeout(() => {
        const el = document.querySelector('.loading-prev');
        el && el.parentNode?.removeChild(el);
      }, 1000);
    });
  },
};
