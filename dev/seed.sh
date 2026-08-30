#!/usr/bin/env bash
# Pull the articles listed in seed/posts.txt off the live blog and put them in the local
# WordPress as Gutenberg blocks.
#
# The fetch runs on the Mac and the import runs in the container: the converter is Python and
# the importer needs WordPress loaded, and pretending either could do the other's job is how
# a seed script grows a second implementation of the first one.
set -euo pipefail

cd "$(dirname "$0")"
mkdir -p seed/json

while read -r url; do
  case "$url" in ''|\#*) continue ;; esac
  slug="${url##*/}"
  echo "--- $slug"
  python3 seed/fetch.py "$url" "seed/json/${slug}.json"
done < seed/posts.txt

docker compose exec -T cli wp --path=/var/www/html eval-file /seed/import.php
