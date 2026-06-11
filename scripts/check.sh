#!/bin/sh

BASE_URL="${BASE_URL:-http://localhost}"
FAILURES=0

pass() {
    printf 'PASS: %s\n' "$1"
}

fail() {
    printf 'FAIL: %s\n' "$1"
    FAILURES=$((FAILURES + 1))
}

has_status() {
    expected_statuses=$1
    actual_status=$2

    for expected_status in $expected_statuses; do
        if [ "$actual_status" = "$expected_status" ]; then
            return 0
        fi
    done

    return 1
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

require_command() {
    if command_exists "$1"; then
        pass "required command found: $1"
        return 0
    fi

    fail "required command not found: $1"
    return 1
}

check_php_syntax() {
    printf '\n== PHP syntax check ==\n'

    if ! command_exists php; then
        fail "php command not found; cannot run php -l"
        return
    fi

    found_php=0
    php_file_list="${TMPDIR:-/tmp}/review-app-check-php-files-$$"

    find . \
        -path './vendor' -prune -o \
        -type f \
        -name '*.php' \
        ! -path './config/env.php' \
        ! -path './config/database.php' \
        -print > "$php_file_list"

    while IFS= read -r php_file; do
        found_php=1

        syntax_output=$(php -l "$php_file" 2>&1)

        if [ "$?" -eq 0 ]; then
            pass "php -l $php_file"
        else
            fail "php syntax error: $php_file"
            printf '%s\n' "$syntax_output"
        fi
    done < "$php_file_list"

    rm -f "$php_file_list"

    if [ "$found_php" -eq 0 ]; then
        fail "no PHP files found"
    fi
}

check_php_cs_fixer() {
    printf '\n== php-cs-fixer dry-run ==\n'

    if ! command_exists php-cs-fixer; then
        fail "php-cs-fixer command not found; install it in the global Composer environment"
        return
    fi

    if php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php; then
        pass "php-cs-fixer dry-run"
    else
        fail "php-cs-fixer found formatting differences or failed"
    fi
}

get_http_status() {
    url=$1

    status=$(curl -sS -I -o /dev/null -w '%{http_code}' "$url" 2>/dev/null)
    curl_exit=$?

    if [ "$curl_exit" -ne 0 ]; then
        printf '000'
        return
    fi

    printf '%s' "$status"
}

check_url_status() {
    path=$1
    expected_statuses=$2
    label=$3
    url="${BASE_URL}${path}"
    actual_status=$(get_http_status "$url")

    if has_status "$expected_statuses" "$actual_status"; then
        pass "$label $path -> $actual_status"
        return
    fi

    if [ "$actual_status" = "000" ]; then
        fail "$label $path -> 000; cannot connect to ${BASE_URL}. Docker/Apache may not be running"
        return
    fi

    fail "$label $path -> $actual_status; expected: $expected_statuses"
}

check_http_ready() {
    printf '\n== HTTP readiness check ==\n'

    if ! command_exists curl; then
        fail "curl command not found; cannot run HTTP checks"
        return 1
    fi

    status=$(get_http_status "${BASE_URL}/")

    if [ "$status" = "000" ]; then
        fail "cannot connect to ${BASE_URL}; Docker/Apache may not be running"
        return 1
    fi

    pass "connected to ${BASE_URL} -> $status"
    return 0
}

check_http_ok_urls() {
    printf '\n== HTTP expected reachable URLs ==\n'

    check_url_status "/" "200" "http ok"
    check_url_status "/index.php" "200" "http ok"
    check_url_status "/items/item_list.php" "200" "http ok"
    check_url_status "/items/item_detail.php?item_id=1" "200 302 404" "http ok"
    check_url_status "/css/style.css" "200" "http ok"
    check_url_status "/js/scroll_arrow.js" "200" "http ok"
    check_url_status "/images/no_image/no_image.png" "200" "http ok"
    check_url_status "/admin/item_list.php" "200 302" "http ok"
}

check_http_denied_urls() {
    printf '\n== HTTP expected denied URLs ==\n'

    check_url_status "/app/security/csrf.php" "403" "http denied"
    check_url_status "/includes/header_nav.php" "403" "http denied"
    check_url_status "/lib/db.php" "403" "http denied"
    check_url_status "/admin/bootstrap.php" "403" "http denied"
    check_url_status "/admin/lib/db.php" "403" "http denied"
    check_url_status "/.env" "403" "http denied"
    check_url_status "/.git/config" "404" "http denied"
    check_url_status "/config/env.php" "403" "http denied"
    check_url_status "/config/database.php" "403" "http denied"
    check_url_status "/README.md" "403" "http denied"
    check_url_status "/AGENTS.md" "403" "http denied"
    check_url_status "/docs/" "403" "http denied"
    check_url_status "/.gitignore" "403 404" "http denied"
}

main() {
    printf 'BASE_URL=%s\n' "$BASE_URL"

    require_command php
    require_command curl

    check_php_syntax
    check_php_cs_fixer

    if check_http_ready; then
        check_http_ok_urls
        check_http_denied_urls
    else
        fail "skipped HTTP URL checks because ${BASE_URL} is not reachable"
    fi

    printf '\n== Summary ==\n'

    if [ "$FAILURES" -ne 0 ]; then
        printf 'FAIL: %d check(s) failed.\n' "$FAILURES"
        exit 1
    fi

    printf 'PASS: all checks passed.\n'
    exit 0
}

main "$@"
