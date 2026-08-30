#!/usr/bin/env python3
"""Take a real Quire Ink article and turn it into Gutenberg block markup.

This is the honest half of the comparison. Copying the rendered HTML into the database would
prove only that the stylesheet still works on the HTML it was written for; what has to be
proved is that the theme survives the trip through the BLOCK EDITOR, which is where the
content model actually differs. So the article's prose is parsed, mapped block by block, and
what WordPress stores is block markup - the same thing an author typing into Gutenberg
would leave behind.

Anything with no block equivalent is reported by name at the end rather than dropped
silently. A converter that quietly loses the pen marks would make the screenshots agree by
deleting the evidence.

Usage:  fetch.py <url> <out.json>
"""

import json
import re
import sys
import urllib.request
from html.parser import HTMLParser

UA = "quireink-theme-dev/0.1 (local parity check)"

# Elements HTMLParser reports through handle_starttag but that never close. Left out of the
# depth count on purpose: an <hr> in the article used to increment it forever, so the closing
# </div> of #post-body was never recognised and the whole extraction came back empty.
VOID = {"br", "hr", "img", "input", "source", "wbr", "col", "area", "embed", "track"}

# Inline tags that ride along inside a block's HTML untouched.
INLINE = {
    "a", "em", "strong", "b", "i", "code", "mark", "u", "s", "del", "ins", "sup", "sub",
    "br", "span", "small", "abbr", "time", "kbd", "img", "picture", "source", "cite", "q",
}

# Quire Ink markup with no Gutenberg equivalent. Kept in the HTML (the stylesheet still
# styles it) but counted, so the report says what the block editor cannot reproduce.
UNMAPPED = {
    "mark[data-pen]": re.compile(r"<mark[^>]*data-pen=", re.I),
    "u[data-pen]": re.compile(r"<u[^>]*data-pen=", re.I),
    "sup.fnref": re.compile(r'<sup[^>]*class="[^"]*fnref', re.I),
    "span.anchor": re.compile(r'<span[^>]*class="[^"]*anchor', re.I),
    "pre.shiki": re.compile(r'<pre[^>]*class="[^"]*shiki', re.I),
    ".gallery": re.compile(r'class="[^"]*\bgallery\b', re.I),
    ".callout": re.compile(r'class="[^"]*\bcallout\b', re.I),
    "math": re.compile(r'class="[^"]*\bkatex|<math', re.I),
}


class Grab(HTMLParser):
    """Pull out the pieces of a Quire Ink article page by class name."""

    def __init__(self):
        super().__init__(convert_charrefs=False)
        self.depth = 0
        self.capturing = None
        self.buf = []
        self.out = {"prose": "", "title": "", "date": "", "tags": [], "cats": []}
        self._in_h1 = False
        self._h1 = []
        self._term_kind = None

    # -- helpers ---------------------------------------------------------------
    @staticmethod
    def _attrs(attrs):
        return " ".join(f'{k}="{v}"' for k, v in attrs if v is not None)

    def _open(self, tag, attrs):
        a = self._attrs(attrs)
        return f"<{tag}{' ' + a if a else ''}>"

    # -- parsing ---------------------------------------------------------------
    def handle_starttag(self, tag, attrs):
        d = dict(attrs)
        cls = d.get("class", "")

        if self.capturing is None:
            if d.get("id") == "post-body":
                self.capturing = "prose"
                self.depth = 0
                return
            if tag == "h1" and "reading-font" in cls:
                self._in_h1 = True
                return
            if tag == "time" and not self.out["date"]:
                self.out["date"] = d.get("datetime", "")
            if tag == "a" and "link-accent" in cls:
                href = d.get("href", "")
                if "/tag/" in href:
                    self._term_kind = "tags"
                elif "/category/" in href:
                    self._term_kind = "cats"
            return

        if tag not in VOID:
            self.depth += 1
        self.buf.append(self._open(tag, attrs))

    def handle_startendtag(self, tag, attrs):
        if self.capturing:
            self.buf.append(self._open(tag, attrs)[:-1] + ">")

    def handle_endtag(self, tag):
        if self._in_h1 and tag == "h1":
            self._in_h1 = False
            self.out["title"] = "".join(self._h1).strip()
            return
        if self._term_kind and tag == "a":
            self._term_kind = None
            return
        if self.capturing is None:
            return
        if self.depth == 0 and tag == "div":
            self.out["prose"] = "".join(self.buf)
            self.capturing = None
            return
        self.depth -= 1
        self.buf.append(f"</{tag}>")

    def handle_data(self, data):
        if self._in_h1:
            self._h1.append(data)
        elif self._term_kind:
            self.out[self._term_kind].append(data.strip())
        elif self.capturing:
            self.buf.append(data)

    def handle_entityref(self, name):
        self.handle_data(f"&{name};")

    def handle_charref(self, name):
        self.handle_data(f"&#{name};")


def blocks(prose: str) -> str:
    """Wrap top-level elements in the block comments Gutenberg stores."""
    out = []
    for tag, attrs, inner, whole in top_level(prose):
        if tag == "p":
            out.append(f"<!-- wp:paragraph -->\n<p{attrs}>{inner}</p>\n<!-- /wp:paragraph -->")
        elif tag in ("h2", "h3", "h4", "h5"):
            level = int(tag[1])
            meta = "" if level == 2 else f' {{"level":{level}}}'
            out.append(
                f"<!-- wp:heading{meta} -->\n<{tag} class=\"wp-block-heading\"{attrs}>{inner}</{tag}>\n<!-- /wp:heading -->"
            )
        elif tag in ("ul", "ol"):
            meta = ' {"ordered":true}' if tag == "ol" else ""
            items = re.findall(r"<li[^>]*>(.*?)</li>", inner, re.S)
            body = "\n".join(
                f"<!-- wp:list-item -->\n<li>{i.strip()}</li>\n<!-- /wp:list-item -->" for i in items
            )
            out.append(
                f"<!-- wp:list{meta} -->\n<{tag} class=\"wp-block-list\"{attrs}>\n{body}\n</{tag}>\n<!-- /wp:list -->"
            )
        elif tag == "blockquote":
            out.append(
                f"<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"{attrs}>{inner}</blockquote>\n<!-- /wp:quote -->"
            )
        elif tag == "pre":
            out.append(
                f"<!-- wp:code -->\n<pre class=\"wp-block-code\"{attrs}>{inner}</pre>\n<!-- /wp:code -->"
            )
        elif tag == "hr":
            out.append('<!-- wp:separator -->\n<hr class="wp-block-separator has-alpha-channel-opacity"/>\n<!-- /wp:separator -->')
        elif tag == "figure":
            # MERGE the classes, never prepend a second class attribute. A figure arrives as
            # `class="img-center img-wide"` and those two names are what makes the picture
            # span the wide measure; emitting `class="wp-block-image" class="img-center
            # img-wide"` is two attributes, the browser keeps the first, and the image
            # renders at body width while every other pixel on the page matches.
            out.append(
                f'<!-- wp:image -->\n<figure{merge_class(attrs, "wp-block-image")}>{inner}</figure>\n<!-- /wp:image -->'
            )
        elif tag == "table":
            out.append(
                f'<!-- wp:table -->\n<figure class="wp-block-table"><table{attrs}>{inner}</table></figure>\n<!-- /wp:table -->'
            )
        else:
            # Anything with no block of its own goes in as custom HTML rather than being
            # dropped. It renders; it just is not editable as a block, which is exactly the
            # cost this exercise is meant to measure.
            out.append(f"<!-- wp:html -->\n{whole}\n<!-- /wp:html -->")
    return "\n\n".join(out)


def merge_class(attrs: str, extra: str) -> str:
    """Return `attrs` with `extra` added to its class list, creating the attribute if absent."""
    m = re.search(r'\sclass="([^"]*)"', attrs)
    if not m:
        return f'{attrs} class="{extra}"'
    names = m.group(1).split()
    if extra not in names:
        names.append(extra)
    return attrs[: m.start()] + f' class="{" ".join(names)}"' + attrs[m.end():]


def top_level(html: str):
    """Yield (tag, attrs, inner, whole) for each top-level element."""
    i = 0
    n = len(html)
    while i < n:
        m = re.compile(r"<([a-zA-Z][a-zA-Z0-9]*)((?:\s[^>]*)?)(/?)>").search(html, i)
        if not m:
            return
        tag, attrs, selfclose = m.group(1).lower(), m.group(2), m.group(3)
        if tag in INLINE and tag not in ("img", "picture"):
            i = m.end()
            continue
        if selfclose or tag in ("hr", "br", "img"):
            yield tag, attrs, "", m.group(0)
            i = m.end()
            continue
        depth = 1
        j = m.end()
        pat = re.compile(rf"</?{tag}\b", re.I)
        while depth and j < n:
            k = pat.search(html, j)
            if not k:
                break
            depth += -1 if html[k.start() + 1] == "/" else 1
            j = k.end()
        close = html.find(">", j)
        inner = html[m.end(): j - len(tag) - 2]
        yield tag, attrs, inner, html[m.start(): close + 1]
        i = close + 1


def absolutise(html: str, origin: str) -> str:
    """Point root-relative asset URLs back at the site they came from.

    The pictures live on the blog, not on the machine running this. Left alone, every
    `/uploads/...` resolves against localhost, 404s, and the comparison shows two empty boxes
    where an image should be - which reads as a theme bug and is not one. Uploading the
    images into the local media library would be the honest thing for a content migration;
    for a look-at-it-side-by-side it is a detour, and hotlinking the author's own blog from
    his own laptop is not a cost to anyone.
    """
    html = re.sub(
        r'(\s(?:src|href|poster)=")(/(?!/)[^"]*)',
        lambda m: m.group(1) + origin + m.group(2),
        html,
    )

    # srcset holds a LIST, and only its first entry starts right after the quote. Fixing that
    # one and leaving the rest is worse than fixing none: the browser picks a candidate by
    # viewport, so the picture loads on a phone and 404s on a desktop.
    def one_srcset(m):
        fixed = ", ".join(
            (origin + c.strip() if c.strip().startswith("/") else c.strip())
            for c in m.group(2).split(",")
        )
        return m.group(1) + fixed + '"'

    return re.sub(r'(\ssrcset=")([^"]*)"', one_srcset, html)


# Block-level elements that a paragraph cannot legally contain. The markdown renderer emits
# `<p><figure>...</figure></p>`, which every browser silently splits into three elements - so
# the page looks right and the SOURCE says something else. Parsed literally, the figure ends
# up inside a paragraph block, WordPress stores it that way, and the browser then splits it
# on the far side too: the picture is orphaned and the text after it loses its own <p>.
BLOCK_IN_P = re.compile(
    r"<p(?:\s[^>]*)?>\s*(<(?:figure|pre|table|blockquote|ul|ol|div|hr)\b.*?)\s*</p>",
    re.S | re.I,
)


def unwrap_blocks(html: str) -> str:
    """Lift block elements out of the paragraphs that illegally wrap them."""
    prev = None
    while prev != html:
        prev = html
        html = BLOCK_IN_P.sub(lambda m: m.group(1), html)
    return html


def main():
    url, out_path = sys.argv[1], sys.argv[2]
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Encoding": "identity"})
    with urllib.request.urlopen(req, timeout=30) as r:
        html = r.read().decode("utf-8")

    g = Grab()
    g.feed(html)
    if not g.out["prose"]:
        sys.exit(f"no #post-body found at {url}")

    origin = "://".join(url.split("://")[:1] + [url.split("://", 1)[1].split("/", 1)[0]])
    g.out["prose"] = unwrap_blocks(absolutise(g.out["prose"], origin))

    found = sorted(name for name, pat in UNMAPPED.items() if pat.search(g.out["prose"]))

    data = {
        "url": url,
        "title": g.out["title"],
        "date": g.out["date"],
        "tags": sorted(set(t for t in g.out["tags"] if t)),
        "cats": sorted(set(c for c in g.out["cats"] if c)),
        "content": blocks(g.out["prose"]),
        "unmapped": found,
        "prose_bytes": len(g.out["prose"]),
    }
    with open(out_path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=1)

    print(f"title    {data['title']}")
    print(f"prose    {data['prose_bytes']} B -> {len(data['content'])} B of blocks")
    print(f"tags     {', '.join(data['tags']) or '-'}")
    print(f"cats     {', '.join(data['cats']) or '-'}")
    print(f"unmapped {', '.join(found) if found else 'none'}")


if __name__ == "__main__":
    main()
