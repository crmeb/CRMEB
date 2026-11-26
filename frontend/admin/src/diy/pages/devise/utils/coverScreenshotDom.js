/**
 * DIY 封面截图用 DOM 预处理（html2canvas 跨域/渐变缺陷规避）。
 * 无 Vue 依赖，仅操作传入的 container。
 */

/**
 * 开发态页面在 Vite，上传图在接口所在站点。
 * 静态图没有 CORS 头，浏览器不能直接拉绝对地址。
 * 仅开发态把 /uploads、/statics 改到当前源，由 Vite 转到接口站点。
 * 线上继续用原地址：页面与图片同源时可以直接读取。
 */
function screenshotAssetUrl(src) {
  if (!import.meta.env.DEV || !/^https?:/i.test(src)) return src;
  const url = new URL(src);
  if (url.origin === location.origin) return src;
  if (url.pathname.startsWith('/uploads/') || url.pathname.startsWith('/statics/')) {
    return `${url.pathname}${url.search}`;
  }
  return src;
}

function blobToDataUrl(blob) {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve(typeof reader.result === 'string' ? reader.result : '');
    reader.onerror = () => resolve('');
    reader.readAsDataURL(blob);
  });
}

async function readImageDataUrl(src) {
  const requestUrl = screenshotAssetUrl(src);
  const separator = requestUrl.includes('?') ? '&' : '?';
  const res = await fetch(`${requestUrl}${separator}_t=${Date.now()}`, { cache: 'no-store' });
  if (!res.ok) return '';
  const blob = await res.blob();
  if (!blob.type.startsWith('image/')) return '';
  return blobToDataUrl(blob);
}

/** 将远程图片转为 base64，绕过 html2canvas 跨域限制。返回恢复函数。 */
export async function convertImagesToBase64(container) {
  const restores = [];
  const imgs = Array.from(container.querySelectorAll('img'));
  await Promise.all(
    imgs.map(async (img) => {
      const src = img.getAttribute('src');
      if (!src || src.startsWith('data:')) return;
      const dataUrl = await readImageDataUrl(src).catch(() => '');
      if (!dataUrl) return;
      img.setAttribute('src', dataUrl);
      restores.push(() => img.setAttribute('src', src));
    }),
  );

  const nodes = [container].concat(Array.from(container.querySelectorAll('*')));
  await Promise.all(
    nodes.map(async (el) => {
      const backgroundImage = getComputedStyle(el).backgroundImage;
      if (!backgroundImage || backgroundImage === 'none' || backgroundImage.indexOf('url(') === -1) return;
      const urls = Array.from(backgroundImage.matchAll(/url\(\s*(['"]?)(.*?)\1\s*\)/g))
        .map((match) => match[2])
        .filter((url) => url && !url.startsWith('data:'));
      if (!urls.length) return;
      let next = backgroundImage;
      for (const url of urls) {
        const dataUrl = await readImageDataUrl(url).catch(() => '');
        if (!dataUrl) continue;
        next = next.split(url).join(dataUrl);
      }
      if (next === backgroundImage) return;
      const previous = el.style.backgroundImage;
      el.style.backgroundImage = next;
      restores.push(() => {
        el.style.backgroundImage = previous;
      });
    }),
  );

  return () => restores.forEach((restore) => restore());
}

/**
 * Chrome 把 color-mix(in srgb, ...) 算成 color(srgb ...)。
 * html2canvas 1.4 只认识 rgb/rgba/hsl/hsla，碰到 color() 会中断整张封面。
 * 截图前把计算值改写成 rgba，截完恢复。
 */
const UNSUPPORTED_COLOR_PROPS = [
  'color',
  'backgroundColor',
  'backgroundImage',
  'borderTopColor',
  'borderRightColor',
  'borderBottomColor',
  'borderLeftColor',
  'outlineColor',
  'textDecorationColor',
  'boxShadow',
  'textShadow',
  'caretColor',
  'columnRuleColor',
  'webkitTextFillColor',
  'webkitTextStrokeColor',
];

function srgbColorToRgba(value) {
  return value.replace(
    /color\(\s*srgb\s+([0-9.]+%?)[,\s]+([0-9.]+%?)[,\s]+([0-9.]+%?)(?:\s*\/\s*([0-9.]+%?))?\s*\)/gi,
    (_, r, g, b, a) => {
      const channel = (part) => {
        if (part.endsWith('%')) return Math.round(parseFloat(part) * 2.55);
        const n = parseFloat(part);
        return Math.round(n <= 1 ? n * 255 : n);
      };
      const alpha = a == null ? 1 : a.endsWith('%') ? parseFloat(a) / 100 : parseFloat(a);
      return `rgba(${channel(r)}, ${channel(g)}, ${channel(b)}, ${alpha})`;
    },
  );
}

export function rewriteUnsupportedColors(container) {
  const restores = [];
  const els = [container].concat(Array.from(container.querySelectorAll('*')));
  els.forEach((el) => {
    const computed = getComputedStyle(el);
    UNSUPPORTED_COLOR_PROPS.forEach((prop) => {
      const value = computed[prop];
      if (!value || value.indexOf('color(') === -1) return;
      const previous = el.style[prop];
      el.style[prop] = srgbColorToRgba(value);
      restores.push(() => {
        el.style[prop] = previous;
      });
    });
  });
  return () => restores.forEach((restore) => restore());
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
