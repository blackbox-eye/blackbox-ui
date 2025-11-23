# 🚀 SMTP Deployment Guide

## Sikker deployment-strategi

Vi bruger to separate filer:
- `.htaccess` (git-tracked) → indeholder placeholder-værdier
- `.htaccess.production` (git-ignored) → indeholder rigtige credentials

## 📋 Deploy-trin

### 1. Via cPanel File Manager (Anbefalet)

1. Log ind på cPanel
2. Åbn **File Manager**
3. Naviger til `/home/blackowu/public_html/`
4. Upload `.htaccess.production` fra dit lokale projekt
5. Omdøb eller erstat eksisterende `.htaccess` med `.htaccess.production`:
   ```bash
   mv .htaccess .htaccess.backup
   mv .htaccess.production .htaccess
   ```
6. Reload PHP-FPM via Terminal:
   ```bash
   sudo systemctl reload php-fpm
   ```

### 2. Via SSH/SFTP

```bash
# Fra dit lokale projekt
scp .htaccess.production blackowu@blackbox.codes:/home/blackowu/public_html/.htaccess

# Log ind på serveren
ssh blackowu@blackbox.codes

# Reload PHP
sudo systemctl reload php-fpm
```

### 3. Via MultiPHP INI Editor (Mest sikker)

Alternativt kan du sætte SMTP-credentials via MultiPHP INI Editor i stedet:

1. cPanel → **Software** → **MultiPHP INI Editor**
2. Vælg domain: `blackbox.codes`
3. **Editor Mode**
4. Tilføj nederst:
   ```ini
   [Environment]
   SMTP_HOST=smtp.protonmail.ch
   SMTP_PORT=587
   SMTP_USERNAME=ops@blackbox.codes
   SMTP_PASSWORD=REPLACE_ON_SERVER
   SMTP_SECURE=tls
   SMTP_DEBUG=true
   CONTACT_EMAIL=ops@blackbox.codes
   ```
5. **Save**

Denne metode holder credentials helt ude af filsystemet.

## ✅ Verificer deployment

### Test formularen
https://blackbox.codes/contact.php

### Tjek error_log for disse linjer:

**✅ Success:**
```
CONTACT FORM MAIL DEBUG: SMTP configuration check
CONTACT FORM MAIL DEBUG: Host=smtp.protonmail.ch
CONTACT FORM MAIL DEBUG: Username=ops***
CONTACT FORM MAIL: Using SMTP mode (host: smtp.protonmail.ch)
SMTP DEBUG: Connection: opening to smtp.protonmail.ch:587
SMTP DEBUG: SMTP INBOUND: "220 smtp.protonmail.ch ESMTP ready"
SMTP DEBUG: AUTH LOGIN accepted
SMTP DEBUG: SMTP -> FROM SERVER:250 2.1.0 Sender OK
CONTACT FORM MAIL DEBUG: SMTP mail sent successfully
```

**❌ Fejl:**
```
CONTACT FORM MAIL DEBUG: SMTP disabled – missing env vars: SMTP_PASSWORD
```

Hvis du ser denne fejl:
1. Kontroller at `.htaccess` indeholder den rigtige password
2. Kør `sudo systemctl reload php-fpm`
3. Test igen

## 🔒 Sikkerhedstjek

- ✅ `.htaccess` (git) indeholder kun placeholder
- ✅ `.htaccess.production` er i `.gitignore`
- ✅ Ingen rigtige passwords i git history
- ✅ SMTP_DEBUG kan slås fra efter test: `SetEnv SMTP_DEBUG "false"`

## 📧 Forventet resultat

Efter korrekt deployment:
- Mail ankommer til `ops@blackbox.codes` inden for 1-2 minutter
- Log viser "SMTP mail sent successfully"
- Ingen fejl i konsol eller error_log

## 🆘 Fejlfinding

**Problem:** "SMTP disabled – missing env vars"
**Løsning:** Password ikke sat korrekt → Gennemgå trin 1 eller 3 igen

**Problem:** "SMTP connection timeout"
**Løsning:** Firewall blokerer port 587 → Prøv port 465 med SMTP_SECURE=ssl

**Problem:** "SMTP authentication failed"
**Løsning:** Forkert password → Generer nyt Proton Mail app-password

**Problem:** Mail sendes, men ankommer ikke
**Løsning:** Tjek spam-folder eller Proton Mail sent-folder
