# History — why the rules in CLAUDE.md exist

Not loaded automatically; `CLAUDE.md` points here. Read it when a rule looks arbitrary. No personal data.

## Verify

`check:all` proves the seams hold. The rail was empty, a figure was at the wrong width, and every headline on the
listing page picked up a link underline: all three passed every check and were obvious in a screenshot.

`kbd-probe.ts` answers what a picture cannot: an off-screen drawer is invisible and still focusable, and a control
under the admin bar looks exactly like one that is not there; both shipped. Its `--width` is honest below 500px,
unlike `shot.sh`, because it emulates the device rather than sizing a window.

`shot.sh` refuses two things, each because it once produced a picture that lied. Below 500px, a 390px request came
back as a 500px page cropped to 390, which reads as a theme that overflows. And a page whose scripts and stylesheets
point at another origin: WordPress writes asset URLs against `localhost`, so on `127.0.0.1` every module script and
font is lost to CORS and the type falls back to something close enough that nobody looks twice.

## Danger zones

- `localhost` vs `127.0.0.1`: scripts used to refuse too — they carried `type="module"`, which is fetched in CORS mode
  whatever the origin, and that is why no render in this project ran JavaScript for three months. They are plain
  deferred scripts since 0.1.4 and survive the wrong origin; the fonts still do not. `getComputedStyle` answers
  `Literata` either way, because a computed font-family is the request and not the result; `document.fonts` said
  `Literata:loaded` against `Literata:error`.
- Quire Ink read-only: six files went into the wrong repository once, and the symptom lies — `php -l` passes on the
  file it just wrote, `ls` from here says the file does not exist, and you go looking for a disk problem.
- Port 8088: Docker publishes on `0.0.0.0`, so nothing collides at bind time and the browser just resolves `localhost`
  to `::1` and shows the other site.
