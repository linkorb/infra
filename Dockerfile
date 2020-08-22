FROM node:current-slim

WORKDIR /app
COPY . /app

RUN apt-get update
RUN apt-get install make
RUN apt-get install -y apache2

# RUN make build

EXPOSE 80

ADD apache-config.conf /etc/apache2/sites-enabled/000-default.conf

CMD ["apachectl", "-D", "FOREGROUND"]
