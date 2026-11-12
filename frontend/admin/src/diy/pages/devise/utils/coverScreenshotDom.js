/**
 * DIY 封面截图用 DOM 预处理（html2canvas 跨域/渐变缺陷规避）。
 * 无 Vue 依赖，仅操作传入的 container。
 */

/** 将远程图片转为 base64，绕过 html2canvas 跨域限制 */
export async function convertImagesToBase64(container) {
  const imgs = container.querySelectorAll('img');
  const tasks = Array.from(imgs).map((img) => {
    return new Promise((resolve) => {
      const src = img.getAttribute('src');
      if (!src || src.startsWith('data:')) {
        resolve();
        return;
      }
      const separator = src.includes('?') ? '&' : '?';
      const cacheBustUrl = `${src}${separator}_t=${Date.now()}`;
      fetch(cacheBustUrl, { mode: 'cors', cache: 'no-store' })
        .then((res) => res.blob())
        .then((blob) => {
          const reader = new FileReader();
          reader.onloadend = () => {
            img.setAttribute('src', reader.result);
            resolve();
          };
          reader.onerror = () => resolve();
          reader.readAsDataURL(blob);
        })
        .catch(() => resolve());
    });
  });
  await Promise.all(tasks);
}

/**
 * 将超级组件文本背景移到独立背景层，避免 html2canvas 绘制时覆盖文本。
 * @returns {() => void} 恢复函数
 */
export function preserveCustomTextBackgrounds(container) {
  const colorRe = /(rgba?\([^)]+\)|#[0-9a-fA-F]{3,8})/g;
  const restores = [];
  const normalizeColor = (color) => color.replace(/\s/g, '').toLowerCase();
  const isTransparent = (color) => {
    const normalized = normalizeColor(color || '');
    return (
      !normalized ||
      normalized === 'transparent' ||
      normalized === 'rgba(0,0,0,0)' ||
      normalized === '#0000' ||
      normalized === '#00000000'
    );
  };

  Array.from(container.querySelectorAll('[data-custom-text-bg]')).forEach((el) => {
    const computed = window.getComputedStyle(el);
    const backgroundImage = computed.backgroundImage;
    const layerBackgroundStyle = {};

    if (backgroundImage && backgroundImage !== 'none') {
      const colors = backgroundImage.indexOf('gradient') > -1 ? backgroundImage.match(colorRe) : null;
      const isMonochromeGradient =
        colors && colors.length && colors.every((color) => normalizeColor(color) === normalizeColor(colors[0]));

      if (isMonochromeGradient) {
        if (isTransparent(colors[0])) return;
        layerBackgroundStyle.backgroundColor = colors[0];
      } else {
        layerBackgroundStyle.backgroundImage = backgroundImage;
        layerBackgroundStyle.backgroundSize = computed.backgroundSize;
        layerBackgroundStyle.backgroundPosition = computed.backgroundPosition;
        layerBackgroundStyle.backgroundRepeat = computed.backgroundRepeat;
      }
    } else if (!isTransparent(computed.backgroundColor)) {
      layerBackgroundStyle.backgroundColor = computed.backgroundColor;
    } else {
      return;
    }

    const backgroundLayer = document.createElement('div');
    backgroundLayer.setAttribute('data-custom-text-bg-layer', '');
    Object.assign(backgroundLayer.style, {
      position: 'absolute',
      top: '0',
      right: '0',
      bottom: '0',
      left: '0',
      zIndex: '0',
      pointerEvents: 'none',
      borderRadius: computed.borderRadius,
      ...layerBackgroundStyle,
    });

    const content = el.firstElementChild;
    const originalPosition = el.style.position;
    const originalBackgroundImage = el.style.backgroundImage;
    const originalBackgroundColor = el.style.backgroundColor;
    const originalContentPosition = content && content.style.position;
    const originalContentZIndex = content && content.style.zIndex;

    if (computed.position === 'static') {
      el.style.position = 'relative';
    }
    el.style.backgroundImage = 'none';
    el.style.backgroundColor = 'transparent';
    el.insertBefore(backgroundLayer, el.firstChild);

    if (content) {
      content.style.position = 'relative';
      content.style.zIndex = '1';
    }

    restores.push(() => {
      backgroundLayer.remove();
      el.style.position = originalPosition;
      el.style.backgroundImage = originalBackgroundImage;
      el.style.backgroundColor = originalBackgroundColor;
      if (content) {
        content.style.position = originalContentPosition;
        content.style.zIndex = originalContentZIndex;
      }
    });
  });

  return () => restores.forEach((restore) => restore());
}

/**
 * 临时去掉「各停点同色」退化渐变背景图，规避 html2canvas 盖字问题。
 * @returns {() => void} 恢复函数
 */
export function flattenMonochromeGradients(container) {
  const colorRe = /(rgba?\([^)]+\)|#[0-9a-fA-F]{3,8})/g;
  const restores = [];
  const els = [container].concat(Array.from(container.querySelectorAll('*')));
  els.forEach((el) => {
    let bg;
    try {
      bg = window.getComputedStyle(el).backgroundImage;
    } catch (e) {
      return;
    }
    if (!bg || bg === 'none' || bg.indexOf('gradient') === -1) return;
    const stops = bg.match(colorRe);
    if (!stops || !stops.every((c) => c.toLowerCase() === stops[0].toLowerCase())) return;
    if (stops[0].replace(/\s/g, '').toLowerCase() === 'rgba(0,0,0,0)') return;
    const prevImage = el.style.backgroundImage;
    el.style.backgroundImage = 'none';
    restores.push(() => {
      el.style.backgroundImage = prevImage;
    });
  });
  return () => restores.forEach((fn) => fn());
}
