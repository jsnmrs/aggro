#!/usr/bin/env bash

# Probe the YouTube and Vimeo endpoints the ingest and plays jobs depend
# on, from wherever this script runs. `ddev sourcecheck` runs it inside
# the local web container or streams it to a remote host over SSH, so the
# same checks produce results that can be compared side by side.
#
# Usage: sourcecheck-probe.sh /path/to/.env
#
# The .env supplies UA_BMXFEED so requests look exactly like fetch_url().
# The /shorts/ probes check the signal ingest uses to tell Shorts apart
# without reading the watch page: a Short answers 200 and a regular video
# redirects to /watch.

ENV_FILE="${1:?Usage: sourcecheck-probe.sh /path/to/.env}"

# A known-good channel, video, Short, and Vimeo source to probe
YT_CHANNEL='UCuSZUuRMLzOP0I6ItdA6uAQ'
YT_VIDEO='NpRmL_eTWiM'
YT_SHORT='w4jB6auYDg0'
VIMEO_SOURCE='heresybmx'
VIMEO_VIDEO='140500276'

UA=$(grep -o '^UA_BMXFEED *= *.*' "$ENV_FILE" 2>/dev/null | sed "s/^UA_BMXFEED *= *//; s/^[\"']//; s/[\"']\$//")
UA="${UA:-Aggro/1.0}"
BODY=$(mktemp)
trap 'rm -f "$BODY"' EXIT
FAILED=0

fetch() {
  curl -sL -A "$UA" --max-time 20 -o "$BODY" -w '%{http_code}' "$1" || true
}

check_status() {
  echo -n "  Testing $1... "
  CODE=$(fetch "$2")
  if [ "$CODE" = "200" ]; then
    echo -e "\033[1;32mOK\033[0m"
  else
    echo -e "\033[1;31mFAILED (HTTP $CODE)\033[0m"
    FAILED=1
  fi
}

echo "  Host: $(hostname)"
echo "  User agent: $UA"

check_status "YouTube channel feed" "https://www.youtube.com/feeds/videos.xml?channel_id=$YT_CHANNEL"
check_status "YouTube oEmbed" "https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D$YT_VIDEO"

# Status and redirect target only, without following the redirect
check_short() {
  echo -n "  Testing $1... "
  RESULT=$(curl -s -A "$UA" --max-time 20 -o /dev/null -w '%{http_code} %{redirect_url}' "https://www.youtube.com/shorts/$2" || true)
  CODE="${RESULT%% *}"
  LOCATION="${RESULT#* }"
  if [ "$3" = "short" ] && [ "$CODE" = "200" ]; then
    echo -e "\033[1;32mOK (Short)\033[0m"
  elif [ "$3" = "regular" ] && [ "$CODE" = "303" ] && [[ "$LOCATION" == *"/watch?v="* ]]; then
    echo -e "\033[1;32mOK (regular, 303 to /watch)\033[0m"
  else
    echo -e "\033[1;31mFAILED (unknown: HTTP $CODE)\033[0m"
    FAILED=1
  fi
}

check_short "YouTube /shorts/ redirect for a regular video" "$YT_VIDEO" "regular"
check_short "YouTube /shorts/ page for a Short" "$YT_SHORT" "short"

check_status "Vimeo channel feed" "https://vimeo.com/api/v2/$VIMEO_SOURCE/videos.json"
check_status "Vimeo video" "https://vimeo.com/api/v2/video/$VIMEO_VIDEO.json"

exit $FAILED
