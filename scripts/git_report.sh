#!/usr/bin/env bash
# GIT REPO ANALYSIS v2 — tanpa set -e agar git section tidak abort
BOLD='\033[1m'; CYAN='\033[0;36m'; GREEN='\033[0;32m'
YELLOW='\033[1;33m'; RED='\033[0;31m'; RESET='\033[0m'

divider() { echo -e "${CYAN}══════════════════════════════════════════════${RESET}"; }
header()  { divider; echo -e "${BOLD}$1${RESET}"; divider; }

# ── 3. GIT COMMIT STATS ────────────────────────
header "3. GIT COMMIT OVERVIEW"
if git rev-parse --git-dir > /dev/null 2>&1; then
  echo "Total commits  : $(git rev-list --count HEAD 2>/dev/null || echo 'N/A')"
  echo "First commit   : $(git log --oneline | tail -1 2>/dev/null || echo 'N/A')"
  echo "Last commit    : $(git log --oneline | head -1 2>/dev/null || echo 'N/A')"
  echo "Active branch  : $(git branch --show-current 2>/dev/null || echo 'N/A')"
  echo "Total branches : $(git branch -a 2>/dev/null | wc -l)"
  echo ""
  echo -e "${YELLOW}Commits per month (recent 24):${RESET}"
  git log --format='%ad' --date=format:'%Y-%m' 2>/dev/null \
    | sort | uniq -c | sort -k2 | tail -24 || echo "git log gagal"
else
  echo -e "${RED}Bukan git repo atau git tidak terdeteksi${RESET}"
fi

# ── 4. AUTHOR STATS ────────────────────────────
header "4. AUTHOR CONTRIBUTION"
if git rev-parse --git-dir > /dev/null 2>&1; then
  echo -e "${YELLOW}Commits per author:${RESET}"
  git shortlog -sn --all 2>/dev/null || echo "N/A"
  echo ""
  echo -e "${YELLOW}Lines changed per author (top authors):${RESET}"
  git log --pretty="%aN" --numstat 2>/dev/null | awk '
    /^[0-9]/ { added[$author] += $1; removed[$author] += $2; files[$author]++ }
    /^[^0-9\t]/ { author = $0 }
    END {
      for (a in added)
        printf "%-30s +%-8d -%-8d files touched: %d\n", a, added[a], removed[a], files[a]
    }
  ' | sort -k2 -rn
else
  echo "skip — bukan git repo"
fi

# ── 5. COMMIT FREQUENCY ────────────────────────
header "5. COMMIT FREQUENCY"
if git rev-parse --git-dir > /dev/null 2>&1; then
  total_days=$(git log --format='%ad' --date=format:'%Y-%m-%d' 2>/dev/null | sort -u | wc -l)
  total_commits=$(git rev-list --count HEAD 2>/dev/null)
  echo "Unique days with commits : $total_days"
  echo "Total commits            : $total_commits"
  echo "Avg commits/active day   : $(echo "scale=1; $total_commits / $total_days" | bc 2>/dev/null || echo 'N/A')"
  echo ""
  echo -e "${YELLOW}Commits per weekday:${RESET}"
  git log --format='%ad' --date=format:'%A' 2>/dev/null \
    | sort | uniq -c | sort -rn || echo "N/A"
  echo ""
  echo -e "${YELLOW}Most active hours (commit hour):${RESET}"
  git log --format='%ad' --date=format:'%H' 2>/dev/null \
    | sort | uniq -c | sort -rn | head -10 || echo "N/A"
fi

# ── 6. FILE CHURN ──────────────────────────────
header "6. TOP 20 MOST CHANGED FILES (churn)"
if git rev-parse --git-dir > /dev/null 2>&1; then
  git log --name-only --format='' 2>/dev/null \
    | grep '\.php$' | grep -v '^$' \
    | sort | uniq -c | sort -rn | head -20 || echo "N/A"
fi

# ── 7. BIGGEST FILES ───────────────────────────
header "7. TOP 15 BIGGEST PHP FILES"
find app tests -name '*.php' 2>/dev/null \
  | xargs wc -l 2>/dev/null | sort -rn | head -16

# ── 8. STRICT_TYPES + FINAL COVERAGE ──────────
header "8. CODE QUALITY SIGNALS"
total_php=$(find app -name '*.php' 2>/dev/null | wc -l)
strict=$(grep -rl "declare(strict_types=1)" app 2>/dev/null | wc -l)
final_c=$(find app -name '*.php' 2>/dev/null | xargs grep -l "^final class" 2>/dev/null | wc -l)
readonly_c=$(grep -r "private readonly\|public readonly" app --include="*.php" 2>/dev/null | wc -l)
immutable=$(grep -r "DateTimeImmutable" app --include="*.php" 2>/dev/null | wc -l)
domain_ex=$(grep -r "DomainException" app --include="*.php" 2>/dev/null | wc -l)
db_table=$(grep -r "DB::table\b" app --include="*.php" 2>/dev/null | wc -l)
eloquent=$(grep -r "Eloquent\|extends Model\b" app --include="*.php" 2>/dev/null | wc -l)
pct_strict=$(echo "scale=1; $strict * 100 / $total_php" | bc 2>/dev/null || echo "?")
pct_final=$(echo "scale=1; $final_c * 100 / $total_php" | bc 2>/dev/null || echo "?")
echo "  declare(strict_types=1)  : $strict / $total_php files (${pct_strict}%)"
echo "  final class              : $final_c / $total_php files (${pct_final}%)"
echo "  readonly properties      : $readonly_c occurrences"
echo "  DateTimeImmutable        : $immutable occurrences"
echo "  DomainException          : $domain_ex occurrences"
echo "  DB::table() (raw query)  : $db_table occurrences"
echo "  Eloquent/Model           : $eloquent occurrences"

# ── 9. TEST DENSITY ────────────────────────────
header "9. TEST DENSITY PER DOMAIN"
for dir in tests/Feature/*/; do
  domain=$(basename "$dir")
  count=$(find "$dir" -name '*.php' 2>/dev/null | wc -l)
  loc=$(find "$dir" -name '*.php' 2>/dev/null | xargs wc -l 2>/dev/null | tail -1 | awk '{print $1}')
  printf "  %-35s %3d files  %6d LOC\n" "$domain" "$count" "${loc:-0}"
done | sort -k3 -rn

# ── 10. LOC RATIO SUMMARY ──────────────────────
header "10. LOC RATIO SUMMARY"
app_loc=$(find app -name '*.php' 2>/dev/null | xargs wc -l 2>/dev/null | tail -1 | awk '{print $1}')
test_loc=$(find tests -name '*.php' 2>/dev/null | xargs wc -l 2>/dev/null | tail -1 | awk '{print $1}')
doc_loc=$(find docs -name '*.md' 2>/dev/null | xargs wc -l 2>/dev/null | tail -1 | awk '{print $1}')
echo "  App code (app/)   : ${app_loc} LOC"
echo "  Test code (tests/): ${test_loc} LOC"
echo "  Docs (docs/)      : ${doc_loc} LOC"
ratio=$(echo "scale=2; $test_loc / $app_loc" | bc 2>/dev/null || echo "?")
echo ""
echo "  Test:App ratio    : ${ratio}  ← > 1.0 sangat langka"
echo "  Docs:App ratio    : $(echo "scale=2; $doc_loc / $app_loc" | bc 2>/dev/null || echo "?")  ← docs > code!"

divider
echo -e "${GREEN}${BOLD}  REPORT SELESAI.${RESET}"
divider
