FROM php:7.3-fpm

LABEL maintainer="Mubasher Iqbal mubasheriqbal693@gmail.com"

ENV DOCKER_DIR /var/www/symfony_docker
ARG XDEBUG_VERSION=2.5.5

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR $DOCKER_DIR

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "mubasheriqbal693@gmail.com" \
    && git config --global user.name "Mubasher693"

#Sqlite3
RUN apt update \
    && apt install -y sqlite3 libsqlite3-dev

#xdebug install xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN apt update \
    && apt install -y vim mlocate
#RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#
## relevant to this answer
#RUN echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN echo "xdebug.client_port=9090" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN echo "xdebug.mode=profile" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
##RUN echo "xdebug.remote_host=docker.for.win.localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#RUN echo 'alias sf="php app/console"' >> ~/.bashrc
#RUN echo 'alias sf3="php bin/console"' >> ~/.bashrc


#COPY xdebug.ini /usr/local/lib/php/doc/xdebug/
COPY docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

#RUN mkdir '/var/log/nginx/' \
#    && mkdir '/var/log/nginx/xdebug' \
#    && mkdir '/var/log/nginx/xdebug/garbageCollection' \
#    && mkdir '/var/log/nginx/xdebug/profiler' \
#    && mkdir '/var/log/nginx/xdebug/trace'
#
#RUN chown www-data:www-data '/var/log/nginx' \
#    && chmod 0777 -R '/var/log/nginx/'
#
#RUN echo "xdebug.log=/tmp/xdebug/debug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#COPY xdebug.ini /etc/php/8.0/cli/conf.d/
#
#RUN if [ ${INSTALL_XDEBUG} = false ]; then \
#    rm -rf /etc/php/8.0/fpm/conf.d/xdebug.ini \
#    rm -rf /etc/php/8.0/cli/conf.d/xdebug.ini \
#;fi
# Copy Scripts
#COPY ./start.sh /start.sh
#CMD ["/start.sh"]