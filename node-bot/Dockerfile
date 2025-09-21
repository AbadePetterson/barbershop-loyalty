FROM node:20-alpine

# Instalar Chromium e dependências que o Puppeteer precisa
RUN apk add --no-cache \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont

# Definir para o puppeteer-core onde encontrar o Chromium
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser

# Definir diretório de trabalho
WORKDIR /usr/src/app

# Instalar dependências Node
COPY package*.json ./
RUN npm install

# Copiar código do bot
COPY . .

# Comando de inicialização
CMD ["npm", "start"]
