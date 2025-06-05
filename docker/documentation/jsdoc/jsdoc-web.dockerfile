FROM httpd:2.4-alpine

# Stelle sicher, dass Dokumentation aus /usr/local/apache2/htdocs bedient wird
COPY ./out/ /usr/local/apache2/htdocs/