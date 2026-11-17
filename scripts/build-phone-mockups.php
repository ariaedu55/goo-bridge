<?php
/**
 * Mockup iPhone — capture mobile → WebP/PNG avec cadre.
 * Usage: php scripts/build-phone-mockups.php <capture.webp|png> <output.webp|png>
 */
declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php build-phone-mockups.php <screen.webp|png> <out.webp|png>\n");
    exit(1);
}

$screenPath = $argv[1];
$outPath = $argv[2];

if (!is_file($screenPath)) {
    fwrite(STDERR, "Capture introuvable: {$screenPath}\n");
    exit(1);
}

$ext = strtolower(pathinfo($screenPath, PATHINFO_EXTENSION));
$screen = match ($ext) {
    'jpg', 'jpeg' => @imagecreatefromjpeg($screenPath),
    'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($screenPath) : false,
    default => @imagecreatefrompng($screenPath),
};

if ($screen === false) {
    fwrite(STDERR, "Impossible de lire: {$screenPath}\n");
    exit(1);
}

/* iPhone 17 Pro Max — ratio ~1:2.15 */
$W = 540;
$H = 1160;
$bezel = 10;
$radius = 58;
$screenX = $bezel;
$screenY = $bezel + 8;
$screenW = $W - ($bezel * 2);
$screenH = $H - ($bezel * 2) - 8;

$out = imagecreatetruecolor($W, $H);
imagesavealpha($out, true);
$transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
imagefill($out, 0, 0, $transparent);

$shadow = imagecolorallocatealpha($out, 15, 23, 42, 100);
imagefilledellipse($out, (int) ($W / 2), $H - 10, (int) ($W * 0.7), 32, $shadow);

$frameOuter = imagecolorallocate($out, 52, 52, 56);
$frameInner = imagecolorallocate($out, 34, 34, 38);
$highlight = imagecolorallocate($out, 118, 118, 126);
$bevel = imagecolorallocate($out, 24, 24, 28);

fillRoundedRect($out, 0, 0, $W - 1, $H - 1, $radius, $frameOuter);
fillRoundedRect($out, 2, 2, $W - 3, $H - 3, $radius - 1, $frameInner);
fillRoundedRect($out, 4, 4, $W - 5, $H - 5, $radius - 2, $bevel);
imagerectangle($out, 5, 5, $W - 6, $H - 6, $highlight);

$black = imagecolorallocate($out, 0, 0, 0);
fillRoundedRect($out, $screenX, $screenY, $screenX + $screenW, $screenY + $screenH, 48, $black);

$sw = imagesx($screen);
$sh = imagesy($screen);
imagecopyresampled($out, $screen, $screenX, $screenY, 0, 0, $screenW, $screenH, $sw, $sh);

$island = imagecolorallocate($out, 6, 6, 8);
$ix = (int) ($W / 2) - 54;
$iy = $screenY + 16;
fillRoundedRect($out, $ix, $iy, $ix + 108, $iy + 30, 15, $island);

$btn = imagecolorallocate($out, 68, 68, 74);
imagefilledrectangle($out, 0, 210, 3, 268, $btn);
imagefilledrectangle($out, 0, 290, 3, 348, $btn);
imagefilledrectangle($out, 0, 370, 3, 410, $btn);
imagefilledrectangle($out, $W - 3, 250, $W - 1, 330, $btn);

$dir = dirname($outPath);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$outExt = strtolower(pathinfo($outPath, PATHINFO_EXTENSION));
if ($outExt === 'webp' && function_exists('imagewebp')) {
    imagewebp($out, $outPath, 82);
} else {
    imagepng($out, $outPath, 6);
}
echo "OK: {$outPath}\n";

function fillRoundedRect(GdImage $img, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
{
    $r = max(1, min($r, (int) floor(min($x2 - $x1, $y2 - $y1) / 2)));
    imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
    imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
    imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
}
