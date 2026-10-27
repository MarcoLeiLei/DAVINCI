"""DAVINCI-Grundrisse neu zeichnen – ein einheitlicher Stil für alle Häuser.

Jeder Plan wird aus der Originalvorlage ausgemessen (Meter, Maßstab 1:100) und hier mit
denselben Bauteilen und Möbelsymbolen neu gezeichnet. So sehen Pläne aus CAD, Scan oder
Handzeichnung am Ende gleich aus.

Schrift: Mundial (lizenziert, liegt NICHT im Repository). Ordner per Umgebungsvariable
DV_SCHRIFTEN angeben, darin MundialLight.otf, MundialRegular.otf, MundialThin.otf.
"""
import math, os, re
import fitz

M = 72 / 2.54                       # 1 m bei 1:100 in pt
SCHRIFTEN = os.environ.get('DV_SCHRIFTEN', os.path.join(os.path.dirname(__file__), 'schriften'))

INK = (29 / 255, 29 / 255, 31 / 255)
GRAU = (0.45, 0.45, 0.47)             # Möbel, Türbögen
HELL = (0.72, 0.71, 0.68)             # Außenanlagen, Dachkante
WAND = (0.70, 0.68, 0.64)             # Wandfüllung
FLAECHE = (0.975, 0.97, 0.955)        # Innenfläche
BELAG = (0.935, 0.93, 0.915)          # Terrasse, Carport
AKZENT = (64 / 255, 105 / 255, 115 / 255)
TEXTGRAU = (0.43, 0.43, 0.45)
D_AUSSEN, D_INNEN, STUETZE = 0.16, 0.11, 0.16
SIGNET = "M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"
SEITE = fitz.Rect(0, 0, 1190.55, 841.89)                 # A3 quer
FELD = fitz.Rect(40, 40, 1190.55 - 300, 841.89 - 40)     # Planbereich links vom Plankopf


class Plan:
    def __init__(self, doc, haus, titel, box, details=()):
        """box = (x0, y0, x1, y1) in m: alles, was gezeichnet wird. Der Plan wird darin mittig gesetzt."""
        self.S = S = doc.new_page(width=SEITE.width, height=SEITE.height)
        for n, f in (('mt', 'MundialThin'), ('ml', 'MundialLight'), ('mr', 'MundialRegular')):
            S.insert_font(fontname=n, fontfile=f'{SCHRIFTEN}/{f}.otf')
        self.font = fitz.Font(fontfile=f'{SCHRIFTEN}/MundialRegular.otf')
        bw, bh = (box[2] - box[0]) * M, (box[3] - box[1]) * M
        if bw > FELD.width or bh > FELD.height:
            raise ValueError(f'Plan zu groß für A3 1:100: {bw / M:.1f} x {bh / M:.1f} m')
        self.ox = FELD.x0 + (FELD.width - bw) / 2 - box[0] * M
        self.oy = FELD.y0 + (FELD.height - bh) / 2 - box[1] * M
        self.haus, self.titel, self.details = haus, titel, details
        self._waende, self._beim_zeichnen = [], False

    # ---------- Grundlagen ----------
    def p(self, x, y):
        if self._waende and not self._beim_zeichnen:
            self._waende_zeichnen()
        return fitz.Point(self.ox + x * M, self.oy + y * M)

    def _waende_zeichnen(self):
        """Gesammelte Wände als eine Fläche zeichnen: erst alle Konturen (doppelt breit), dann alle Füllungen.
        So verschwinden innere Kanten an Ecken und Anschlüssen, nur der Außenumriss bleibt sichtbar."""
        self._beim_zeichnen = True
        rects = []
        for (x0, y0, x1, y1, d, fuell, senk) in self._waende:
            def trifft(px, py):
                for (a0, b0, a1, b1, dd, _, sk) in self._waende:
                    r = (a0 - dd / 2, b0, a1 + dd / 2, b1) if sk else (a0, b0 - dd / 2, a1, b1 + dd / 2)
                    if (a0, b0, a1, b1) != (x0, y0, x1, y1) and r[0] - 1e-3 <= px <= r[2] + 1e-3 and r[1] - 1e-3 <= py <= r[3] + 1e-3:
                        return True
                return False
            if senk:
                e0 = d / 2 if trifft(x0, y0) else 0; e1 = d / 2 if trifft(x0, y1) else 0
                rects.append(((x0 - d / 2, y0 - e0, x0 + d / 2, y1 + e1), d, fuell))
            else:
                e0 = d / 2 if trifft(x0, y0) else 0; e1 = d / 2 if trifft(x1, y0) else 0
                rects.append(((x0 - e0, y0 - d / 2, x1 + e1, y0 + d / 2), d, fuell))
        for r, d, f in rects:
            self.S.draw_rect(self.r(*r), color=INK, width=(1.1 if d >= D_AUSSEN else 0.9), fill=None)
        for r, d, f in rects:
            self.S.draw_rect(self.r(*r), color=None, fill=f)
        self._waende = []
        self._beim_zeichnen = False

    def r(self, x0, y0, x1, y1):
        return fitz.Rect(self.p(min(x0, x1), min(y0, y1)), self.p(max(x0, x1), max(y0, y1)))

    def ton(self, x0, y0, x1, y1, farbe=FLAECHE):
        self.S.draw_rect(self.r(x0, y0, x1, y1), color=None, fill=farbe)

    def polygon(self, pts, farbe=FLAECHE):
        sh = self.S.new_shape()
        sh.draw_polyline([self.p(*q) for q in pts] + [self.p(*pts[0])])
        sh.finish(color=None, fill=farbe, closePath=True)
        sh.commit()

    def linie(self, pts, farbe=GRAU, breite=0.35, strich=None, zu=False):
        sh = self.S.new_shape()
        sh.draw_polyline([self.p(*q) for q in pts] + ([self.p(*pts[0])] if zu else []))
        sh.finish(color=farbe, width=breite, dashes=strich, lineCap=1, lineJoin=1, closePath=False)
        sh.commit()

    def kasten(self, x0, y0, x1, y1, farbe=GRAU, breite=0.35, fuellung=None, strich=None, rund=None):
        self.S.draw_rect(self.r(x0, y0, x1, y1), color=farbe, fill=fuellung, width=breite, dashes=strich, radius=rund)

    def kreis(self, x, y, rad, farbe=GRAU, breite=0.35, fuellung=None):
        self.S.draw_circle(self.p(x, y), rad * M, color=farbe, fill=fuellung, width=breite)

    def bogen(self, x, y, rad, a0, a1, farbe=GRAU, breite=0.3, n=20):
        a0, a1 = math.radians(a0), math.radians(a1)
        self.linie([(x + rad * math.cos(a0 + (a1 - a0) * k / n), y + rad * math.sin(a0 + (a1 - a0) * k / n)) for k in range(n + 1)], farbe, breite)

    def strichlinie(self, pts, farbe=HELL, breite=0.5, zu=False):
        self.linie(pts, farbe, breite, '[4 3] 0', zu)

    # ---------- Außenanlagen ----------
    def terrasse(self, x0, y0, x1, y1):
        self.ton(x0, y0, x1, y1, BELAG)

    def dach(self, pts):
        """Dachkante / Dachüberstand als Strichlinie (geschlossener Umriss)."""
        self.strichlinie(pts, HELL, 0.55, zu=True)

    def dachflaeche(self, pts, titel=None, tx=None, ty=None):
        """Dach eines tieferen Bauteils in der Aufsicht (Obergeschoss): fast weiß, feine Kontur."""
        self.polygon(pts, (0.99, 0.988, 0.982))
        self.linie(pts, HELL, 0.5, zu=True)
        if titel:
            self.name(titel, tx, ty, klein=True)

    def carport(self, x0, y0, x1, y1, pfosten, dach=None, wand=None, titel='Carport', auto=2):
        self.ton(x0, y0, x1, y1, BELAG)
        if dach:
            self.strichlinie(dach, HELL, 0.55, zu=True)
        if wand:
            for w in wand:
                self.wand(*w, d=D_AUSSEN, fuellung=WAND)
        for x, y in pfosten:
            self.stuetze(x, y)
        nebeneinander_x = (y1 - y0) >= 4.4 and (x1 - x0) >= 2.4 * auto
        for k in range(auto):
            if nebeneinander_x:                       # Autos senkrecht, nebeneinander
                b = (x1 - x0) / auto
                cx, l = x0 + b * (k + 0.5), min(4.6, y1 - y0 - 0.2) / 2
                self.auto(cx - 0.9, (y0 + y1) / 2 - l, cx + 0.9, (y0 + y1) / 2 + l)
            else:                                     # Autos waagrecht, untereinander
                b = (y1 - y0) / auto
                cy, l = y0 + b * (k + 0.5), min(4.6, x1 - x0 - 0.2) / 2
                self.auto((x0 + x1) / 2 - l, cy - 0.9, (x0 + x1) / 2 + l, cy + 0.9)
        lang_y = not nebeneinander_x
        if titel:
            self.name(titel, (x0 + x1) / 2, (y0 + y1) / 2, klein=True)

    def auto(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1, HELL, 0.45, rund=0.18)
        lang_y = (y1 - y0) > (x1 - x0)
        l = (y1 - y0) if lang_y else (x1 - x0)
        for t in (0.3, 0.7):
            if lang_y:
                self.linie([(x0 + 0.12, y0 + l * t), (x1 - 0.12, y0 + l * t)], HELL, 0.35)
            else:
                self.linie([(x0 + l * t, y0 + 0.12), (x0 + l * t, y1 - 0.12)], HELL, 0.35)

    # ---------- Rohbau ----------
    def wand(self, x0, y0, x1, y1, d=D_INNEN, fuellung=WAND):
        """Wand auf der Achse (x0,y0)-(x1,y1), Dicke d."""
        if x0 == x1:
            self._waende.append((x0, min(y0, y1), x0, max(y0, y1), d, fuellung, True))
        else:
            self._waende.append((min(x0, x1), y0, max(x0, x1), y0, d, fuellung, False))

    def aussenwand(self, x0, y0, x1, y1):
        self.wand(x0, y0, x1, y1, D_AUSSEN)

    def glas(self, x0, y0, x1, y1, d=D_AUSSEN):
        """Festverglasung / Fenster zwischen zwei Stützen (zwei feine Linien, Rahmen an den Enden)."""
        if y0 == y1:
            self.S.draw_rect(self.r(x0, y0 - d / 2, x1, y0 + d / 2), color=None, fill=(1, 1, 1))
            for dy in (-d / 2, d / 2):
                self.linie([(x0, y0 + dy), (x1, y0 + dy)], INK, 0.3)
            self.linie([(x0, y0), (x1, y0)], INK, 0.3)
        else:
            self.S.draw_rect(self.r(x0 - d / 2, y0, x0 + d / 2, y1), color=None, fill=(1, 1, 1))
            for dx in (-d / 2, d / 2):
                self.linie([(x0 + dx, y0), (x0 + dx, y1)], INK, 0.3)
            self.linie([(x0, y0), (x0, y1)], INK, 0.3)

    def stuetze(self, x, y, s=STUETZE):
        self.S.draw_rect(self.r(x - s / 2, y - s / 2, x + s / 2, y + s / 2), color=None, fill=INK)

    def stuetzen(self, punkte):
        for x, y in punkte:
            self.stuetze(x, y)

    def tuer(self, x, y, b, achse, entlang=1, auf=1, d=D_INNEN, winkel=90):
        """Drehtür. Anschlag bei (x, y); achse 'h' = Wand waagrecht, 'v' = senkrecht.
        entlang: Richtung der Öffnung entlang der Wand (+1/-1), auf: Schlagrichtung quer zur Wand (+1/-1).
        winkel: Öffnungswinkel des gezeichneten Türblatts (Standard 90°)."""
        if achse == 'h':
            xa, xb = sorted((x, x + entlang * b))
            self.S.draw_rect(self.r(xa, y - d / 2 - 0.01, xb, y + d / 2 + 0.01), color=None, fill=(1, 1, 1))
            for xx in (xa, xb):
                self.linie([(xx, y - d / 2), (xx, y + d / 2)], INK, 0.55)
            senk, wand = (90 if auf > 0 else -90), (0 if entlang > 0 else 180)
        else:
            ya, yb = sorted((y, y + entlang * b))
            self.S.draw_rect(self.r(x - d / 2 - 0.01, ya, x + d / 2 + 0.01, yb), color=None, fill=(1, 1, 1))
            for yy in (ya, yb):
                self.linie([(x - d / 2, yy), (x + d / 2, yy)], INK, 0.55)
            senk, wand = (0 if auf > 0 else 180), (90 if entlang > 0 else -90)
        if abs(senk - wand) > 180:
            senk += 360 if senk < wand else -360
        a0 = wand + (senk - wand) * winkel / 90
        blatt = (x + b * math.cos(math.radians(a0)), y + b * math.sin(math.radians(a0)))
        self.linie([(x, y), blatt], INK, 0.55)
        self.bogen(x, y, b, a0, wand, GRAU, 0.3)

    def schiebetuer(self, x0, y0, x1, y1, d=D_AUSSEN):
        """Schiebetür in einer Glasfassade: Glas plus versetzte Flügel."""
        self.glas(x0, y0, x1, y1, d)
        if y0 == y1:
            m = (x0 + x1) / 2
            self.linie([(x0, y0 - 0.05), (m + 0.1, y0 - 0.05)], INK, 0.6); self.linie([(m - 0.1, y0 + 0.05), (x1, y0 + 0.05)], INK, 0.6)
        else:
            m = (y0 + y1) / 2
            self.linie([(x0 - 0.05, y0), (x0 - 0.05, m + 0.1)], INK, 0.6); self.linie([(x0 + 0.05, m - 0.1), (x0 + 0.05, y1)], INK, 0.6)

    def treppe(self, x0, y0, x1, y1, stufen, rauf, schnitt=None):
        """Gerade Treppe im Rechteck. rauf: '+x', '-x', '+y', '-y' (Laufrichtung nach oben).
        schnitt: Anteil (0..1) des Laufs, ab dem die Treppe als 'über der Schnittebene' gestrichelt ist (EG)."""
        self.S.draw_rect(self.r(x0, y0, x1, y1), color=INK, fill=(1, 1, 1), width=0.45)
        senk = rauf[1] == 'y'
        a, b = (y0, y1) if senk else (x0, x1)
        if rauf[0] == '-':
            a, b = b, a
        for k in range(1, stufen):
            t = a + (b - a) * k / stufen
            gestr = schnitt is not None and k / stufen > schnitt
            pts = [(x0, t), (x1, t)] if senk else [(t, y0), (t, y1)]
            self.linie(pts, GRAU, 0.3, '[2 2] 0' if gestr else None)
        m = (x0 + x1) / 2 if senk else (y0 + y1) / 2
        ende = a + (b - a) * (schnitt if schnitt else 1)
        start = a + (b - a) * 0.5 / stufen
        lauf = [(m, start), (m, ende)] if senk else [(start, m), (ende, m)]
        self.kreis(*lauf[0], 0.06, INK, 0.4, (1, 1, 1))
        self.linie(lauf, INK, 0.4)
        sg = 1 if b > a else -1
        e = lauf[1]
        if senk:
            self.linie([(e[0] - 0.13, e[1] - sg * 0.22), e, (e[0] + 0.13, e[1] - sg * 0.22)], INK, 0.4)
        else:
            self.linie([(e[0] - sg * 0.22, e[1] - 0.13), e, (e[0] - sg * 0.22, e[1] + 0.13)], INK, 0.4)
        if schnitt:
            c = a + (b - a) * schnitt
            if senk:
                self.linie([(x0, c + 0.15), (x1, c - 0.15)], INK, 0.45)
            else:
                self.linie([(c - 0.15, y1), (c + 0.15, y0)], INK, 0.45)

    def luftraum(self, x0, y0, x1, y1):
        self.linie([(x0, y0), (x1, y1)], HELL, 0.4, '[3 2] 0'); self.linie([(x0, y1), (x1, y0)], HELL, 0.4, '[3 2] 0')

    def gelaender(self, pts):
        self.linie(pts, INK, 0.9)

    def kniestock(self, pts):
        """Linie der Dachschräge (ca. 1,5 m Raumhöhe)."""
        self.linie(pts, HELL, 0.4, '[6 2 1 2] 0')

    # ---------- Möbel ----------
    def bett(self, x, y, b=1.8, l=2.0, kopf='-y'):
        if kopf in ('-y', '+y'):
            x0, y0, x1, y1 = x, y, x + b, y + l
        else:
            x0, y0, x1, y1 = x, y, x + l, y + b
        self.kasten(x0, y0, x1, y1)
        n = 2 if b > 1.3 else 1
        for k in range(n):
            if kopf == '-y':
                a = x0 + 0.08 + k * (b - 0.16) / n
                self.kasten(a, y0 + 0.08, a + (b - 0.16) / n - 0.06, y0 + 0.48, rund=0.05)
            elif kopf == '+y':
                a = x0 + 0.08 + k * (b - 0.16) / n
                self.kasten(a, y1 - 0.48, a + (b - 0.16) / n - 0.06, y1 - 0.08, rund=0.05)
            elif kopf == '-x':
                a = y0 + 0.08 + k * (b - 0.16) / n
                self.kasten(x0 + 0.08, a, x0 + 0.48, a + (b - 0.16) / n - 0.06, rund=0.05)
            else:
                a = y0 + 0.08 + k * (b - 0.16) / n
                self.kasten(x1 - 0.48, a, x1 - 0.08, a + (b - 0.16) / n - 0.06, rund=0.05)
        if kopf == '-y':
            self.linie([(x0, y0 + 0.65), (x1, y0 + 0.65)])
        elif kopf == '+y':
            self.linie([(x0, y1 - 0.65), (x1, y1 - 0.65)])
        elif kopf == '-x':
            self.linie([(x0 + 0.65, y0), (x0 + 0.65, y1)])
        else:
            self.linie([(x1 - 0.65, y0), (x1 - 0.65, y1)])

    def sofa(self, x0, y0, x1, y1, lehne='-y'):
        """Sofa im Rechteck, Lehne an der genannten Seite, Armlehnen an den Enden."""
        self.kasten(x0, y0, x1, y1, rund=0.06)
        d, a = 0.2, 0.18
        if lehne in ('-y', '+y'):
            ly = y0 + d if lehne == '-y' else y1 - d
            self.linie([(x0 + a, ly), (x1 - a, ly)])
            self.linie([(x0 + a, y0), (x0 + a, y1)]); self.linie([(x1 - a, y0), (x1 - a, y1)])
        else:
            lx = x0 + d if lehne == '-x' else x1 - d
            self.linie([(lx, y0 + a), (lx, y1 - a)])
            self.linie([(x0, y0 + a), (x1, y0 + a)]); self.linie([(x0, y1 - a), (x1, y1 - a)])

    def sessel(self, x, y, lehne='-y', s=0.75):
        self.sofa(x - s / 2, y - s / 2, x + s / 2, y + s / 2, lehne)

    def couchtisch(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1, rund=0.04)

    def tisch(self, x0, y0, x1, y1, laengs=None, stirn=0):
        """Esstisch mit Stühlen: laengs = Stühle je Längsseite (automatisch), stirn = Stühle je Stirnseite."""
        self.kasten(x0, y0, x1, y1)
        lang_x = (x1 - x0) >= (y1 - y0)
        n = laengs or max(1, int(((x1 - x0) if lang_x else (y1 - y0)) / 0.7))
        def stuhl(cx, cy, seite):
            s = 0.21
            self.kasten(cx - s, cy - s, cx + s, cy + s, rund=0.05)
            if seite == 'o':
                self.linie([(cx - s, cy - s + 0.06), (cx + s, cy - s + 0.06)])
            elif seite == 'u':
                self.linie([(cx - s, cy + s - 0.06), (cx + s, cy + s - 0.06)])
            elif seite == 'l':
                self.linie([(cx - s + 0.06, cy - s), (cx - s + 0.06, cy + s)])
            else:
                self.linie([(cx + s - 0.06, cy - s), (cx + s - 0.06, cy + s)])
        for k in range(n):
            t = (k + 0.5) / n
            if lang_x:
                cx = x0 + (x1 - x0) * t
                stuhl(cx, y0 - 0.3, 'o'); stuhl(cx, y1 + 0.3, 'u')
            else:
                cy = y0 + (y1 - y0) * t
                stuhl(x0 - 0.3, cy, 'l'); stuhl(x1 + 0.3, cy, 'r')
        for k in range(stirn):
            t = (k + 0.5) / stirn
            if lang_x:
                cy = y0 + (y1 - y0) * t
                stuhl(x0 - 0.3, cy, 'l'); stuhl(x1 + 0.3, cy, 'r')
            else:
                cx = x0 + (x1 - x0) * t
                stuhl(cx, y0 - 0.3, 'o'); stuhl(cx, y1 + 0.3, 'u')

    def rundtisch(self, x, y, rad=0.45, stuehle=4):
        self.kreis(x, y, rad)
        for k in range(stuehle):
            a = 2 * math.pi * k / stuehle + math.pi / 4
            cx, cy = x + (rad + 0.25) * math.cos(a), y + (rad + 0.25) * math.sin(a)
            self.kasten(cx - 0.19, cy - 0.19, cx + 0.19, cy + 0.19, rund=0.05)

    def schrank(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1)
        self.linie([(x0, y0), (x1, y1)], GRAU, 0.25); self.linie([(x0, y1), (x1, y0)], GRAU, 0.25)

    def regal(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1)

    def sideboard(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1)
        if (x1 - x0) > (y1 - y0):
            self.linie([(x0, (y0 + y1) / 2), (x1, (y0 + y1) / 2)], GRAU, 0.2)
        else:
            self.linie([((x0 + x1) / 2, y0), ((x0 + x1) / 2, y1)], GRAU, 0.2)

    def schreibtisch(self, x0, y0, x1, y1, stuhl=None):
        self.kasten(x0, y0, x1, y1)
        if stuhl:
            self.kreis(*stuhl, 0.24)

    def zeile(self, x0, y0, x1, y1):
        """Küchenzeile / Arbeitsplatte (ohne Geräte)."""
        self.kasten(x0, y0, x1, y1)

    def kochfeld(self, x, y, quer=False):
        for dx, dy in ((-0.13, -0.13), (0.13, -0.13), (-0.13, 0.13), (0.13, 0.13)):
            self.kreis(x + dx, y + dy, 0.09, GRAU, 0.3)

    def spuele(self, x, y, quer=False):
        if quer:
            self.kasten(x - 0.2, y - 0.38, x + 0.2, y + 0.38, rund=0.05); self.kreis(x, y - 0.1, 0.03)
        else:
            self.kasten(x - 0.38, y - 0.2, x + 0.38, y + 0.2, rund=0.05); self.kreis(x - 0.1, y, 0.03)

    def wc(self, x, y, rueck='-y'):
        """WC mit Spülkasten an der Wand (rueck = Seite der Wand)."""
        if rueck == '-y':
            self.kasten(x - 0.2, y, x + 0.2, y + 0.15); self.S.draw_oval(self.r(x - 0.18, y + 0.15, x + 0.18, y + 0.6), color=GRAU, width=0.35)
        elif rueck == '+y':
            self.kasten(x - 0.2, y - 0.15, x + 0.2, y); self.S.draw_oval(self.r(x - 0.18, y - 0.6, x + 0.18, y - 0.15), color=GRAU, width=0.35)
        elif rueck == '-x':
            self.kasten(x, y - 0.2, x + 0.15, y + 0.2); self.S.draw_oval(self.r(x + 0.15, y - 0.18, x + 0.6, y + 0.18), color=GRAU, width=0.35)
        else:
            self.kasten(x - 0.15, y - 0.2, x, y + 0.2); self.S.draw_oval(self.r(x - 0.6, y - 0.18, x - 0.15, y + 0.18), color=GRAU, width=0.35)

    def waschtisch(self, x0, y0, x1, y1, becken=1):
        self.kasten(x0, y0, x1, y1)
        quer = (y1 - y0) > (x1 - x0)
        for k in range(becken):
            if quer:
                cx, cy = (x0 + x1) / 2, y0 + (y1 - y0) * (k + 0.5) / becken
                self.S.draw_oval(self.r(cx - 0.13, cy - 0.19, cx + 0.13, cy + 0.19), color=GRAU, width=0.3)
            else:
                cx, cy = x0 + (x1 - x0) * (k + 0.5) / becken, (y0 + y1) / 2
                self.S.draw_oval(self.r(cx - 0.19, cy - 0.13, cx + 0.19, cy + 0.13), color=GRAU, width=0.3)

    def dusche(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1)
        self.linie([(x0, y0), (x1, y1)], GRAU, 0.22); self.linie([(x0, y1), (x1, y0)], GRAU, 0.22)

    def wanne(self, x0, y0, x1, y1):
        self.kasten(x0, y0, x1, y1)
        self.kasten(x0 + 0.08, y0 + 0.08, x1 - 0.08, y1 - 0.08, rund=0.35)

    def geraet(self, x0, y0, x1, y1, text=None):
        """Waschmaschine, Trockner u. ä.: Quadrat mit Kreis."""
        self.kasten(x0, y0, x1, y1)
        self.kreis((x0 + x1) / 2, (y0 + y1) / 2, min(x1 - x0, y1 - y0) * 0.32, GRAU, 0.3)

    def speicher(self, x, y, rad=0.35):
        """Speicher / Heizung: Kreis."""
        self.kreis(x, y, rad); self.kreis(x, y, rad * 0.55, GRAU, 0.25)

    def kaminofen(self, x, y, rad=0.3):
        self.kreis(x, y, rad, INK, 0.45, (1, 1, 1)); self.kreis(x, y, rad * 0.45, INK, 0.3, INK)

    def drehkasten(self, x, y, w, l, winkel=0, farbe=GRAU, innen=None):
        """Gedrehtes Rechteck um Mittelpunkt (x, y), z. B. Chaiselongue; innen = Abstand einer inneren Kontur."""
        a = math.radians(winkel)
        def ecken(w, l):
            return [(x + px * math.cos(a) - py * math.sin(a), y + px * math.sin(a) + py * math.cos(a))
                    for px, py in ((-w / 2, -l / 2), (w / 2, -l / 2), (w / 2, l / 2), (-w / 2, l / 2))]
        self.linie(ecken(w, l), farbe, 0.35, zu=True)
        if innen:
            self.linie(ecken(w - 2 * innen, l - 2 * innen), farbe, 0.3, zu=True)

    def lichtschacht(self, x0, y0, x1, y1):
        self.ton(x0, y0, x1, y1, BELAG); self.kasten(x0, y0, x1, y1, INK, 0.45)

    def tor(self, x0, y0, x1, y1):
        """Garagentor: Öffnung mit Torlinie."""
        self.linie([(x0, y0), (x1, y1)], INK, 0.5, '[4 2] 0')

    def liege(self, x, y, w=0.7, l=1.9, winkel=0):
        a = math.radians(winkel)
        ecken = [(-w / 2, -l / 2), (w / 2, -l / 2), (w / 2, l / 2), (-w / 2, l / 2)]
        pts = [(x + px * math.cos(a) - py * math.sin(a), y + px * math.sin(a) + py * math.cos(a)) for px, py in ecken]
        self.linie(pts, HELL, 0.45, zu=True)
        q = [(-w / 2, -l / 2 + 0.55), (w / 2, -l / 2 + 0.55)]
        self.linie([(x + px * math.cos(a) - py * math.sin(a), y + px * math.sin(a) + py * math.cos(a)) for px, py in q], HELL, 0.4)

    # ---------- Beschriftung ----------
    def name(self, text, x, y, klein=False):
        fs = 6.6 if klein else 8
        m = self.p(x, y)
        b = self.font.text_length(text, fontsize=fs)
        self.S.draw_rect(fitz.Rect(m.x - b / 2 - 2.5, m.y - fs * 0.6, m.x + b / 2 + 2.5, m.y + fs * 0.45), color=None, fill=(1, 1, 1), fill_opacity=0.8)
        self.S.insert_text(fitz.Point(m.x - b / 2, m.y + fs * 0.33), text, fontname='mr', fontsize=fs, color=INK if not klein else TEXTGRAU)

    def nordpfeil(self, x, y):
        self.kreis(x, y, 0.45, GRAU, 0.35)
        self.S.draw_polyline([self.p(x, y - 0.62), self.p(x + 0.2, y + 0.28), self.p(x, y + 0.12), self.p(x - 0.2, y + 0.28), self.p(x, y - 0.62)], color=None, fill=INK)

    def signet(self, x, y, h):
        s = h / 1073
        sh = self.S.new_shape()
        for teil in SIGNET.split('Z'):
            zahlen = re.findall(r'([ML])(\d+),(\d+)', teil)
            if zahlen:
                pts = [fitz.Point(x + int(a) * s, y + int(b) * s) for _, a, b in zahlen]
                sh.draw_polyline(pts + [pts[0]])
        sh.finish(fill=INK, color=None, closePath=True)
        sh.commit()

    def fertig(self):
        """Plankopf rechts."""
        if self._waende:
            self._waende_zeichnen()
        S = self.S
        x0 = SEITE.width - 260
        S.draw_line(fitz.Point(x0 - 30, 40), fitz.Point(x0 - 30, SEITE.height - 40), color=(0.88, 0.875, 0.86), width=0.6)
        self.signet(x0, 46, 22)
        S.insert_text(fitz.Point(x0 + 27, 63.5), 'D A V I N C I', fontname='ml', fontsize=15, color=INK)
        y = 150
        S.draw_line(fitz.Point(x0, y - 3.5), fitz.Point(x0 + 18, y - 3.5), color=AKZENT, width=0.7)
        S.insert_text(fitz.Point(x0 + 26, y), 'G R U N D R I S S', fontname='mr', fontsize=7, color=AKZENT)
        S.insert_text(fitz.Point(x0, y + 36), self.haus, fontname='ml', fontsize=30, color=INK)
        S.insert_text(fitz.Point(x0, y + 66), self.titel, fontname='ml', fontsize=19, color=TEXTGRAU)
        # Eckdaten aus dem Originalentwurf, klein
        yd = y + 104
        for k, (lab, wert) in enumerate(self.details):
            S.draw_line(fitz.Point(x0, yd + k * 17 - 10.5), fitz.Point(x0 + 200, yd + k * 17 - 10.5), color=(0.9, 0.895, 0.88), width=0.5)
            S.insert_text(fitz.Point(x0, yd + k * 17), lab, fontname='mr', fontsize=7, color=TEXTGRAU)
            S.insert_text(fitz.Point(x0 + 78, yd + k * 17), wert, fontname='mr', fontsize=7.5, color=INK)
        if self.details:
            S.draw_line(fitz.Point(x0, yd + len(self.details) * 17 - 10.5), fitz.Point(x0 + 200, yd + len(self.details) * 17 - 10.5), color=(0.9, 0.895, 0.88), width=0.5)
        hinweis = ['Beispielgrundriss. Jedes DAVINCI Haus wird', 'für Grundstück und Bauherren individuell', 'geplant. Maße, Fenster und Ausstattung', 'können im Entwurf abweichen.']
        for k, z in enumerate(hinweis):
            S.insert_text(fitz.Point(x0, SEITE.height - 100 + k * 11.5), z, fontname='ml', fontsize=7.5, color=TEXTGRAU)
        S.insert_text(fitz.Point(x0, SEITE.height - 44), 'davinci-homes.de', fontname='mr', fontsize=8.5, color=INK)
        S.insert_text(fitz.Point(x0 + 118, SEITE.height - 44), 'Designed to stay.', fontname='ml', fontsize=8.5, color=TEXTGRAU)


def speichern(doc, haus, pfad, einzeln=None):
    """Alle Geschosse in pfad; mit einzeln = Ordner zusätzlich je Geschoss eine Datei."""
    if einzeln:
        for i in range(len(doc)):
            e = fitz.open(); e.insert_pdf(doc, from_page=i, to_page=i)
            titel = doc[i].get_text().split('\n')
            g = next((t for t in titel if t.strip().endswith(('geschoss', 'haus'))), f'Seite {i + 1}')
            e.set_metadata({'title': f'{haus} · {g}', 'author': 'DAVINCI', 'creator': 'DAVINCI', 'producer': 'DAVINCI', 'subject': 'Grundriss', 'keywords': ''})
            e.save(os.path.join(einzeln, f'{haus} - {g.strip()}.pdf'), garbage=4, deflate=True, clean=True)
    doc.set_metadata({'title': f'{haus} · Grundrisse', 'author': 'DAVINCI', 'creator': 'DAVINCI', 'producer': 'DAVINCI', 'subject': 'Grundriss', 'keywords': ''})
    doc.save(pfad, garbage=4, deflate=True, clean=True)
