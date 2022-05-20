FROM php:7.4-apache

ENV VERSION=23.0.02

WORKDIR /var/www/html/

RUN apt-get update && apt-get -y upgrade && \
    apt-get install -y gettext-base locales git default-mysql-client && \
    echo "es_MX.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql gd opcache zip gettext

ADD https://github.com/GibbonEdu/core/releases/download/v${VERSION}/GibbonEduCore-InstallBundle.tar.gz . 
RUN tar -xzf GibbonEduCore-InstallBundle.tar.gz && \
    rm -rf GibbonEduCore-InstallBundle.tar.gz 
RUN git clone https://github.com/GibbonEdu/i18n.git ./i18n 

ADD auto.php ./installer/

RUN chmod -Rv 755 . && chown -R www-data:www-data . 

RUN apt-get clean autoclean && \
    apt-get autoremove -y && \
    rm -rfv /var/lib/{apt,dpkg,cache,log}/

RUN a2enmod rewrite &&\
    a2enmod headers

RUN echo "ServerTokens Prod\n" >> /etc/apache2/apache2.conf
RUN echo "ServerSignature Off\n" >> /etc/apache2/apache2.conf

RUN echo 'Header set X-Content-Type-Options: "nosniff"' >> /etc/apache2/conf-enabled/security.conf
RUN echo 'Header set X-Frame-Options: "sameorigin"' >> /etc/apache2/conf-enabled/security.conf
RUN echo 'Header set X-XSS-Protection "1; mode=block"' >> /etc/apache2/conf-enabled/security.conf



RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

EXPOSE 80

ADD entrypoint /usr/local/bin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]