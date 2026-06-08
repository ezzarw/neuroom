#!/usr/bin/env bash
set -u

PROJECT="/opt/lampp/htdocs/neuroom"
NOTIFY="/home/ezzar/.local/bin/miku-notify"
STATE_DIR="$PROJECT/.monitor/state"
mkdir -p "$STATE_DIR"
cd "$PROJECT" || exit 1

now="$(date '+%Y-%m-%d %H:%M:%S')"
host="$(hostname)"
status="OK"
urgency="normal"
issues=()
notes=()

add_issue() {
  status="PERLU CEK"
  issues+=("$1")
}

add_note() {
  notes+=("$1")
}

# Git status summary
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  git_short="$(git status --short 2>/dev/null | sed -n '1,12p')"
  dirty_count="$(git status --short 2>/dev/null | wc -l | tr -d ' ')"
  branch="$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
  if [ "${dirty_count:-0}" -gt 0 ]; then
    add_note "Git: $dirty_count perubahan di branch $branch"
  else
    add_note "Git: clean di branch $branch"
  fi
else
  add_issue "Git: bukan repo / tidak bisa dibaca"
fi

# Laravel basics
if [ -f artisan ]; then
  if command -v php >/dev/null 2>&1; then
    php artisan about --only=environment >/dev/null 2>&1 || add_issue "Laravel artisan bermasalah"
  else
    add_issue "PHP tidak ditemukan di PATH"
  fi
else
  add_issue "artisan tidak ditemukan"
fi

# Composer/package dependency presence
[ -d vendor ] || add_issue "Folder vendor tidak ada"
[ -d node_modules ] || add_note "Folder node_modules tidak ada"

# PHP syntax quick check on changed PHP files, fallback key dirs
if command -v php >/dev/null 2>&1; then
  php_files=""
  if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    php_files="$(git diff --name-only -- '*.php' 2>/dev/null; git diff --cached --name-only -- '*.php' 2>/dev/null; git ls-files --others --exclude-standard -- '*.php' 2>/dev/null)"
  fi
  if [ -z "${php_files// }" ]; then
    php_files="$(find app routes config database -type f -name '*.php' 2>/dev/null | sed -n '1,80p')"
  fi
  lint_fail=""
  while IFS= read -r f; do
    [ -n "$f" ] || continue
    [ -f "$f" ] || continue
    if ! php -l "$f" >/tmp/neuroom_php_lint.$$ 2>&1; then
      lint_fail="$f: $(cat /tmp/neuroom_php_lint.$$ | tr '\n' ' ' | sed 's/  */ /g')"
      break
    fi
  done <<EOF2
$php_files
EOF2
  rm -f /tmp/neuroom_php_lint.$$
  [ -z "$lint_fail" ] || add_issue "PHP lint gagal: $lint_fail"
fi

# Recent Laravel log errors
log_file="$PROJECT/storage/logs/laravel.log"
if [ -f "$log_file" ]; then
  last_size_file="$STATE_DIR/laravel_log_size"
  prev_size="0"
  [ -f "$last_size_file" ] && prev_size="$(cat "$last_size_file" 2>/dev/null || echo 0)"
  cur_size="$(stat -c%s "$log_file" 2>/dev/null || echo 0)"
  if [ "$cur_size" -lt "${prev_size:-0}" ]; then prev_size=0; fi
  new_log="$(tail -c +$((prev_size + 1)) "$log_file" 2>/dev/null || true)"
  printf '%s' "$cur_size" > "$last_size_file"
  if printf '%s' "$new_log" | grep -Eiq '(ERROR|CRITICAL|ParseError|Fatal error|Exception|SQLSTATE)'; then
    last_err="$(printf '%s' "$new_log" | grep -Ei '(ERROR|CRITICAL|ParseError|Fatal error|Exception|SQLSTATE)' | tail -n 1 | cut -c1-220)"
    add_issue "Log error baru: $last_err"
  else
    add_note "Log: tidak ada error baru"
  fi
else
  add_note "Log Laravel belum ada"
fi

# Disk usage
usage="$(df -P "$PROJECT" | awk 'NR==2 {print $5}' | tr -d '%')"
if [ -n "${usage:-}" ] && [ "$usage" -ge 90 ]; then
  add_issue "Disk hampir penuh: ${usage}%"
else
  add_note "Disk: ${usage:-?}% terpakai"
fi

# Build notification body
body="[$now@$host] Neuroom: $status"
if [ "${#issues[@]}" -gt 0 ]; then
  body+=$'\n\nMasalah:'
  for i in "${issues[@]}"; do body+=$'\n- '"$i"; done
fi
body+=$'\n\nRingkas:'
for n in "${notes[@]}"; do body+=$'\n- '"$n"; done

# Keep notification readable
body="$(printf '%s' "$body" | cut -c1-1800)"

if [ "$status" != "OK" ]; then
  urgency="critical"
fi

"$NOTIFY" -u "$urgency" "Neuroom Monitor" "$body"
printf '%s\n' "$body"
