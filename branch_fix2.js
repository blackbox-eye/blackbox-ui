const fs = require('fs');
let css = fs.readFileSync('assets/css/components/landing-p0-fix.css', 'utf8');

css += `
/* Force desktop nav hidden on mobile explicitly */
@media (max-width: 1023px) {
  .main-nav-list,
  .main-nav.header-nav {
    display: none !important;
  }
}
`;
fs.writeFileSync('assets/css/components/landing-p0-fix.css', css);

let css2 = fs.readFileSync('assets/css/custom-ui.css', 'utf8');
css2 += `
@media (max-width: 1023px) {
  .main-nav-list {
    display: none !important;
  }
}
`;
fs.writeFileSync('assets/css/custom-ui.css', css2);
