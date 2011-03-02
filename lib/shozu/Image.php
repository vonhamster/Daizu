<?php
namespace shozu;
/**
 * GD-based image manipulation class
 *
 * Uses a chainable API:
 *
 * <code>
 * $image = new Image('mypic.jpg');
 * $image->fitInSquare(150)->emboss()->negate()->toFile('thumbs/mypic-150');
 * </code>
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 * @link www.desfrenes.com
 */
class Image
{
    public $originalFilePath;
    public $originalMimeType;
    public $originalX;
    public $originalY;
    private $originalBuffer;
    private $tempBuffer;
    private $outputQuality = 75;

    /**
     * Create new Image object.
     *
     * @param string Original file path.
     * @param boolean Init temporary buffer.
     */
    public function __construct($filePath, $initTempBuffer = true)
    {
        $imageInfos = getimagesize($filePath);
        if(in_array($imageInfos['mime'], array('image/jpeg',
        'image/png',
        'image/gif')))
        {
            $this->originalFilePath = $filePath;
            $this->originalMimeType = $imageInfos['mime'];
            $this->originalX        = $imageInfos[0];
            $this->originalY        = $imageInfos[1];

            if($initTempBuffer)
            {
                $this->initTempBuffer();
            }
        }
        else
        {
            throw new \shozu\Image\Exception('invalid image format.');
        }
    }

    /**
     * Set image output quality.
     *
     * Defaults to 75
     *
     * @param integer
     * @return Image
     */
    public function setOutputQuality($quality = 75)
    {
        $this->outputQuality = (int)$quality;
        return $this;
    }

    /**
     * Init temporary buffer, copying original buffer.
     *
     * Try to get original buffer from file.
     *
     * @return Image
     */
    public function initTempBuffer()
    {
        $this->getImageFromFile();
        $this->tempBuffer = $this->originalBuffer;
        return $this;
    }

    /**
     * Resize Image, keeping scale.
     *
     * @param integer New size.
     * @param string Reference side to resize ("x" or "y"), "x" by default.
     * @return Image
     */
    public function resizeKeepScale($new_value,$side="x")
    {
        if((int)$new_value > 0)
        {
            if($side=="x")
            {
                $factor = $this->originalX / (int)$new_value;
                $new_y  = round($this->originalY / $factor);
                $this->resize((int)$new_value,$new_y);
            }
            if($side=="y")
            {
                $factor = $this->originalY / (int)$new_value;
                $new_x  = round($this->originalX / $factor);
                $this->resize($new_x,(int)$new_value);
            }
        }
        else
        {
            throw new \shozu\Image\Exception('Can\'t resize image to 0 pixel !');
        }
        return $this;
    }

    /**
     * Resize image so that it would fit in a square of given pixel size.
     *
     * @param integer Size of square.
     * @return Image
     */
    public function fitToSquare($size)
    {
        if($this->originalX > $this->originalY)
        {
            $this->resizeKeepScale((int)$size);
        }
        else
        {
            $this->resizeKeepScale((int)$size,"y");
        }
        return $this;
    }

    /**
     * Draw a border
     *
     * @param integer $thickness thickness in px
     * @param array border color as RGB, defaults to array(255,255,255)
     * @return Image
     */
    public function drawBorder($thickness = 1, $color = array(255, 255, 255))
    {
        $x1 = 0;
        $y1 = 0;
        $x2 = ImageSX($this->tempBuffer) - 1;
        $y2 = ImageSY($this->tempBuffer) - 1;

        for($i = 0; $i < $thickness; $i++)
        {
            ImageRectangle($this->tempBuffer, $x1++, $y1++, $x2--, $y2--,
                imagecolorallocate($this->tempBuffer, $color[0], $color[1], $color[2]));
        }
        return $this;
    }

    public function fitInSquare($size, $color = array(255, 255, 255), $padding = 0)
    {
        if(empty($color))
        {
            $color = array(255, 255, 255);
        }
        if(count($color) < 3)
        {
            throw new \shozu\Image\Exception('wrong color');
        }

        $this->fitToSquare($size - ($padding * 2));
        $dst= imagecreatetruecolor($size, $size);
        $color = imagecolorallocate($dst, (int)$color[0], (int)$color[1], (int)$color[2]);
        imagefill($dst, 0, 0, $color);

        $bufferX = imagesx($this->tempBuffer);
        $bufferY = imagesy($this->tempBuffer);

        if($bufferX > $bufferY)
        {
            $moveY = $size / 2 - $bufferY / 2;
            $moveX = 0 + $padding;
        }
        else
        {
            $moveY = 0 + $padding;
            $moveX = $size / 2 - $bufferX / 2;
        }
        imagecopy($dst, $this->tempBuffer, $moveX, $moveY, 0, 0, $bufferX, $bufferY);
        $this->tempBuffer = $dst;
        return $this;
    }

    public function fitInRectangle($x, $y, $color = array(255, 255, 255), $padding = 0)
    {
        if(empty($color))
        {
            $color = array(255, 255, 255);
        }
        if(count($color) < 3)
        {
            throw new \shozu\Image\Exception('wrong color');
        }
        $minVal = $x < $y ? 'x' : 'y';
        $this->fitInSquare($$minVal, $color, $padding);
        $dst= imagecreatetruecolor($x, $y);
        $color = imagecolorallocate($dst, (int)$color[0], (int)$color[1], (int)$color[2]);
        imagefill($dst, 0, 0, $color);
        $bufferX = imagesx($this->tempBuffer);
        $bufferY = imagesy($this->tempBuffer);
        if($minVal == 'x')
        {
            $offsetX = 0;
            $offsetY = ($y - $bufferY) / 2;
        }
        else
        {
            $offsetX = ($x - $bufferX) / 2;
            $offsetY = 0;
        }
        imagecopy($dst, $this->tempBuffer, $offsetX, $offsetY, 0, 0, $bufferX, $bufferY);
        $this->tempBuffer = $dst;
        return $this;
    }

    /**
     * Resize image to new x and new y.
     *
     * @param integer New size of x.
     * @param integer New size of y.
     */
    public function resize($new_x,$new_y)
    {
        if(!empty($this->tempBuffer))
        {
            $to = imagecreatetruecolor($new_x, $new_y);
            $from = $this->tempBuffer;
            imagecopyresampled($to, $from, 0, 0, 0, 0, $new_x, $new_y,
                $this->originalX, $this->originalY);
            $this->tempBuffer = $to;
        }
        else
        {
            throw new \shozu\Image\Exception('Can\'t resize empty image buffer');
        }
        return $this;
    }

    /**
     * Save temporary buffer to file.
     *
     * @param string New file path.
     * @param boolean Add extension, based on original file.
     */
    public function toFile($newPath, $addExtension = true)
    {
        if(!empty($this->tempBuffer))
        {
            if($addExtension)
            {
                $newPath .= '.' . $this->getOriginalExtension();
            }
            switch($this->originalMimeType)
            {
                case 'image/jpeg':
                    imagejpeg($this->tempBuffer, $newPath, $this->outputQuality);
                    break;
                case 'image/png':
                    imagepng($this->tempBuffer, $newPath);
                    break;
                case 'image/gif':
                    imagegif($this->tempBuffer, $newPath);
                    break;
            }
        }
        else
        {
            throw new \shozu\Image\Exception('Can not save empty buffer.');
        }
        return $this;
    }

    /**
     * Send buffer to browser
     */
    public function toBrowser($die = true)
    {
        header('content-type: ' . $this->originalMimeType);
        switch($this->originalMimeType)
        {
            case 'image/jpeg':
                imagejpeg($this->tempBuffer, null, $this->outputQuality);
                break;
            case 'image/png':
                imagepng($this->tempBuffer);
                break;
            case 'image/gif':
                imagegif($this->tempBuffer);
                break;
        }
        if($die)
        {
            die;
        }
    }

    /**
     * Convert image to gray scale.
     */
    public function grayScale()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_GRAYSCALE);
        }
        return $this;
    }


    /**
     * Colorize image.
     *
     * @param integer Red value (0-255).
     * @param integer Blue value (0-255).
     * @param integer Green value (0-255).
     * @param mixed Alpha channel.
     */
    public function colorize($r, $b, $g, $alpha = null)
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_COLORIZE,
                (int)$r,
                (int)$b,
                (int)$g,
                $alpha);
        }
        return $this;
    }

    /**
     * Negates colors.
     */
    public function negate()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_NEGATE);
        }
        return $this;
    }

    /**
     * Adjust brightness.
     *
     * @param integer brightness value
     */
    public function brightness($val)
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_BRIGHTNESS, (int)$val);
        }
        return $this;
    }

    /**
     * Adjust contrast.
     *
     * @param integer contrast value
     */
    public function contrast($val)
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_CONTRAST, (int)$val);
        }
        return $this;
    }

    /**
     * Edge effect
     */
    public function edgeDetect()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_EDGEDETECT);
        }
        return $this;
    }

    /**
     * Emboss effect
     */
    public function emboss()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_EMBOSS);
        }
        return $this;
    }

    /**
     * Gaussian blur
     */
    public function gaussianBlur()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $this;
    }


    /**
     * Selective blur
     */
    public function selectiveBlur()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_SELECTIVE_BLUR);
        }
        return $this;
    }

    /**
     * Mean removal
     */
    public function meanRemoval()
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_MEAN_REMOVAL);
        }
        return $this;
    }

    /**
     * Smooth effect
     */
    public function smooth($val)
    {
        if(!empty($this->tempBuffer))
        {
            imagefilter($this->tempBuffer, IMG_FILTER_SMOOTH, (int)$val);
        }
        return $this;
    }

    /**
     * Create "Apple-like" reflection effect
     */
    public function reflection()
    {
        $src_img = $this->tempBuffer;
        $src_height = imagesy($src_img);
        $src_width = imagesx($src_img);
        $dest_height = $src_height + ($src_height / 2);
        $dest_width = $src_width;
        $reflected = imagecreatetruecolor($dest_width, $dest_height);
        imagealphablending($reflected, false);
        imagesavealpha($reflected, true);
        imagecopy($reflected, $src_img, 0, 0, 0, 0, $src_width, $src_height);
        $reflection_height = $src_height / 2;
        $alpha_step = 80 / $reflection_height;
        for ($y = 1; $y <= $reflection_height; $y++)
        {
            for ($x = 0; $x < $dest_width; $x++)
            {
            // copy pixel from x / $src_height - y to x / $src_height + y
                $rgba = imagecolorat($src_img, $x, $src_height - $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $alpha =  max($alpha, 47 + ($y * $alpha_step));
                $rgba = imagecolorsforindex($src_img, $rgba);
                $rgba = imagecolorallocatealpha($reflected, $rgba['red'], $rgba['green'], $rgba['blue'], $alpha);
                imagesetpixel($reflected, $x, $src_height + $y - 1, $rgba);
            }
        }
        $this->tempBuffer = $reflected;
        return $this;
    }


    public function logo($src_image, $src_w, $src_h, $position='random')
    {
        $dst_w = imagesx($this->tempBuffer);
        $dst_h = imagesy($this->tempBuffer);

        imagealphablending($this->tempBuffer,true);
        imagealphablending($src_image,true);
        if ($position == 'random')
        {
            $position = rand(1,8);
        }
        switch ($position)
        {
            case 'top-right':
            case 'right-top':
            case 1:
                imagecopy($this->tempBuffer, $src_image, ($dst_w-$src_w), 0, 0, 0, $src_w, $src_h);
                break;
            case 'top-left':
            case 'left-top':
            case 2:
                imagecopy($this->tempBuffer, $src_image, 0, 0, 0, 0, $src_w, $src_h);
                break;
            case 'bottom-right':
            case 'right-bottom':
            case 3:
                imagecopy($this->tempBuffer, $src_image, ($dst_w-$src_w), ($dst_h-$src_h), 0, 0, $src_w, $src_h);
                break;
            case 'bottom-left':
            case 'left-bottom':
            case 4:
                imagecopy($this->tempBuffer, $src_image, 0 , ($dst_h-$src_h), 0, 0, $src_w, $src_h);
                break;
            case 'center':
            case 5:
                imagecopy($this->tempBuffer, $src_image, (($dst_w/2)-($src_w/2)), (($dst_h/2)-($src_h/2)), 0, 0, $src_w, $src_h);
                break;
            case 'top':
            case 6:
                imagecopy($this->tempBuffer, $src_image, (($dst_w/2)-($src_w/2)), 0, 0, 0, $src_w, $src_h);
                break;
            case 'bottom':
            case 7:
                imagecopy($this->tempBuffer, $src_image, (($dst_w/2)-($src_w/2)), ($dst_h-$src_h), 0, 0, $src_w, $src_h);
                break;
            case 'left':
            case 8:
                imagecopy($this->tempBuffer, $src_image, 0, (($dst_h/2)-($src_h/2)), 0, 0, $src_w, $src_h);
                break;
            case 'right':
            case 9:
                imagecopy($this->tempBuffer, $src_image, ($dst_w-$src_w), (($dst_h/2)-($src_h/2)), 0, 0, $src_w, $src_h);
                break;
        }
        return $this;
    }


    /**
     * Sharpens image
     *
     * @param integer amount
     * @param float radius
     * @param integer threshold
     */
    public function unsharpMask($amount = 140, $radius = 0.8, $threshold = 1)
    {
    //$img = $this->tempBuffer;
    ////////////////////////////////////////////////////////////////////////////////////////////////
    ////
    ////                  Unsharp Mask for PHP - version 2.1.1
    ////
    ////    Unsharp mask algorithm by Torstein HÃ¸nsi 2003-07.
    ////             thoensi_at_netcom_dot_no.
    ////               Please leave this notice.
    ////
    ///////////////////////////////////////////////////////////////////////////////////////////////

    // $img is an image that is already created within php using
    // imgcreatetruecolor. No url! $img must be a truecolor image.

    // Attempt to calibrate the parameters to Photoshop:
        if ($amount > 500)    $amount = 500;
        $amount = $amount * 0.016;
        if ($radius > 50)    $radius = 50;
        $radius = $radius * 2;
        if ($threshold > 255)    $threshold = 255;

        $radius = abs(round($radius));     // Only integers make sense.
        if ($radius == 0)
        {
            return;
        }
        $w = imagesx($this->tempBuffer); $h = imagesy($this->tempBuffer);
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);


        // Gaussian blur matrix:
        //
        //    1    2    1
        //    2    4    2
        //    1    2    1
        //
        //////////////////////////////////////////////////


        if (function_exists('imageconvolution'))
        {
            $matrix = array(
                array( 1, 2, 1 ),
                array( 2, 4, 2 ),
                array( 1, 2, 1 ));
            imagecopy ($imgBlur, $this->tempBuffer, 0, 0, 0, 0, $w, $h);
            imageconvolution($imgBlur, $matrix, 16, 0);
        }
        else
        {
        // Move copies of the image around one pixel at the time and merge them with weight
        // according to the matrix. The same matrix is simply repeated for higher radii.
            for ($i = 0; $i < $radius; $i++)
            {
                imagecopy ($imgBlur, $this->tempBuffer, 0, 0, 1, 0, $w - 1, $h); // left
                imagecopymerge ($imgBlur, $this->tempBuffer, 1, 0, 0, 0, $w, $h, 50); // right
                imagecopymerge ($imgBlur, $this->tempBuffer, 0, 0, 0, 0, $w, $h, 50); // center
                imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
            }
        }

        if($threshold>0)
        {
        // Calculate the difference between the blurred pixels and the original
        // and set the pixels
            for ($x = 0; $x < $w-1; $x++)
            { // each row
                for ($y = 0; $y < $h; $y++)
                { // each pixel

                    $rgbOrig = ImageColorAt($this->tempBuffer, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    // When the masked pixels differ less from the original
                    // than the threshold specifies, they are set to their original value.
                    $rNew = (abs($rOrig - $rBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                        : $rOrig;
                    $gNew = (abs($gOrig - $gBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                        : $gOrig;
                    $bNew = (abs($bOrig - $bBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                        : $bOrig;



                    if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew))
                    {
                        $pixCol = ImageColorAllocate($this->tempBuffer, $rNew, $gNew, $bNew);
                        ImageSetPixel($this->tempBuffer, $x, $y, $pixCol);
                    }
                }
            }
        }
        else
        {
            for ($x = 0; $x < $w; $x++)
            { // each row
                for ($y = 0; $y < $h; $y++)
                { // each pixel
                    $rgbOrig = ImageColorAt($this->tempBuffer, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if($rNew>255)
                    {$rNew=255;}
                    elseif($rNew<0)
                    {$rNew=0;}
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if($gNew>255)
                    {$gNew=255;}
                    elseif($gNew<0)
                    {$gNew=0;}
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if($bNew>255)
                    {$bNew=255;}
                    elseif($bNew<0)
                    {$bNew=0;}
                    $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;
                    ImageSetPixel($this->tempBuffer, $x, $y, $rgbNew);
                }
            }
        }
        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);
        return $this;
    }

    private function getImageFromFile()
    {
        switch($this->originalMimeType)
        {
            case 'image/jpeg':
                $this->originalBuffer = imagecreatefromjpeg($this->originalFilePath);
                break;
            case 'image/png':
                $this->originalBuffer = imagecreatefrompng($this->originalFilePath);
                break;
            case 'image/gif':
                $this->originalBuffer = imagecreatefromgif($this->originalFilePath);
                break;
           }
    }

    public function getOriginalExtension()
    {
        switch($this->originalMimeType)
        {
            case 'image/jpeg':
                return 'jpg';
                break;
            case 'image/png':
                return 'png';
                break;
            case 'image/gif':
                return 'gif';
                break;
            default:
                return '';
                break;
        }
    }
}
