#!/bin/sh
set -eu

CAPTURE_DIR=/capture
PID_FILE="$CAPTURE_DIR/fastcgi.pid"
STAMP="$(date +%Y%m%d-%H%M%S)"
PCAP_FILE="${1:-$CAPTURE_DIR/fastcgi-$STAMP.pcap}"

mkdir -p "$CAPTURE_DIR"

if [ -f "$PID_FILE" ] && kill -0 "$(cat "$PID_FILE")" 2>/dev/null; then
    echo "capture already running: pid=$(cat "$PID_FILE")" >&2
    exit 1
fi

rm -f "$PID_FILE"

nohup tcpdump -i any -nn -s0 -U -w "$PCAP_FILE" port 9000 >/dev/null 2>&1 &
PID=$!
echo "$PID" >"$PID_FILE"

sleep 1

if ! kill -0 "$PID" 2>/dev/null; then
    echo "failed to start tcpdump" >&2
    rm -f "$PID_FILE"
    exit 1
fi

echo "started fastcgi capture"
echo "pid=$PID"
echo "pcap=$PCAP_FILE"
