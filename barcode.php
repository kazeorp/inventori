<?php
class BarcodeGeneratorPNG
{
    const TYPE_CODE_128 = 'code128';

    public function getBarcode($text, $type = self::TYPE_CODE_128, $widthFactor = 2, $totalHeight = 60)
    {
        if ($type !== self::TYPE_CODE_128) {
            throw new Exception('Unsupported barcode type');
        }

        // Simple implementation using GD for CODE128
        $im = imagecreate($widthFactor * strlen($text) * 11, $totalHeight);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);

        // Basic simulation: draw vertical lines for each character
        $x = 10;
        for ($i = 0; $i < strlen($text); $i++) {
            $ascii = ord($text[$i]);
            $barWidth = ($ascii % 10 + 1) * $widthFactor;
            imagefilledrectangle($im, $x, 10, $x + $barWidth, $totalHeight - 10, $black);
            $x += $barWidth + 2;
        }

        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);

        return $imageData;
    }
}
