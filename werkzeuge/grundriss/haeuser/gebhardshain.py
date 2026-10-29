"""Haus Gebhardshain – realisiertes DAVINCI Haus, nach den colorierten Bauherrenplänen (EG, DG), Scan 1:100.
Satteldach, Wohnebene mit Kochen/Essen/Wohnen, DG mit Schlafen, Bad und Studio, Doppelcarport und Naturteich.
Ausgabe in Grundriss-Ausrichtung des DG (Schrift lesbar); Ursprung = Außenstütze oben links des Hauses.
EG wird in Scan-Koordinaten erfasst (Blatt hochkant, Plan um 90° gedreht) und in Q.p gedreht und auf 9,20 m Achsmaß gebracht,
das DG in Koordinaten des um 180° gedrehten Scans (nur verschoben).
Aufruf: gebhardshain.py ausgabe.pdf [ordner_einzeln] [--farbig]"""
import sys, os, math
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import fitz
from zeichnen import Plan, speichern, D_AUSSEN, D_INNEN, GRAU, HELL, INK, BELAG, FLAECHE, FELD, SEITE

HAUS = 'Gebhardshain'
HINWEIS = ['Realisiertes DAVINCI Haus in Gebhardshain.', 'Grundriss nach den Bauherrenplänen;', 'Möblierung beispielhaft, Maße können', 'vom gebauten Zustand abweichen.']
BOX = (-9.7, -4.9, 18.1, 14.2)
DETAILS = [('Dach', 'SD'), ('Ebenen', 'EG · DG')]
FARBIG = '--farbig' in sys.argv

# Farben der colorierten Fassung (gedämpft, passend zum DAVINCI Look)
HOLZ = (0.935, 0.880, 0.790)
FLIESE = (0.865, 0.890, 0.905)
RASEN = (0.790, 0.870, 0.700)
RASEN_DUNKEL = (0.700, 0.810, 0.610)
BAUM = (0.560, 0.700, 0.480)
BAUM_RAND = (0.420, 0.560, 0.360)
STRAUCH = (0.640, 0.760, 0.540)
WASSER = (0.600, 0.780, 0.880)
UFER = (0.420, 0.600, 0.700)
KIES = (0.885, 0.875, 0.845)
STEIN = (0.900, 0.850, 0.760)
DACH_DG = (0.800, 0.800, 0.800)


class EG(Plan):
    """Erdgeschoss-Scan: Achsen x 0.24..9.40, y 1.29..10.50 -> Plan X 0..9.2, Y 0..9.2 (90° gegen den Uhrzeigersinn)."""
    def p(self, x, y):
        return super().p((y - 1.29) * 9.2 / 9.21, (9.40 - x) * 9.2 / 9.16)

    def r(self, x0, y0, x1, y1):
        a, b = self.p(x0, y0), self.p(x1, y1)
        return fitz.Rect(min(a.x, b.x), min(a.y, b.y), max(a.x, b.x), max(a.y, b.y))


def eg_inv(X, Y):
    """Plan- in Scan-Koordinaten (für Elemente, die im DG-Rahmen bemessen sind)."""
    return 9.40 - Y * 9.16 / 9.2, X * 9.21 / 9.2 + 1.29


class DG(Plan):
    """Dachgeschoss-Scan um 180° gedreht: Achsen x 1.30..10.49, y -0.82..8.37."""
    def p(self, x, y):
        return super().p(x - 1.30, y + 0.82)


def glatt(pts, n=8):
    """Geschlossene Catmull-Rom-Kurve durch die Punkte."""
    out = []
    for i in range(len(pts)):
        p0, p1, p2, p3 = pts[i - 1], pts[i], pts[(i + 1) % len(pts)], pts[(i + 2) % len(pts)]
        for k in range(n):
            t = k / n
            out.append(tuple(0.5 * ((2 * p1[j]) + (-p0[j] + p2[j]) * t + (2 * p0[j] - 5 * p1[j] + 4 * p2[j] - p3[j]) * t * t
                                    + (-p0[j] + 3 * p1[j] - 3 * p2[j] + p3[j]) * t ** 3) for j in (0, 1)))
    return out


def scheibe(P, x, y, r, farbe, rand=None):
    P.kreis(x, y, r, rand or farbe, 0.4 if rand else 0.01, farbe)


def maske(P):
    """Alles außerhalb des Planfelds weiß abdecken (Garten läuft über den Ausschnitt hinaus)."""
    S = P.S
    for r in (fitz.Rect(0, 0, SEITE.width, FELD.y0), fitz.Rect(0, FELD.y1, SEITE.width, SEITE.height),
              fitz.Rect(0, 0, FELD.x0, SEITE.height), fitz.Rect(FELD.x1, 0, SEITE.width, SEITE.height)):
        S.draw_rect(r, color=None, fill=(1, 1, 1))


TEICH = [(1.0, 14.2), (2.2, 13.2), (3.6, 13.0), (4.6, 13.6), (5.6, 14.0), (6.8, 13.4), (8.0, 12.4), (9.6, 12.0), (10.9, 12.5),
         (11.0, 13.8), (10.2, 15.0), (8.6, 15.6), (7.2, 16.2), (6.2, 17.4), (5.0, 18.6), (3.4, 19.0), (1.8, 18.4), (0.8, 17.0), (0.6, 15.5)]
GRUNDSTUECK = [(4.6, -13.3), (17.4, -2.7), (17.4, 7.0), (11.4, 25.5), (-1.6, 25.5), (-9.8, -3.0), (-9.8, -13.3)]
PFLASTER = [(-4.95, -9.6), (4.6, -9.6), (4.6, 1.2), (0.24, 1.2), (0.24, 10.5), (1.7, 10.5), (1.7, 11.4), (1.0, 11.9), (-3.0, 11.9),
            (-3.6, 11.4), (-3.6, 5.6), (-4.0, 4.5), (-4.0, -2.5), (-4.95, -3.2)]


def eg_plan(x, y):
    return (y - 1.29) * 9.2 / 9.21, (9.40 - x) * 9.2 / 9.16


BAEUME = [(5.9, -7.4, 0.95), (7.4, -6.3, 0.6), (10.2, -2.2, 0.8), (12.6, -1.8, 1.0), (13.6, -0.9, 0.8), (12.9, 8.6, 0.9),
          (13.4, 3.0, 0.7), (-3.9, 13.4, 0.8), (12.2, 10.6, 0.7), (-4.4, 0.5, 0.9), (-4.6, 2.4, 0.8)]
STRAEUCHER = [(10.6, 3.6), (11.3, 5.4), (10.4, 6.6), (11.0, 0.6), (10.7, 9.9), (4.0, 11.5), (8.4, 11.6), (11.8, 11.0), (-1.0, 12.4),
              (-4.3, 12.5), (6.5, -8.3), (8.6, -8.0), (9.8, -4.6), (12.2, 1.2), (12.8, 4.8), (9.6, 10.9), (2.4, 12.2), (11.6, 13.6),
              (1.4, 19.3), (6.8, 18.6)]


def garten(P, f, farbig=None):
    """Grundstück, Pflaster, Teich, Sitzplatz, Bäume. Koordinaten im EG-Scan, f bildet auf den Plan ab."""
    farbig = FARBIG if farbig is None else farbig
    F = lambda pts: [f(*q) for q in pts]
    if farbig:
        P.polygon(F(GRUNDSTUECK), RASEN)
        for x, y, r in ((6.3, -4.5, 1.3), (6.25, -1.6, 0.5)):
            scheibe(P, *f(x, y), r, KIES, (0.78, 0.77, 0.74))
        scheibe(P, *f(9.4, 4.0), 2.3, STEIN, (0.80, 0.74, 0.64))
    else:
        scheibe(P, *f(9.4, 4.0), 2.3, (0.975, 0.97, 0.96))
    P.strichlinie(F(GRUNDSTUECK), HELL, 0.6, zu=True)
    P.polygon(F(PFLASTER), KIES if farbig else BELAG)
    teich = glatt(F(TEICH))
    if farbig:
        ufer = glatt(F([(x + (x - 5.8) * 0.06, y + (y - 15.0) * 0.06) for x, y in TEICH]))
        P.polygon(ufer, STEIN)
    P.polygon(teich, WASSER if farbig else (0.965, 0.975, 0.98)); P.linie(teich, UFER if farbig else HELL, 0.6, zu=True)
    scheibe(P, *f(5.85, 12.6), 1.45, STEIN if farbig else BELAG, HELL)
    x0, y0 = f(5.45, 12.2); x1, y1 = f(6.25, 13.0)
    P.tisch(min(x0, x1), min(y0, y1), max(x0, x1), max(y0, y1), laengs=1, stirn=1)
    if farbig:
        for x, y in STRAEUCHER:
            scheibe(P, *f(x, y), 0.38, STRAUCH, BAUM_RAND)
        for x, y, r in BAEUME:
            scheibe(P, *f(x, y), r, BAUM, BAUM_RAND)
            scheibe(P, *f(x - r * 0.25, y - r * 0.25), r * 0.45, (0.62, 0.75, 0.53))


def erdgeschoss(doc):
    P = EG(doc, HAUS, 'Erdgeschoss', BOX, DETAILS)
    garten(P, lambda x, y: (x, y))
    for l in [(-0.22, 1.89, 0.16, 2.99), (-0.22, 6.49, 0.16, 7.55), (4.89, 0.78, 5.91, 1.17), (2.58, 10.6, 3.64, 11.0),
              (9.47, 3.6, 10.0, 5.9), (9.47, 7.3, 10.0, 9.3)]:
        P.lichtschacht(*l)
    # Doppelcarport, Abstell
    P.ton(0.12, -8.0, 4.55, -3.05, BELAG)
    P.auto(0.6, -7.6, 4.3, -5.8); P.auto(0.6, -5.2, 4.3, -3.4)
    P.name('Doppelcarport', 2.45, -5.5, klein=True)
    P.ton(-3.35, 5.85, 0.24, 8.45, FLAECHE if not FARBIG else FLIESE)
    P.dach([eg_inv(X, Y) for X, Y in ((-1.35, -1.13), (10.30, -1.13), (10.30, 10.07), (-1.35, 10.07))])
    # Haus
    P.polygon([(0.24, 1.29), (9.40, 1.29), (9.40, 10.50), (0.24, 10.50)], HOLZ if FARBIG else FLAECHE)
    if FARBIG:
        P.ton(0.24, 1.29, 4.80, 3.60, FLIESE); R0 = [eg_inv(0.0, 0.0), eg_inv(1.12, 2.20)]; P.ton(R0[1][0], R0[0][1], R0[0][0], R0[1][1], FLIESE)
    for w in [(0.24, 1.29, 2.53, 1.29), (7.10, 1.29, 9.40, 1.29), (0.24, 1.89, 0.24, 2.99), (0.24, 5.90, 0.24, 10.50), (9.40, 1.29, 9.40, 2.52),
              (9.40, 3.50, 9.40, 5.90), (9.40, 7.00, 9.40, 9.37), (2.53, 10.50, 4.80, 10.50)]:
        P.aussenwand(*w)
    for w in [(-3.35, 5.85, -3.35, 10.60), (-3.35, 5.85, -1.10, 5.85), (-3.35, 8.45, -1.05, 8.45), (0.12, -8.05, 1.00, -8.05),
              (2.60, -8.05, 4.55, -8.05), (2.40, -3.05, 4.55, -3.05)]:
        P.wand(*w, d=0.16)
    for w in [(1.42, 1.29, 1.42, 2.06), (1.42, 2.82, 1.42, 3.60), (0.24, 3.60, 2.60, 3.60), (4.80, 1.29, 4.80, 3.60), (0.24, 5.90, 4.80, 5.90)]:
        P.wand(*w)
    P.wand(4.55, -7.80, 4.55, 0.0, d=0.08)
    for g in [(3.62, 1.29, 6.00, 1.29), (0.24, 1.29, 0.24, 1.89), (0.24, 2.99, 0.24, 5.90), (9.40, 5.90, 9.40, 7.00), (9.40, 9.37, 9.40, 10.50),
              (0.24, 10.50, 2.53, 10.50), (4.80, 10.50, 6.03, 10.50), (7.10, 10.50, 9.40, 10.50)]:
        P.glas(*g)
    P.glas(3.45, 3.60, 4.80, 3.60, d=0.06)
    for t in [(3.62, 1.29, 0.98, 'h', -1, 1, D_AUSSEN, 32), (7.10, 1.29, 0.98, 'h', -1, 1, D_AUSSEN, 35), (9.40, 2.52, 0.95, 'v', 1, -1, D_AUSSEN, 30),
              (6.03, 10.50, 0.98, 'h', 1, -1, D_AUSSEN, 30), (1.42, 2.82, 0.76, 'v', -1, 1, D_INNEN, 40), (2.60, 3.60, 0.85, 'h', 1, -1, 0.06, 35),
              (-1.10, 5.85, 0.80, 'h', 1, -1, 0.16, 35), (-1.05, 8.45, 0.85, 'h', 1, 1, 0.16, 30)]:
        P.tuer(*t[:6], d=t[6], winkel=t[7])
    P.stuetzen([(0.24, 1.29), (2.53, 1.29), (3.62, 1.29), (4.80, 1.29), (6.00, 1.29), (7.10, 1.29), (9.40, 1.29), (9.40, 2.52), (9.40, 3.50),
                (9.40, 5.90), (9.40, 7.00), (9.40, 9.37), (9.40, 10.50), (7.10, 10.50), (6.03, 10.50), (4.80, 10.50), (2.53, 10.50),
                (0.24, 10.50), (0.24, 5.90), (0.24, 3.60), (2.53, 3.60), (2.53, 5.90), (4.80, 3.60), (7.10, 2.44), (7.10, 3.61), (7.10, 5.90),
                (0.12, -8.0), (4.55, -8.0), (0.12, -3.05), (4.55, -3.05)])
    P.treppe_u(0.32, 3.67, 2.45, 5.83, 6, podest=0.95, oben='-x', start='rechts')
    # WC, Windfang
    P.wc(0.31, 2.05, '-x'); P.waschtisch(0.31, 2.44, 0.85, 2.95)
    P.kasten(1.52, 1.42, 2.58, 1.63); P.regal(1.52, 2.92, 2.45, 3.50)
    # Kochen, Speisekammer (in Plan-Koordinaten bemessen)
    def K(X, Y):
        return eg_inv(X, Y)
    def R(fn, X0, Y0, X1, Y1, *a, **k):
        x0, y0 = K(X0, Y0); x1, y1 = K(X1, Y1)
        fn(min(x0, x1), min(y0, y1), max(x0, x1), max(y0, y1), *a, **k)
    for w in [((0.08, 2.20), (1.12, 2.20))]:
        (xa, ya), (xb, yb) = K(*w[0]), K(*w[1])
        P.wand(xa, ya, xb, yb)
    R(P.kasten, 0.99, 0.12, 1.07, 1.25, INK, 0.45, (1, 1, 1)); R(P.kasten, 1.07, 1.00, 1.15, 2.15, INK, 0.45, (1, 1, 1))  # Schiebetür, geschlossen
    R(P.regal, 0.10, 0.10, 0.44, 2.12); R(P.regal, 0.44, 0.10, 0.98, 0.40)
    R(P.zeile, 2.43, 0.10, 3.89, 0.68); R(P.zeile, 3.89, 0.10, 4.52, 2.18)
    P.kochfeld(*K(3.16, 0.40)); P.spuele(*K(4.21, 1.17))
    P.linie([K(X, Y) for X, Y in ((2.30, 2.18), (4.52, 2.18), (4.52, 2.62), (4.15, 2.88), (3.70, 3.00), (3.10, 3.00), (2.62, 2.86),
                                  (2.30, 2.58))], GRAU, 0.35, zu=True)
    for X, Y in ((2.90, 3.38), (3.47, 3.48), (4.04, 3.38)):
        P.kreis(*K(X, Y), 0.2)
    P.sessel(*K(0.48, 3.72), '+x', 0.5); P.bogen(*K(1.45, 4.47), 0.65, 0, 180); P.linie([K(0.80, 4.47), K(2.10, 4.47)])
    # Essen, Wohnen
    P.rundtisch(7.69, 8.18, 0.55, 6); P.sideboard(9.07, 6.96, 9.30, 9.41)
    P.sideboard(2.50, 6.00, 4.70, 6.48); P.sideboard(2.50, 9.88, 4.70, 10.38)
    P.sofa(0.37, 7.00, 1.31, 9.37, '-x'); P.kasten(0.54, 6.24, 1.22, 6.87); P.kasten(0.54, 9.50, 1.22, 10.13); P.kreis(0.88, 9.82, 0.14)
    P.sessel(1.97, 6.66, '-x', 0.58); P.sessel(1.97, 9.83, '-x', 0.58)
    P.bogen(1.52, 8.16, 0.80, -90, 90); P.linie([(1.52, 7.36), (1.52, 8.96)])
    P.kasten(3.95, 8.38, 4.62, 9.18); P.kaminofen(4.29, 8.78, 0.22)
    # Abstell
    P.strichlinie([(-2.5, 6.45), (-1.6, 6.45), (-1.6, 7.35), (-2.5, 7.35)], GRAU, 0.35, zu=True)
    P.strichlinie([(-2.5, 6.45), (-1.6, 7.35)], GRAU, 0.35); P.strichlinie([(-2.5, 7.35), (-1.6, 6.45)], GRAU, 0.35)
    for t, x, y in (('WC', 0.95, 3.25), ('Windfang', 3.30, 2.40), ('Diele', 3.70, 4.80), ('Kochen', 8.10, 3.10), ('Essen / Wohnen', 5.60, 8.30),
                    ('Abstell', -1.65, 8.00), ('Terrasse', 4.30, 12.60)):
        P.name(t, x, y)
    maske(P)
    P.hinweis = HINWEIS
    P.fertig()


def dachgeschoss(doc):
    P = DG(doc, HAUS, 'Dachgeschoss', BOX, DETAILS)
    if FARBIG:
        g = lambda x, y: (eg_plan(x, y)[0] + 1.30, eg_plan(x, y)[1] - 0.82)
        garten(P, g)
        for pts in ([(0.12, -8.0), (4.55, -8.0), (4.55, -3.05), (0.12, -3.05)], [(-3.35, 5.85), (0.24, 5.85), (0.24, 10.6), (-3.35, 10.6)]):
            P.dachflaeche([g(*q) for q in pts])
        P.name('Doppelcarport', *g(2.45, -5.5), klein=True)
        # Umgebung zurücknehmen: das DG liegt eine Ebene darüber
        P.S.draw_rect(FELD, color=None, fill=(1, 1, 1), fill_opacity=0.55)
        P.polygon([(-0.05, -1.95), (11.60, -1.95), (11.60, 9.25), (-0.05, 9.25)], (0.875, 0.875, 0.87))
    for a, b in ((0.10, 1.22), (10.57, 11.60)):
        P.terrasse(a, 1.38, b, 6.05)
        aussen = a if a < 1 else b
        innen = b if a < 1 else a
        P.gelaender([(innen, 1.38), (aussen, 1.38), (aussen, 6.05), (innen, 6.05)])
    P.dach([(-0.05, -1.95), (11.60, -1.95), (11.60, 9.25), (-0.05, 9.25)])
    P.ton(1.30, -0.82, 10.49, 8.37, HOLZ if FARBIG else FLAECHE)
    if FARBIG:
        P.ton(1.30, 3.75, 3.62, 8.37, FLIESE)
    for w in [(1.30, -0.82, 10.49, -0.82), (1.30, -0.82, 1.30, 1.58), (1.30, 3.75, 1.30, 4.98), (1.30, 6.00, 1.30, 8.37), (1.30, 8.37, 3.62, 8.37),
              (5.93, 8.37, 10.49, 8.37)]:
        P.aussenwand(*w)
    for w in [(5.93, -0.82, 5.93, 3.75), (5.93, 4.78, 5.93, 8.37), (2.52, 3.75, 4.87, 3.75), (3.62, 3.75, 3.62, 8.37)]:
        P.wand(*w)
    for w in [(2.70, 5.03, 3.62, 5.03), (2.70, 6.02, 3.62, 6.02)]:
        P.wand(*w, d=0.08)
    for g in [(1.30, 2.50, 1.30, 3.75), (3.62, 8.37, 5.93, 8.37), (10.49, -0.82, 10.49, 1.45), (10.49, 2.45, 10.49, 5.00), (10.49, 6.00, 10.49, 8.37)]:
        P.glas(*g)
    for t in [(1.30, 1.58, 0.92, 'v', 1, 1, D_AUSSEN, 30), (1.30, 4.98, 0.95, 'v', 1, 1, D_AUSSEN, 30), (10.49, 1.45, 0.95, 'v', 1, -1, D_AUSSEN, 30),
              (10.49, 5.00, 0.95, 'v', 1, -1, D_AUSSEN, 30), (1.60, 3.75, 0.92, 'h', 1, -1, D_INNEN, 35), (5.82, 3.75, 0.92, 'h', -1, -1, D_INNEN, 35),
              (5.93, 3.75, 1.00, 'v', 1, 1, D_INNEN, 30)]:
        P.tuer(*t[:6], d=t[6], winkel=t[7])
    P.stuetzen([(1.30, -0.82), (10.49, -0.82), (1.30, 8.37), (10.49, 8.37), (1.30, 1.58), (1.30, 2.50), (1.30, 3.75), (1.30, 4.98), (1.30, 6.00),
                (10.49, 1.45), (10.49, 2.45), (10.49, 5.00), (10.49, 6.00), (5.93, -0.82), (5.93, 3.75), (5.93, 8.37), (3.62, 8.37), (3.62, 3.75)])
    P.treppe_u(3.70, 6.04, 5.86, 8.30, 5, podest=0.85, oben='+y', start='rechts')
    P.gelaender([(3.70, 6.04), (4.62, 6.04)])
    # Schlafen
    P.bett(2.72, -0.35, 1.80, 2.00, '-y'); P.regal(1.42, -0.72, 1.93, 1.37); P.regal(5.32, -0.72, 5.87, 1.38)
    P.schrank(2.75, 3.17, 4.65, 3.62)
    # Bad
    P.waschtisch(2.89, 4.10, 3.35, 4.70); P.dusche(2.76, 5.08, 3.55, 5.97); P.wc(3.46, 6.60, '+x'); P.wanne(1.39, 7.16, 3.35, 8.22)
    # Studio
    P.sideboard(6.04, -0.72, 10.00, -0.30); P.regal(6.04, 1.38, 6.50, 3.65)
    P.rundtisch(8.20, 1.95, 0.55, 3)
    P.sideboard(8.39, 3.94, 10.00, 4.44)
    P.sofa(6.16, 6.10, 6.88, 8.20, '-x'); P.sofa(6.88, 7.35, 9.30, 8.20, '+y'); P.kreis(6.53, 5.98, 0.28)
    P.bogen(8.06, 6.90, 0.65, 180, 360); P.linie([(7.41, 6.90), (8.71, 6.90)])
    P.sessel(9.62, 6.65, '+x', 0.60); P.kasten(9.38, 7.35, 10.00, 8.00); P.kreis(9.69, 7.67, 0.15)
    for t, x, y in (('Schlafen', 3.62, 2.50), ('Bad', 2.20, 5.60), ('Flur', 4.75, 5.10), ('Studio', 8.10, 5.20)):
        P.name(t, x, y)
    for t, x, y in (('Balkon', 0.66, 3.70), ('Balkon', 11.08, 3.70)):
        P.name(t, x, y, klein=True)
    if FARBIG:
        maske(P)
    P.hinweis = HINWEIS
    P.fertig()


if __name__ == '__main__':
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    doc = fitz.open()
    erdgeschoss(doc); dachgeschoss(doc)
    speichern(doc, HAUS + (' (coloriert)' if FARBIG else ''), args[0], args[1] if len(args) > 1 else None)
