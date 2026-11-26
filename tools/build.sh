#!/usr/bin/env bash
# Собирает архив для загрузки на хостинг: dist/avto-m-site-<версия>-<дата>.zip
# В архив не попадают config.php, база, журналы, копии и обработанные фото — их создаёт сам сайт.
set -euo pipefail
cd "$(dirname "$0")/.."
ver=$(grep -oP "APP_VERSION', '\K[^']+" site/app/lib/bootstrap.php)
out="dist/avto-m-site-${ver}-$(date +%Y%m%d).zip"
mkdir -p dist
rm -f "$out"
php -l site/index.php >/dev/null
for f in $(find site -name '*.php'); do php -l "$f" >/dev/null || { echo "Ошибка синтаксиса: $f"; exit 1; }; done
(cd site && zip -qr9 "../$out" . \
  -x 'app/config.php' -x 'app/data/*' -x 'uploads/p/*' -x '*.DS_Store' && \
  zip -q "../$out" app/data/.htaccess)
echo "Готово: $out ($(du -h "$out" | cut -f1))"
unzip -l "$out" | tail -1
