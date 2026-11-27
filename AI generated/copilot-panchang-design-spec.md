# Copilot Instructions — Modern Panchang Page (Light + Dark themes)

Paste this whole file into Copilot Chat (or save as `.github/copilot-instructions.md` / `docs/panchang-design.md` and reference it with `#file`).

## 0. Goal

Re-skin the existing **Tamil Panchang screen/report** of my Vedic astrology app (Android WebView, HTML/CSS/JS) into a modern, "glass-card" design.
**Do not change data logic, calculations, or field names.** Only change markup structure + CSS. The same page must also print/export to **one A4 portrait page**.

Constraints:
- Must work in **Android WebView (Chromium)** and in print-to-PDF. No external CDN/network dependencies.
- All colours must come from **CSS custom properties named `--theme-*`** (my app already themes through a class like `.theme-rose-gold`). Create two new theme classes: `.theme-panchang-light` and `.theme-panchang-dark`. Never hard-code colours inside component rules.
- Reuse existing data bindings; if a binding is missing, leave a `TODO` instead of inventing data.
- Tamil text: use **Noto Sans Tamil** (bundled locally as a font file, `font-display: swap`) with fallback `'Mukta Malar', system-ui, sans-serif`. Set `lang="ta"` on the container. Do **not** add zero-width-character hacks or custom glyph tricks; a proper Tamil font shapes correctly.

## 1. Design tokens

Define on the theme classes. Light values first, dark in brackets.

```css
.theme-panchang-light {
  --theme-bg-1:#fffaf0; --theme-bg-2:#f6f1ff; --theme-bg-3:#eef6fb;          /* page gradient 170deg */
  --theme-glow-warm:rgba(255,170,110,.30);                                     /* top-right radial */
  --theme-glow-cool:rgba(150,130,255,.20);                                     /* left radial */
  --theme-glow-teal:rgba(60,200,220,.16);                                      /* bottom-right radial */
  --theme-card:rgba(255,255,255,.78);
  --theme-card-border:rgba(160,115,40,.30);
  --theme-card-shadow:0 1px 6px rgba(80,60,120,.10);
  --theme-gold:#b8791f;  --theme-gold-strong:#8a5a10;  --theme-title:#9a5b0f;
  --theme-text:#1d1b4b;  --theme-text-2:#4a4e7a;  --theme-muted:#6b6f93;
  --theme-bad:#d64545;   --theme-bad-bg:rgba(255,122,122,.13);
  --theme-good:#16a06a;  --theme-good-bg:rgba(110,231,168,.20);
  --theme-accent:#e0663a;
  --theme-row-alt:rgba(184,121,31,.06);
}
.theme-panchang-dark {
  --theme-bg-1:#0a0b26; --theme-bg-2:#141042; --theme-bg-3:#0b1233;
  --theme-glow-warm:rgba(255,138,91,.28); --theme-glow-cool:rgba(110,80,255,.30); --theme-glow-teal:rgba(40,200,220,.18);
  --theme-card:rgba(255,255,255,.055); --theme-card-border:rgba(232,193,112,.22); --theme-card-shadow:none;
  --theme-gold:#e8c170; --theme-gold-strong:#ffe3a3; --theme-title:#f6d98f;
  --theme-text:#eef0ff; --theme-text-2:#d6dbf5; --theme-muted:#9ea6cf;
  --theme-bad:#ff7a7a; --theme-bad-bg:rgba(255,122,122,.10);
  --theme-good:#6ee7a8; --theme-good-bg:rgba(110,231,168,.10);
  --theme-accent:#ff9a6b; --theme-row-alt:rgba(255,255,255,.03);
}
```

Planet colour tokens (light / dark), used for chart text and table dots:

| Planet | Tamil abbr | Light | Dark |
|---|---|---|---|
| Rahu | ரா | `#6b57e0` | `#9b8cff` |
| Ketu | கே | `#8a55d8` | `#b08cff` |
| Venus | சுக் | `#d6458f` | `#ff9ecb` |
| Jupiter | குரு | `#d99200` | `#ffd166` |
| Mars | செ | `#d63a3a` | `#ff6b6b` |
| Lagna | லக் | `#0a9db0` | `#5ce1e6` |
| Sun | சூரி | `#e06a00` | `#ff9a3c` |
| Saturn | சனி (வ) | `#3b63d9` | `#7aa2ff` |
| Moon | சந் | `#5b6280` | `#e8eefc` |
| Maandi | மா | `#7b8197` | `#c0c6d4` |
| Mercury | புத | `#14a06a` | `#6ee7a8` |

Expose as `--theme-planet-rahu`, `--theme-planet-ketu`, … etc.

Typography: base 8.6pt (print) / 14px (screen). Title 24pt weight 800 in `--theme-title`. Section titles 11pt/800 in `--theme-gold-strong`. Labels 6.8–7.2pt in `--theme-muted`. Values 9–10.6pt/800 in `--theme-text`.
Shape: card radius `3.4mm` (≈12px), small chips radius `2.4mm`, pills `20mm`. Page padding `7mm 9mm 5mm`, vertical gap between blocks `2.4mm`.

## 2. Page background

`.panchang-page` = A4 (210mm × 297mm, `overflow:hidden`, `position:relative`) with:

```css
background:
  radial-gradient(800px 500px at 88% -6%, var(--theme-glow-warm), transparent 60%),
  radial-gradient(700px 560px at -8% 32%, var(--theme-glow-cool), transparent 60%),
  radial-gradient(700px 500px at 100% 96%, var(--theme-glow-teal), transparent 60%),
  linear-gradient(170deg, var(--theme-bg-1) 0%, var(--theme-bg-2) 55%, var(--theme-bg-3) 100%);
```

Decor: an inline-SVG zodiac ring (concentric circles + 12 spokes, stroke `--theme-gold`, 0.4 stroke width, opacity .45) positioned top-right, `170mm` square, partly off-page (`right:-60mm; top:-62mm`), `pointer-events:none`, `z-index:0`. Dark theme only: ~90 tiny star dots (1–2px white circles, opacity .25–.8) absolutely positioned behind content.
All content sits in `.content` (`position:relative; z-index:2; display:flex; flex-direction:column; gap:2.4mm; height:100%`).

## 3. Layout — top to bottom (single column flex)

### 3.1 Header
Left: `பஞ்சாங்கம்` (h1, `--theme-title`) and tiny letter-spaced subtitle `DAILY PANCHANG · தினசரி பஞ்சாங்கம்`.
Right (right-aligned): brand `PNK ASTRO` (letter-spacing .14em; "ASTRO" in `--theme-accent`) and phone number below in `--theme-muted`. Keep these bound to existing app/brand fields.

### 3.2 Hero row — grid `1.05fr 1.5fr 1fr`, gap 3mm (three cards)
1. **Date card** (gold-tinted gradient background + stronger border): big day number (34pt/800, `--theme-title`), small label `தேதி`, full date `DD-MM-YYYY`, and a pill with weekday (`வெள்ளி`) — pill bg `--theme-gold`, text white/dark-contrast.
2. **Sun card**: inline SVG viewBox `0 0 300 80`: dotted quadratic arc `M20 68 Q150 -34 280 68` (gradient stroke `#ff9a6b → #ffd166 → #ff7a8a`), horizon line, glowing sun at arc apex (decorative), sunrise dot left / sunset dot right. Below: left `சூரிய உதயம்` + time, right `சூரிய அஸ்தமனம்` + time (times in `--theme-gold-strong`, 10.5pt/800), centered small text `பகல் HH:MM மணி` = (sunset − sunrise), computed in JS.
3. **Location card**: label `இடம்`, city (15pt/800, `--theme-gold-strong`), two lines with a small glowing dot bullet: `lat° N` and `lon° E`.

### 3.3 Section: `பஞ்சாங்க விவரங்கள்` — 4 equal cards (grid 4 cols, gap 2.6mm)
Cards: **திதி, நட்சத்திரம், யோகம், கரணம்**. Each has a header with a 4.2mm inline-SVG icon (moon-phase circle, star, sun-rays, half-circle) in `--theme-gold`, then **two entries** separated by a thin divider with a tiny `▼`:
- Entry 1 (running): name 10.6pt/800 `--theme-text`.
- Entry 2 (next): name 9.2pt/800 `--theme-text-2`.
- Under each name two rows: label `முதல்` / `வரை` (6.6pt muted, fixed 7mm width) + `DD-Mon h:mm AM/PM`.
Keep the original strings exactly (e.g. `திருதியை(கிரு)`, `திருவாதிரை (3)`).

### 3.4 Section: `நேர வரைபடம்` (NEW) — one card
- A pill-shaped **day timeline track** from 6 AM to 6 PM (720 min). Height 5.5mm, bg = horizontal gradient of warm/neutral/warm tints, 1px `--theme-card-border`.
- Absolutely-positioned **bands**: `left = (startMin-360)/720*100%`, `width = durationMin/720*100%`.
  - Bad (red gradient, `--theme-bad`): Rahu Kalam (`ராகு காலம்`), Yama Gandam (`எம கண்டம்`), Gulikai (`குளிகை`).
  - Good (green gradient, `--theme-good`): Abhijit (`அபிஜித்`).
- Tick labels under the track every 2 hours (6AM … 6PM), 6.2pt muted.
- Below the track: 4 equal **chips** (grid 4 cols): colour dot + small label + `h:mm AM – h:mm PM`. Chip tint = `--theme-bad-bg` / `--theme-good-bg` with matching 1px border.
- Write one pure function `timeToPercent(h, m)` and one `renderBands(items)`; parse times from the existing fields, don't hard-code.

### 3.5 Section: `தினசரி குறிப்புகள் மற்றும் நேரங்கள்` — 4-column grid of small cards
12 cells in this order (label small/muted, value bold): தற்போதைய ஹோரை, பஞ்ச பக்ஷி, அமிர்தாதி யோகம், நேத்திரம், ஜீவன், நட்சத்திர நோக்கு, லக்கினம், ராசி, அவயோகி, திதி சூன்யம், வார சூலை (value + second smaller line `பரிகாரம் - …`), சந்திராஷ்டமம்.
Cell style: `--theme-card` bg, 1px `--theme-card-border`, **2px left border `--theme-gold`**, radius 2.4mm, padding `1.2mm 2.8mm`.

### 3.6 `விவாஹ சக்ரம்` banner (full width, one row)
Title left (`--theme-gold-strong`), middle two lines: `சூரியன் நின்ற நட்சத்திரம் **X**` / `சந்திரன் நின்ற நட்சத்திரம் **Y**`, right a **status pill**. If the existing result is "not good": pill uses `--theme-bad` text/border + `--theme-bad-bg`, banner tinted red→card gradient. If good: same layout with `--theme-good`. Keep the original sentence text.

### 3.6 Section: `கிரக விவரங்கள்` — grid `70mm 1fr` (flex:1 to fill remaining height)
**Left card `தற்போதைய கோட்சாரம்`:** South-Indian **D1 chart as inline SVG** (viewBox 384×384, cell 96):
- Fixed sign grid: top row Meena, Mesha, Rishabha, Mithuna; right col Karka, Simha; bottom row Kanya, Tula, Vrischika, Dhanu (right→left); left col Kumbha, Makara. Centre 2×2 merged cell shows `ராசி` (gold, 24px/800) + `D1`, with two concentric circles (one dashed).
- Outer rect radius 14, stroke `--theme-gold` @ .55 opacity; cell strokes @ .28.
- Each planet printed as `abbr-deg°` (centered, stacked, 14.5px/700, colour = planet token). Show `தி.சு` (திதி சூன்யம்) in `--theme-bad` in the affected signs, `இந்` for Indu lagna.
- **Lagna cell**: tinted background (`--theme-planet-lagna` @ 10%) + small filled triangle in its top-left corner.
- Under the chart a legend: `தி.சு = திதி சூன்யம்` and `◤ லக்னம்`.
- Make `renderChart(planets, lagnaSign)` data-driven; use degrees from the existing planetary positions and **use the same degree/minute value as the table** (nodes must not differ between chart and table).

**Right card `கிரக சாரம்`:** table, columns `கிரகம் | நக்ஷ் பாதம் | தாரை | சூக்ஷ்ம தாரை | பாகை`.
- First column: 2mm coloured dot (planet token) + abbreviation (bold).
- Keep the **bold flags** from the existing data (e.g. `ப.பகை`, `ப.மைத்ர`, `சமு.பகை`) as `font-weight:800; color:--theme-title`.
- `பாகை` cell: `300°27'` then the in-sign value in muted smaller text `(0°27')`.
- Zebra rows with `--theme-row-alt`; header 6.8pt in `--theme-gold`, 1px bottom border; rows stretch to fill the card height (`table{flex:1}`).

### 3.7 Footer
One line: `PNK ASTRO · <CITY>` left, `DD-MM-YYYY · <weekday>` right, 6.6pt muted.

## 4. Print / PDF requirements

```css
@page { size: A4; margin: 0; }
html, body { width:210mm; height:297mm; }
body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
```
- Must fit **exactly one page**; if content overflows, reduce paddings/gaps first, never font below 6.2pt.
- No `backdrop-filter` in print mode (remove inside `@media print`).
- Avoid page breaks inside cards: `break-inside: avoid`.

## 5. Component structure to generate

```
.panchang-page.theme-panchang-light|dark
  svg.zodiac-ring
  .content
    header.pk-hdr
    .pk-hero  > .pk-card.date | .pk-card.sun | .pk-card.loc
    h2.pk-st  + .pk-limbs  > .pk-card.limb ×4
    h2.pk-st  + .pk-card.timeline > .track + .chips
    h2.pk-st  + .pk-dgrid  > .pk-dc ×12
    .pk-card.vivaha
    h2.pk-st  + .pk-grahas > .pk-card.chart + .pk-card.table
    footer.pk-foot
```
Prefix all new classes with `pk-` to avoid clashing with existing styles. Section titles (`.pk-st`): 11pt/800, with a 1.2mm × 4.4mm gold→accent vertical bar before and a fading hairline after.

## 6. Acceptance checklist

- [ ] Switching only the theme class (`.theme-panchang-light` ↔ `.theme-panchang-dark`) restyles everything; zero hard-coded colours in components.
- [ ] All Tamil labels/values identical to current data; nothing translated or reworded.
- [ ] Timeline bands positions computed from data (verify Rahu Kalam 10:30–11:55 starts at 37.5%).
- [ ] D1 chart and graha table show identical degrees.
- [ ] Renders correctly in Android WebView and exports to a single A4 page.
- [ ] Text contrast ≥ 4.5:1 for body text in both themes.
- [ ] No external requests; fonts bundled locally.

## 7. Reference

The target visual is the attached `panchang_20261127_light.pdf` (light) and `panchang_20261127_modern.pdf` (dark). Attach both to the Copilot chat as visual references, and ask Copilot to compare its screenshot against them before finishing.
