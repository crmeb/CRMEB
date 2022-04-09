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

# 
