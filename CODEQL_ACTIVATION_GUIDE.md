# CodeQL Aktiveringsguide
**Repository:** AlphaAcces/ALPHA-Interface-GUI
**Dato:** 2025-11-24

---

## 📋 Om CodeQL Security Scanning

CodeQL er GitHubs avancerede security analysis tool, der scanner din kode for:
- 🐛 SQL Injection vulnerabilities
- 🔓 Cross-Site Scripting (XSS)
- 🔐 Hardcoded credentials
- 📝 Insecure deserialization
- 🛡️ Path traversal issues
- ... og meget mere

---

## 🚀 Aktivering af CodeQL (Repository Owner)

### Trin 1: Aktivér Code Scanning Feature

1. **Gå til Repository Settings:**
   ```
   https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/security_analysis
   ```

2. **Find "Code scanning" sektionen:**
   - Scroll ned til "Code security and analysis"
   - Lokalisér "Code scanning" sektionen

3. **Klik "Set up" eller "Enable":**
   - Vælg "GitHub Actions" som analysis metode
   - GitHub vil vise tilgængelige workflows

4. **Gem ændringerne**

### Trin 2: Aktivér Automatiske Triggers

Efter feature er aktiveret, opdater `.github/workflows/codeql-analysis.yml`:

```yaml
# Uncommment disse linjer i codeql-analysis.yml:
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
  schedule:
    - cron: '0 0 * * 0'  # Ugentligt scan (søndag kl. 00:00 UTC)
```

### Trin 3: Første Scanning

**Metode A: Manuel Trigger (Anbefalet til test)**
1. Gå til Actions tab
2. Vælg "CodeQL" workflow
3. Klik "Run workflow"
4. Vælg branch (main)
5. Enable PHP analysis: true
6. Enable JS analysis: false (eller true hvis ønsket)
7. Klik "Run workflow"

**Metode B: Push til main**
Efter automatiske triggers er aktiveret, vil CodeQL køre automatisk ved:
- Push til main branch
- Pull requests til main
- Ugentligt scheduled scan (hver søndag)

---

## 📊 Forventede Resultater

### Første Scanning
- **Varighed:** 5-10 minutter for PHP
- **Resultater:** Synlige under Security tab → Code scanning alerts
- **Alerts:** Kan indeholde findings om kendt kode-mønstre

### Løbende Scanning
- **Frekvens:** Ved hver push + ugentlig full scan
- **Notifikationer:** Email ved nye high/critical alerts
- **Tracking:** Alerts kan lukkes/dismisses med reason

---

## 🛡️ Håndtering af Alerts

Når CodeQL finder en vulnerability:

1. **Review Alert:**
   - Gå til Security → Code scanning
   - Klik på alert for detaljer
   - Læs CodeQL's forklaring og anbefaling

2. **Vurder Severity:**
   - **Critical/High:** Fix omgående
   - **Medium:** Plan fix i næste sprint
   - **Low:** Vurder risk vs. effort

3. **Fix eller Dismiss:**
   - **Fix:** Lav kodeændring og commit
   - **Dismiss:** Hvis false positive, dismiss med reason:
     - "False positive"
     - "Won't fix" (med begrundelse)
     - "Used in tests"

4. **Verify Fix:**
   - CodeQL vil automatisk re-scan
   - Verificér at alert er lukket

---

## 🔍 Manual Validation (Uden Code Scanning)

Hvis Code scanning ikke kan aktiveres endnu, kan du bruge:

### Alternativ 1: CodeQL CLI (Lokal)
```bash
# Install CodeQL CLI
wget https://github.com/github/codeql-cli-binaries/releases/latest/download/codeql-linux64.zip
unzip codeql-linux64.zip

# Create database
./codeql/codeql database create codeql-db --language=php

# Run queries
./codeql/codeql database analyze codeql-db \
  --format=sarif-latest \
  --output=results.sarif \
  php-security-extended.qls
```

### Alternativ 2: Manual Code Review
Se `SECURITY_VALIDATION_REPORT.md` for manuel security analyse.

---

## ✅ Success Criteria

CodeQL er korrekt aktiveret når:

- ✅ Security tab viser "Code scanning" sektion
- ✅ CodeQL workflow kører uden fejl
- ✅ Alerts (hvis nogen) vises under Security → Code scanning
- ✅ Scheduled scans kører ugentligt
- ✅ Team modtager notifications ved nye alerts

---

## 📞 Support

Hvis du oplever problemer:

1. **Check Workflow Logs:**
   - Actions tab → CodeQL workflow → Seneste run
   - Læs error messages

2. **Verificér Permissions:**
   - Repo settings → Actions → General
   - Sikr "Read and write permissions" er enabled for GITHUB_TOKEN

3. **Kontakt:**
   - Email: ops@blackbox.codes
   - GitHub Issues: Tag med `security` label

---

## 📚 Yderligere Ressourcer

- [GitHub CodeQL Documentation](https://docs.github.com/en/code-security/code-scanning)
- [CodeQL Query Reference](https://codeql.github.com/docs/)
- `SECURITY_IMPLEMENTATION_SUMMARY.md` (i dette repo)

---

**Næste skridt:** Aktivér Code scanning og kør første scan! 🚀
