FROM node:16 AS builder
WORKDIR /frontend
COPY ./frontend/package.json .
RUN npm install
COPY ./.git ./.git
COPY ./frontend ./
RUN npm run build

FROM node:16
WORKDIR /app
COPY ./backend/package.json .
RUN npm install
COPY ./backend ./
COPY --from=builder /frontend/dist /app/public