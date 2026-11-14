"""Render a straight-down rectilinear view from the down-facing lens of an A1 dual-fisheye still.

Usage: python3 bin/aerial-nadir.py <in.jpg> <out.jpg> [fov_deg] [size] [c]
Equidistant fisheye: r = R * c * theta / (pi/2), where c = radius fraction at 90 degrees
(the viewer uses c = 0.946 for this camera).
"""
import sys, math
import numpy as np, cv2

src, out = sys.argv[1], sys.argv[2]
fov = float(sys.argv[3]) if len(sys.argv) > 3 else 110
size = int(sys.argv[4]) if len(sys.argv) > 4 else 2400
c = float(sys.argv[5]) if len(sys.argv) > 5 else 0.946

img = cv2.imread(src)
h, w = img.shape[:2]
lens = img[:, : w // 2]
# Find the lens circle from the non-black pixels.
mask = cv2.cvtColor(lens, cv2.COLOR_BGR2GRAY) > 12
ys, xs = np.nonzero(mask)
cx, cy = (xs.min() + xs.max()) / 2, (ys.min() + ys.max()) / 2
R = max(xs.max() - xs.min(), ys.max() - ys.min()) / 2
print(f"lens centre=({cx:.0f},{cy:.0f}) R={R:.0f}", file=sys.stderr)

f = (size / 2) / math.tan(math.radians(fov / 2))
u, v = np.meshgrid(np.arange(size) - size / 2 + 0.5, np.arange(size) - size / 2 + 0.5)
d = np.hypot(u, v)
theta = np.arctan2(d, f)
r = R * c * theta / (math.pi / 2)
scale = np.where(d > 0, r / np.maximum(d, 1e-9), 0)
mapx = (cx + u * scale).astype(np.float32)
mapy = (cy + v * scale).astype(np.float32)
res = cv2.remap(lens, mapx, mapy, cv2.INTER_CUBIC, borderMode=cv2.BORDER_CONSTANT)
cv2.imwrite(out, res, [cv2.IMWRITE_JPEG_QUALITY, 90])
