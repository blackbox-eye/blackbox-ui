# Design Guidelines - Blackbox EYE™

## Overview

These guidelines ensure visual consistency across the Blackbox EYE platform, creating a premium, high-end aesthetic inspired by B&O and Apple design principles.

## Design Philosophy

### Core Principles

1. **Refined Elegance**: Subtle, sophisticated design over flashy elements
2. **Glass Morphism**: Transparent layers with blur effects for depth
3. **Gold Accents**: Strategic use of metallic gold for premium feel
4. **Dark Foundation**: Dark theme as primary, light theme as alternative
5. **Minimal Friction**: Clear hierarchy and intuitive interactions

## Visual Identity

### Color Strategy

#### Gold as Accent, Not Dominant
- Use gold strategically for highlights, borders, and text accents
- Avoid solid gold backgrounds that overwhelm
- Prefer transparent backgrounds with gold outlines

#### Dark Theme (Primary)
```
Background: Deep charcoal (#0A1217, #101419)
Surface: Semi-transparent gray layers
Text: Off-white (#EAEAEA) with medium emphasis (#B0B8C6)
Accent: Refined gold gradient (#D4AF37 → #B8860B)
```

#### Light Theme (Secondary)
```
Background: Soft white/gray (#F7F9FC, #F3F6FB)
Surface: White with subtle shadows
Text: Dark gray (#1F2933) with medium emphasis (#4B5563)
Accent: Darker gold/copper (#B8860B → #8B6914)
```

### Typography Hierarchy

#### Headings
- **H1**: 2.5rem–3.75rem, font-weight: 700-900, gradient or solid
- **H2**: 2rem–3rem, font-weight: 600-700
- **H3**: 1.5rem–2rem, font-weight: 600
- **H4**: 1.25rem, font-weight: 600

#### Body Text
- **Large**: 1.125rem, line-height: 1.75
- **Base**: 1rem, line-height: 1.5
- **Small**: 0.875rem, line-height: 1.5
- **Tiny**: 0.75rem, line-height: 1.5, uppercase for labels

### Spacing System

Use consistent spacing multiples of 0.25rem (4px):

```
xs:  0.25rem (4px)
sm:  0.5rem  (8px)
md:  1rem    (16px)
lg:  1.5rem  (24px)
xl:  2rem    (32px)
2xl: 3rem    (48px)
3xl: 4rem    (64px)
4xl: 5rem    (80px)
```

## Component Patterns

### Navigation Header

#### Desktop Layout
```
┌─────────────────────────────────────────────────┐
│ [Logo]  [About] [Products] [Cases] [Pricing]   │
│         [Contact] [More▾]                       │
│                                                 │
│         [Book Demo] [Free Check] [Agent Login] │
│         [DA|EN] [Theme] [☰]                     │
└─────────────────────────────────────────────────┘
```

- Logo: Fixed left
- Nav links: Center-aligned pills
- CTAs: Right-aligned, distinct styles
- Theme toggle: Icon-based
- Language switcher: Compact pills

#### Mobile Layout
- Hamburger menu (right-aligned)
- Logo (left-aligned)
- Drawer navigation from right
- Full-width CTAs in drawer

### Button Hierarchy

#### Primary CTA (Book Demo)
- Glass background with gold border
- Gold text
- Prominent but not overwhelming
- Used for main conversion actions

```css
Background: rgba(212, 175, 55, 0.08)
Border: 1px solid rgba(212, 175, 55, 0.4)
Color: #D4AF37
Hover: Brighter gold (#F4D03F), stronger border
```

#### Secondary CTA (Free Check)
- More subtle glass effect
- Light border with gold tint
- White/light text
- Used for secondary actions

```css
Background: rgba(255, 255, 255, 0.04)
Border: 1px solid rgba(212, 175, 55, 0.25)
Color: #EAEAEA
Hover: Gold tint, stronger border
```

#### Tertiary (Agent Login)
- Minimal glass effect
- Thin border
- Text-focused
- Used for utility actions

```css
Background: transparent
Border: 1px solid rgba(255, 255, 255, 0.18)
Color: #EAEAEA
Hover: Fill with gold, dark text
```

### Hero Sections

#### Structure
```
[Eyebrow Label]     ← Small, gold, uppercase
[Large Heading]     ← Gradient text, bold
[Description]       ← Medium gray, readable size
[CTA Buttons]       ← Primary + Secondary combo
```

#### Gold Eyebrow Labels
```css
font-size: 0.75rem–0.875rem
letter-spacing: 0.2em–0.35em
text-transform: uppercase
color: gold accent (dark: #D4AF37, light: #B8860B)
margin-bottom: 1rem
```

#### Gradient Headings
```css
background: linear-gradient(135deg, 
  #D4AF37 0%, 
  #F4D03F 50%, 
  #B8860B 100%
);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
```

### Card Components

#### Glass Card Base
```css
background: rgba(255, 255, 255, 0.03);
border: 1px solid rgba(255, 255, 255, 0.08);
border-radius: 1rem;
padding: 1.5rem–2rem;
backdrop-filter: blur(12px);
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
```

#### Card Hover State
```css
transform: translateY(-4px);
box-shadow: 0 12px 48px rgba(0, 0, 0, 0.4),
            0 0 0 1px rgba(212, 175, 55, 0.2);
transition: all 0.3s ease;
```

### Form Elements

#### Input Fields (Glass Style)
```css
background: rgba(15, 23, 42, 0.6);
border: 1px solid rgba(255, 255, 255, 0.12);
border-radius: 0.5rem;
padding: 0.75rem 1rem;
color: #F8FAFC;
backdrop-filter: blur(8px);
```

#### Focus State
```css
outline: none;
border-color: var(--primary-accent);
box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.15);
```

#### Error State
```css
border-color: #EF4444;
box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
```

### Badges & Labels

#### Status Badges
```css
/* Success */
background: rgba(34, 197, 94, 0.15);
color: #4ADE80;
border: 1px solid rgba(34, 197, 94, 0.3);

/* Warning */
background: rgba(245, 158, 11, 0.15);
color: #FCD34D;
border: 1px solid rgba(245, 158, 11, 0.3);

/* Critical */
background: rgba(239, 68, 68, 0.15);
color: #FCA5A5;
border: 1px solid rgba(239, 68, 68, 0.3);
```

## Interaction Design

### Hover Effects

#### Buttons
- Lift: `translateY(-2px)`
- Brighten colors slightly
- Increase border opacity
- Add glow shadow
- Timing: 0.24s cubic-bezier

#### Cards
- Lift: `translateY(-4px)`
- Strengthen shadows
- Add gold border glow
- Timing: 0.3s ease

#### Links
- Color shift to gold
- Underline animation (left to right)
- No background change

### Focus States

All interactive elements need clear focus indicators for keyboard navigation:

```css
:focus-visible {
  outline: 2px solid var(--primary-accent);
  outline-offset: 2px;
  border-radius: inherit;
}
```

### Loading States

#### Button Loading
```css
opacity: 0.6;
cursor: wait;
/* Show spinner or pulse animation */
```

#### Skeleton Loading
Use subtle pulse animation with glass-effect placeholder:
```css
background: linear-gradient(
  90deg,
  rgba(255, 255, 255, 0.03) 25%,
  rgba(255, 255, 255, 0.06) 50%,
  rgba(255, 255, 255, 0.03) 75%
);
animation: shimmer 2s infinite;
```

## Responsive Design

### Breakpoint Strategy

#### Mobile First Approach
1. Design for 375px first (iPhone SE)
2. Scale up to tablet (768px)
3. Optimize for desktop (1024px+)
4. Cap at max-width (1280px)

### Mobile Adaptations

#### Navigation
- Hide desktop nav links
- Show hamburger menu
- Full-screen drawer from right
- Larger touch targets (min 48px)

#### Typography
- Scale down headings (clamp values)
- Increase line-height for readability
- Reduce letter-spacing

#### Spacing
- Reduce padding/margins by 25-50%
- Stack elements vertically
- Full-width CTAs

#### Forms
- Full-width inputs
- Larger tap targets
- Simplified layouts

## Performance

### CSS Optimization
- Use minified CSS in production
- Leverage CSS custom properties
- Minimize specificity conflicts
- Use efficient selectors

### Images
- Use WebP with fallbacks
- Lazy load below-fold images
- Optimize SVGs (remove metadata)
- Use appropriate sizes (srcset)

### Animations
- Use transform and opacity (GPU-accelerated)
- Avoid animating layout properties
- Use will-change sparingly
- Respect prefers-reduced-motion

## Accessibility

### WCAG 2.1 AA Compliance

#### Color Contrast
- Body text: Minimum 4.5:1
- Large text: Minimum 3:1
- Gold on dark: 7.2:1 (AAA)
- Gold on light: 4.8:1 (AA)

#### Keyboard Navigation
- All interactive elements focusable
- Logical tab order
- Skip links for main content
- Focus indicators always visible

#### Screen Readers
- Semantic HTML structure
- ARIA labels where needed
- Alt text for images
- Form labels properly associated

#### Motion
- Respect prefers-reduced-motion
- Provide alternatives to animations
- Keep animations under 5 seconds

## Testing Checklist

Before deploying design changes:

- [ ] Test in Chrome, Firefox, Safari, Edge
- [ ] Test on iOS Safari and Chrome
- [ ] Test at breakpoints: 375px, 768px, 1024px, 1440px
- [ ] Verify color contrast ratios
- [ ] Test keyboard navigation
- [ ] Test with screen reader
- [ ] Validate HTML and CSS
- [ ] Run Lighthouse audit
- [ ] Check loading performance
- [ ] Verify dark and light themes
- [ ] Test hover/focus states
- [ ] Confirm touch targets are 48px+

## Common Mistakes to Avoid

❌ **Using bright yellow instead of refined gold**
- Wrong: `#FFFF00`, `#FFC700`
- Right: `#D4AF37`, `#B8860B`

❌ **Solid backgrounds on buttons**
- Wrong: `background: #D4AF37;`
- Right: `background: rgba(212, 175, 55, 0.08);`

❌ **Hard shadows without blur**
- Wrong: `box-shadow: 0 4px 0 black;`
- Right: `box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);`

❌ **Square corners on buttons**
- Wrong: `border-radius: 4px;`
- Right: `border-radius: 999px;` (pills)

❌ **Inconsistent spacing**
- Wrong: Random px values (13px, 17px)
- Right: System multiples (0.5rem, 1rem, 1.5rem)

❌ **Poor color contrast**
- Wrong: Gray text on gray background
- Right: Check contrast ratio tool (minimum 4.5:1)

## Resources

### Design Tools
- Figma (mockups and prototypes)
- ColorSpace (palette generation)
- WebAIM Contrast Checker
- Tailwind CSS (utility framework)

### Inspiration
- Bang & Olufsen (premium aesthetics)
- Apple (refined minimalism)
- Stripe (clean interfaces)
- Linear (modern glass morphism)

### Code Standards
- BEM naming convention for custom CSS
- Utility-first with Tailwind
- CSS custom properties for theming
- PostCSS for processing
