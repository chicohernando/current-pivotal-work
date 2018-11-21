FROM php:5.6-cli

RUN echo "date.timezone=UTC" > /usr/local/etc/php/conf.d/timezone.ini
WORKDIR /srv/src/current-pivotal-work/web
ENTRYPOINT ["php", "-S", "0.0.0.0:80"]
