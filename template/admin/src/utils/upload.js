import md5 from 'js-md5'; //引入MD5加密
import { upload } from '@/api/upload.js'; // 这里指前端调用接口的api方法
export const uploadByPieces = ({ file, pieceSize = 2, success, error, uploading }) => {
  // 如果文件传入为空直接 return 返回
  if (!file) return;
  let fileMD5 = ''; // 总文件列表
  const chunkSize = pieceSize * 1024 * 1024; // 5MB一片
  const chunkCount = Math.ceil(file.size / chunkSize); // 总片数
  // 获取md5
  const readFileMD5 = () => {
    // 读取视频文件的md5
    console.log('获取文件的MD5值');
    let fileRederInstance = new FileReader();
    console.log('file', file);
    fileRederInstance.readAsBinaryString(file);
    fileRederInstance.addEventListener('load', (e) => {
      let fileBolb = e.target.result;
      fileMD5 = md5(fileBolb);
      console.log('fileMD5', fileMD5);
      console.log('文件未被上传，将分片上传');
      readChunkMD5();
    });
  };
  const getChunkInfo = (file, currentChunk, chunkSize) => {
    let start = currentChunk * chunkSize;
    let end = Math.min(file.size, start + chunkSize);
    let chunk = file.slice(start, end);
    return { start, end, chunk };
  };
  // 针对每个文件进行chunk处理
  const readChunkMD5 = async () => {
    // 针对单个文件进行chunk上传
    for (var i = 0; i < chunkCount; i++) {
      const { chunk } = getChunkInfo(file, i, chunkSize);
      console.log('总片数' + chunkCount);
      console.log('分片后的数据---测试：' + i);
      await uploadChunk({ chunk, currentChunk: i, chunkCount });
    }
  };
  const uploadChunk = (chunkInfo) => {
    // progressFun()
    return new Promise((resolver, reject) => {
      let config = {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      };
      // 创建formData对象，下面是结合不同项目给后端传入的对象。
      let fetchForm = new FormData();
      fetchForm.append('chunkNumber', chunkInfo.currentChunk + 1); // 第几片
      fetchForm.append('chunkSize', chunkSize); // 分片大小的限制  例如限制 5M
      fetchForm.append('currentChunkSize', chunkInfo.chunk.size); // 每一片的大小
      fetchForm.append('file', chunkInfo.chunk); //每一片的文件
      fetchForm.append('filename', file.name); // 文件名
      fetchForm.append('totalChunks', chunkInfo.chunkCount); //总片数
      fetchForm.append('md5', fileMD5);
      upload(fetchForm, config)
        .then((res) => {
          console.log('分片上传返回信息：', res);
          if (res.data.code == 1) {
            // // 结合不同项目 将成功的信息返回出去
            // 下面如果在项目中没有用到可以不用打开注释
            uploading(chunkInfo.currentChunk + 1, chunkInfo.chunkCount);
            resolver(true);
          } else if (res.data.code == 2) {
            if (chunkInfo.currentChunk < chunkInfo.chunkCount - 1) {
              console.log('分片上传成功');
            } else {
              // 当总数大于等于分片个数的时候
              if (chunkInfo.currentChunk + 1 == chunkInfo.chunkCount) {
                console.log('文件开始------合并成功');
                success(res.data);
              }
            }
          }
        })
        .catch((e) => {
          error && error(e);
        });
    });
  };
  readFileMD5(); // 开始执行代码
};
