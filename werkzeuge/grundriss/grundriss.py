"""DAVINCI – alte Allplan-Grundrisse in das neue, einheitliche Planlayout übertragen.
Übernommen wird nur die Hausgeometrie (Wände, Fenster, Türen, Treppen, Möbel, Dächer) und die Raumnamen.
Planköpfe, Bauherren, Adressen, Architekten, Logos, Nordpfeil, Grundstücksgrenzen und Hinweise entfallen.
Aufruf: python3 grundriss.py eingabe.pdf ausgabe.pdf [--titel "Dachgeschoss"] [--haus "Alpina"]
"""
import sys, re, math, argparse
import pymupdf as fitz

HIER = __file__.rsplit('/', 1)[0]
INK = (29/255, 29/255, 31/255)
GRAU = (0.42, 0.42, 0.44)
HELL = (0.70, 0.69, 0.66)
RAND = 60
CLUSTER = 90
WAND_HELL = (0.66, 0.64, 0.60)
AKZENT = (64/255, 105/255, 115/255)
TEXTGRAU = (0.43, 0.43, 0.45)
RAEUME = {   # alter Text -> neuer Raumname (None = weglassen). Unbekanntes wird weggelassen und gemeldet.
    'Luftraum': 'Luftraum', 'Galerie': 'Galerie', 'Schlafen': 'Schlafen', 'SChlafen': 'Schlafen', 'Ankleide': 'Ankleide', 'Ankl.': 'Ankleide',
    'Studio': 'Studio', 'Bad': 'Bad', 'Essen': 'Essen', 'Wohnen': 'Wohnen', 'Kochen': 'Kochen', 'Diele': 'Diele', 'Musik/Gast': 'Musik/Gast',
    'Hzg./Ha./Hwr.': 'Technik/HWR', 'Hzg./Ha.': 'Technik', 'Hzg./Ha./Keller': 'Technik/Keller', 'Hwr.': 'HWR', 'Hwr./': 'HWR', 'Schleuse': 'Schleuse',
    'Du./WC': 'Du/WC', 'WC': 'WC', 'Zwischenebene': 'Zwischenebene', 'Flur': 'Flur', 'Arbeiten': 'Arbeiten', 'Garderobe': 'Garderobe',
    'Gästeappartement': 'Gästeappartement', 'Galerie/Studio': 'Galerie/Studio', 'Windf.': 'Windfang', 'Windfang': 'Windfang', 'Gast': 'Gast',
    'Spk.': 'Speisekammer', 'Bauseitige Garage': 'Garage', 'Hobby': 'Hobby', 'Abst.': 'Abstellraum', 'Abstell': 'Abstellraum', 'Bibliothek': 'Bibliothek',
    'Werkstatt/Motorrad/': 'Werkstatt', 'Keller': 'Keller', 'Fitness/Wellness': 'Fitness/Wellness', 'Doppelgarage': 'Doppelgarage', 'Bar': 'Bar',
    'Jacuzzi': 'Whirlpool', 'Sauna': 'Sauna', 'Kind/Gast': 'Kind/Gast', 'Diele/Gard.': 'Diele/Garderobe', 'Flachdach Doppelcarport': 'Carport', 'Doppelcarport': 'Carport', 'bauseitige Garage': 'Garage', 'Brennwert-': None,
    'Anna': 'Kind', 'Maya': 'Kind', 'Kind': 'Kind', 'Carport': 'Carport', 'Garage': 'Garage', 'Terrasse': 'Terrasse', 'Balkon': 'Balkon',
    'Kind 1': 'Kind 1', 'Kind 2': 'Kind 2', 'Vorrat': 'Vorrat', 'Kamin': 'Kamin', 'Keller/Abstell': 'Keller/Abstell', 'Fitness/Hobby': 'Fitness/Hobby',
    'Werkstatt/Elektro': 'Werkstatt', 'Werkstatt/Holz': 'Werkstatt', 'Windfang/Garderobe': 'Windfang/Garderobe', 'Technik': 'Technik', 'Gast/Arbeiten': 'Gast/Arbeiten',
    'Relaxen': 'Ruheraum', 'Du.': 'Dusche', 'Du./': 'Dusche', 'Skiraum': 'Skiraum', 'Skiraum/Fahrräder': 'Skiraum', 'Hwr./Keller 1': 'HWR/Keller', 'Keller 2': 'Keller',
    'Abstell/': 'Abstellraum', 'Kochen/Essen/': 'Kochen/Essen', 'baus.Sauna': 'Sauna', 'Flachdach Doppelgarage': 'Doppelgarage', 'Stellplätze': 'Stellplätze',
}
WEG = {'Stufe', 'Wäscheabwurf', 'baus.', 'Absturzsicherung', 'Deckenabsenkung', 'Stützenfundamente', 'Unterzug', 'Streifenfundamente', 'WP', 'LWP Außenaufstellung',
       'Fahrräder', 'Unterzug/Deckenversprung', 'UZ/DV', 'bauseitiger Außenpool', 'Amalie-Zoe', 'Nur Entwurfsplanung', 'Die Vorentwurfsplanung ist Eigentum',
       'von DAVINCI HAUS und ausschließlich', 'für Sie bestimmt !', 'Die endgültige Positionierung des Hauses', 'erfolgt im Zuge der Bauantragserstellung.',
       'kessel', 'für Hochbett', 'bauseitige Absturzsicherung', 'noch geprüft !!!', 'WA', 'TR', 'Hausanschlüsse', 'Zählerkasten', 'BrennwertKessel', 'Leiter', 'Für Hochbett', 'Bodenplatte', 'Fahrrad', 'Nicole', 'Pierre',
       'Bauseitige Absturzsicherung', 'Heizungsart wird', 'Noch geprüft !!!', 'Flachdach', 'Abstell'}
SIGNET = "M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"


def punkte(it, M):
    return [p * M for p in it[1:] if isinstance(p, fitz.Point)] if it[0] != 're' else [fitz.Rect(it[1]).tl * M, fitz.Rect(it[1]).br * M]


def diagonal(d):
    """Nur schräge Linien, typisch für Grundstücksgrenzen (Strich-Punkt)."""
    if d['type'] == 'f':
        return False
    for it in d['items']:
        if it[0] != 'l':
            return False
        a, b = it[1], it[2]
        if abs(a.x - b.x) < 0.3 or abs(a.y - b.y) < 0.3:
            return False
    return True


def lesen(pfad):
    doc = fitz.open(pfad)
    p = doc[0]
    M = p.rotation_matrix
    W, H = p.rect.width, p.rect.height
    zeichnungen = []
    for d in p.get_drawings():
        r = fitz.Rect(d['rect']) * M
        zeichnungen.append({'d': d, 'r': r, 'diag': diagonal(d), 'w': round(d.get('width') or 0, 2)})
    texte = []
    for b in p.get_text('dict')['blocks']:
        for l in b.get('lines', []):
            for s in l['spans']:
                t = s['text'].strip()
                if t:
                    texte.append({'t': t, 'r': fitz.Rect(s['bbox']) * M, 'size': s['size'], 'dir': l.get('dir')})
    return doc, p, M, W, H, zeichnungen, texte


def vereinen(a, b):
    """Vereinigung, auch mit Linien ohne Fläche (PyMuPDF ignoriert sonst leere Rechtecke)."""
    return fitz.Rect(min(a.x0, b.x0), min(a.y0, b.y0), max(a.x1, b.x1), max(a.y1, b.y1))


def abstand(a, b):
    dx = max(0, max(a.x0, b.x0) - min(a.x1, b.x1))
    dy = max(0, max(a.y0, b.y0) - min(a.y1, b.y1))
    return math.hypot(dx, dy)


def achsig(d):
    """Nur waagerechte und senkrechte Linien bzw. Rechtecke (Dächer, Sparren, Carport). Bäume bestehen aus schrägen Strichen."""
    for it in d['items']:
        if it[0] == 'l':
            a, b = it[1], it[2]
            if abs(a.x - b.x) > 0.3 and abs(a.y - b.y) > 0.3:
                return False
        elif it[0] not in ('re', 'qu'):
            return False
    return True


def strichpunkt(zeichnungen):
    """Grundstücksgrenzen im Strich-Punkt-Muster (lange und sehr kurze Striche auf einer Geraden)."""
    gruppen = {}
    for i, z in enumerate(zeichnungen):
        it = z['d']['items']
        if z['d']['type'] != 's' or len(it) != 1 or it[0][0] != 'l':
            continue
        a, b = it[0][1], it[0][2]
        L = math.hypot(b.x - a.x, b.y - a.y)
        if L > 40:
            continue
        w = math.atan2(b.y - a.y, b.x - a.x) % math.pi
        n = (-math.sin(w), math.cos(w))
        off = a.x * n[0] + a.y * n[1]
        gruppen.setdefault((round(w, 2), round(off)), []).append((i, L))
    weg = set()
    for liste in gruppen.values():
        kurz = sum(1 for _, L in liste if L < 3.5)
        lang = sum(1 for _, L in liste if 15 < L < 40)
        if kurz >= 4 and lang >= 4:
            weg.update(i for i, _ in liste)
    return weg


def gebaeude_waehlen(W, H, zeichnungen, rand=RAND, luecke=CLUSTER):
    """Kern = Wände (kräftige Linien) und Stützen (schwarze Flächen) des größten Clusters.
    Behalten wird alles im Kern; außerhalb nur gerade Linien bis RAND pt (Dachüberstand, Carport),
    ohne Bäume, Terrassenschraffur, Grundstücksgrenzen und lange Strichlinien."""
    def wandflaeche(z):
        f = z['d'].get('fill') or (1, 1, 1)
        r = z['r']
        if sum(f) > 2.9:
            return False
        if sum(f) < 0.3:
            return True                                   # Stützen
        kurz, lang = sorted((r.width, r.height))
        return kurz < 14 and lang > 4 * max(kurz, 0.5) and achsig(z['d'])    # gerade Wandstreifen (z. B. Beton im Keller)
    grenzen = strichpunkt(zeichnungen)
    struktur = [z for k, z in enumerate(zeichnungen) if k not in grenzen and z['r'].width < W * 0.45 and z['r'].height < H * 0.6 and z['r'].x1 < W * 0.95 and (
        (z['d']['type'] == 's' and z['w'] >= 0.9 and not z['diag'] and achsig(z['d'])) or (z['d']['type'] == 'f' and wandflaeche(z)))]
    # Cluster bilden (Union-Find)
    eltern = list(range(len(struktur)))
    def wurzel(i):
        while eltern[i] != i:
            eltern[i] = eltern[eltern[i]]; i = eltern[i]
        return i
    zellen = {}
    for i, z in enumerate(struktur):            # Raster beschleunigt die Nachbarsuche
        r = z['r']
        for cx in range(int(r.x0 // luecke), int(r.x1 // luecke) + 1):
            for cy in range(int(r.y0 // luecke), int(r.y1 // luecke) + 1):
                zellen.setdefault((cx, cy), []).append(i)
    for (cx, cy), liste in zellen.items():
        nachbarn = []
        for dx in (-1, 0, 1):
            for dy in (-1, 0, 1):
                nachbarn += zellen.get((cx + dx, cy + dy), [])
        for i in liste:
            for j in nachbarn:
                if i < j and wurzel(i) != wurzel(j) and abstand(struktur[i]['r'], struktur[j]['r']) < luecke:
                    eltern[wurzel(i)] = wurzel(j)
    gruppen = {}
    for i in range(len(struktur)):
        gruppen.setdefault(wurzel(i), []).append(struktur[i])
    beste = max(gruppen.values(), key=lambda g: sum(max(z['r'].width, z['r'].height) for z in g))
    kern = fitz.Rect(beste[0]['r'])
    for z in beste:
        kern = vereinen(kern, z['r'])
    grenze = kern + (-rand, -rand, rand, rand)

    # Strichlinien erkennen: viele kurze, auf einer Achse liegende Einzelstriche
    achsen = {}
    for i, z in enumerate(zeichnungen):
        it = z['d']['items']
        if z['d']['type'] == 's' and len(it) == 1 and it[0][0] == 'l':
            r = z['r']
            if r.width < 0.3 and r.height < 20:
                achsen.setdefault(('v', round(r.x0)), []).append(i)
            elif r.height < 0.3 and r.width < 20:
                achsen.setdefault(('h', round(r.y0)), []).append(i)
    strich = set()
    for k, liste in achsen.items():
        if len(liste) < 6:
            continue
        lo = min(zeichnungen[i]['r'].y0 if k[0] == 'v' else zeichnungen[i]['r'].x0 for i in liste)
        hi = max(zeichnungen[i]['r'].y1 if k[0] == 'v' else zeichnungen[i]['r'].x1 for i in liste)
        if (k[0] == 'v' and (lo < grenze.y0 or hi > grenze.y1)) or (k[0] == 'h' and (lo < grenze.x0 or hi > grenze.x1)):
            strich.update(liste)

    behalten = set()
    for i, z in enumerate(zeichnungen):
        r = z['r']
        if r.x0 >= kern.x0 - 2 and r.y0 >= kern.y0 - 2 and r.x1 <= kern.x1 + 2 and r.y1 <= kern.y1 + 2:
            if i not in grenzen:
                behalten.add(i)
            continue
        if i in strich or i in grenzen or z['d']['type'] == 'f':
            continue
        if r.x0 >= grenze.x0 and r.y0 >= grenze.y0 and r.x1 <= grenze.x1 and r.y1 <= grenze.y1 and achsig(z['d']):
            behalten.add(i)
    hülle = fitz.Rect(kern)
    for i in behalten:
        hülle = vereinen(hülle, zeichnungen[i]['r'])
    return kern, hülle, behalten


def pfade(sh, d, T):
    for it in d['items']:
        art = it[0]
        if art == 'l':
            sh.draw_line(it[1] * T, it[2] * T)
        elif art == 're':
            sh.draw_quad(fitz.Rect(it[1]).quad * T)
        elif art == 'qu':
            sh.draw_quad(it[1] * T)
        elif art == 'c':
            sh.draw_bezier(it[1] * T, it[2] * T, it[3] * T, it[4] * T)


def zeichnen_gruppe(ziel, liste, T, farbe, breite, fuellung=None):
    """Viele Pfade mit gleichem Stil in einem Zug zeichnen (schnell, kleine Datei)."""
    if not liste:
        return
    sh = ziel.new_shape()
    for d in liste:
        pfade(sh, d, T)
        if fuellung:
            sh.finish(color=None, fill=fuellung, closePath=True, even_odd=d.get('even_odd', False))
    if not fuellung:
        sh.finish(color=farbe, width=breite, lineCap=1, lineJoin=1, closePath=False)
    sh.commit()


def signet(seite, x, y, h, farbe):
    s = h / 1073
    sh = seite.new_shape()
    for teil in SIGNET.split('Z'):
        zahlen = re.findall(r'([ML])(\d+),(\d+)', teil)
        if not zahlen:
            continue
        pts = [fitz.Point(x + int(a) * s, y + int(b) * s) for _, a, b in zahlen]
        sh.draw_polyline(pts + [pts[0]])
    sh.finish(fill=farbe, color=None, closePath=True)
    sh.commit()


def baumkronen(zeichnungen, behalten, M):
    """Baumkronen und geschwungene Wege: lange Züge aus winzigen Strichen (Kreise > 40 pt)."""
    kurz = []
    for i in behalten:
        it = zeichnungen[i]['d']['items']
        if zeichnungen[i]['d']['type'] == 's' and len(it) == 1 and it[0][0] == 'l':
            a, b = it[0][1], it[0][2]
            if math.hypot(b.x - a.x, b.y - a.y) < 3:
                kurz.append(i)
    eltern = {i: i for i in kurz}
    def wurzel(i):
        while eltern[i] != i:
            eltern[i] = eltern[eltern[i]]; i = eltern[i]
        return i
    punkte = {}
    for i in kurz:
        it = zeichnungen[i]['d']['items'][0]
        for q in (it[1], it[2]):
            k = (round(q.x * 4), round(q.y * 4))
            if k in punkte:
                eltern[wurzel(i)] = wurzel(punkte[k])
            else:
                punkte[k] = i
    gruppen = {}
    for i in kurz:
        gruppen.setdefault(wurzel(i), []).append(i)
    weg = set()
    for g in gruppen.values():
        if len(g) < 12:
            continue
        r = zeichnungen[g[0]]['r']
        x0, y0, x1, y1 = r.x0, r.y0, r.x1, r.y1
        for i in g:
            q = zeichnungen[i]['r']
            x0, y0, x1, y1 = min(x0, q.x0), min(y0, q.y0), max(x1, q.x1), max(y1, q.y1)
        if max(x1 - x0, y1 - y0) > 40:
            weg.update(g)
    return weg


def grundflaeche(zeichnungen, behalten, kern):
    """Fläche innerhalb der Außenwände als Rechteck-Streifen (Raster 2 pt)."""
    import numpy as np
    from scipy import ndimage
    R = 2.0
    w, h = int(kern.width / R) + 4, int(kern.height / R) + 4
    m = np.zeros((h, w), bool)
    for i in behalten:
        z = zeichnungen[i]
        if not achsig(z['d']) or z['w'] < 0.45 and z['d']['type'] != 'f':
            continue
        r = z['r']
        if r.x0 < kern.x0 - 1 or r.x1 > kern.x1 + 1 or r.y0 < kern.y0 - 1 or r.y1 > kern.y1 + 1 or (r.width > 0.6 * kern.width and r.height > 0.6 * kern.height):
            continue
        m[int((r.y0 - kern.y0) / R) + 2:int((r.y1 - kern.y0) / R) + 3, int((r.x0 - kern.x0) / R) + 2:int((r.x1 - kern.x0) / R) + 3] = True
    m = ndimage.binary_dilation(m, iterations=3)
    m = ndimage.binary_fill_holes(m)
    m = ndimage.binary_erosion(m, iterations=3)
    m = ndimage.binary_opening(m, iterations=6)       # dünne Reste (Möbel außen, Linien) entfernen
    nah = ndimage.binary_dilation(m, iterations=5)
    def innen(x, y):
        cx, cy = int((x - kern.x0) / R) + 2, int((y - kern.y0) / R) + 2
        return 0 <= cx < w and 0 <= cy < h and bool(nah[cy, cx])
    streifen = []
    for y in range(h):
        zeile = m[y]
        x = 0
        while x < w:
            if zeile[x]:
                s = x
                while x < w and zeile[x]:
                    x += 1
                streifen.append([kern.x0 + (s - 2) * R, kern.y0 + (y - 2) * R, kern.x0 + (x - 2) * R, kern.y0 + (y - 1) * R])
            else:
                x += 1
    # gleiche Streifen untereinander zusammenfassen
    zus = []
    for st in streifen:
        if zus and abs(zus[-1][0] - st[0]) < 0.1 and abs(zus[-1][2] - st[2]) < 0.1 and abs(zus[-1][3] - st[1]) < 0.1:
            zus[-1][3] = st[3]
        else:
            zus.append(st)
    return zus, innen


def plankopf(S, titel, haus):
    """Plankopf rechts: Signet, Hausname, Geschoss, Hinweis."""
    x0 = 1190.55 - 260
    S.draw_line(fitz.Point(x0 - 30, 40), fitz.Point(x0 - 30, 841.89 - 40), color=(0.88, 0.875, 0.86), width=0.6)
    signet(S, x0, 46, 22, INK)
    S.insert_text(fitz.Point(x0 + 26, 63), 'D A V I N C I', fontname='o250', fontsize=15.5, color=INK)
    y = 150
    S.draw_line(fitz.Point(x0, y - 4), fitz.Point(x0 + 18, y - 4), color=AKZENT, width=0.7)
    S.insert_text(fitz.Point(x0 + 26, y), 'GRUNDRISS', fontname='o400', fontsize=7.5, color=AKZENT)
    if haus:
        S.insert_text(fitz.Point(x0, y + 34), haus, fontname='o250', fontsize=30, color=INK)
        y += 40
    S.insert_text(fitz.Point(x0, y + 34), titel, fontname='o250', fontsize=haus and 20 or 30, color=INK if not haus else TEXTGRAU)

    hinweis = ['Beispielgrundriss. Jedes DAVINCI Haus wird', 'für Grundstück und Bauherren individuell', 'geplant. Maße, Fenster und Ausstattung', 'können im Entwurf abweichen.']
    for k, z in enumerate(hinweis):
        S.insert_text(fitz.Point(x0, 841.89 - 100 + k * 11.5), z, fontname='o300', fontsize=7.5, color=TEXTGRAU)
    S.insert_text(fitz.Point(x0, 841.89 - 44), 'davinci-homes.de', fontname='o400', fontsize=8.5, color=INK)
    S.insert_text(fitz.Point(x0 + 112, 841.89 - 44), 'Designed to stay.', fontname='o300', fontsize=8.5, color=TEXTGRAU)



def erzeugen(eingabe, ausgabe, titel=None, haus=None, bereich=None, ohne=()):
    doc, p, M, W, H, zeichnungen, texte = lesen(eingabe)
    if bereich:                                  # nur einen Ausschnitt des Blatts verwenden (Anteile 0..1)
        b = fitz.Rect(bereich[0] * W, bereich[1] * H, bereich[2] * W, bereich[3] * H)
        zeichnungen = [z for z in zeichnungen if b.contains(z['r'])]
        texte = [t for t in texte if b.contains(fitz.Point((t['r'].x0 + t['r'].x1) / 2, (t['r'].y0 + t['r'].y1) / 2))]
    for o in ohne:                               # Bereiche leeren (z. B. Bäume zwischen Haus und Garage)
        b = fitz.Rect(o[0] * W, o[1] * H, o[2] * W, o[3] * H)
        zeichnungen = [z for z in zeichnungen if not b.contains(z['r'])]
    kern, hülle, behalten = gebaeude_waehlen(W, H, zeichnungen)

    # Planname aus dem alten Plankopf (größte Schrift außerhalb des Hauses)
    if not titel:
        kopf = [t for t in texte if not hülle.intersects(t['r'])]
        titel = max(kopf, key=lambda t: t['size'])['t'] if kopf else 'Grundriss'

    neu = fitz.open()
    S = neu.new_page(width=1190.55, height=841.89)          # A3 quer, Maßstab bleibt 1:100
    for n, w in (('o250', 250), ('o300', 300), ('o400', 400)):
        S.insert_font(fontname=n, fontfile=f'{HIER}/outfit-{w}.ttf')

    # Plan in den Planbereich mittig setzen (rechts 290 pt für den Plankopf)
    feld = fitz.Rect(40, 40, 1190.55 - 300, 841.89 - 40)
    k = min(1, feld.width / hülle.width, feld.height / hülle.height)   # zu große Pläne verkleinern
    if k < 1:
        print(f'  Plan verkleinert auf {k:.2f}', file=sys.stderr)
    V = fitz.Matrix(1, 0, 0, 1, -hülle.x0, -hülle.y0) * fitz.Matrix(k, k) * fitz.Matrix(1, 0, 0, 1, feld.x0 + (feld.width - hülle.width * k) / 2, feld.y0 + (feld.height - hülle.height * k) / 2)

    # Bäume und Pflanzen: sehr viele kurze schräge Striche auf engem Raum
    dichte = {}
    for i in behalten:
        z = zeichnungen[i]
        if z['diag'] or (z['d']['type'] == 'f' and not achsig(z['d'])):
            c = (int(z['r'].x0 // 15), int(z['r'].y0 // 15))
            dichte[c] = dichte.get(c, 0) + 1
    baum = {c for c, n in dichte.items() if n > 25}
    vorher = len(behalten)
    behalten = {i for i in behalten if not ((zeichnungen[i]['diag'] or (zeichnungen[i]['d']['type'] == 'f' and not achsig(zeichnungen[i]['d'])))
                                            and (int(zeichnungen[i]['r'].x0 // 15), int(zeichnungen[i]['r'].y0 // 15)) in baum)}
    print(f'  {vorher - len(behalten)} Pflanzen-Striche entfernt', file=sys.stderr)

    # Grundfläche des Hauses als zarter Ton (aus Wänden, Fenstern und Stützen)
    kronen = baumkronen(zeichnungen, behalten, M)
    behalten -= kronen
    print(f'  {len(kronen)} Striche von Baumkronen/Wegen entfernt', file=sys.stderr)
    flaechen, innen = grundflaeche(zeichnungen, behalten, hülle)
    # Schräge Linien und Bögen außerhalb des Hauses (Baumkronen, Liegen, Wege) entfernen
    # Winzige graue Flächen außerhalb der Wände (Laub, Pflaster-Schraffur) entfallen
    behalten = {i for i in behalten if not (zeichnungen[i]['d']['type'] == 'f' and sum(zeichnungen[i]['d'].get('fill') or (0, 0, 0)) > 0.3
                and max(zeichnungen[i]['r'].width, zeichnungen[i]['r'].height) < 8)}
    for x0, y0, x1, y1 in flaechen:
        S.draw_rect(fitz.Rect(x0, y0, x1, y1) * V, color=None, fill=(0.975, 0.97, 0.955))

    T = M * V
    gruppen = {'hell': [], 'grau': [], 'ink': [], 'f_ink': [], 'f_hell': [], 'f_flaeche': []}
    for i in behalten:
        z = zeichnungen[i]
        imKern = z['r'].x0 >= kern.x0 - 2 and z['r'].x1 <= kern.x1 + 2 and z['r'].y0 >= kern.y0 - 2 and z['r'].y1 <= kern.y1 + 2
        if z['d']['type'] == 'f':
            f = z['d'].get('fill') or (0, 0, 0)
            kurz, lang = sorted((z['r'].width, z['r'].height))
            if sum(f) < 0.3:
                gruppen['f_ink'].append(z['d'])
            elif kurz < 14 and lang > 4 * max(kurz, 0.5) and achsig(z['d']):
                gruppen['f_hell'].append(z['d'])            # Wände
            else:
                gruppen['f_flaeche'].append(z['d'])         # Terrassen, Beläge, Schraffuren: nur zarter Ton
        elif z['w'] >= 0.9:
            gruppen['ink'].append(z['d'])
        else:
            gruppen['grau' if imKern else 'hell'].append(z['d'])
    zeichnen_gruppe(S, gruppen['f_flaeche'], T, None, 0, (0.93, 0.925, 0.91))
    zeichnen_gruppe(S, gruppen['hell'], T, HELL, 0.3)
    zeichnen_gruppe(S, gruppen['grau'], T, GRAU, 0.35)
    zeichnen_gruppe(S, gruppen['f_hell'], T, None, 0, WAND_HELL)
    zeichnen_gruppe(S, gruppen['f_ink'], T, None, 0, INK)
    zeichnen_gruppe(S, gruppen['ink'], T, INK, 0.8)

    # Raumnamen (nur bekannte Raumbezeichnungen, keine Namen von Personen)
    schrift = fitz.Font(fontfile=f'{HIER}/outfit-400.ttf')
    for t in texte:
        if not hülle.contains(fitz.Point((t['r'].x0 + t['r'].x1) / 2, (t['r'].y0 + t['r'].y1) / 2)):
            continue
        if re.fullmatch(r'[-–\s\d.,:/m²]+', t['t']) or t['t'] in WEG:
            continue
        name = RAEUME.get(t['t'])
        if not name:
            print('  Text weggelassen:', t['t'], file=sys.stderr)
            continue
        mitte = fitz.Point((t['r'].x0 + t['r'].x1) / 2, (t['r'].y0 + t['r'].y1) / 2) * V
        fs = 8.5 if t['size'] >= 8 else 7
        breite = schrift.text_length(name, fontsize=fs)
        S.draw_rect(fitz.Rect(mitte.x - breite / 2 - 3, mitte.y - fs * 0.62, mitte.x + breite / 2 + 3, mitte.y + fs * 0.5), color=None, fill=(1, 1, 1), fill_opacity=0.85)
        S.insert_text(fitz.Point(mitte.x - breite / 2, mitte.y + fs * 0.35), name, fontname='o400', fontsize=fs, color=INK)

    plankopf(S, titel, haus)

    neu.set_metadata({'title': f'DAVINCI Grundriss · {haus + " · " if haus else ""}{titel}', 'author': 'DAVINCI', 'creator': 'DAVINCI', 'producer': 'DAVINCI', 'subject': 'Grundriss', 'keywords': ''})
    neu.save(ausgabe, garbage=4, deflate=True, clean=True)
    return titel, hülle


if __name__ == '__main__':
    a = argparse.ArgumentParser()
    a.add_argument('eingabe'); a.add_argument('ausgabe'); a.add_argument('--titel'); a.add_argument('--haus'); a.add_argument('--bereich', help='x0,y0,x1,y1 als Anteile des Blatts'); a.add_argument('--ohne', action='append', default=[])
    x = a.parse_args()
    print(erzeugen(x.eingabe, x.ausgabe, x.titel, x.haus, [float(v) for v in x.bereich.split(',')] if x.bereich else None, [[float(v) for v in o.split(',')] for o in x.ohne]))
