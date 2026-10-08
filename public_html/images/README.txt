CHACO HORIZON — IMAGE SLOTS
Easiest route: drop ONE original per slot into scripts/assets/photos/ named
<slot>.jpg (or .png/.webp) and run  node scripts/photos.mjs  — it writes every
.jpg and .webp width listed below into this folder and reports any source
that is too small for its slot. Or drop files here by hand using these exact
names. Until a file exists its slot shows a labelled placeholder — nothing breaks.

FILLED (sources in scripts/assets/photos/; Pexels = free for commercial use, no attribution)
  hero-dubai-port    aerial container ship        Pexels video 30523151 frame, 1920 px — poster for the hero loop
  band-solar-farm    ship under tow by tugs       Pexels video 3840442 frame, 1920 px — poster for the band loop
  NOTE: these two are stills pulled from the background videos so the poster
  matches the footage exactly. Widths are 1200/1920 and 1600/1920 — the old
  2000/2800 files were deleted. Regenerate with scripts/video.mjs + ffmpeg if
  the clips change.
  cta-port-dusk      crane loading                user-supplied    1024 px — acceptable
  supply-food        Dubai spice souk             Pexels 16412108  3597 px — good (2026-09-21)
  supply-panels      module with barcode          user-supplied     800 px — acceptable
  supply-auto        new-tyre warehouse racks     Pexels 3806252   5760 px — good (2026-09-21)
  supply-general     pallet warehouse, forklift   Pexels 35665496  2268 px — good (2026-09-21)
  about-warehouse    forklift, pallets            user-supplied    1024 px — acceptable
  about-inspection   dates / raisins / apricots   Pexels 38806965  3448 px — good (2026-09-21)
  Still empty: contact-map, team/person-1.jpg, docs/*, the per-page mastheads (all reuse hero-dubai-port).

PRIORITY: CREDIBILITY SHOTS (the company's own — not stock)
  A trading company sells on verifiability. These matter more than the
  atmosphere shots below and should be photographed, not downloaded:
  - office exterior with the company signage           -> about-mast, cred-mast, contact-mast
  - the consolidation warehouse actually used in Dubai  -> about-warehouse, faq-mast
  - the desk: 1–3 portraits of the people on orders     -> team/person-1.jpg, team/person-2.jpg (square, 400×400)
  - labelled goods being loaded / palletised            -> supply-general, band
  - product close-ups: spice sacks / dry-fruit trays,
    a module with its brand label, stacked tyre tubes   -> supply-food, supply-panels, supply-auto
  Product, warehouse and document photos display in FULL COLOUR. Only the
  large hero / masthead / band / CTA atmosphere shots get the bronze duotone.

HERO / MASTHEADS  (widths: 1600, 2400 — plus 1200 / 2000 / 2800 for the homepage hero)
  NOTE 2026-09-20: all six inner-page mastheads currently point at the
  hero-dubai-port-* set (shared, so it is cached across pages). To give a page
  its own photo, generate its slot below and switch the <picture> in that page
  (and the "image:" line in its frontmatter) back to the slot name.
  hero-dubai-port-{1200,2000,2800}.{jpg,webp}   Dubai skyline at dusk or Jebel Ali cranes
  about-mast-{1600,2400}.{jpg,webp}             Office exterior with signage
  supply-mast-{1600,2400}.{jpg,webp}            Rows of modules or a loaded warehouse
  process-mast-{1600,2400}.{jpg,webp}           Container terminal, crane, loading
  cred-mast-{1600,2400}.{jpg,webp}              Office exterior or documents on a desk
  faq-mast-{1600,2400}.{jpg,webp}               Warehouse aisle or containers
  contact-mast-{1600,2400}.{jpg,webp}           Office exterior with signage

CATEGORY PANELS  (widths: 800, 1400 — portrait-ish crop, FULL COLOUR)
  Used on the homepage track and the products-page category cards.
  supply-food-{800,1400}.{jpg,webp}             Sacks of whole spices or trays of dry fruits, close-up
  supply-panels-{800,1400}.{jpg,webp}           Solar module close-up, manufacturer label visible (DONE)
  supply-auto-{800,1400}.{jpg,webp}             Stacked tyre tubes / tyre workshop shelf, brand mark visible
  supply-general-{800,1400}.{jpg,webp}          Labelled goods being loaded / warehouse aisle ("sourcing on request")

ABOUT PAIR  (widths: 800, 1400, FULL COLOUR)
  about-warehouse-{800,1400}.{jpg,webp}         The warehouse interior, pallets (PORTRAIT)
  about-inspection-{800,1400}.{jpg,webp}        Hands inspecting a panel at a factory (LANDSCAPE)

TEAM  (square 400×400 .jpg only)
  team/person-1.jpg, team/person-2.jpg          Portraits for the "Who you'll deal with" block on about.html

BANDS  (widths: 1600, 2400 / 2800 — very wide crop)
  band-solar-farm-{1600,2800}.{jpg,webp}        Aerial solar farm or vessel at berth
  cta-port-dusk-{1600,2400}.{jpg,webp}          Port at dusk, gantry cranes

CONTACT  (widths: 800, 1400 — portrait, FULL COLOUR)
  contact-map-{800,1400}.{jpg,webp}             Colour photo of the building — or replace the
                                                <picture> in contact.html with the Google Maps
                                                <iframe> (see the comment in contact.html)

CERTIFICATE SCANS  (certifications.html — drop into images/docs/, full colour, ~1200x1600)
  docs/trade-licence.jpg          Trade licence 1653213 scan
  docs/certificate-2.jpg          Placeholder slot — e.g. Dubai Chamber or TRN certificate
  docs/certificate-3.jpg          Placeholder slot — e.g. a product / supplier certificate
Each opens full size in a new tab and has a download link. Rename the
placeholder slots (and their captions in certifications.html) to the real
document, and add more <figure> blocks for extra documents as they come in.

ALREADY PRESENT
  og-logo.jpg (1200×630 share card) and logo/ (header mark, curtain mark,
  footer wordmark in bronze and in tan) are generated by scripts/logo.mjs.

STOCK SOURCES for the atmosphere shots only: unsplash.com and pexels.com —
free for commercial use, no attribution. Search: "solar farm aerial",
"Jebel Ali port", "shipping containers Dubai", "Dubai skyline dusk".

ADDED 2026-10-02 (Pexels = free for commercial use, no attribution; 3:2 crops at 800/1600, card 800/1400)
  detail-coffee      roasted beans in burlap sack      Pexels 9535503
  detail-tea         tea market stall                  Pexels 12944684
  detail-led         LED bulbs on wood                 Pexels 5853953
  detail-cars        new-car storage lot               Pexels 5992514
  detail-autoparts   brake disc in workshop            Pexels 34277923
  detail-garments    jeans on rack                     Pexels 7679454
  detail-glassware   glasses on wooden table           Pexels 10115519
  supply-consumer    folded shirts in retail store     Pexels 31155535

PRODUCT SHOWCASE CARDS (show-<product>-320/560 .jpg/.webp, 10:13 portrait, the scrolling section under the banner)
  Cut from the detail-* images above, plus:
  batteries          car battery in engine bay         Pexels 37177070
  inverters          wall inverter + battery stack     Pexels 37929911
  detail-batteries / detail-inverters (800/1200, 3:2) cut from the same two photos for the product cards
