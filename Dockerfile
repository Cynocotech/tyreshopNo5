# Node.js — front site + booking API (port 3001)
FROM node:20-alpine

WORKDIR /app

# Install dependencies first (layer cache)
COPY package*.json ./
RUN npm ci --omit=dev

# Copy full project
COPY . .

EXPOSE 3001

CMD ["node", "server/index.js"]
