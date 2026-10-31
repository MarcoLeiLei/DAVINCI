"""Design-Book Vol. 01 auf den Stand der Website bringen.
- Basis ist die ursprüngliche Ausgabe aus Git (Commit a9b5140), damit das Skript beliebig oft laufen kann.
- Bilder: Tre mit dem aktuellen Website-Motiv, drei Seiten mit Fotos gebauter DAVINCI Häuser (wie auf der Website).
- Zwei neue Seiten am Ende von Kapitel 06 mit Grundrissen der Kollektion als Teaser und Hinweis auf die Website.
- Seitenzahlen und Inhaltsverzeichnis werden nachgezogen.
Aufruf (im Repo-Ordner): DV_SCHRIFTEN=<Mundial-Ordner> python3 werkzeuge/designbook/aktualisieren.py downloads/DAVINCI_Design-Book_Vol01.pdf"""
import sys, os, io, subprocess
import fitz
from PIL import Image

REPO = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
SCHRIFTEN = os.environ.get('DV_SCHRIFTEN', os.path.join(REPO, 'werkzeuge', 'grundriss', 'schriften'))
IMG = os.path.join(REPO, 'assets', 'img')
GR = os.path.join(REPO, 'downloads', 'grundrisse')
BASIS = 'a9b5140:downloads/DAVINCI_Design-Book_Vol01.pdf'

def farbe(h): return ((h >> 16) / 255, ((h >> 8) & 255) / 255, (h & 255) / 255)
INK, GRAU, HELLGRAU, AKZENT = farbe(0x1d1d1f), farbe(0x6e6e73), farbe(0x9a9a9f), farbe(0x9dbcc4)
PAPIER, DUNKEL = (0.9412, 0.9412, 0.9333), (0.0431, 0.0431, 0.0431)
NEU_NACH = 31          # neue Seiten nach dieser Seite (Index, "Wohnsituationen II")
NEU = 2


def schriften(p):
    for n, f in (('mt', 'MundialThin'), ('ml', 'MundialLight'), ('mr', 'MundialRegular')):
        p.insert_font(fontname=n, fontfile=f'{SCHRIFTEN}/{f}.otf')

FONT = {}
def breite(text, name, size):
    if name not in FONT:
        FONT[name] = fitz.Font(fontfile=f'{SCHRIFTEN}/' + {'mt': 'MundialThin', 'ml': 'MundialLight', 'mr': 'MundialRegular'}[name] + '.otf')
    return FONT[name].text_length(text, fontsize=size)

def text(p, x, y, s, name, size, col, rechts=False):
    if rechts:
        x -= breite(s, name, size)
    p.insert_text(fitz.Point(x, y), s, fontname=name, fontsize=size, color=col)

def absatz(p, x, y, zeilen, name='ml', size=12, col=GRAU, abstand=18):
    for k, z in enumerate(zeilen):
        text(p, x, y + k * abstand, z, name, size, col)

def umbrechen(s, name, size, w):
    out, z = [], ''
    for wort in s.split():
        t = (z + ' ' + wort).strip()
        if breite(t, name, size) > w and z:
            out.append(z); z = wort
        else:
            z = t
    return out + ([z] if z else [])


def bild(datei, aspekt, pos=(0.5, 0.5), breite_px=1700):
    """Bild auf Seitenverhältnis zuschneiden (object-fit: cover), als JPEG-Bytes."""
    im = Image.open(os.path.join(IMG, datei)).convert('RGB')
    w, h = im.size
    if w / h > aspekt:
        nw = int(h * aspekt); x0 = int((w - nw) * pos[0]); im = im.crop((x0, 0, x0 + nw, h))
    else:
        nh = int(w / aspekt); y0 = int((h - nh) * pos[1]); im = im.crop((0, y0, w, y0 + nh))
    if im.width > breite_px:
        im = im.resize((breite_px, int(breite_px / aspekt)), Image.LANCZOS)
    b = io.BytesIO(); im.save(b, 'JPEG', quality=84, optimize=True, progressive=True)
    return b.getvalue()


def foto_hinweis(p, r, s='Foto · gebautes DAVINCI Haus'):
    """Kleines Etikett unten links im Bild, wie in der Historie."""
    size = 9.8
    w = breite(s, 'mr', size) + 20
    k = fitz.Rect(r.x0 + 12, r.y1 - 12 - 22, r.x0 + 12 + w, r.y1 - 12)
    p.draw_rect(k, color=None, fill=(0, 0, 0), fill_opacity=0.45, radius=0.5)
    text(p, k.x0 + 10, k.y1 - 7.5, s, 'mr', size, (1, 1, 1))


def plan_ausschnitt(pdf, seite):
    """Umriss des gezeichneten Plans (ohne Plankopf) auf der Grundriss-Seite."""
    p = fitz.open(pdf)[seite]
    r = fitz.Rect()
    for d in p.get_drawings():
        q = d['rect']
        if q.x1 <= 892 and q.width < 840 and q.height < 750 and not q.is_empty:
            r |= q
    for b in p.get_text('dict')['blocks']:
        q = fitz.Rect(b['bbox'])
        if q.x1 <= 892:
            r |= q
    return r + (-4, -4, 4, 4)


def plan_zeigen(p, rect, haus, seite, ausschnitt=None, skala=None):
    pdf = os.path.join(GR, haus + '.pdf')
    clip = ausschnitt or plan_ausschnitt(pdf, seite)
    s = skala or min(rect.width / clip.width, rect.height / clip.height)
    w, h = clip.width * s, clip.height * s
    ziel = fitz.Rect(rect.x0 + (rect.width - w) / 2, rect.y0 + (rect.height - h) / 2, 0, 0)
    ziel.x1, ziel.y1 = ziel.x0 + w, ziel.y0 + h
    p.show_pdf_page(ziel, fitz.open(pdf), seite, clip=clip)
    return ziel


def kopf(p, rubrik, nr):
    text(p, 45, 43.5, rubrik, 'mr', 11.2, INK)
    text(p, 1395, 43.5, 'Design-Book · Vol. 01', 'ml', 11.2, HELLGRAU, rechts=True)
    text(p, 1395, 777.6, f'{nr:02d}', 'ml', 10.5, HELLGRAU, rechts=True)


def titel(p, t, unter):
    text(p, 45, 144.8, t, 'ml', 39, INK)
    absatz(p, 45, 182.2, umbrechen(unter, 'ml', 15.8, 860), 'ml', 15.8, GRAU, 22.6)


def karte(p, x0, x1, name, info, beschreibung):
    r = fitz.Rect(x0, 240, x1, 712.5)
    p.draw_rect(r, color=None, fill=(1, 1, 1))
    text(p, x0 + 21, 273.8, name, 'mr', 18, INK)
    text(p, x0 + 21, 289.5, info, 'ml', 11.2, GRAU)
    absatz(p, x0 + 21, 671.2, umbrechen(beschreibung, 'ml', 12, x1 - x0 - 42))
    return r


def seite_kollektion(doc, nr):
    p = doc.new_page(-1, width=1440, height=810); schriften(p)
    p.draw_rect(p.rect, color=None, fill=PAPIER)
    kopf(p, '06 Grundrisse · Aus der Kollektion', nr)
    titel(p, 'Grundrisse aus der Kollektion.',
          'Drei Häuser der Kollektion, gezeigt ist jeweils die Wohnebene. Alle Geschosse aller zehn Häuser '
          'finden Sie auf davinci-homes.de.')
    for x0, haus, name, info, txt in (
            (45, 'piano', 'DAVINCI Piano', 'ca. 150 m² · 1 Ebene · Flachdach',
             'Wohnen, Essen und Kochen in einem Raum, Schlafen mit Ankleide und Bad, Kinderzimmer und Doppelgarage unter einem Dach.'),
            (501, 'corte', 'DAVINCI Corte', 'Erdgeschoss · Bungalow im Winkel',
             'Zwei Flügel fassen die Terrasse. Wohnen, Essen und Kochen öffnen sich zum Garten, die Schlafräume liegen im Untergeschoss.'),
            (957, 'vista', 'DAVINCI Vista', 'Wohnebene · Pultdach · Hanglage',
             'Die Wohnebene als gläserner Pavillon mit Bad und Sauna, Terrassen und Pool. Darunter eine eigene Einliegerwohnung.')):
        r = karte(p, x0, x0 + 438, name, info, txt)
        plan_zeigen(p, fitz.Rect(r.x0 + 18, 308, r.x1 - 18, 640), haus, 0)
    text(p, 45, 742, 'Beispielgrundrisse. Jedes DAVINCI Haus wird für Grundstück und Bauherren individuell geplant.', 'ml', 9.8, HELLGRAU)


def seite_tre(doc, nr):
    p = doc.new_page(-1, width=1440, height=810); schriften(p)
    p.draw_rect(p.rect, color=None, fill=PAPIER)
    kopf(p, '06 Grundrisse · Zwei Ebenen', nr)
    titel(p, 'Zwei Ebenen, ein Raum.',
          'Der Klassiker DAVINCI Tre: Der Luftraum über dem Wohnen verbindet beide Geschosse, die Galerie wird zum Studio.')
    r = karte(p, 45, 939, 'DAVINCI Tre', '2 Ebenen · Satteldach · 3 Achsen',
              'Unten Wohnen mit Kamin, Essen, Kochen und Windfang, oben Studio, Galerie, Schlafen mit Ankleide und Bad. Doppelgarage mit Flachdach.')
    pdf = os.path.join(GR, 'tre.pdf')
    a, b = plan_ausschnitt(pdf, 0), plan_ausschnitt(pdf, 1)
    u = fitz.Rect(min(a.x0, b.x0), min(a.y0, b.y0), max(a.x1, b.x1), max(a.y1, b.y1))
    halb = (r.width - 36 - 24) / 2
    s = min(halb / u.width, (612 - 308) / u.height)
    for k, (seite, g) in enumerate(((0, 'Erdgeschoss'), (1, 'Dachgeschoss'))):
        f = fitz.Rect(r.x0 + 18 + k * (halb + 24), 308, r.x0 + 18 + k * (halb + 24) + halb, 612)
        plan_zeigen(p, f, 'tre', seite, u, s)
        text(p, (f.x0 + f.x1) / 2 - breite(g, 'mr', 11.5) / 2, 633, g, 'mr', 11.5, GRAU)
    # Hinweis auf die Website
    d = fitz.Rect(957, 240, 1395, 712.5)
    p.draw_rect(d, color=None, fill=DUNKEL)
    x = d.x0 + 30
    text(p, x, 286, 'Alle Grundrisse online.', 'ml', 27, (1, 1, 1))
    absatz(p, x, 322, umbrechen('Zehn Häuser, jedes Geschoss im DAVINCI Look. Wählen Sie Ihr Haus auf der Website, '
                                 'wir senden Ihnen den Grundriss als PDF per E-Mail.', 'ml', 12, d.width - 60), 'ml', 12, (0.8, 0.8, 0.82))
    namen = ['Tre', 'Quattro', 'Piano', 'Alpina', 'Terrazza', 'Pendio', 'Corte', 'Ala', 'Vista', 'Ombra']
    y0 = 412
    for k, n in enumerate(namen):
        cx, cy = x + (k // 5) * 190, y0 + (k % 5) * 30
        p.draw_line(fitz.Point(cx, cy - 19), fitz.Point(cx + 170, cy - 19), color=(0.25, 0.25, 0.26), width=0.6)
        text(p, cx, cy, f'{k + 1:02d}', 'ml', 10.5, AKZENT)
        text(p, cx + 28, cy, n, 'ml', 13.5, (1, 1, 1))
    text(p, x, 640, 'davinci-homes.de/haeuser', 'mr', 15, (1, 1, 1))
    text(p, x, 662, 'Grundriss je Haus anfordern', 'ml', 12, AKZENT)
    ax = x + breite('Grundriss je Haus anfordern', 'ml', 12) + 10
    p.draw_line(fitz.Point(ax, 658), fitz.Point(ax + 16, 658), color=AKZENT, width=0.9)
    p.draw_polyline([fitz.Point(ax + 11, 654), fitz.Point(ax + 16, 658), fitz.Point(ax + 11, 662)], color=AKZENT, width=0.9)
    p.insert_link({'kind': fitz.LINK_URI, 'from': fitz.Rect(x, 620, d.x1 - 30, 670), 'uri': 'https://davinci-homes.de/haeuser/'})


def seitenzahlen(doc, ab):
    """Seitenzahl im Fuß ab Seite 'ab' (Index) um NEU erhöhen."""
    for i in range(ab, len(doc)):
        p = doc[i]
        for b in p.get_text('dict')['blocks']:
            for l in b.get('lines', []):
                for s in l['spans']:
                    t = s['text'].strip()
                    if t.isdigit() and s['size'] < 12 and s['bbox'][1] > 760 and s['bbox'][0] > 1300:
                        # alte Zahl mit der Hintergrundfarbe abdecken (Schwärzen würde große Kapitelziffern mitlöschen)
                        r = fitz.Rect(s['bbox']) + (-2, -1, 2, 1)
                        pix = p.get_pixmap(clip=fitz.Rect(r.x0 - 8, r.y0, r.x0 - 4, r.y1), dpi=72)
                        bg = tuple(v / 255 for v in pix.pixel(0, pix.height // 2)[:3])
                        p.draw_rect(r, color=None, fill=bg)
                        schriften(p)
                        text(p, r.x1, s['origin'][1], f'{int(t) + NEU:02d}', 'ml', s['size'], farbe(s['color']), rechts=True)


def inhalt(doc):
    p = doc[1]
    neu = {'33': '35', '41': '43', '45': '47'}
    treffer = []
    for b in p.get_text('dict')['blocks']:
        for l in b.get('lines', []):
            for s in l['spans']:
                if s['text'].strip() in neu and s['size'] > 13:
                    treffer.append(s)
    for s in treffer:
        p.add_redact_annot(fitz.Rect(s['bbox']))
    p.apply_redactions(images=fitz.PDF_REDACT_IMAGE_NONE, graphics=fitz.PDF_REDACT_LINE_ART_NONE)
    schriften(p)
    for s in treffer:
        text(p, s['bbox'][2], s['origin'][1], neu[s['text'].strip()], 'ml', s['size'], farbe(s['color']), rechts=True)


def hinweise(doc):
    for i, alt, neu, rechts in (
            (9, ['Alle Abbildungen von Häusern sind mit Unterstützung von KI erstellte Visualisierungen.'],
             ['Alle Abbildungen der Kollektion sind mit Unterstützung von KI erstellte Visualisierungen.'], False),
            (47, ['Alle Abbildungen von Häusern sind mit Unterstützung von KI erstellte',
                  'Visualisierungen. Sie zeigen Beispiele und teilweise Sonderausstattungen.',
                  'Änderungen in Ausführung und Details vorbehalten.'],
             ['Abbildungen von Häusern sind mit Unterstützung von KI erstellte Visualisierungen,',
              'sofern sie nicht als Foto gekennzeichnet sind. Sie zeigen Beispiele und teilweise',
              'Sonderausstattungen. Änderungen in Ausführung und Details vorbehalten.'], True)):
        p = doc[i]
        spans = [s for b in p.get_text('dict')['blocks'] for l in b.get('lines', []) for s in l['spans'] if s['text'] in alt]
        assert len(spans) == len(alt), (i, len(spans))
        for sp in spans:
            p.add_redact_annot(fitz.Rect(sp['bbox']))
        p.apply_redactions(images=fitz.PDF_REDACT_IMAGE_NONE, graphics=fitz.PDF_REDACT_LINE_ART_NONE)
        schriften(p)
        for sp, z in zip(spans, neu):
            x = sp['bbox'][2] if rechts else sp['bbox'][0]
            text(p, x, sp['origin'][1], z, 'ml', sp['size'], farbe(sp['color']), rechts=rechts)


def main(ziel):
    roh = subprocess.run(['git', '-C', REPO, 'show', BASIS], capture_output=True, check=True).stdout
    doc = fitz.open(stream=roh, filetype='pdf')
    # 1 · Tre: aktuelles Motiv der Website (Bild wird auf allen Seiten ersetzt, auf denen es vorkommt)
    xref_tre = 66
    doc[9].replace_image(xref_tre, stream=bild('dv-24-giebel-3achser-tag.jpg', 1700 / 1063, (0.5, 0.4)))
    # 2 · Fotos gebauter Häuser wie auf der Website
    for i, datei, r, pos in ((4, 'echt-fassade-garten.jpg', fitz.Rect(398, 112, 1395, 742), (0.5, 0.5)),
                             (21, 'echt-luft-landschaft.jpg', fitz.Rect(398, 112, 1395, 742), (0.5, 0.5)),
                             (37, 'echt-carport-zufahrt.jpg', fitz.Rect(397, 430, 972, 742), (0.6, 0.6))):
        p = doc[i]
        alt = [x['xref'] for x in p.get_image_info(xrefs=True)]
        neu = bild(datei, r.width / r.height, pos)
        einmal = [x for x in alt if sum(1 for q in doc for y in q.get_image_info(xrefs=True) if y['xref'] == x) == 1]
        if einmal:
            # Bild nur hier verwendet: ersetzen und mit passendem Ausschnitt neu platzieren
            p.add_redact_annot(r); p.apply_redactions(images=fitz.PDF_REDACT_IMAGE_REMOVE, graphics=fitz.PDF_REDACT_LINE_ART_NONE, text=fitz.PDF_REDACT_TEXT_NONE)
        p.insert_image(r, stream=neu, keep_proportion=False)
        schriften(p); foto_hinweis(p, r)
    # KI-Hinweise: Das Buch enthält jetzt auch Fotos gebauter Häuser
    hinweise(doc)
    # 3 · Seitenzahlen ab Kapitel 07 und Inhaltsverzeichnis
    seitenzahlen(doc, NEU_NACH + 1)
    inhalt(doc)
    # 4 · Neue Seiten mit Grundrissen, ans Ende von Kapitel 06 verschieben
    n = len(doc)
    seite_kollektion(doc, NEU_NACH + 2)
    seite_tre(doc, NEU_NACH + 3)
    doc.move_page(n, NEU_NACH + 1)
    doc.move_page(n + 1, NEU_NACH + 2)
    doc.set_metadata({'title': 'DAVINCI Design-Book Vol. 01', 'author': 'DAVINCI', 'creator': 'DAVINCI', 'producer': 'DAVINCI',
                      'subject': 'Häuser aus Holz und Glas', 'keywords': ''})
    doc.save(ziel, garbage=4, deflate=True, clean=True)
    print(ziel, len(doc), 'Seiten', round(os.path.getsize(ziel) / 1e6, 1), 'MB')


if __name__ == '__main__':
    main(sys.argv[1])
