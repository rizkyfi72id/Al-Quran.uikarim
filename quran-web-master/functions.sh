#!/bin/sh

quranweb_docker_build() {
  [ -z "$QURAN_JSON_DIR" ] && {
    printf "Missing env QURAN_JSON_DIR.\n" >&2
    return 1
  }

  [ -z "$PHP_DOCKER_IMAGE" ] && PHP_DOCKER_IMAGE="php:8.3-cli"
  [ -z "$QURAN_DOWNLOAD_JSON" ] && QURAN_DOWNLOAD_JSON="no"
  [ -z "$PHP_DOCKER_UID" ] && PHP_DOCKER_UID="$(id -u)"
  [ -z "$PHP_DOCKER_GID" ] && PHP_DOCKER_GID="$(id -g)"

  [ -z "$QURAN_DOWNLOAD_JSON" ]

  docker run --rm \
    --name quranweb -it -w /quran-web \
    -v $PWD:/quran-web \
    -v $QURAN_JSON_DIR:/quran-json \
    -u $PHP_DOCKER_UID:$PHP_DOCKER_GID \
    -e QURAN_BASE_URL="$QURAN_BASE_URL" \
    -e QURAN_JSON_DIR="/quran-json" \
    -e QURAN_RAW_HTML_META="$RAW_HTML_META" \
    -e QURAN_DOWNLOAD_JSON="$QURAN_DOWNLOAD_JSON" \
    $PHP_DOCKER_IMAGE sh build.sh
}

quranweb_docker_run() {
  [ -z "$PHP_DOCKER_IMAGE" ] && PHP_DOCKER_IMAGE="php:8.3-cli"
  [ -z "$PHP_DOCKER_PORT" ] && PHP_DOCKER_PORT="8081:80"

  docker run --rm \
    --name quranweb -it \
    -v $PWD:/quran-web \
    -p 8081:80 \
    $PHP_DOCKER_IMAGE php -S 0.0.0.0:80 -t /quran-web/build/public/
}