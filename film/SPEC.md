GOAL:    a juror understands in 90 s that MemorialAtlas turns messy phone photos of graves into
         reviewed records with three tools (Read, Decide, Clean up), and that the photo stays the evidence.
OUTPUT:  HyperFrames, 1920 x 1080, 30 fps
TIME:    90 s x 30 = 2700 frames. No music yet (HeyGen not signed in); sound effects only, from the bundle.
LOOK:    ground: polished dark granite #1b2220 with white and grey speckle (assets/granite.jpg), never flat.
         ink: carved-letter white #ece9e2. accent: brass #d2a24a, only on the signal, a winning probability
         and the end card. OCR boxes: #f2c200 (the site's colour), 3 px stroke.
         display: Playfair Display 700, caps, tracking +4%, 104-140 px (carved Roman capitals on stones).
         body: Inter 700, 40-56 px. numbers: JetBrains Mono 700, 44-72 px (the machine's readout).
         photos: full bleed or at least 60% of the frame height, 6 px radius, shadow 0 18px 40px rgba(0,0,0,.45).
OBJECTS: phone photos (real files in assets/photos), the OCR box (yellow rectangle on the real block
         coordinates), a gate (a brass-rimmed slot with a question and a probability bar), a wire
         (an engraved groove 6 px wide, #0f1413 with a 1 px #3a4541 edge) that the brass signal runs
         along, the record card (the site's real item page screenshot).
MOTION:  enter: expo.out 18-24 frames. move: power2.inOut 15-18 frames. exit: power3.in 9-12 frames.
         carved title: clip-path inset wipe left to right in 18 frames with blur 6 px -> 0.
         signal: a 26 px brass bead travelling a wire at 900 px/s, linear.
         bars: width 0 -> p over 15 frames, power3.out; the number counts up on the same tween.
SHOTS:   0-6     Hamilton photo full bleed, slow push 1.00 -> 1.06; OCR box lands on "Hamilton" at 0.6 s;
                 title "A photo of a grave" then "is not a record yet".
         6-15    ring of 22 real photos rotating (rotateY, 24 deg/s); counter 0 -> 111; lines "111 photos",
                 "One cold, windy afternoon", "St. Peter's churchyard".
         15-23   five hard photos snap in tilted, each with its problem in brass: tilted, worn, flat in the
                 grass, at an angle, upside down. Line: "Who is buried here?"
         23-28   MemorialAtlas carved in; three slots below: Read · Decide · Clean up.
         28-42   READ. Hamilton photo at 1440 px wide; 12 real Mistral OCR boxes draw in order; their text
                 flies into a list; 6 neighbour-stone lines (VAILLANT, PUBLISHED, 2017年1月1日...) turn grey.
         42-60   DECIDE. Jev machine: 4 gates (Gravestone? 0.86, Main subject? 0.84, OCR usable? damaged 0.75,
                 Camera square? angled 0.89); the signal routes Hamilton to the vision route and to cleanup.
                 Then Trinkle takes the text route, Cameron and Beglis go to vision and cleanup. Real counts:
                 37 vision, 2 text, 30 cleaned of 39 processed.
         60-72   CLEAN UP. original -> brass scan line sweeps -> Gemini cleaned view; label "AI-cleaned view.
                 For display only. OCR never reads it."
         72-84   the real item page screenshot slides in; then an MCP call search_memorials("hamilton") and
                 its real answer typed line by line.
         84-90   end card: MemorialAtlas carved, "Built with Symfony AI and the PHP MCP SDK", memorialatlas.org.
BANNED:  black void with small floating things; glow, bloom, particles; purple/blue gradients, glass cards;
         cream paper with serif and mono caps labels; pill chips; three cards in a row; everything fading up;
         titles that drift after landing; corner labels, HUD brackets; the logo docked in a corner;
         any photo of a person; any photo with the photographer's shadow; the drone.
QA:      2.0 s OCR box on Hamilton; 10 s ring readable; 20 s problem labels; 35 s boxes align with letters;
         50 s gate 3 switch visible with 0.75; 66 s before/after split; 78 s site screenshot legible; 88 s end card.
