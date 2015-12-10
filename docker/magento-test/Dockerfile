FROM occitech/magento:php5.4-apache

COPY ./bin/test.sh /

RUN rm -rf /var/www/htdocs
WORKDIR /var/www
CMD "/test.sh"
