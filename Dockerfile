# Multi-stage build: build React app, then serve with Nginx
FROM node:18-alpine AS build
WORKDIR /app

# Install deps
COPY package*.json ./
RUN npm ci || npm install

# Copy source
COPY . .

# Build with optional API base
ARG REACT_APP_API_BASE
ENV REACT_APP_API_BASE=$REACT_APP_API_BASE
RUN npm run build

# ---- Production image
FROM nginx:alpine
# Copy build output
COPY --from=build /app/build /usr/share/nginx/html
# Nginx config for SPA routing
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
