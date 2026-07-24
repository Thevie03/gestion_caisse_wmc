<?php
/** Script one-shot : génère les icônes PWA aux bonnes dimensions. */
$src = __DIR__ . '/public/images/logos/logo_wmc_orange.png';
$dir = __DIR__ . '/public/images/icons';

if (!extension_loaded('gd')) {
    fwrite(STDERR, "Extension GD requise.\n");
    exit(1);
}

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function resizeIcon(string $source, string $dest, int $size): void
{
    $img = imagecreatefrompng($source);
    $w = imagesx($img);
    $h = imagesy($img);
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $trans = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefill($out, 0, 0, $trans);
    imagecopyresampled($out, $img, 0, 0, 0, 0, $size, $size, $w, $h);
    imagepng($out, $dest, 9);
    imagedestroy($out);
    imagedestroy($img);
    echo "Created {$dest} ({$size}x{$size})\n";
}

resizeIcon($src, $dir . '/icon-192.png', 192);
resizeIcon($src, $dir . '/icon-512.png', 512);
resizeIcon($src, $dir . '/apple-touch-icon.png', 180);
