FROM occitech/magento:php5.4-apache

RUN yes | pecl install xdebug \
    && echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20100525/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.max_nesting_level=500" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN apt-get update && apt-get install -y unzip wget

COPY ./bin/test.sh /

RUN rm -rf /var/www/htdocs
WORKDIR /var/www
CMD "/test.sh"

