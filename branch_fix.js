const fs = require('fs');

let css = fs.readFileSync('assets/css/components/landing-p0-fix.css', 'utf8');

css = css.replace(/\.page-home #mobile-menu\.active,\n\.page-home #mobile-menu\.active,\n\.page-home #mobile-menu\.active {/g, '.page-home #mobile-menu.active {');
css = css.replace(/\.page-home #mobile-menu-overlay\.active,\n\.page-home #mobile-menu-overlay\.active {/g, '.page-home #mobile-menu-overlay.active {');

fs.writeFileSync('assets/css/components/landing-p0-fix.css', css);
