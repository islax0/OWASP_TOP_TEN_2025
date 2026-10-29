# VULNERABLE build recipe - every line, a supply-chain failure
FROM php:8.4-apache:latest

# 1. "trusted" remote script executed as ROOT at build time
RUN curl -sSL http://internal-tools.example.acme/install.sh | bash

# 2. dependency download with NO integrity check (dist sha ignored)
RUN curl -o /tmp/app-bundle.tar.gz http://dl.example-old-mirror.acme/app/app-bundle.tar.gz \
    && cd /tmp && tar xzf app-bundle.tar.gz -C /var/www/html

# 3. app dependencies resolved at BUILD time with scripts enabled
COPY composer.json ./
RUN composer install --no-interaction        # no --no-scripts: post-install-cmd EXECUTES

# 4. secrets baked into the image
COPY .env /var/www/html/.env

# 5. runs as root
# (no USER instruction)
