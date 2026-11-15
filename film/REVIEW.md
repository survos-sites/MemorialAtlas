# Review

Checked with `hyperframes check` (passes) and stills across every scene; the final render is
`renders/memorialatlas-90s-v8.mp4` (90 s, 1080p, −13.2 LUFS, peak −1.4 dBFS).

Structure: hook · montage · hard photos · reveal (Read · Notice · Remember) · 1 Read · 2 Notice
(decisions, a short alignment beat, carved symbols) · 3 Remember (one line, then the chat) · end card.

Facts on screen and their source:

- OCR boxes: Mistral OCR blocks for the Hamilton photo.
- Gate values: Jev answers for the Hamilton photo (gravestone 0.86, subject 0.84, damaged 0.75,
  needs alignment 0.89).
- Montage: 172 photos and 39 minutes come from the capture times (6 minutes on 11 November,
  33 on 13 November).
- Symbols: names stored by the symbol step (Mistral), each checked against the photo:
  polo player (Burdick), caduceus and Ohio State logo (Geneva), lacrosse sticks (Warzinski),
  scales of justice (Renzy), strawberry (Jarrett).
- Remember line: the stored `aiResults.remember` line for the White and Goodenough stone, after a
  logged human correction of the names (WHITE over James Felix; GOODENOUGH over Joan Frances White
  and Fred Wesley). Qwen on Hugging Face confirmed the reading against the photo.
- Chat: real tool calls and sentences from the Ask page answering "Which stones show hobbies or
  interests?".

Corrections made on the way, all logged on the records:

- Geneva: OCR read "PHYSICIAN, FAMILY, PHYSICIST"; the stone reads "PHYSICIAN, FATHER, HUSBAND".
- Violet Clare Fischer: the symbol step read "Violet" as a violin; removed.
- White and Goodenough: names were merged into "James Felix Goodenough White".

Rule: text on screen is copied from stored pipeline output and checked against the photo first.

Sound: 60-odd bundled effects plus a HeyGen catalog music bed, placed so its own fade-out lands on
the end card. Measured, not heard.
