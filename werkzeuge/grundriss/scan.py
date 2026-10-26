"""Gescannte Grundrisse (Bild statt Vektoren) in das einheitliche DAVINCI-Layout bringen.

Nur dunkle Linien bleiben (Farbe, Möbel-Kolorierung, Bäume und Wasser fallen weg), der Ausschnitt
wird auf das Haus begrenzt, Plankopf und Personendaten entfallen damit.

  python3 scan.py ein.pdf aus.pdf --seite 0 --bereich 0.2,0.25,0.7,0.66 --titel Erdgeschoss --haus "DAVINCI Tre"
"""
import argparse, io, sys
import fitz
import numpy as np
from PIL import Image
from scipy import ndimage
sys.path.insert(0, __file__.rsplit('/', 1)[0])
from grundriss import plankopf, HIER, INK


def erzeugen(eingabe, ausgabe, seite, bereich, titel, haus, loeschen=(), texte=()):
    p = fitz.open(eingabe)[seite]
    W, H = p.rect.width, p.rect.height
    clip = fitz.Rect(bereich[0] * W, bereich[1] * H, bereich[2] * W, bereich[3] * H)
    pix = p.get_pixmap(dpi=300, clip=clip)
    a = np.frombuffer(pix.samples, np.uint8).reshape(pix.height, pix.width, pix.n)[:, :, :3].astype(np.int16)
    hell = a.max(axis=2)
    bunt = a.max(axis=2) - a.min(axis=2)
    linie = (hell < 125) & (bunt < 0.5 * hell + 45)                          # dunkel und nicht farbig
    for x0, y0, x1, y1 in loeschen:                             # Bereiche (Anteile des Ausschnitts) leeren
        linie[int(y0 * pix.height):int(y1 * pix.height), int(x0 * pix.width):int(x1 * pix.width)] = False
    # Rauschen und Restpunkte der Kolorierung entfernen
    lab, n = ndimage.label(linie, structure=np.ones((3, 3)))
    groesse = ndimage.sum(linie, lab, range(1, n + 1))
    klein = np.isin(lab, np.nonzero(groesse < 12)[0] + 1)
    linie &= ~klein
    # Grundfläche: Inneres der Außenwände zart tönen
    wand = ndimage.binary_opening(linie, structure=np.ones((7, 7)))     # nur kräftige Wände (schwarz gefüllt)
    wand = ndimage.binary_dilation(wand, iterations=30)
    flaeche = ndimage.binary_erosion(ndimage.binary_fill_holes(wand), iterations=30)
    flaeche = ndimage.binary_opening(flaeche, structure=np.ones((80, 80)))

    rgba = np.zeros((pix.height, pix.width, 4), np.uint8)
    rgba[flaeche] = (249, 247, 244, 255)
    ink = tuple(int(c * 255) for c in INK)
    alpha = np.clip((150 - hell) * 255 / 110, 0, 255).astype(np.uint8)
    rgba[linie, :3] = ink
    rgba[linie, 3] = np.maximum(rgba[linie, 3], alpha[linie])
    buf = io.BytesIO()
    Image.fromarray(rgba, 'RGBA').save(buf, 'PNG', optimize=True)

    neu = fitz.open()
    S = neu.new_page(width=1190.55, height=841.89)
    for nm, w in (('o250', 250), ('o300', 300), ('o400', 400)):
        S.insert_font(fontname=nm, fontfile=f'{HIER}/outfit-{w}.ttf')
    feld = fitz.Rect(40, 40, 1190.55 - 300, 841.89 - 40)
    k = min(1, feld.width / clip.width, feld.height / clip.height)
    w, h = clip.width * k, clip.height * k
    ziel = fitz.Rect(feld.x0 + (feld.width - w) / 2, feld.y0 + (feld.height - h) / 2, 0, 0)
    ziel.x1, ziel.y1 = ziel.x0 + w, ziel.y0 + h
    S.insert_image(ziel, stream=buf.getvalue())
    schrift = fitz.Font(fontfile=f'{HIER}/outfit-400.ttf')
    for name, fx, fy in texte:                                   # Raumnamen, die im Scan auf Farbe lagen
        m = fitz.Point(ziel.x0 + fx * w, ziel.y0 + fy * h)
        b = schrift.text_length(name, fontsize=7)
        S.draw_rect(fitz.Rect(m.x - b / 2 - 3, m.y - 4.4, m.x + b / 2 + 3, m.y + 3.5), color=None, fill=(1, 1, 1), fill_opacity=0.85)
        S.insert_text(fitz.Point(m.x - b / 2, m.y + 2.45), name, fontname='o400', fontsize=7, color=INK)
    plankopf(S, titel, haus)
    neu.set_metadata({'title': f'DAVINCI Grundriss · {haus} · {titel}', 'author': 'DAVINCI', 'creator': 'DAVINCI', 'producer': 'DAVINCI', 'subject': 'Grundriss', 'keywords': ''})
    neu.save(ausgabe, garbage=4, deflate=True, clean=True)


if __name__ == '__main__':
    a = argparse.ArgumentParser()
    a.add_argument('eingabe'); a.add_argument('ausgabe'); a.add_argument('--seite', type=int, default=0)
    a.add_argument('--bereich', required=True); a.add_argument('--titel', required=True); a.add_argument('--haus', required=True)
    a.add_argument('--text', action='append', default=[], help='Name@x,y im Ausschnitt')
    a.add_argument('--loeschen', action='append', default=[], help='x0,y0,x1,y1 im Ausschnitt leeren')
    x = a.parse_args()
    erzeugen(x.eingabe, x.ausgabe, x.seite, [float(v) for v in x.bereich.split(',')], x.titel, x.haus,
             [[float(v) for v in l.split(',')] for l in x.loeschen],
             [(t.split('@')[0], *[float(v) for v in t.split('@')[1].split(',')]) for t in x.text])
