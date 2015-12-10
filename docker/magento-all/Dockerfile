FROM phusion/baseimage:0.9.18

RUN apt-get update \
  && apt-get install -y unzip \
  && apt-get install -y wget

RUN mkdir /magento-src

WORKDIR /magento-src

RUN wget --no-check-certificate https://github.com/bragento/magento-core/archive/1.9.2.2-patch1.zip
RUN unzip -q 1.9.2.2-patch1.zip && rm -rf 1.9.2.2-patch1.zip && mv magento-core-1.9.2.2-patch1 1.9.2.2

VOLUME ["/magento-src"]
