FROM alpine:latest
RUN apk update
RUN apk upgrade

# Setup apache and php
RUN apk --no-cache --update \
    add apache2 \
    apache2-ssl \
    curl \
    php8-apache2 \
    php8-bcmath \
    php8-bz2 \
    php8-calendar \
    php8-common \
    php8-ctype \
    php8-curl \
    php8-dom \
    php8-gd \
    php8-iconv \
    php8-mbstring \
    php8-mysqli \
    php8-mysqlnd \
    php8-openssl \
    php8-pdo_mysql \
    php8-pdo_pgsql \
    php8-pdo_sqlite \
    php8-phar \
    php8-session \
    php8-xml \
    php8-pear \
    php8-xdebug \
    && mkdir /htdocs \
    && mkdir -p docker/php/conf.d

RUN pear8 config-set php_ini /etc/php8/php.ini

RUN apk add --no-cache wget curl bash vim openssl tar runit openssh openrc \
    && mkdir -p /etc/service \
    && mkdir -p /etc/app \
    && ssh-keygen -A \
    && mkdir -p /root/.ssh \
    && mkdir -p /run/sshd \
    && chmod 0700 /root/.ssh

COPY docker/sshd_config /etc/ssh/
COPY docker/startservice.sh /etc/startservice.sh
COPY docker/startapache.sh /etc/startapache.sh
RUN chmod +x /etc/*.sh \
    && /etc/startservice.sh --app=sshd --capturelogs --run=/usr/sbin/sshd \
    && /etc/startservice.sh --app=apache --run=/etc/startapache.sh \
    && mkdir -p /etc/service/rootforce \
    && echo -e "#!/bin/bash\nexec 2>&1\necho \"root:\${ROOT_PWD}\"|chpasswd\nsleep infinity\n" > /etc/service/rootforce/run \
    && chmod +x /etc/service/rootforce/run

COPY docker/xdebug.ini /etc/php8/conf.d/docker-php-ext-xdebug.ini
COPY docker/error_reporting.ini /etc/php8/conf.d/error_reporting.ini

EXPOSE 80 443 9001

CMD ["runsvdir", "/etc/service"]