<?php
/**
 * @author luchaos
 */

namespace Completionist\Vanity\Card\Layout;

use Imagine\Gd\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;

class Classic extends Layout
{
    protected $id = 'classic';

    // TODO: add settings below as layout options

    protected $width = 400;
    protected $height = 116;
    protected $border = 1;
    protected $padding = 4;
    protected $backgroundColor = '000000';
    protected $backgroundAlpha = 100;
    protected $textColorMuted = '999';
    protected $textColor = 'CCC';
    protected $textColorHighlight = 'FFFFFF';
    protected $fontNameThin = 'MyriadPro-Regular.otf';
    protected $fontName = 'MyriadPro-Bold.otf';
    protected $fontNameBold = 'MyriadPro-Bold.otf';
    protected $fontSize = 8;
    protected $schemeColor = '4F4F57';
    //protected $schemeColor = '156';
    //protected $schemeColor = '621';

    private $topAreaHeight = 30;
    private $bottomAreaHeight = 15;

    /**
     * @return Image
     */
    public function draw()
    {
        $this->addBackground();
        $this->addOverlays();

        // == top left elements

        $area = $this->renderArea(new Box($this->width, $this->topAreaHeight), 'areas.top.left');
        $this->image->paste($area, new Point($this->border, $this->border));

        // == top right elements

        $area = $this->renderArea(new Box($this->width, $this->topAreaHeight), 'areas.top.right');
        $this->image->paste(
            $area, new Point(max(0, $this->width - $area->getSize()->getWidth() - $this->border), $this->border)
        );

        // == bottom left elements

        $area = $this->renderArea(new Box($this->width, $this->bottomAreaHeight), 'areas.bottom.left');
        $this->image->paste(
            $area, new Point($this->border, max(0, $this->height - $area->getSize()->getHeight() - $this->border))
        );

        // == bottom right elements

        $area = $this->renderArea(new Box($this->width, $this->bottomAreaHeight), 'areas.bottom.right');
        $this->image->paste(
            $area, new Point(
                max(0, $this->width - $area->getSize()->getWidth() - $this->border),
                max(0, $this->height - $area->getSize()->getHeight() - $this->border)
            )
        );

        // == center elements

        $columns = $this->option('areas.center');
        if (count($columns) > 0) {
            $minGutterWidth = 0;
            $gridColumnWidth = (int)($this->width / count($columns));
            if (count($columns) > 1) {
                $gridColumnWidth -= (int)($minGutterWidth / (count($columns) - 1));
            }

            $colCount = 0;
            foreach ($columns as $elements) {
                if (!empty($elements)) {
                    foreach ($elements as $element) {
                        $items = isset($element['data']) ? $this->data($element['data']) : null;
                        $items = isset($element['items']) ? $element['items'] : $items;
                        $span = isset($element['span']) && !empty($element['span']) ? $element['span'] : 1;
                        $rows = isset($element['rows']) && !empty($element['rows']) ? $element['rows'] : 1;
                        $vertical = isset($element['vertical']) ? $element['vertical'] : null;
                        $align = isset($element['align']) ? $element['align'] : null;
                        if (!empty($items) && is_array($items)) {
                            $availableHeight = $this->height - $this->topAreaHeight - $this->bottomAreaHeight;
                            $area = $this->createArea(new Box($gridColumnWidth * $span, $availableHeight));
                            $this->imagesGrid($area, $items, $rows);
                            switch ($align) {
                                case 'left':
                                    $posX = $colCount * $gridColumnWidth + $this->padding;
                                    break;
                                case 'right':
                                    $posX =
                                        $colCount * $gridColumnWidth + $gridColumnWidth * $span - $area->getSize()
                                            ->getWidth()
                                        - $this->padding;
                                    break;
                                case 'center':
                                    // default
                                default:
                                    $posX =
                                        $colCount * $gridColumnWidth + (int)(($gridColumnWidth * $span - $area->getSize(
                                                )
                                                    ->getWidth()) / 2);
                                    break;
                            }
                            switch ($vertical) {
                                case 'top':
                                    $posY = $this->topAreaHeight + $this->padding - 1;
                                    break;
                                case 'bottom':
                                    $posY = $this->topAreaHeight + $availableHeight - $area->getSize()->getHeight()
                                        - $this->padding;
                                    break;
                                case 'center':
                                    // default
                                default:
                                    $posY =
                                        $this->topAreaHeight + (int)(($availableHeight - $area->getSize()->getHeight())
                                            / 2);
                                    break;
                            }
                            $this->image->paste($area, new Point($posX, $posY));
                        }
                    }
                }
                $colCount++;
            }
        }
        return $this->image;
    }

    private function addOverlays()
    {
        // == top overlay

        $this->addGradient(
            1, 1, $this->width - 2, $this->topAreaHeight,
            $this->color($this->schemeColor, 30), $this->color('111', 65),
            true
        );

        // == bottom overlay

        $this->addGradient(
            1, $this->height - $this->bottomAreaHeight - 1, $this->width - 2, $this->bottomAreaHeight,
            $this->color($this->schemeColor, 30), $this->color('111', 65),
            true
        );
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $size
     */
    protected function addSteamLevel($x, $y, $size = 32
    ) {
        $this->image->draw()->ellipse(new Point($x, $y), new Box($size, $size), $this->color('fff'));
    }
}