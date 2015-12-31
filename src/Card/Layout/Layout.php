<?php
/**
 * @author luchaos
 */

namespace Completionist\Card\Layout;

use Completionist\Card\Card;
use Exception;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Fill\Gradient\Vertical;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;

class Layout
{
    protected $id;

    protected $width;
    protected $height;

    protected $cardPadding;
    protected $areaPadding;

    protected $backgroundColor;
    protected $backgroundAlpha;
    protected $backgroundSize;
    protected $backgroundAlign;
    protected $backgroundVertical;

    protected $gridItemBorderSize;
    protected $gridItemBorderColor;
    protected $gridItemBorderAlpha;
    protected $gridItemMargin;

    protected $textColor;
    protected $textColorLabel;
    protected $textColorIcon;

    protected $textShadow;
    protected $labelSpace;

    protected $fontName;
    protected $fontNameBold;
    protected $fontNameIcon;
    protected $fontSize;
    protected $fontSizeLabel;
    protected $fontSizeIcon;

    protected $schemeColor;

    /** @var Card $card */
    protected $card;

    /** @var Imagine $imagine */
    protected $imagine;

    /** @var Image $image */
    protected $image;

    /**
     * Layout constructor.
     *
     * @param Card $card
     */
    public function __construct(Card $card = null)
    {
        $this->imagine = new Imagine;

        $this->card($card);
    }

    /**
     * @return bool
     */
    protected function create()
    {
        if (is_null($this->card)) {
            return false;
        }

        $this->width = max(10, (int)$this->option('width', 10));
        $this->height = max(10, (int)$this->option('height', 10));

        $this->cardPadding = max(0, (int)$this->option('cardPadding', 1));
        $this->areaPadding = max(0, (int)$this->option('areaPadding', 4));

        $this->backgroundColor = $this->option('background.color', '#000000');
        $this->backgroundAlpha = max(0, min(100, (int)$this->option('background.alpha', 100)));
        $this->backgroundSize = $this->option('background.size', 'cover');
        $this->backgroundAlign = $this->option('background.align', 'left');
        $this->backgroundVertical = $this->option('background.vertical', 'center');

        $this->gridItemBorderSize = $this->option('gridItem.borderSize', 1);
        $this->gridItemBorderColor = $this->option('gridItem.borderColor', '#000000');
        $this->gridItemBorderAlpha = $this->option('gridItem.borderAlpha', 100);
        $this->gridItemMargin = $this->option('gridItem.margin', 1);

        $this->textColor = $this->option('text.color', '#FFFFFF');
        $this->textColorLabel = $this->option('text.labelColor', '#CCCCCC');
        $this->textColorIcon = $this->option('text.iconColor', '#CCCCCC');

        $this->textShadow = $this->option('text.shadow', true);
        $this->labelSpace = max(0, (int)$this->option('text.labelSpace', 4));

        $this->fontName = $this->option('text.font', 'fontawesome-webfont.ttf');
        $this->fontSize = max(0, (int)$this->option('text.size', 8));

        $this->fontNameIcon = $this->option('text.iconFont', 'fontawesome-webfont.ttf');
        $this->fontSizeIcon = max(0, (int)$this->option('font.iconSize', 10));

        $this->fontSizeLabel = max(0, (int)$this->option('font.labelSize', 5));

        $this->schemeColor = $this->option('color', '#6699CC');

        $this->image =
            $this->imagine->create(
                new Box($this->width, $this->height), $this->color($this->backgroundColor, $this->backgroundAlpha)
            );

        return true;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param Card $card
     */
    public function card(Card $card = null)
    {
        $this->card = $card;

        $this->create();
    }

    /**
     * @param string $key
     * @param null   $default
     * @return array|null|string
     */
    protected function option($key, $default = null)
    {
        return $this->card->option($key, $default);
    }

    /**
     * @param $key
     * @return array|string|null
     */
    protected function data($key)
    {
        return $this->card->data($key);
    }

    /**
     * @param string $color
     * @param null   $alpha
     * @return \Imagine\Image\Palette\Color\ColorInterface
     */
    protected function color($color, $alpha = null)
    {
        static $palette;
        if (!$palette) {
            $palette = new RGB();
        }
        return $palette->color($color, $alpha);
    }

    /**
     * @param int    $fontSize
     * @param string $textColor
     * @param string $fontName
     * @return \Imagine\Gd\Font|\Imagine\Image\FontInterface
     */
    protected function font($fontSize = null, $textColor = null, $fontName = null)
    {
        return $this->imagine->font(
            ($fontName ? $fontName : $this->fontName),
            $fontSize ? $fontSize : $this->fontSize,
            $textColor
                ? is_object($textColor) ? $textColor : $this->color($textColor)
                : $this->color(
                $this->textColor
            )
        );
    }

    /**
     * http://php.net/manual/en/function.imagettfbbox.php
     *
     * 0    lower left corner, X position
     * 1    lower left corner, Y position
     * 2    lower right corner, X position
     * 3    lower right corner, Y position
     * 4    upper right corner, X position
     * 5    upper right corner, Y position
     * 6    upper left corner, X position
     * 7    upper left corner, Y position
     *
     * @param        $text
     * @param int    $fontSize
     * @param string $fontName
     * @return mixed
     */
    protected function textBoundaries($text, $fontSize = null, $fontName = null)
    {
        return imagettfbbox(
            $fontSize ? $fontSize : $this->fontSize, 0, ($fontName ? $fontName : $this->fontName),
            $text
        );
    }

    /**
     * @param Image  $target
     * @param string $text
     * @param int    $x
     * @param int    $y
     * @param int    $size
     * @param string $color
     * @param string $font
     * @param bool   $shadow
     */
    protected function addText($target, $text, $x, $y, $size = null, $color = null, $font = null, $shadow = true)
    {
        if ($shadow) {
            $target->draw()->text(
                $text, $this->font($size, $this->color($this->schemeColor, 60), $font), new Point($x + 1, $y + 1), 0
            );
        }
        $target->draw()->text($text, $this->font($size, $color, $font), new Point($x, $y), 0);
    }

    /**
     * @param Image   $target
     * @param string  $url
     * @param int     $x
     * @param int     $y
     * @param int|Box $size
     * @param bool    $shadow
     */
    protected function addImage($target, $url, $x, $y, $size, $shadow = false)
    {
        try {
            $image = $this->imagine->open($url);

            if (is_numeric($size)) {
                $aspect = $image->getSize()->getHeight() / $image->getSize()->getWidth();
                $width = $size / $aspect;
                $height = $size;
                $size = new Box($width, $height);
            }

            $image->resize($size);

            if ($shadow) {
                $backdrop = $image->copy();
                $backdrop->effects()->gamma(0.4);
                $target->paste($backdrop, new Point($x + 1, $y + 1));
            }

            // TODO: implement alpha masking

            $target->paste($image, new Point($x, $y));

        } catch (Exception $e) {
            // could not load image
        }
    }

    /**
     * @param int            $x
     * @param int            $y
     * @param int            $width
     * @param int            $height
     * @param ColorInterface $start
     * @param ColorInterface $end
     * @param bool           $flip
     */
    protected function addGradient($x, $y, $width, $height, $start, $end, $flip = false)
    {
        $gradient = $this->imagine->create(new Box($width, $height));
        $gradient->fill(new Vertical($height, $start, $end));
        if ($flip) {
            $gradient->flipVertically();
        }

        //$white = $this->color('fff');
        //$fill = new Vertical($height, $white->darken(127), $white);
        //$mask = $this->imagine->create(new Box($width, $height))->fill($fill);
        //$gradient->applyMask($mask);

        $this->image->paste($gradient, new Point($x, $y));
    }

    protected function addBackground()
    {
        $imageUrl = $this->data($this->option('background.data'));
        $imageUrl = $this->option('background.value') ? $this->option('background.value') : $imageUrl;

        if (empty($imageUrl)) {
            return;
        }

        $image = $this->imagine->open($imageUrl);
        $imageSize = $image->getSize();
        $imageAspect = $imageSize->getWidth() / $imageSize->getHeight();

        $cardSize = new Box($this->width, $this->height);
        $cardAspect = $this->width / $this->height;

        // position of background
        $position = new Point(0, 0);

        // crop if resize mode is cover
        if ($this->backgroundSize == 'cover') {

            if ($imageAspect <= $cardAspect) {
                // resize to max width => align the image vertically
                $image->resize(new Box($this->width, $this->width / $imageAspect));
            } else {
                // resize to max height => align the image horizontally
                $image->resize(new Box($this->height * $imageAspect, $this->height));
            }

            // get new image size
            $imageSize = $image->getSize();

            // cover will add cropped image always at 0/0 and be of the card's size
            $position = new Point(0, 0);

            if ($imageAspect <= $cardAspect) {
                switch ($this->backgroundVertical) {
                    case 'bottom':
                        $image->crop(new Point(0, (int)(($imageSize->getHeight() - $this->height))), $cardSize);
                        break;
                    case 'lowercenter':
                        $image->crop(new Point(0, (int)(($imageSize->getHeight() - $this->height) / 3 * 2)), $cardSize);
                        break;
                    case 'center':
                        $image->crop(new Point(0, (int)(($imageSize->getHeight() - $this->height) / 2)), $cardSize);
                        break;
                    case 'uppercenter':
                        $image->crop(new Point(0, (int)(($imageSize->getHeight() - $this->height) / 3)), $cardSize);
                        break;
                    case 'top':
                        // default
                    default:
                        $image->crop(new Point(0, 0), $cardSize);
                        break;
                }
            } else {
                switch ($this->backgroundAlign) {
                    case 'right':
                        $image->crop(new Point($imageSize->getWidth() - $this->width, 0), $cardSize);
                        break;
                    case 'center':
                        $image->crop(new Point((int)(($imageSize->getWidth() - $this->width) / 2), 0), $cardSize);
                        break;
                    case 'left':
                        // default
                    default:
                        $image->crop(new Point(0, 0), $cardSize);
                        break;
                }
            }
        }

        if ($this->backgroundSize == 'contain') {

            if ($imageAspect <= $cardAspect) {
                // resize to max height => align the image horizontally
                $image->resize(new Box($this->height * $imageAspect, $this->height));
            } else {
                // resize to max width => align the image vertically
                $image->resize(new Box($this->width, $this->width / $imageAspect));
            }

            // get new image size
            $imageSize = $image->getSize();

            // position by size and alignment options
            $x = 0;
            $y = 0;

            switch ($this->backgroundAlign) {
                case 'center':
                    $x = (int)(($this->width - $imageSize->getWidth()) / 2);
                    break;
                case 'right':
                    $x = $this->width - $imageSize->getWidth();
                    break;
            }

            switch ($this->backgroundVertical) {
                case 'center':
                    $y = (int)(($this->height - $imageSize->getHeight()) / 2);
                    break;
                case 'bottom':
                    $y = (int)($this->height - $imageSize->getHeight());
                    break;
            }

            $position = new Point($x, $y);
        }

        // add filters before pasting
        $blur = $this->option('background.blur');
        $darken = $this->option('background.darken');
        $colorize = $this->option('background.colorize');
        if ($darken || $colorize) {
            $image->effects()->gamma(1.2);
        }
        if ($blur) {
            $image->effects()->blur();
        }

        // paste background
        $this->image->paste($image, $position);

        // == background tint overlay

        if ($darken) {
            $this->image->draw()->polygon(
                [
                    new Point(0, 0),
                    new Point($this->width, 0),
                    new Point($this->width, $this->height),
                    new Point(0, $this->height)
                ],
                $this->color('222', 50),
                true
            );
        }
        if ($colorize) {
            $this->image->draw()->polygon(
                [
                    new Point(0, 0),
                    new Point($this->width, 0),
                    new Point($this->width, $this->height),
                    new Point(0, $this->height)
                ],
                $this->color($this->schemeColor, 30),
                true
            );
        }
    }

    /**
     * @param $size
     * @return Image|\Imagine\Image\ImageInterface
     */
    protected function createArea($size)
    {
        return $this->imagine->create($size, $this->color('0C0', 0));
    }

    /**
     * @param Box    $areaSize
     * @param string $key
     * @return Image|\Imagine\Image\ImageInterface
     */
    protected function renderArea($areaSize, $key)
    {
        $area = $this->createArea($areaSize);
        $elements = $this->option($key);
        if (!count($elements)) {
            return $area;
        }

        // TODO: add as setting
        $itemMargin = 8;
        $offsetX = 0;

        foreach ($elements as $element) {

            // element overrides
            $type = isset($element['type']) ? $element['type'] : 'text';
            $label = isset($element['label']) ? $element['label'] : null;
            $icon = isset($element['icon']) ? $element['icon'] : null;
            $iconColor = isset($element['iconColor']) ? $element['iconColor'] : $this->textColorIcon;
            $labelColor = isset($element['labelColor']) ? $element['labelColor'] : $this->textColorLabel;
            $color = isset($element['color']) ? $element['color'] : $this->textColor;
            $alpha = isset($element['alpha']) ? (int)$element['alpha'] : 100;
            $shadow = isset($element['shadow']) ? $element['shadow'] : true;
            $fontSize = isset($element['fontSize']) ? $element['fontSize'] : $this->fontSize;
            $fontSizeLabel = isset($element['labelFontSize']) ? $element['labelFontSize'] : $this->fontSizeLabel;
            $fontSizeIcon = isset($element['iconFontSize']) ? $element['iconFontSize'] : $this->fontSizeIcon;
            $labelSpace = isset($element['labelSpace']) ? $element['labelSpace'] : $this->labelSpace;

            $padding = isset($element['padding']) ? $element['padding'] : $this->areaPadding;

            $value = null;
            $value = isset($element['data']) ? $this->data($element['data']) : $value;
            $value = isset($element['value']) ? $element['value'] : $value;

            // TODO: add as "vertical" flag to type "text"
            if ($type == 'text' && strpos($key, 'areas.top') === 0) {

                // text preset with larger font size
                $fontSize = $this->fontSize + 2;

                // switch to two rows if a label should be displayed too
                if ($label) {
                    // TODO: check if font size and label font size together fit into area (does not matter if top or bottom)
                    $type = 'text-vertical';
                }
            }

            // TODO: only for left alignment in the future, more dynamic implementation of areas
            $offsetX += empty($offsetX) ? $padding : $itemMargin;

            switch ($type) {

                case 'image':

                    // TODO: implement alpha masking
                    //$alpha = isset($element['alpha']) ? $element['alpha'] : 100;

                    $shadow = isset($element['shadow']) ? $element['shadow'] : false;
                    $imageSize = $areaSize->getHeight() - $padding * 2;

                    if ($value) {
                        $this->addImage(
                            $area, $value, $offsetX, (int)(($areaSize->getHeight() - $imageSize) / 2), $imageSize,
                            $shadow
                        );
                    }
                    $offsetX += $imageSize;

                    break;

                case 'text-vertical':

                    $offsetY = (int)(($areaSize->getHeight() - $fontSize) / 2);

                    $textWidth = 0;
                    if ($value) {
                        $boundaries = $this->textBoundaries($value, $fontSize, $this->fontName);
                        $textWidth = $boundaries[2];
                    }

                    $iconWidth = 0;
                    if ($icon) {
                        $boundaries = $this->textBoundaries($icon, $fontSizeIcon, $this->fontNameIcon);
                        $iconWidth = $boundaries[2];
                        $textWidth = $textWidth + $iconWidth + $labelSpace;
                    }

                    $labelWidth = 0;
                    if ($label) {
                        $boundaries = $this->textBoundaries($label, $fontSizeLabel, $this->fontName);
                        $labelWidth = $boundaries[2];
                        $offsetY = (int)($areaSize->getHeight() - $fontSize - $fontSizeLabel - $labelSpace) / 2;
                    }

                    // add a pixel to font boundary
                    $elementWidth = max($textWidth, $labelWidth) + 1;

                    if ($icon) {
                        $x = $offsetX + (int)(($elementWidth - $textWidth) / 2);
                        $y = $offsetY - ($shadow ? 1 : 0);
                        $this->addText(
                            $area, $icon, $x, $y, $fontSizeIcon, $this->color($iconColor, $alpha),
                            $this->fontNameIcon
                        );
                    }

                    if ($value) {
                        $x = $offsetX + (int)(($elementWidth - $textWidth) / 2);
                        if ($icon) {
                            $x += $iconWidth + $labelSpace;
                        }
                        $y = $offsetY - ($shadow ? 1 : 0);
                        $this->addText(
                            $area, $value, $x, $y, $fontSize, $this->color($color, $alpha), $this->fontName, $shadow
                        );
                        $offsetY += $fontSize + $labelSpace;
                    }

                    if ($label) {
                        $x = $offsetX + (int)(($elementWidth - $labelWidth) / 2);
                        $y = $offsetY - ($shadow ? 1 : 0);
                        $this->addText(
                            $area, $label, $x, $y, $fontSizeLabel, $this->color($labelColor, $alpha), $this->fontName,
                            $shadow
                        );
                    }

                    $offsetX += $elementWidth;
                    break;

                case 'text':
                    // default

                default:

                    $y = (int)(($areaSize->getHeight() - $fontSize) / 2);

                    if ($icon) {
                        $iconY = (int)(($areaSize->getHeight() - $fontSizeIcon) / 2);
                        $boundaries = $this->textBoundaries($icon, $fontSizeIcon, $this->fontNameIcon);
                        if ($boundaries[2] + $offsetX <= $areaSize->getWidth()) {
                            $this->addText(
                                $area, $icon, $offsetX, $iconY, $fontSizeIcon,
                                $this->color($iconColor, $alpha), $this->fontNameIcon
                            );
                            $offsetX += $boundaries[2] + $labelSpace;
                        }
                    }

                    if ($label) {
                        $boundaries = $this->textBoundaries($label);
                        if ($boundaries[2] + $offsetX <= $areaSize->getWidth()) {
                            $this->addText(
                                $area, $label, $offsetX, $y, null, $this->color($labelColor, $alpha)
                            );
                            $offsetX += $boundaries[2] + $labelSpace;
                        }
                    }

                    if ($value) {
                        $boundaries = $this->textBoundaries($value, $fontSize);
                        $y = (int)(($areaSize->getHeight() - $fontSize) / 2);
                        if ($boundaries[2] + $offsetX <= $areaSize->getWidth()) {
                            $this->addText($area, $value, $offsetX, $y, $fontSize, $this->color($color, $alpha));
                            // add a pixel to font boundary
                            $offsetX += $boundaries[2] + ($shadow ? 1 : 0);
                        }
                    }
                    break;
            }
        }

        // final trim width
        // TODO: only add trailing padding if alignment is right
        $trimWidth = $offsetX + $this->areaPadding;

        $area->crop(
            new Point(0, 0), new Box(min(max($trimWidth, 1), $this->width - $this->areaPadding), $areaSize->getHeight())
        );
        return $area;
    }

    /**
     * @param Image $area
     * @param array $items
     * @param int   $rows
     */
    protected function imagesGrid(Image $area, array $items, $rows)
    {
        if (!count($items) || !isset($items[0]) || !isset($items[0]['image'])) {
            return;
        }

        // border size acts as padding
        // item dimensions area effective (image size + border)

        $itemHeight = min(42, (int)(($area->getSize()->getHeight() - $this->gridItemMargin * ($rows + 1)) / $rows));

        $testImageSize = $this->imagine->open($items[0]['image'])->getSize();

        $imageAspect = $testImageSize->getWidth() / $testImageSize->getHeight();
        $imageHeight = $itemHeight - $this->gridItemBorderSize * 2;
        $imageWidth = (int)($imageHeight * $imageAspect);
        $itemWidth = $imageWidth + $this->gridItemBorderSize * 2;

        $imageSize = new Box($imageWidth, $imageHeight);

        $trimWidth = $itemWidth;
        $trimHeight = $itemHeight;
        $offsetX = 0;
        $offsetY = 0;
        foreach ($items as $item) {

            $item = is_array($item) ? (object)$item : $item;

            // break row
            if (($offsetX + $itemWidth) > $area->getSize()->getWidth()) {
                $offsetX = 0;
                $offsetY += $itemHeight + $this->gridItemMargin;
            }
            if ($offsetY + $itemHeight > $area->getSize()->getHeight()) {
                break;
            }

            $trimHeight = max($trimHeight, $offsetY + $itemHeight);
            $trimWidth = max($trimWidth, $offsetX + $itemWidth);

            if ($trimWidth > $area->getSize()->getWidth()) {
                continue;
            }

            if (!empty($this->gridItemBorderSize)) {

                $borderColor = isset($item->color) ? $item->color : $this->gridItemBorderColor;

                // remove one pixel on all sides - polygon coordinates are inclusive!
                $area->draw()->polygon(
                    [new Point($offsetX, $offsetY),
                     new Point($offsetX + $itemWidth - 1, $offsetY),
                     new Point($offsetX + $itemWidth - 1, $offsetY + $itemHeight - 1),
                     new Point($offsetX, $offsetY + $itemHeight - 1)],
                    $this->color($borderColor, $this->gridItemBorderAlpha), true, 1
                );
            }

            $this->addImage(
                $area, $item->image,
                $offsetX + $this->gridItemBorderSize,
                $offsetY + $this->gridItemBorderSize,
                $imageSize
            );
            $offsetX += $itemWidth + $this->gridItemMargin;
        }

        $area->crop(
            new Point(0, 0),
            new Box(min($area->getSize()->getWidth(), $trimWidth), $trimHeight)
        );
    }
}