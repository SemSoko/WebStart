FROM node:lts-alpine3.22
WORKDIR /app

# Tooling-Dateien vom Host ins Image kopieren
COPY .jsdoc-tooling/package*.json ./

# Abhaengigkeiten installieren (inkl. jsdoc)
RUN npm install -g npm@11.4.1

# Projektabhaengigkeiten installieren (inkl. jsdoc)
RUN npm install

COPY .jsdoc-tooling/jsdoc.json ./

# CMD: Generiert die Doku beim Start
CMD ["npx", "jsdoc", "-c", "jsdoc.json"]
