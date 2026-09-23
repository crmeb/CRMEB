/** 将图片压缩为后缀和内容一致的 JPEG 文件。 */
export default function compressImg(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    let img;
    let canvas;
    let settled = false;
    const timer = setTimeout(() => finish(new Error('图片处理超时，请重新选择')), 30000);
    function finish(error, result) {
      if (settled) return;
      settled = true;
      clearTimeout(timer);
      reader.onload = reader.onerror = reader.onabort = null;
      if (reader.readyState === 1) reader.abort();
      if (img) img.onload = img.onerror = null;
      if (canvas) canvas.width = canvas.height = 0;
      if (error) reject(error);
      else resolve(result);
    }
    reader.onerror = () => finish(new Error('图片读取失败，请重新选择'));
    reader.onabort = () => finish(new Error('图片读取已取消'));
    reader.onload = () => {
      if (settled) return;
      try {
        img = new Image();
        img.onerror = () => finish(new Error('图片内容无法解析，请重新选择'));
        img.onload = () => {
          if (settled) return;
          try {
            const w = img.naturalWidth;
            const h = img.naturalHeight;
            // 分配画布前限制像素，避免大图耗尽浏览器内存。
            if (!w || !h || w > 20000000 / h) throw new Error('图片像素超过处理限制');
            canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            if (!ctx) throw new Error('图片处理组件不可用');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, w, h);
            ctx.drawImage(img, 0, 0, w, h);
            canvas.toBlob(
              (blob) => {
                if (settled) return;
                try {
                  if (!blob || !blob.size || blob.type !== 'image/jpeg') throw new Error('图片压缩失败');
                  const name = (file.name || 'image').replace(/\.[^.]*$/, '') || 'image';
                  finish(null, new File([blob], `${name}.jpg`, { type: blob.type }));
                } catch (error) {
                  finish(error);
                }
              },
              'image/jpeg',
              0.8,
            );
          } catch (error) {
            finish(error);
          }
        };
        img.src = reader.result;
      } catch (error) {
        finish(error);
      }
    };
    try {
      reader.readAsDataURL(file);
    } catch (error) {
      finish(error);
    }
  });
}
