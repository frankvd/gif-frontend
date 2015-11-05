FROM ubuntu:14.04

RUN \
	apt-get update && \
	apt-get upgrade -y && \
	apt-get install -y curl php5-fpm php5-cli php5-curl php5-sqlite sqlite3

ADD . /app
WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php
RUN php composer.phar install

RUN mkdir data
RUN touch data/db.sqlite

RUN cat sql/import.sql | sqlite3 data/db.sqlite

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]