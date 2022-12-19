// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import './jigsaw.css';

let w = 310; // canvas宽度
let h = 155; // canvas高度
const l = 42; // 滑块边长
const r = 9; // 滑块半径
const PI = Math.PI;
const L = l + r * 2 + 3; // 滑块实际边长

function getRandomNumberByRange(start, end) {
  return Math.round(Math.random() * (end - start) + start);
}

function createCanvas(width, height) {
  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;
  return canvas;
}

function createImg(onload) {
  const img = new Image();
  img.crossOrigin = 'Anonymous';
  img.onload = onload;
  img.onerror = () => {
    img.setSrc(getRandomImgSrc());
  };

  img.setSrc = function (src) {
    const isIE = window.navigator.userAgent.indexOf('Trident') > -1;
    if (isIE) {
      // IE浏览器无法通过img.crossOrigin跨域，使用ajax获取图片blob然后转为dataURL显示
      const xhr = new XMLHttpRequest();
      xhr.onloadend = function (e) {
        const file = new FileReader(); // FileReader仅支持IE10+
        file.readAsDataURL(e.target.response);
        file.onloadend = function (e) {
          img.src = e.target.result;
        };
      };
      xhr.open('GET', src);
      xhr.responseType = 'blob';
      xhr.send();
    } else img.src = src;
  };

  img.setSrc(getRandomImgSrc());
  return img;
}

function createElement(tagName, className) {
  const elment = document.createElement(tagName);
  elment.className = className;
  return elment;
}

function addClass(tag, className) {
  tag.classList.add(className);
}

function removeClass(tag, className) {
  tag.classList.remove(className);
}

function getRandomImgSrc() {
  return `https://picsum.photos/${w}/${h}/?image=${getRandomNumberByRange(0, 1084)}`;
}

function draw(ctx, x, y, operation) {
  ctx.beginPath();
  ctx.moveTo(x, y);
  ctx.arc(x + l / 2, y - r + 2, r, 0.72 * PI, 2.26 * PI);
  ctx.lineTo(x + l, y);
  ctx.arc(x + l + r - 2, y + l / 2, r, 1.21 * PI, 2.78 * PI);
  ctx.lineTo(x + l, y + l);
  ctx.lineTo(x, y + l);
  ctx.arc(x + r - 2, y + l / 2, r + 0.4, 2.76 * PI, 1.24 * PI, true);
  ctx.lineTo(x, y);
  ctx.lineWidth = 2;
  ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
  ctx.stroke();
  ctx[operation]();
  ctx.globalCompositeOperation = 'destination-over';
}

function sum(x, y) {
  return x + y;
}

function square(x) {
  return x * x;
}

class jigsaw {
  constructor({ el, width = 310, height = 155, onSuccess, onFail, onRefresh }) {
    w = width;
    h = height;
    el.style.position = 'relative';
    el.style.width = w + 'px';
    Object.assign(el.style, {
      position: 'relative',
      width: w + 'px',
      margin: '0 auto',
    });
    this.el = el;
    this.onSuccess = onSuccess;
    this.onFail = onFail;
    this.onRefresh = onRefresh;
  }

  init() {
    this.initDOM();
    this.initImg();
    this.bindEvents();
  }

  initDOM() {
    const canvas = createCanvas(w, h); // 画布
    const block = canvas.cloneNode(true); // 滑块
    const sliderContainer = createElement('div', 'sliderContainer');
    sliderContainer.style.width = w + 'px';
    const refreshIcon = createElement('div', 'refreshIcon');
    const sliderMask = createElement('div', 'sliderMask');
    const slider = createElement('div', 'slider');
    const sliderIcon = createElement('span', 'sliderIcon');
    const text = createElement('span', 'sliderText');

    block.className = 'block';
    text.innerHTML = '向右滑动填充拼图';

    const el = this.el;
    el.appendChild(canvas);
    el.appendChild(refreshIcon);
    el.appendChild(block);
    slider.appendChild(sliderIcon);
    sliderMask.appendChild(slider);
    sliderContainer.appendChild(sliderMask);
    sliderContainer.appendChild(text);
    el.appendChild(sliderContainer);

    Object.assign(this, {
      canvas,
      block,
      sliderContainer,
      refreshIcon,
      slider,
      sliderMask,
      sliderIcon,
      text,
      canvasCtx: canvas.getContext('2d'),
      blockCtx: block.getContext('2d'),
    });
  }

  initImg() {
    const img = createImg(() => {
      this.draw();
      this.canvasCtx.drawImage(img, 0, 0, w, h);
      this.blockCtx.drawImage(img, 0, 0, w, h);
      const y = this.y - r * 2 - 1;
      const ImageData = this.blockCtx.getImageData(this.x - 3, y, L, L);
      this.block.width = L;
      this.blockCtx.putImageData(ImageData, 0, y);
    });
    this.img = img;
  }

  draw() {
    // 随机创建滑块的位置
    this.x = getRandomNumberByRange(L + 10, w - (L + 10));
    this.y = getRandomNumberByRange(10 + r * 2, h - (L + 10));
    draw(this.canvasCtx, this.x, this.y, 'fill');
    draw(this.blockCtx, this.x, this.y, 'clip');
  }

  clean() {
    this.canvasCtx.clearRect(0, 0, w, h);
    this.blockCtx.clearRect(0, 0, w, h);
    this.block.width = w;
  }

  bindEvents() {
    this.el.onselectstart = () => false;
    this.refreshIcon.onclick = () => {
      this.reset();
      typeof this.onRefresh === 'function' && this.onRefresh();
    };

    let originX,
      originY,
      trail = [],
      isMouseDown = false;

    const handleDragStart = function (e) {
      originX = e.clientX || e.touches[0].clientX;
      originY = e.clientY || e.touches[0].clientY;
      isMouseDown = true;
    };

    const handleDragMove = (e) => {
      if (!isMouseDown) return false;
      const eventX = e.clientX || e.touches[0].clientX;
      const eventY = e.clientY || e.touches[0].clientY;
      const moveX = eventX - originX;
      const moveY = eventY - originY;
      if (moveX < 0 || moveX + 38 >= w) return false;
      this.slider.style.left = moveX + 'px';
      const blockLeft = ((w - 40 - 20) / (w - 40)) * moveX;
      this.block.style.left = blockLeft + 'px';

      addClass(this.sliderContainer, 'sliderContainer_active');
      this.sliderMask.style.width = moveX + 'px';
      trail.push(moveY);
    };

    const handleDragEnd = (e) => {
      if (!isMouseDown) return false;
      isMouseDown = false;
      const eventX = e.clientX || e.changedTouches[0].clientX;
      if (eventX === originX) return false;
      removeClass(this.sliderContainer, 'sliderContainer_active');
      this.trail = trail;
      const { spliced, verified } = this.verify();
      if (spliced) {
        if (verified) {
          addClass(this.sliderContainer, 'sliderContainer_success');
          typeof this.onSuccess === 'function' && this.onSuccess();
        } else {
          addClass(this.sliderContainer, 'sliderContainer_fail');
          this.text.innerHTML = '请再试一次';
          this.reset();
        }
      } else {
        addClass(this.sliderContainer, 'sliderContainer_fail');
        typeof this.onFail === 'function' && this.onFail();
        setTimeout(() => {
          this.reset();
        }, 1000);
      }
    };
    this.slider.addEventListener('mousedown', handleDragStart);
    this.slider.addEventListener('touchstart', handleDragStart);
    this.block.addEventListener('mousedown', handleDragStart);
    this.block.addEventListener('touchstart', handleDragStart);
    document.addEventListener('mousemove', handleDragMove);
    document.addEventListener('touchmove', handleDragMove);
    document.addEventListener('mouseup', handleDragEnd);
    document.addEventListener('touchend', handleDragEnd);
  }

  verify() {
    const arr = this.trail; // 拖动时y轴的移动距离
    const average = arr.reduce(sum) / arr.length;
    const deviations = arr.map((x) => x - average);
    const stddev = Math.sqrt(deviations.map(square).reduce(sum) / arr.length);
    const left = parseInt(this.block.style.left);
    return {
      spliced: Math.abs(left - this.x) < 10,
      verified: stddev !== 0, // 简单验证拖动轨迹，为零时表示Y轴上下没有波动，可能非人为操作
    };
  }

  reset() {
    this.sliderContainer.className = 'sliderContainer';
    this.slider.style.left = 0;
    this.block.style.left = 0;
    this.sliderMask.style.width = 0;
    this.clean();
    this.img.setSrc(getRandomImgSrc());
  }
}

window.jigsaw = {
  init: function (opts) {
    let $jigsaw = new jigsaw(opts);
    $jigsaw.init();
    return $jigsaw;
  },
};
