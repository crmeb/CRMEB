#!/bin/sh
set -eu

php think queue:listen --queue &
queue_pid=$!

stop_queue() {
    kill -TERM "$queue_pid" 2>/dev/null || true
    wait "$queue_pid" || true
    exit 0
}

trap stop_queue INT TERM

while kill -0 "$queue_pid" 2>/dev/null; do
    date +%s > runtime/.queue_heartbeat
    # 必须用「后台 sleep + wait」而非前台 sleep：前台 sleep 期间 SIGTERM 不会被处理，
    # 会拖到本轮 sleep 结束（最长 30 秒），超过 docker stop 默认 10 秒宽限期而被 SIGKILL，
    # 导致上面的 stop_queue 来不及执行。后台 wait 可被 trap 立即中断。
    sleep 30 &
    wait $! 2>/dev/null || true
done

wait "$queue_pid"
