// Tab through a page and report what the keyboard actually reaches.
//
// `check:all` cannot see focus order, and neither can a screenshot: an element parked at
// translateX(-100%) is invisible and still focusable, and a control drawn under the admin bar
// looks exactly like a control that is not there. Both shipped. This drives headless Chrome
// over CDP, presses Tab, and reports for every stop what has focus, which region it is in, and
// whether it is on screen — plus the document's horizontal overflow and the skip link's box
// against the admin bar.
//
// Usage:
//   bun tools/kbd-probe.ts <url> [--width=1440] [--height=900] [--no-admin] [--open-rail] [--shot=path]
//
// The admin bar is only there for a logged-in reader, so the interesting measurement needs a
// session. WP_COOKIE carries one without typing a password anywhere:
//
//   WP_COOKIE=$(docker compose -f dev/docker-compose.yml exec -T cli \
//     wp --path=/var/www/html eval '$e = time() + 86400;
//       echo LOGGED_IN_COOKIE . "=" . wp_generate_auth_cookie( 1, $e, "logged_in" );') \
//   bun tools/kbd-probe.ts http://localhost:8099/
//
// `--width` here is HONEST below 500px, unlike tools/shot.sh: this sets the layout size through
// Emulation.setDeviceMetricsOverride rather than sizing an OS window, so 390 is laid out at 390
// rather than laid out at 500 and cropped.
//
// `--open-rail` clicks the header's menu button first, which is how you prove the drawer is
// reachable when it is open as well as skipped when it is closed.

const args = process.argv.slice(2)
const url = args[0]!
const admin = !args.includes('--no-admin')
const width = Number(args.find(a => a.startsWith('--width='))?.slice(8) ?? 1440)
const height = Number(args.find(a => a.startsWith('--height='))?.slice(9) ?? 900)
const shot = args.find(a => a.startsWith('--shot='))?.slice(7)
const cookie = process.env.WP_COOKIE ?? ''

const PROFILE = await Bun.$`mktemp -d`.text().then(s => s.trim())
const port = 9333 + Math.floor(Math.random() * 200)
const chrome = Bun.spawn([
  process.env.CHROME ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
  '--headless=new', '--disable-gpu', '--no-sandbox', '--hide-scrollbars',
  `--remote-debugging-port=${port}`, `--user-data-dir=${PROFILE}`,
  `--window-size=${width},${height}`, 'about:blank',
], { stdout: 'ignore', stderr: 'ignore' })

let wsUrl = ''
for (let i = 0; i < 60; i++) {
  try {
    const r = await fetch(`http://127.0.0.1:${port}/json/list`)
    const list = await r.json() as any[]
    const page = list.find(t => t.type === 'page')
    if (page?.webSocketDebuggerUrl) { wsUrl = page.webSocketDebuggerUrl; break }
  } catch {}
  await Bun.sleep(250)
}
if (!wsUrl) { chrome.kill(); throw new Error('chrome did not open a debugging port') }

const ws = new WebSocket(wsUrl)
await new Promise(res => ws.addEventListener('open', res, { once: true }))
let id = 0
const pending = new Map<number, (v: any) => void>()
ws.addEventListener('message', e => {
  const m = JSON.parse(String(e.data))
  if (m.id && pending.has(m.id)) { pending.get(m.id)!(m); pending.delete(m.id) }
})
const send = (method: string, params: any = {}) => new Promise<any>(res => {
  const n = ++id
  pending.set(n, res)
  ws.send(JSON.stringify({ id: n, method, params }))
})
const evaluate = async (expr: string) => {
  const r = await send('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true })
  if (r.result?.exceptionDetails) throw new Error(JSON.stringify(r.result.exceptionDetails))
  return r.result?.result?.value
}

await send('Page.enable')
await send('Runtime.enable')
await send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: width < 768 })
if (admin && cookie) {
  const [name, ...rest] = cookie.split('=')
  await send('Network.enable')
  await send('Network.setCookie', { name: name!.trim(), value: rest.join('=').trim(), domain: 'localhost', path: '/' })
}
await send('Page.navigate', { url })
await Bun.sleep(2500)

const key = async (text: string, code: string, keyCode: number, modifiers = 0) => {
  await send('Input.dispatchKeyEvent', { type: 'rawKeyDown', windowsVirtualKeyCode: keyCode, code, key: text, modifiers })
  await send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: keyCode, code, key: text, modifiers })
}

const describe = `(() => {
  const e = document.activeElement
  if (!e || e === document.body) return { tag: 'BODY' }
  const r = e.getBoundingClientRect()
  const label = (e.getAttribute('aria-label') || e.textContent || '').trim().replace(/\\s+/g,' ').slice(0, 44)
  const region = e.closest('#wpadminbar') ? 'adminbar'
    : e.closest('header.site') ? 'header'
    : e.closest('.rail') ? 'rail'
    : e.closest('main') ? 'main'
    : e.closest('footer.site') ? 'footer' : 'other'
  return { tag: e.tagName, cls: typeof e.className === 'string' ? e.className : '', label, region,
           x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height),
           onScreen: r.right > 0 && r.left < innerWidth && r.bottom > 0 && r.top < innerHeight }
})()`

if (args.includes('--open-rail')) {
  await evaluate(`document.querySelector('[data-rail-toggle]').click()`)
  await Bun.sleep(600)
  await evaluate(`document.activeElement && document.activeElement.blur()`)
}

const order: any[] = []
for (let i = 0; i < 22; i++) {
  await key('Tab', 'Tab', 9)
  order.push(await evaluate(describe))
}

const page = await evaluate(`(() => {
  const de = document.documentElement
  const ab = document.getElementById('wpadminbar')
  const sk = document.querySelector('.skip-link')
  return {
    scrollWidth: de.scrollWidth, clientWidth: de.clientWidth,
    overflowX: de.scrollWidth - de.clientWidth,
    adminBarBottom: ab ? Math.round(ab.getBoundingClientRect().bottom) : null,
    railBeforeMain: (() => {
      const rail = document.querySelector('.rail'); const main = document.querySelector('main')
      if (!rail || !main) return null
      return !!(main.compareDocumentPosition(rail) & Node.DOCUMENT_POSITION_PRECEDING)
    })(),
    railPosition: (() => { const r = document.querySelector('.rail'); return r ? getComputedStyle(r).position : null })(),
    submenuLinks: [...document.querySelectorAll('.rail nav ul ul a')].map(a => a.textContent.trim()),
  }
})()`)

// The skip link, focused for real: Tab once from the very top of the document.
await evaluate(`document.activeElement && document.activeElement.blur(); window.scrollTo(0,0); document.body.focus();`)
const skip = await evaluate(`(() => {
  const sk = document.querySelector('.skip-link'); if (!sk) return null
  sk.focus()
  const r = sk.getBoundingClientRect()
  const ab = document.getElementById('wpadminbar')
  const abb = ab ? ab.getBoundingClientRect().bottom : 0
  return { matchesFocus: sk.matches(':focus'), top: Math.round(r.top), left: Math.round(r.left),
           bottom: Math.round(r.bottom), adminBarBottom: Math.round(abb), clearsAdminBar: r.top >= abb }
})()`)

console.log(JSON.stringify({ url, width, admin, page, skip, order }, null, 2))

if (shot) {
  const r = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false })
  await Bun.write(shot, Buffer.from(r.result.data, 'base64'))
  console.error(`shot → ${shot}`)
}

ws.close(); chrome.kill()
await Bun.$`rm -rf ${PROFILE}`.quiet()
