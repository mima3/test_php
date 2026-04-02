#!/bin/sh
set -eu

PID_FILE=/capture/fastcgi.pid

if [ ! -f "$PID_FILE" ]; then
    echo "no capture pid file found" >&2
    exit 1
fi

PID="$(cat "$PID_FILE")"

if ! kill -0 "$PID" 2>/dev/null; then
    echo "capture process is not running: pid=$PID" >&2
    rm -f "$PID_FILE"
    exit 1
fi

kill -INT "$PID"

for _ in 1 2 3 4 5; do
    if ! kill -0 "$PID" 2>/dev/null; then
        rm -f "$PID_FILE"
        echo "stopped fastcgi capture"
        exit 0
    fi
    sleep 1
done

kill -TERM "$PID" 2>/dev/null || true
rm -f "$PID_FILE"
echo "stopped fastcgi capture"
