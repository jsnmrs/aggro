#!/usr/bin/env bash

# Probe the YouTube and Vimeo endpoints the ingest, duration, and plays
# jobs depend on, from wherever this script runs. `ddev sourcecheck` runs it
# inside the local web container or streams it to a remote host over SSH,
# so the same checks produce results that can be compared side by side.
#
# Usage: sourcecheck-probe.sh /path/to/.env
#
# The .env supplies UA_BMXFEED so requests look exactly like fetch_url().
# The watch page is the one YouTube gates when it rate limits a host, so
# its failure reason names what came back: a 429 CAPTCHA page, or a 200
# whose playabilityStatus is not OK.

ENV_FILE="${1:?Usage: sourcecheck-probe.sh /path/to/.env}"

# A known-good channel, video, and Vimeo source to probe
YT_CHANNEL='UCuSZUuRMLzOP0I6ItdA6uAQ'
YT_VIDEO='NpRmL_eTWiM'
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

echo -n "  Testing YouTube watch page... "
CODE=$(fetch "https://www.youtube.com/watch?v=$YT_VIDEO")
STATUS=$(grep -o '"playabilityStatus":{"status":"[A-Z_]*"' "$BODY" | head -1 | sed 's/.*"status":"//; s/"$//')
LENGTH=$(grep -o '"lengthSeconds":"[0-9]*"' "$BODY" | head -1 | sed 's/.*"lengthSeconds":"//; s/"$//')
if [ "$CODE" = "429" ]; then
  echo -e "\033[1;31mFAILED (429, CAPTCHA page)\033[0m"
  FAILED=1
elif [ "$CODE" != "200" ]; then
  echo -e "\033[1;31mFAILED (HTTP $CODE)\033[0m"
  FAILED=1
elif [ -z "$STATUS" ]; then
  echo -e "\033[1;31mFAILED (no playabilityStatus in body)\033[0m"
  FAILED=1
elif [ "$STATUS" = "LOGIN_REQUIRED" ]; then
  echo -e "\033[1;31mFAILED (LOGIN_REQUIRED, bot wall)\033[0m"
  FAILED=1
elif [ "$STATUS" != "OK" ]; then
  echo -e "\033[1;31mFAILED ($STATUS)\033[0m"
  FAILED=1
elif [ -z "$LENGTH" ]; then
  echo -e "\033[1;31mFAILED (OK but no lengthSeconds)\033[0m"
  FAILED=1
else
  echo -e "\033[1;32mOK (${LENGTH}s)\033[0m"
fi

check_status "Vimeo channel feed" "https://vimeo.com/api/v2/$VIMEO_SOURCE/videos.json"
check_status "Vimeo video" "https://vimeo.com/api/v2/video/$VIMEO_VIDEO.json"

exit $FAILED
