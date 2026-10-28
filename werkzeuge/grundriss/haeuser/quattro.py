"""DAVINCI Quattro – nach Vorlage Weidner & Kranz (EG, DG, KG), Scan 1:100 ausgemessen.
Satteldach 30°, Glasfassaden mit Balkonen an beiden Giebelseiten, Carport mit Flachdach.
Koordinaten in m wie im Scan abgelesen; Q.p rechnet sie auf die Maßketten um
(Haus außen 9,00 x 9,36 m, Ursprung = Außenecke oben links), damit Scanverzug verschwindet."""
import sys, os
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import fitz
from zeichnen import Plan, speichern, D_AUSSEN, D_INNEN, GRAU, HELL, INK

HAUS = 'DAVINCI Quattro'
BOX = (-4.4, -1.9, 18.8, 11.2)
DETAILS = [('Dach', 'SD 30°'), ('Ebenen', 'KG · EG · DG')]
BETON, D_KG = 0.20, 0.17
# Scan -> Plan: (x + dx) * sx, (y - dy) * sy
EGDG = (0.15, 2.00, 9.00 / 8.90, 9.36 / 9.10)
KG = (0.33, 1.98, 9.00 / 9.01, 9.36 / 9.17)


class Q(Plan):
    def __init__(self, doc, titel, t):
        super().__init__(doc, HAUS, titel, BOX, DETAILS)
        self.t = t

    def p(self, x, y):
        dx, dy, sx, sy = self.t
        return super().p((x + dx) * sx, (y - dy) * sy)


def chaise(P, x0, y0, x1, y1):
    """Liegesessel innen: Kasten mit Kopfteil."""
    P.kasten(x0, y0, x1, y1, rund=0.08)
    if x1 - x0 > y1 - y0:
        P.linie([(x0 + 0.45, y0), (x0 + 0.45, y1)])
    else:
        P.linie([(x0, y0 + 0.45), (x1, y0 + 0.45)])


def dach(P):
    P.dach([(-1.95, 0.55), (10.15, 0.55), (10.15, 12.50), (-1.95, 12.50)])


def erdgeschoss(doc):
    P = Q(doc, 'Erdgeschoss', EGDG)
    # Außenanlagen
    P.terrasse(-4.05, 4.10, -0.15, 9.00); P.terrasse(8.75, 4.10, 12.95, 9.00)
    P.ton(-4.05, 2.00, -0.15, 4.10, (0.955, 0.95, 0.935)); P.kasten(-4.05, 2.00, -0.15, 4.10, HELL, 0.45)
    P.liege(-3.15, 6.55); P.liege(-2.05, 6.55)
    P.tisch(10.45, 6.05, 11.25, 6.85, laengs=1, stirn=1)
    for l in [(-0.72, 2.60, -0.15, 3.50), (1.85, 11.10, 2.95, 11.50), (5.75, 11.10, 6.75, 11.50)]:
        P.lichtschacht(*l)
    P.carport(13.05, 1.60, 18.00, 6.95, [(17.78, 2.40), (17.78, 6.72)], dach=[(12.75, 1.55), (18.00, 1.55), (18.00, 6.95), (12.75, 6.95)],
              wand=[(13.00, 2.00, 13.00, 6.95)], titel=None, auto=1)
    dach(P)
    P.ton(-0.07, 2.08, 8.67, 11.02)
    # Fassade
    P.aussenwand(-0.07, 2.08, 0.20, 2.08); P.aussenwand(5.05, 2.08, 8.67, 2.08)
    P.aussenwand(-0.07, 2.08, -0.07, 4.47); P.aussenwand(8.67, 2.08, 8.67, 6.95)
    # Innenwände
    for w in [(-0.07, 4.40, 4.02, 4.40), (5.05, 4.40, 8.67, 4.40), (1.80, 2.08, 1.80, 2.90), (1.80, 3.73, 1.80, 4.40),
              (4.02, 2.08, 4.02, 2.85), (4.02, 3.73, 4.02, 4.40)]:
        P.wand(*w)
    for g in [(0.20, 2.08, 1.80, 2.08), (1.80, 2.08, 2.42, 2.08), (3.38, 2.08, 4.02, 2.08), (4.02, 2.08, 5.05, 2.08),
              (-0.07, 5.35, -0.07, 11.02), (8.67, 7.90, 8.67, 11.02), (-0.07, 11.02, 4.05, 11.02), (5.00, 11.02, 8.67, 11.02)]:
        P.glas(*g)
    for t in [(2.42, 2.08, 0.96, 'h', 1, 1, D_AUSSEN, 35), (-0.07, 4.47, 0.88, 'v', 1, 1, D_AUSSEN, 35), (8.67, 6.95, 0.95, 'v', 1, -1, D_AUSSEN, 45),
              (4.05, 11.02, 0.95, 'h', 1, -1, D_AUSSEN, 30), (1.80, 3.73, 0.83, 'v', -1, -1, D_INNEN, 30), (4.02, 3.73, 0.88, 'v', -1, -1, D_INNEN, 30)]:
        P.tuer(*t[:6], d=t[6], winkel=t[7])
    P.stuetzen([(-0.07, 2.08), (8.67, 2.08), (-0.07, 11.02), (8.67, 11.02), (1.80, 2.08), (2.42, 2.08), (3.38, 2.08), (4.02, 2.08),
                (-0.07, 9.00), (8.67, 9.00), (4.02, 11.02)])
    P.treppe_u(6.40, 2.16, 8.59, 4.33, 6, podest=0.95, oben='+x', start='rechts')
    # WC, Windfang
    P.ton(0.01, 2.16, 0.15, 4.32, (0.86, 0.85, 0.82)); P.waschtisch(0.15, 2.45, 0.60, 2.95); P.wc(0.15, 3.62, '-x')
    P.regal(1.86, 3.80, 3.96, 4.33)
    # Wohnen
    for k in range(4):
        P.schrank(1.20 + k * 0.70, 4.47, 1.90 + k * 0.70, 5.02)
    P.sofa(0.60, 5.55, 2.65, 6.50, '-y'); P.kasten(2.95, 5.60, 3.60, 6.20); P.kreis(3.27, 5.90, 0.15)
    P.sofa(2.95, 6.60, 3.80, 9.00, '+x'); P.couchtisch(1.35, 7.25, 2.35, 8.55)
    P.kasten(0.15, 7.40, 0.60, 9.40); P.linie([(0.30, 7.60), (0.30, 9.20)])
    P.sideboard(0.85, 10.50, 3.95, 10.92)
    # Kochen, Essen
    P.zeile(5.10, 4.47, 8.59, 5.05); P.zeile(7.95, 5.05, 8.59, 6.60); P.spuele(8.27, 6.10, quer=True)
    P.zeile(5.20, 6.00, 6.95, 6.95); P.kochfeld(6.43, 6.47)
    P.tisch(5.15, 8.45, 6.95, 9.05, laengs=3)
    P.sideboard(5.05, 10.50, 7.95, 10.92)
    for t, x, y in (('WC', 1.15, 3.20), ('Windfang', 2.90, 3.25), ('Flur', 5.20, 3.30), ('Kochen', 6.60, 5.55), ('Essen', 6.05, 10.00),
                    ('Wohnen', 1.80, 9.60), ('Terrasse', -2.10, 8.40), ('Terrasse', 10.85, 8.40), ('Carport', 15.40, 4.25)):
        P.name(t, x, y)
    P.name('Holz / Müll / Fahrräder', -2.10, 3.05, klein=True)
    P.fertig()


def dachgeschoss(doc):
    P = Q(doc, 'Dachgeschoss', EGDG)
    P.dachflaeche([(12.75, 1.55), (18.00, 1.55), (18.00, 6.95), (12.75, 6.95)], 'Flachdach Carport', 15.40, 4.25)
    for x0, x1 in ((-1.60, -0.15), (8.75, 10.15)):
        P.terrasse(x0, 4.35, x1, 9.00)
        a = x0 if x0 < 0 else x1
        b = x1 if x0 < 0 else x0
        P.gelaender([(b, 4.35), (a, 4.35), (a, 9.00), (b, 9.00)])
    dach(P)
    P.ton(-0.07, 2.08, 8.67, 11.02)
    P.aussenwand(-0.07, 2.08, 4.02, 2.08); P.aussenwand(5.00, 2.08, 8.67, 2.08); P.aussenwand(-0.07, 11.02, 4.02, 11.02)
    for w in [(2.95, 2.08, 2.95, 4.40), (4.02, 2.08, 4.02, 7.97), (4.02, 8.97, 4.02, 11.02), (0.95, 5.55, 4.02, 5.55),
              (0.95, 7.90, 3.05, 7.90), (5.00, 4.40, 8.67, 4.40)]:
        P.wand(*w)
    for g in [(4.02, 2.08, 5.00, 2.08), (-0.07, 2.08, -0.07, 4.45), (-0.07, 5.55, -0.07, 7.97), (-0.07, 8.97, -0.07, 11.02),
              (8.67, 2.08, 8.67, 6.95), (8.67, 7.92, 8.67, 11.02), (4.02, 11.02, 8.67, 11.02)]:
        P.glas(*g)
    for t in [(-0.07, 4.45, 0.50, 'v', 1, 1, D_AUSSEN, 60), (-0.07, 5.55, 0.60, 'v', -1, 1, D_AUSSEN, 60), (-0.07, 7.97, 0.95, 'v', 1, 1, D_AUSSEN, 35),
              (8.67, 6.95, 0.97, 'v', 1, -1, D_AUSSEN, 40), (4.02, 7.97, 0.95, 'v', 1, -1, D_INNEN, 35)]:
        P.tuer(*t[:6], d=t[6], winkel=t[7])
    P.stuetzen([(-0.07, 2.08), (8.67, 2.08), (-0.07, 11.02), (8.67, 11.02), (4.02, 2.08), (5.00, 2.08), (-0.07, 4.45), (-0.07, 5.55),
                (-0.07, 7.97), (-0.07, 8.97), (8.67, 4.40), (8.67, 9.00), (4.02, 11.02), (5.20, 11.02), (7.45, 11.02)])
    P.gelaender([(4.10, 9.00), (8.59, 9.00)])
    P.luftraum(4.10, 9.08, 8.59, 10.94)
    P.treppe_u(6.40, 2.16, 8.59, 4.33, 6, podest=0.95, oben='+x', start='rechts')
    # Bad
    P.wanne(0.40, 2.20, 2.60, 3.25); P.ton(2.65, 2.16, 2.90, 4.25, (0.86, 0.85, 0.82)); P.wc(2.65, 3.72, '+x')
    P.dusche(3.00, 2.16, 3.96, 4.33); P.waschtisch(1.05, 5.05, 2.10, 5.48)
    # Ankleide, Schlafen
    P.schrank(1.05, 5.62, 2.85, 6.17); P.schrank(2.85, 5.62, 3.96, 6.17); P.schrank(1.05, 7.07, 2.85, 7.84)
    P.bett(1.17, 8.93, 1.60, 2.00, '+y'); P.kasten(0.62, 10.48, 1.12, 10.93); P.kasten(2.82, 10.48, 3.32, 10.93)
    # Flur, Studio
    P.sessel(4.45, 2.65, '-x', 0.62); P.kreis(5.20, 2.65, 0.25)
    P.sideboard(5.30, 4.47, 7.90, 4.95); P.regal(4.10, 5.40, 4.45, 7.80)
    chaise(P, 5.35, 5.60, 7.15, 6.25); chaise(P, 5.35, 6.65, 7.15, 7.30)
    for t, x, y in (('Bad', 1.50, 4.10), ('Ankleide', 2.00, 6.62), ('Schlafen', 2.00, 8.45), ('Flur', 5.40, 3.40), ('Studio / Galerie', 6.25, 8.20)):
        P.name(t, x, y)
    for t, x, y in (('Luftraum', 6.35, 10.00), ('Balkon', -0.88, 6.70), ('Balkon', 9.45, 6.70)):
        P.name(t, x, y, klein=True)
    P.fertig()


def kellergeschoss(doc):
    P = Q(doc, 'Kellergeschoss', KG)
    for l in [(-0.78, 2.85, -0.33, 3.70), (1.75, 11.15, 2.60, 11.50), (5.60, 11.15, 6.45, 11.50)]:
        P.lichtschacht(*l)
    P.strichlinie([(12.85, 1.90), (13.15, 1.90), (13.15, 6.90), (12.85, 6.90)], HELL, 0.5, zu=True)
    for x, y in ((17.85, 2.00), (17.85, 6.60)):
        P.strichlinie([(x - 0.15, y - 0.15), (x + 0.15, y - 0.15), (x + 0.15, y + 0.15), (x - 0.15, y + 0.15)], HELL, 0.5, zu=True)
    P.name('Fundamente Carport', 15.40, 4.30, klein=True)
    P.ton(-0.23, 2.08, 8.58, 11.05)
    for w in [(-0.23, 2.08, 8.58, 2.08), (-0.23, 2.08, -0.23, 2.95), (-0.23, 3.60, -0.23, 11.05), (8.58, 2.08, 8.58, 11.05),
              (-0.23, 11.05, 1.75, 11.05), (2.60, 11.05, 5.60, 11.05), (6.45, 11.05, 8.58, 11.05)]:
        P.wand(*w, d=BETON)
    for w in [(4.92, 2.08, 4.92, 2.85), (4.92, 3.75, 4.92, 4.45), (3.92, 4.45, 4.92, 4.45), (5.85, 4.45, 8.58, 4.45), (3.92, 4.45, 3.92, 11.05)]:
        P.wand(*w, d=D_KG)
    for g in [(-0.23, 2.95, -0.23, 3.60), (1.75, 11.05, 2.60, 11.05), (5.60, 11.05, 6.45, 11.05)]:
        P.glas(*g, d=BETON)
    P.tuer(4.92, 3.75, 0.88, 'v', -1, -1, D_KG, 60); P.tuer(5.00, 4.45, 0.85, 'h', 1, 1, D_KG, 70)
    P.treppe_u(6.45, 2.18, 8.48, 4.37, 6, podest=0.85, oben='+x', start='rechts')
    P.kasten(4.02, 7.60, 4.14, 8.85)
    P.geraet(4.02, 10.40, 4.58, 10.96); P.geraet(4.60, 10.40, 5.16, 10.96)
    P.kasten(7.55, 8.45, 8.40, 8.98); P.kasten(7.05, 9.15, 8.48, 10.96)
    P.speicher(7.75, 9.95, 0.36); P.speicher(7.75, 10.62, 0.30)
    for t, x, y in (('Keller', 1.85, 6.60), ('Flur', 5.60, 3.15), ('Technik / HWR', 6.10, 7.80)):
        P.name(t, x, y)
    P.name('WP', 7.97, 8.72, klein=True)
    P.fertig()


if __name__ == '__main__':
    doc = fitz.open()
    erdgeschoss(doc); dachgeschoss(doc); kellergeschoss(doc)
    speichern(doc, HAUS, sys.argv[1], sys.argv[2] if len(sys.argv) > 2 else None)
