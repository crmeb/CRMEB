FROM phpdockerio/php:7.4-fpm AS crmeb_php
WORKDIR "/var/www"

# 扩展依赖
RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        php7.4-bcmath \
        php7.4-redis \
        php7.4-mysqli \
        php7.4-gd
RUN apt-get clean
RUN apt-get autoremove
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
RUN echo "open_basedir = /tmp:/var" > /etc/php/7.4/fpm/conf.d/open_basedir.ini

# 启动命令
CMD ["sh", "-c", "php think timer start --d"]
CMD ["sh", "-c", "php think workerman start --d"]
CMD ["sh", "-c", "php think queue:listen --queue"]
