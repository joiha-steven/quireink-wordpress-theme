/**
 * The bytes that are copied rather than derived: the self-hosted faces, and Quire Ink's own
 * reader bundles.
 *
 * Split out of `tools/extract.ts` when that file passed the 400-line ceiling. The seam is the
 * one the file actually has: everything left there runs an emitter, mirrors a sheet or builds
 * a table, and this moves files across from the sibling checkout without changing them.
 */
import { mkdir, readdir, copyFile, rm } from 'node:fs/promises'
import { join } from 'node:path'

/**
 * @param quire     the Quire Ink checkout to read from — ABSOLUTE or relative, never cd'd into
 * @param theme     where the theme's files go; `.tmp/...` when check:generated is driving
 * @param intoTheme false when writing a throwaway copy, which has no licence file to keep
 */
export async function copyAssets(quire: string, theme: string, intoTheme: boolean) {
  const QUIRE = quire
  const THEME = theme

  // ----- the faces

  const FONT_SRC = join(QUIRE, 'src', 'assets', 'static', 'fonts')
  const FONT_DST = join(THEME, 'assets', 'fonts')
  await mkdir(FONT_DST, { recursive: true })
  // ONLY THE FACES ARE SWEPT. This was `rm(FONT_DST, {recursive:true})`, which also took
  // `OFL.txt` with it on every run - and did, silently, in a commit about picture frames. The
  // released 0.1.3 zip ships six OFL families with no copy of their licence in it, which the
  // licence itself requires and which no check here could see, because the file was nobody's
  // output. Dropping a face the engine no longer ships is what the sweep is for.
  for (const f of await readdir(FONT_DST)) {
  if (f.endsWith('.woff2')) await rm(join(FONT_DST, f))
  }
  const faces = (await readdir(FONT_SRC)).filter((f) => f.endsWith('.woff2'))
  for (const f of faces) await copyFile(join(FONT_SRC, f), join(FONT_DST, f))

  // And the licence has to still be there afterwards. It is not generated, so nothing else in
  // this file would notice its absence; this is the only place that can.
  if (intoTheme && !(await Bun.file(join(FONT_DST, 'OFL.txt')).exists())) {
  throw new Error(
    'quire-ink/assets/fonts/OFL.txt is missing.\n'
    + `  ${faces.length} OFL faces are about to ship without the licence text the OFL\n`
    + '  requires to travel with them. Restore it before committing this extract.',
  )
  }

  // ----- the islands

  // Quire Ink's own reader bundles, built by its `build:assets`. `core` is the chrome (palette
  // switch, rail, search overlay, back-to-top, newsletter) and `post` is the article (table of
  // contents scrollspy, book mode, lightbox, quote copy, resume). They are copied rather than
  // rewritten, which is the whole point — a second implementation of book mode would be a
  // second thing to be wrong.
  //
  // Two of core's features talk to endpoints WordPress does not have (the newsletter form and
  // the comment thread post to Quire Ink's API). They fail the way any fetch to a 404 fails,
  // which is quietly; wiring them to WordPress is a decision for later and is written up in
  // docs/gaps.md rather than patched in here.
  const JS_SRC = join(QUIRE, 'src', 'assets', 'dist')
  const JS_DST = join(THEME, 'assets', 'js')
  await mkdir(JS_DST, { recursive: true })
  // `book-mode.js` is the third because the engine split it out of post.js on 2026-09-06 and
  // this list did not follow (docs/gaps.md). The other two island bundles are not wanted:
  // comment-thread never mounts here, and the pen is ADR 0010.
  const bundles = ['core.js', 'post.js', 'book-mode.js']
  for (const b of bundles) await copyFile(join(JS_SRC, b), join(JS_DST, b))

  return { faces, bundles }
}
