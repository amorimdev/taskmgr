FROM node:10.13.0-alpine as build-stage
WORKDIR /app
COPY ./app/ /app
COPY ./app/package.json /app
RUN npm install
RUN npm run build


FROM nginx:alpine as production-stage
COPY --from=build-stage /app/dist /usr/share/nginx/html

COPY .docker/nginx-prod.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
