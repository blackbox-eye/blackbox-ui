# How to Get Your reCAPTCHA Secret Key

## Step 1: Go to Google Cloud Console
Open your browser and navigate to:
```
https://console.cloud.google.com/security/recaptcha/6Lf0xxIsAAAAALw3SGPZYYFJLIZE3dZ0ophlEK4G/integration
```

## Step 2: Find the Integration Tab
You should already be on the **Integration** tab (see screenshot 2 you provided).

## Step 3: Locate the API Key Section
Look for the section that shows:
- **PHP** tab (you provided this in your code sample)
- Sample code showing the API key

## Step 4: Copy the Secret Key
The API key (SECRET_KEY) should be visible in one of these places:
- In the PHP sample code
- In an "API Key" or "Credentials" field on the Integration page
- Under "Keys" > "View Key Details"

## Step 5: Update .htaccess
Replace this line in your `.htaccess` file:
```apache
SetEnv RECAPTCHA_SECRET_KEY "YOUR_SECRET_KEY_HERE"
```

With your actual key:
```apache
SetEnv RECAPTCHA_SECRET_KEY "YOUR_ACTUAL_KEY_FROM_GCP"
```

## Current Configuration Status
✅ Site Key: `6Lf0xxIsAAAAALw3SGPZYYFJLIZE3dZ0ophlEK4G`
✅ Project ID: `gen-lang-client-0126039859`
❌ Secret Key: **MISSING - ADD THIS NOW**
✅ Debug Mode: `true`

## Test After Adding Key
1. Open: `https://blackbox.codes/contact.php?RECAPTCHA_DEBUG=true`
2. Open browser console (F12)
3. Fill out and submit the form
4. Check console for reCAPTCHA logs
5. Check `logs/contact-submissions.log` for backend results

## Troubleshooting
If you don't see the secret key in the Integration tab:
1. Click on "Keys" tab in the left menu
2. Find your key `blackbox-codes-contact-form`
3. Click "Show credentials" or similar button
4. Copy the API Key / Secret Key shown there
