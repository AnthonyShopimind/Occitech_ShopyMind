FROM phusion/baseimage:0.9.18

RUN apt-get update \
  && apt-get install -y unzip \
  && apt-get install -y wget

RUN mkdir /magento-src

WORKDIR /magento-src

RUN wget --no-check-certificate https://github.com/bragento/magento-core/archive/1.6.2.0-patch2.zip \
        && unzip -q 1.6.2.0-patch2.zip && rm -rf 1.6.2.0-patch2.zip && mv magento-core-1.6.2.0-patch2 1.6.2.2 \
    wget --no-check-certificate https://github.com/bragento/magento-core/archive/1.7.0.2-patch8.zip \
        && unzip -q 1.7.0.2-patch8.zip && rm -rf 1.7.0.2-patch8.zip && mv magento-core-1.7.0.2-patch8 1.7.0.2 \
    wget --no-check-certificate https://github.com/bragento/magento-core/archive/1.8.1.0-patch2.zip \
        && unzip -q 1.8.1.0-patch2.zip && rm -rf 1.8.1.0-patch2.zip && mv magento-core-1.8.1.0-patch2 1.8.1.0 \
    wget --no-check-certificate https://github.com/bragento/magento-core/archive/1.9.1.1-patch4.zip \
        && unzip -q 1.9.1.1-patch4.zip && rm -rf 1.9.1.1-patch4.zip && mv magento-core-1.9.1.1-patch4 1.9.1.1 \
    wget --no-check-certificate https://github.com/bragento/magento-core/archive/1.9.2.2-patch1.zip \
        && unzip -q 1.9.2.2-patch1.zip && rm -rf 1.9.2.2-patch1.zip && mv magento-core-1.9.2.2-patch1 1.9.2.2

VOLUME ["/magento-src"]
