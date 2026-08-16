#!/usr/bin/env python3
"""Lightweight structural PHP linter: checks brace/paren/bracket balance and
unterminated strings while respecting PHP string/comment/heredoc syntax.
Not a full parser — it catches gross structural errors for files we cannot
`php -l` (no PHP runtime in the sandbox)."""
import re, sys, glob

def check_file(path):
    src = open(path, encoding='utf-8').read()
    # Strip block/line comments (naive but good enough; careful with */ in strings is rare)
    i = 0
    n = len(src)
    stack = []          # ('{',line) etc.
    errors = []
    line = 1
    sline = 1
    i = 0
    while i < n:
        c = src[i]
        nxt = src[i+1] if i+1 < n else ''
        if c == '\n':
            line += 1; i += 1; continue
        # comments
        if c == '/' and nxt == '*':
            j = src.find('*/', i+2)
            if j == -1:
                errors.append(f"{path}: unterminated block comment at line {line}"); break
            line += src.count('\n', i, j+2)
            i = j + 2; continue
        if c == '/' and nxt == '/':
            j = src.find('\n', i)
            if j == -1: break
            line += 1; i = j+1; continue
        if c == '#':
            j = src.find('\n', i)
            if j == -1: break
            line += 1; i = j+1; continue
        # strings
        if c in ('"', "'"):
            quote = c; start = i; i += 1; sline = line
            while i < n:
                if src[i] == '\\':
                    i += 2; continue
                if src[i] == quote:
                    break
                if src[i] == '\n':
                    line += 1
                i += 1
            else:
                errors.append(f"{path}: unterminated string starting line {sline}")
                break
            i += 1; continue
        # heredoc/nowdoc
        if c == '<' and src[i:i+3] == '<<<':
            m = re.match(r"<<<[ \t]*([A-Za-z_][A-Za-z0-9_]*)[ \t]*\r?\n", src[i:])
            if m:
                label = m.group(1)
                i += m.end()
                endpat = re.compile(r'^[ \t]*' + re.escape(label) + r'[ \t]*;?[ \t]*\r?\n', re.M)
                m2 = endpat.search(src, i)
                if not m2:
                    errors.append(f"{path}: unterminated heredoc {label} at line {line}"); break
                line += src.count('\n', i, m2.end())
                i = m2.end(); continue
        # structural
        if c in '([{':
            stack.append((c, sline if False else line))
        elif c in ')]}':
            if not stack:
                errors.append(f"{path}: unmatched '{c}' at line {line}"); i += 1; continue
            openc, _ = stack.pop()
            pair = {'(':')','[':']','{':'}'}[openc]
            if pair != c:
                errors.append(f"{path}: mismatched '{openc}' (line {line-0}) vs '{c}' at line {line}")
        i += 1
    if stack:
        for openc, ln in stack:
            errors.append(f"{path}: unclosed '{openc}' from line {ln}")
    return errors

def main():
    files = sys.argv[1:] or (glob.glob('app/controllers/*.php') +
            glob.glob('app/models/*.php') +
            glob.glob('core/**/*.php', recursive=True) +
            glob.glob('config/*.php') + ['routes.php'])
    bad = 0
    for f in files:
        errs = check_file(f)
        if errs:
            bad += 1
            for e in errs:
                print(e)
    print(f"checked {len(files)} files; {bad} with issues")

if __name__ == '__main__':
    main()
