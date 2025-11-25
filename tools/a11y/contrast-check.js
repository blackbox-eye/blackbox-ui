/**
 * Simple contrast verification for defined design tokens.
 * WCAG 2.1 contrast ratios: normal text AA >= 4.5, large text (>=24px regular or >=18.66px bold) AA >= 3.0.
 */

function hexToRgb(hex) {
  hex = hex.replace('#','');
  if (hex.length === 3) hex = hex.split('').map(c=>c+c).join('');
  const num = parseInt(hex,16);
  return { r:(num>>16)&255, g:(num>>8)&255, b:num&255 };
}
function srgbToLinear(c){ c/=255; return c<=0.03928? c/12.92 : Math.pow((c+0.055)/1.055,2.4); }
function luminance(hex){ const {r,g,b}=hexToRgb(hex); return 0.2126*srgbToLinear(r)+0.7152*srgbToLinear(g)+0.0722*srgbToLinear(b); }
function contrastRatio(fg,bg){ const L1=luminance(fg); const L2=luminance(bg); const lighter=Math.max(L1,L2); const darker=Math.min(L1,L2); return (lighter+0.05)/(darker+0.05); }

const colors = {
  bg: '#101419',
  glassApprox: '#161C27', // approximate solid version of rgba(22,28,39,0.6)
  primaryAccent: '#FFC700',
  textHigh: '#EAEAEA',
  textMedium: '#B0B8C6',
  gray700: '#374151',
  gray400: '#9ca3af'
};

const pairs = [
  { fg: 'textHigh', bg: 'bg' },
  { fg: 'textMedium', bg: 'bg' },
  { fg: 'primaryAccent', bg: 'bg' },
  { fg: 'textHigh', bg: 'glassApprox' },
  { fg: 'textMedium', bg: 'glassApprox' },
  { fg: 'primaryAccent', bg: 'glassApprox' },
  { fg: 'bg', bg: 'primaryAccent' }, // dark text on accent background (buttons)
  { fg: 'gray400', bg: 'bg' },
];

const results = pairs.map(p => {
  const ratio = contrastRatio(colors[p.fg], colors[p.bg]);
  return { ...p, ratio: parseFloat(ratio.toFixed(2)) };
});

function classify(ratio){
  if (ratio >= 7) return 'AAA Text';
  if (ratio >= 4.5) return 'AA Normal';
  if (ratio >= 3) return 'AA Large Only';
  return 'Fail';
}

console.log('\nContrast Audit (WCAG 2.1)');
console.log('--------------------------------');
results.forEach(r => {
  console.log(`${r.fg} on ${r.bg} => ${r.ratio}: ${classify(r.ratio)}`);
});

const failing = results.filter(r => r.ratio < 3);
if (failing.length){
  console.log('\nRecommendations:');
  failing.forEach(f => {
    if (f.fg === 'primaryAccent' && (f.bg === 'glassApprox' || f.bg === 'bg')) {
      console.log('- Consider darkening accent (#e0b000) or adding outline for small text usage.');
    } else if (f.fg === 'textMedium') {
      console.log('- Increase contrast of medium emphasis text (lighten toward #D0D6E0 or use font-weight 500).');
    }
  });
} else {
  console.log('\nAll tested pairs meet at least AA Large; review medium-emphasis for small sizes manually.');
}

