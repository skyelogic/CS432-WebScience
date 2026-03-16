#!/bin/bash
MEMGATOR="./memgator-windows-amd64.exe"   # adjust for your OS
EMAIL="dgarn008@odu.com"
ARCHIVES="https://raw.githubusercontent.com/odu-cs432-websci/public/main/archives.json"
URI_FILE="uris.txt"
OUT_DIR="timemaps"

mkdir -p "$OUT_DIR"

while IFS= read -r uri; do
    # Create an MD5 hash of the URI for the filename
    hash=$(echo -n "$uri" | md5sum | cut -d' ' -f1)
    outfile="$OUT_DIR/${hash}.json"
    
    echo "Fetching TimeMap for: $uri"
			"$MEMGATOR" -c "ODU CS432/532 $EMAIL" \
            -a "$ARCHIVES" \
            -F 2 -f JSON \
            "$uri" > "$outfile" 2>/dev/null < /dev/null
    
    # Sleep to avoid getting blocked
    sleep 15

done < "$URI_FILE"

echo "Done!"