FROM wordpress:5.0.3-php7.2

RUN apt-get update && apt-get install -y less wget subversion mysql-client

RUN wget https://phar.phpunit.de/phpunit-6.1.0.phar && \
    chmod +x phpunit-6.1.0.phar  && \
    mv phpunit-6.1.0.phar /usr/local/bin/phpunit