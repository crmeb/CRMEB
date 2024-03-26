# 使用官方的 Node.js 14 作为父镜像  
FROM node:14 AS crmeb
  
# 设置工作目录为 /app  
WORKDIR /app  
  
# 将当前目录内容复制到容器的 /app 内  
COPY package.json /app  

# 安装任何需要的包  
RUN npm install  
  
# 对外暴露的端口号  
EXPOSE 1617  
  
# 定义环境变量  
ENV NODE_ENV=development  
  
# 当容器启动时运行 npm start  
# CMD ["node","./index.js"]