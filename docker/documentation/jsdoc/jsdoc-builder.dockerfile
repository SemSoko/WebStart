FROM node:lts-alpine3.22

WORKDIR /app

RUN npm init -y

RUN npm install --save-dev jsdoc

RUN ls -la