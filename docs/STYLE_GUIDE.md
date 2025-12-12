# Blackbox EYE™ Style Guide

## Color Palette

### Gold/Copper Design System

Blackbox EYE uses a refined gold and copper palette to convey premium quality and high-end security positioning.

#### Dark Theme Colors

```css
/* Primary Gold Palette */
--primary-accent: #FFC700;           /* Primary gold - buttons, highlights */
--gold-gradient-start: #D4AF37;      /* Rich gold - gradient start */
--gold-gradient-mid: #B8860B;        /* Dark gold - gradient middle */
--gold-gradient-end: #8B6914;        /* Deep gold - gradient end */
--gold-shine: #F4D03F;               /* Bright gold - hover states */
--gold-shadow: #5C4800;              /* Shadow gold - depth */
--gold-highlight: #FFE066;           /* Light gold - accents */
```

#### Light Theme Colors

```css
/* Copper/Bronze Palette */
--primary-accent: #B8860B;           /* Dark gold/copper base */
--gold-gradient-start: #D4AF37;      /* Warm gold */
--gold-gradient-mid: #B8860B;        /* Rich copper */
--gold-gradient-end: #8B6914;        /* Deep bronze */
```

### Text Colors

```css
/* Dark Theme */
--text-high-emphasis: #EAEAEA;       /* Primary text */
--text-medium-emphasis: #B0B8C6;     /* Secondary text */
--muted: #9CA3AF;                    /* Muted text */

/* Light Theme */
--text-high-emphasis: #1F2933;       /* Primary text */
--text-medium-emphasis: #4B5563;     /* Secondary text */
--muted: #6B7280;                    /* Muted text */
```

## Component Design Principles

### Glass Morphism

All interactive components use a glass-morphic design with:
- Semi-transparent backgrounds (`rgba()` with 0.04-0.15 opacity)
- Backdrop blur effects (`backdrop-filter: blur(8px)`)
- Subtle borders with gold accents
- Soft shadows for depth

### Button Styles

#### Primary CTA (Glass Gold)
```css
.header-cta--primary {
  background: rgba(212, 175, 55, 0.08);
  color: #D4AF37;
  border: 1px solid rgba(212, 175, 55, 0.4);
  backdrop-filter: blur(8px);
}
```

#### Secondary CTA (Glass Outline)
```css
.header-cta--secondary {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(212, 175, 55, 0.25);
  backdrop-filter: blur(8px);
}
```

#### Agent Login (Minimal)
```css
.header-cta.agent-login-cta {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: var(--text-high-emphasis);
}
```

### Navigation Components

#### Nav Chips (Pill Design)
```css
.nav-chip {
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.05);
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: 600;
  backdrop-filter: blur(8px);
}
```

### Form Elements

#### Input Fields
```css
input:focus {
  outline: none;
  ring: 2px solid var(--primary-accent);
  border-color: var(--primary-accent);
}
```

### Hero Sections

#### Gradient Text
```css
.hero-gradient-text {
  background: linear-gradient(
    135deg,
    #D4AF37 0%,
    #F4D03F 50%,
    #B8860B 100%
  );
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
```

## Typography

### Font Families
- Primary: Inter (300, 400, 500, 600, 700, 900)
- Display: Chakra Petch (700)

### Font Sizes
- Hero Heading: `clamp(2.5rem, 5vw, 4rem)`
- H1: `2.5rem - 3.75rem`
- H2: `2rem - 3rem`
- H3: `1.5rem - 2rem`
- Body: `1rem`
- Small: `0.875rem`
- Tiny: `0.75rem`

## Spacing

### Container Widths
- Max width: `1280px`
- Padding: `1rem` (mobile), `2rem` (desktop)

### Section Padding
- Mobile: `4rem 0`
- Desktop: `5rem 0`

### Component Gaps
- Small: `0.5rem`
- Medium: `1rem`
- Large: `2rem`

## Border Radius

- Pills/Buttons: `999px` (full rounded)
- Cards: `0.75rem - 1rem`
- Inputs: `0.5rem`
- Small elements: `0.25rem`

## Shadows

### Glass Shadow
```css
box-shadow: 
  0 8px 32px rgba(0, 0, 0, 0.3),
  inset 0 1px 0 rgba(255, 255, 255, 0.05);
```

### Gold Glow
```css
box-shadow: 0 0 24px rgba(212, 175, 55, 0.25);
```

## Animation & Transitions

### Standard Transitions
```css
transition: all 0.3s ease;
```

### Hover Effects
- Transform: `translateY(-2px)`
- Timing: `0.24s cubic-bezier(0.21, 0.83, 0.36, 0.99)`

## Accessibility

### Focus States
All interactive elements must have visible focus indicators:
```css
:focus-visible {
  outline: 2px solid var(--primary-accent);
  outline-offset: 2px;
}
```

### Color Contrast
- Text on dark backgrounds: WCAG AA minimum 4.5:1
- Text on light backgrounds: WCAG AA minimum 4.5:1
- Gold on dark: Passes AAA (7.2:1)
- Gold on light: Passes AA (4.8:1)

## Responsive Breakpoints

```css
/* Mobile First */
@media (min-width: 640px)  { /* sm */ }
@media (min-width: 768px)  { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
@media (min-width: 1536px) { /* 2xl */ }
```

## Usage Examples

### Creating a Gold Button
```html
<a href="#" class="header-cta header-cta--primary">
  <span class="header-cta__label">Book Demo</span>
</a>
```

### Creating a Glass Card
```html
<div class="glass-effect rounded-xl p-6">
  <!-- Content -->
</div>
```

### Gold Hero Text
```html
<h1 class="hero-gradient-text">
  Premium Security Platform
</h1>
```

## Don't Use

❌ Bright yellow (#FFFF00, #FFC700 on light backgrounds)  
❌ Pure amber Tailwind classes (amber-400, yellow-400)  
❌ Solid color backgrounds on buttons  
❌ Hard shadows without blur  
❌ Non-rounded corners on buttons  

## Do Use

✅ Refined gold tones (#D4AF37, #B8860B)  
✅ Glass-morphic designs with backdrop blur  
✅ Outline/border buttons with transparent fills  
✅ Soft shadows with blur radius  
✅ Full rounded pill shapes (border-radius: 999px)  
✅ CSS custom properties for colors  

## Brand Voice

The visual design should communicate:
- **Premium**: High-end, refined aesthetics like B&O or Apple
- **Security**: Professional, trustworthy, authoritative
- **Intelligence**: Modern, AI-driven, sophisticated
- **Readiness**: Alert, operational, always-on

Avoid:
- Flashy or gaudy designs
- Overly bright or saturated colors
- Cluttered layouts
- Generic corporate styling
