<?php

declare(strict_types=1);

/**
 * Generates the LoL Client app icons (PNG / ICO / ICNS) into public/.
 *
 * Requires the PHP `gd` extension. Run from the project root:
 *
 *     php scripts/generate-icons.php
 */
const MASTER_SIZE = 2048;
const UNIT_SCALE = MASTER_SIZE / 100;

function u(float $v): float
{
    return $v * UNIT_SCALE;
}

function px(float $v): int
{
    return (int) round($v * UNIT_SCALE);
}

function lerp(int $a, int $b, float $t): int
{
    return (int) round($a + ($b - $a) * $t);
}

function lerpColor(array $c1, array $c2, float $t): array
{
    return [
        lerp($c1[0], $c2[0], $t),
        lerp($c1[1], $c2[1], $t),
        lerp($c1[2], $c2[2], $t),
    ];
}

function color(GdImage $im, array $c, int $alpha = 0): int
{
    return imagecolorallocatealpha($im, $c[0], $c[1], $c[2], $alpha);
}

/**
 * Horizontal span of a rounded rectangle at a given row (unit coords).
 *
 * @return array{0: float, 1: float}
 */
function roundRectRow(float $x0, float $y0, float $x1, float $y1, float $r, float $y): array
{
    if ($y < $y0 || $y > $y1) {
        return [1, 0];
    }

    if ($y < $y0 + $r) {
        $dx = sqrt(max(0.0, $r * $r - (($y0 + $r) - $y) ** 2));

        return [$x0 + $r - $dx, $x1 - $r + $dx];
    }

    if ($y > $y1 - $r) {
        $dx = sqrt(max(0.0, $r * $r - ($y - ($y1 - $r)) ** 2));

        return [$x0 + $r - $dx, $x1 - $r + $dx];
    }

    return [$x0, $x1];
}

function fillRoundRectGradient(
    GdImage $im,
    float $x0,
    float $y0,
    float $x1,
    float $y1,
    float $r,
    array $top,
    array $bottom,
): void {
    for ($row = px($y0); $row <= px($y1); $row++) {
        [$left, $right] = roundRectRow($x0, $y0, $x1, $y1, $r, $row / UNIT_SCALE);

        if ($right <= $left) {
            continue;
        }

        $t = (($row / UNIT_SCALE) - $y0) / max(0.001, $y1 - $y0);
        $c = lerpColor($top, $bottom, $t);

        imageline($im, px($left), $row, px($right), $row, color($im, $c));
    }
}

/**
 * Convex polygon filled with a vertical gradient.
 *
 * @param  array<int, array{0: float, 1: float}>  $poly
 */
function fillPolygonGradient(
    GdImage $im,
    array $poly,
    array $top,
    array $bottom,
): void {
    $ys = array_map(fn (array $p): float => $p[1], $poly);
    $yMin = min($ys);
    $yMax = max($ys);
    $n = count($poly);

    for ($row = (int) ceil($yMin * UNIT_SCALE); $row <= (int) floor($yMax * UNIT_SCALE); $row++) {
        $yy = $row / UNIT_SCALE;
        $xs = [];

        for ($i = 0; $i < $n; $i++) {
            [$x1, $y1] = $poly[$i];
            [$x2, $y2] = $poly[($i + 1) % $n];

            if ($y1 === $y2) {
                continue;
            }

            if ($yy < min($y1, $y2) || $yy >= max($y1, $y2)) {
                continue;
            }

            $t = ($yy - $y1) / ($y2 - $y1);
            $xs[] = $x1 + ($x2 - $x1) * $t;
        }

        if (count($xs) < 2) {
            continue;
        }

        sort($xs);

        $t = ($yy - $yMin) / max(0.001, $yMax - $yMin);
        $c = lerpColor($top, $bottom, $t);

        imageline($im, px($xs[0]), $row, px($xs[count($xs) - 1]), $row, color($im, $c));
    }
}

/**
 * @return array<int, array{0: float, 1: float}>
 */
function roundRectOutline(float $x0, float $y0, float $x1, float $y1, float $r, float $step = 3.0): array
{
    $pts = [];

    $arc = function (float $cx, float $cy, float $a0, float $a1) use (&$pts, $r, $step): void {
        for ($a = $a0; $a >= $a1; $a -= $step) {
            $rad = deg2rad($a);
            $pts[] = [$cx + $r * cos($rad), $cy + $r * sin($rad)];
        }
    };

    $pts[] = [$x0 + $r, $y0];
    $pts[] = [$x1 - $r, $y0];
    $arc($x1 - $r, $y0 + $r, 270.0, 0.0);
    $pts[] = [$x1, $y1 - $r];
    $arc($x1 - $r, $y1 - $r, 90.0, 0.0);
    $pts[] = [$x1 - $r, $y1];
    $pts[] = [$x0 + $r, $y1];
    $arc($x0 + $r, $y1 - $r, 90.0, 180.0);
    $pts[] = [$x0, $y0 + $r];
    $arc($x0 + $r, $y0 + $r, 270.0, 180.0);

    return $pts;
}

function drawThickSegment(GdImage $im, array $a, array $b, array $c, float $thickness): void
{
    imagesetthickness($im, max(1, (int) round(u($thickness))));
    imageline($im, px($a[0]), px($a[1]), px($b[0]), px($b[1]), color($im, $c));
}

function drawJoint(GdImage $im, array $p, array $c, float $radius): void
{
    $d = max(1, (int) round(u($radius * 2)));
    imagefilledellipse($im, px($p[0]), px($p[1]), $d, $d, color($im, $c));
}

/**
 * Draws a check mark as two thick segments with rounded joints.
 */
function drawCheck(GdImage $im, array $a, array $b, array $c, array $color, float $thickness): void
{
    drawThickSegment($im, $a, $b, $color, $thickness);
    drawThickSegment($im, $b, $c, $color, $thickness);

    $r = $thickness / 2;
    drawJoint($im, $a, $color, $r);
    drawJoint($im, $b, $color, $r);
    drawJoint($im, $c, $color, $r);
}

function clearCanvas(GdImage $im): void
{
    imagealphablending($im, false);
    imagesavealpha($im, true);
    imagefilledrectangle($im, 0, 0, MASTER_SIZE, MASTER_SIZE, imagecolorallocatealpha($im, 0, 0, 0, 127));
    imagealphablending($im, true);
}

function renderIcon(): GdImage
{
    $im = imagecreatetruecolor(MASTER_SIZE, MASTER_SIZE);
    clearCanvas($im);

    $void = [7, 10, 19];
    $steel = [20, 28, 44];
    $goldBright = [243, 221, 176];
    $gold = [200, 170, 110];
    $goldDeep = [138, 117, 71];
    $goldDark = [107, 90, 54];
    $cream = [240, 230, 210];

    // Rounded-square background with a vertical gradient.
    fillRoundRectGradient($im, 4, 4, 96, 96, 18, $steel, $void);

    // Faint gold hairline just inside the edge.
    $ring = roundRectOutline(4, 4, 96, 96, 18, 4.0);
    imagesetthickness($im, max(1, (int) round(u(0.3))));
    $ringN = count($ring);
    for ($i = 0; $i < $ringN; $i++) {
        [$x1, $y1] = $ring[$i];
        [$x2, $y2] = $ring[($i + 1) % $ringN];
        imageline($im, px($x1), px($y1), px($x2), px($y2), color($im, $gold, 70));
    }

    // Soft golden glow behind the shield.
    for ($r = 34.0; $r >= 16.0; $r -= 1.5) {
        $alpha = (int) min(20, 2 + (34 - $r) * 0.7);
        $d = max(1, (int) round(u($r * 2)));
        imagefilledellipse($im, px(50), px(52), $d, $d, color($im, $gold, $alpha));
    }

    // Hextech shield.
    $shield = [
        [50, 25],
        [68, 35],
        [68, 52],
        [50, 86],
        [32, 52],
        [32, 35],
    ];

    fillPolygonGradient($im, $shield, $goldBright, $goldDeep);

    // Bevel highlight at the top "pike".
    $pike = [
        [50, 25],
        [37, 40],
        [50, 54],
        [63, 40],
    ];
    fillPolygonGradient($im, $pike, $gold, lerpColor($goldBright, $gold, 0.55));

    // Shield outline.
    imagesetthickness($im, max(1, (int) round(u(1.1))));
    $shieldN = count($shield);
    for ($i = 0; $i < $shieldN; $i++) {
        [$x1, $y1] = $shield[$i];
        [$x2, $y2] = $shield[($i + 1) % $shieldN];
        imageline($im, px($x1), px($y1), px($x2), px($y2), color($im, $goldDark));
    }

    // Inner facet seams (elongated diamond in the shield face).
    drawThickSegment($im, [36, 38], [50, 52], $goldDark, 0.6);
    drawThickSegment($im, [50, 52], [64, 38], $goldDark, 0.6);
    drawThickSegment($im, [36, 38], [50, 70], $goldDark, 0.45);
    drawThickSegment($im, [64, 38], [50, 70], $goldDark, 0.45);
    foreach ([[36, 38], [50, 52], [64, 38], [50, 70]] as $joint) {
        drawJoint($im, $joint, $goldDark, 0.3);
    }

    // Cream check mark.
    drawCheck($im, [42.2, 55.6], [47.9, 61.9], [59.1, 48.3], $cream, 2.1);

    return $im;
}

function renderTemplate(): GdImage
{
    $im = imagecreatetruecolor(128, 128);
    clearCanvas($im);

    $black = [0, 0, 0];
    $s = 128 / 100;

    $shield = [
        [50, 22],
        [70, 34],
        [70, 54],
        [50, 90],
        [30, 54],
        [30, 34],
    ];

    $poly = array_map(fn (array $p) => [(int) round($p[0] * $s), (int) round($p[1] * $s)], $shield);
    imagefilledpolygon($im, $poly, color($im, $black));

    $check = [
        [42.2, 55.6],
        [47.9, 61.9],
        [59.1, 48.3],
    ];

    foreach ($check as $p) {
        $d = max(1, (int) round(2.1 * $s));
        imagefilledellipse($im, (int) round($p[0] * $s), (int) round($p[1] * $s), $d, $d, color($im, $black));
    }

    return $im;
}

function makeSized(GdImage $src, int $size): GdImage
{
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, MASTER_SIZE, MASTER_SIZE);

    return $dst;
}

function pngBlob(GdImage $src, int $size): string
{
    $img = makeSized($src, $size);
    ob_start();
    imagepng($img);

    return ob_get_clean();
}

function writeIco(GdImage $src, array $sizes, string $path): void
{
    $pngs = array_map(fn (int $size) => pngBlob($src, $size), $sizes);

    $header = pack('vvv', 0, 1, count($sizes));
    $offset = 6 + 16 * count($sizes);
    $entries = '';

    foreach ($sizes as $i => $size) {
        $dim = $size >= 256 ? 0 : $size;
        $entries .= pack('CCCCvvVV', $dim, $dim, 0, 0, 1, 32, strlen($pngs[$i]), $offset);
        $offset += strlen($pngs[$i]);
    }

    file_put_contents($path, $header.$entries.implode('', $pngs));
}

function writeIcns(GdImage $src, array $spec, string $path): void
{
    $chunks = '';

    foreach ($spec as [$type, $size]) {
        $png = pngBlob($src, $size);
        $chunks .= $type.pack('N', strlen($png) + 8).$png;
    }

    file_put_contents($path, 'icns'.pack('N', 8 + strlen($chunks)).$chunks);
}

function writePng(GdImage $src, int $size, string $path): void
{
    file_put_contents($path, pngBlob($src, $size));
}

$root = dirname(__DIR__);
$public = $root.'/public';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "The gd extension is required to generate the icons.\n");
    exit(1);
}

echo "Rendering master icon...\n";
$icon = renderIcon();

echo "Writing public/icon.png (1024px)...\n";
writePng($icon, 1024, $public.'/icon.png');

echo "Writing public/icon.ico...\n";
writeIco($icon, [256, 128, 64, 48, 32, 16], $public.'/icon.ico');

echo "Writing public/icon.icns...\n";
writeIcns($icon, [
    ['icp4', 16],
    ['icp5', 32],
    ['icp6', 64],
    ['ic07', 128],
    ['ic08', 256],
    ['ic09', 512],
    ['ic10', 1024],
], $public.'/icon.icns');

echo "Writing public/favicon.ico...\n";
writeIco($icon, [48, 32, 16], $public.'/favicon.ico');

echo "Writing tray template icons...\n";
$template = renderTemplate();
writePng($template, 32, $public.'/IconTemplate@2x.png');
$template16 = makeSized($template, 16);
file_put_contents($public.'/IconTemplate.png', pngBlob($template, 16));

echo "Done.\n";
