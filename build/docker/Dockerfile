FROM php:7.4-fpm-alpine3.16

WORKDIR /root

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

COPY docker-entrypoint.sh .

RUN apk update && \
    apk add nginx && \
    mkdir -p /run/nginx && \
    chmod 755 docker-entrypoint.sh && \
    docker-php-ext-install mysqli

# Discuz_X3.4_SC_GBK_20230315.zip
ENV DZ_URL https://gitee.com/Discuz/DiscuzX/attach_files/1344962/download

ENV DZ_WWW_ROOT /var/www/html

ADD ${DZ_URL} /tmp/discuz.zip
RUN unzip /tmp/discuz.zip \
    && mv upload/* ${DZ_WWW_ROOT} \
    && cd ${DZ_WWW_ROOT} \
    && chmod a+w -R config data uc_server/data uc_client/data \
    && rm -rf /var/Lib/apt/lists/*

EXPOSE 80/tcp

ENTRYPOINT ["./docker-entrypoint.sh"]

CMD ["php-fpm"]
