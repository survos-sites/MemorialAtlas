# Review

Checked against SPEC.md with `hyperframes check` (no errors) and snapshots at 1.5, 4, 7, 10, 10.5, 13,
18.5, 21, 26.5, 33, 37, 47.5, 49.6, 50.2, 53.2, 57, 63.2, 66, 70.5, 75, 80.5 and 88.5 s.

Fixed after the first pass:

- Hard photos and the cleanup pair rendered without images (zero-size wrappers).
- The montage ring was a thin strip; cards are now 580 x 436 on a 1300 px radius, 13 photos.
- Decide gates wrapped each probability under its bar; bars are now 190 px.
- Captions on screen shorter than their reading time; copy shortened and Decide retimed.
- Lamp labels under the 28 px floor; now 30 px.
- A 3.6 s dead stretch in the cleanup pair; the OCR boxes now draw on the phone photo there.
- "MEMORIALATLAS" in caps read as one word; the name is now set in its own mixed case.
- Photos with the photographer's shadow removed from the montage, the hard photos and the race
  (Kovach, Mello, Wiersberg, Mulcahy, Cameron and others). Trinkle is cropped to its right side.

Facts on screen and their source:

- OCR boxes and texts: Mistral OCR result for Hamilton (media 84), pages[0].blocks, frame 1600 x 901.
- Gate values: Jev answers for Hamilton (gravestone 0.86, subject 0.84, damaged 0.75, angled 0.89).
- Race routes: Trinkle text route + cleaned, Goodenough vision + kept (angled 0.69), Beglis vision + cleaned.
- Tally: 54 processed photos at render time, 8 text route, 46 vision route, 39 cleaned views.
- MCP lines: real search_memorials("hamilton") and get_memorial(84) output.

Sound: 59 bundled effects (no credits needed), each placed so its measured peak lands one frame after
its action; no music bed (HeyGen not signed in). Measured, not heard.

Open: re-render the tally when processing finishes; add a music bed after `npx hyperframes auth login`.
