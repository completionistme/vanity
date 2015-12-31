<?php
/**
 * @author luchaos
 */

namespace Completionist\Card\Layout;

use Imagine\Gd\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;

class Classic extends Layout
{
    protected $id = 'classic';

    private $topAreaHeight = 30;
    private $bottomAreaHeight = 15;

    protected function create()
    {
        if (!parent::create()) {
            return false;
        }

        $this->topAreaHeight = (int)$this->option('topAreaHeight', $this->topAreaHeight);
        $this->bottomAreaHeight = (int)$this->option('bottomAreaHeight', $this->bottomAreaHeight);
    }

    /**
     * @return Image
     */
    public function draw()
    {
        $this->addBackground();
        $this->addOverlays();

        // == top left elements

        $area = $this->renderArea(
            new Box($this->width - $this->cardPadding * 2, $this->topAreaHeight),
            'areas.top.left'
        );
        $this->image->paste($area, new Point($this->cardPadding, $this->cardPadding));

        // == top right elements

        $area = $this->renderArea(
            new Box($this->width - $this->cardPadding * 2, $this->topAreaHeight),
            'areas.top.right'
        );
        $this->image->paste(
            $area,
            new Point(max(0, $this->width - $area->getSize()->getWidth() - $this->cardPadding), $this->cardPadding)
        );

        // == bottom left elements

        $area = $this->renderArea(
            new Box($this->width - $this->cardPadding * 2, $this->bottomAreaHeight),
            'areas.bottom.left'
        );
        $this->image->paste(
            $area,
            new Point($this->cardPadding, max(0, $this->height - $area->getSize()->getHeight() - $this->cardPadding))
        );

        // == bottom right elements

        $area = $this->renderArea(
            new Box($this->width - $this->cardPadding * 2, $this->bottomAreaHeight),
            'areas.bottom.right'
        );
        $this->image->paste(
            $area, new Point(
                max(0, $this->width - $area->getSize()->getWidth() - $this->cardPadding),
                max(0, $this->height - $area->getSize()->getHeight() - $this->cardPadding)
            )
        );


        // == center elements

        $columns = $this->option('areas.center');
        if (count($columns) > 0) {
            //$minGutterWidth = $this->areaPadding;
            $gridColumnWidth = (int)($this->width / count($columns));
            //if (count($columns) > 1) {
            //    $gridColumnWidth -= (int)($minGutterWidth / (count($columns) - 1));
            //}

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
                            $availableHeight = $this->height - $this->topAreaHeight - $this->bottomAreaHeight - $this->cardPadding * 2 - $this->areaPadding * 2;
                            $area = $this->createArea(new Box($gridColumnWidth * $span, $availableHeight));
                            $this->imagesGrid($area, $items, $rows);
                            switch ($vertical) {
                                case 'top':
                                    $posY = $this->topAreaHeight + $this->cardPadding + $this->areaPadding;
                                    break;
                                case 'bottom':
                                    $posY = $this->topAreaHeight + $this->cardPadding + $this->areaPadding + $availableHeight - $area->getSize()->getHeight();
                                    break;
                                case 'center':
                                    // default
                                default:
                                    $posY = $this->topAreaHeight + $this->cardPadding + $this->areaPadding
                                        + (int)(($availableHeight - $area->getSize()->getHeight()) / 2);
                                    break;
                            }
                            switch ($align) {
                                case 'left':
                                    $posX = $colCount * $gridColumnWidth + $this->areaPadding;
                                    break;
                                case 'right':
                                    $posX = $colCount * $gridColumnWidth
                                        + $gridColumnWidth * $span - $area->getSize()->getWidth() - $this->areaPadding;
                                    break;
                                case 'center':
                                    // default
                                default:
                                    $posX = $colCount * $gridColumnWidth
                                        + (int)(($gridColumnWidth * $span - $area->getSize()->getWidth()) / 2);
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
            $this->cardPadding,
            $this->cardPadding,
            $this->width - $this->cardPadding * 2,
            $this->topAreaHeight,
            $this->color($this->schemeColor, 30), $this->color('111', 65),
            true
        );

        // == bottom overlay

        $this->addGradient(
            $this->cardPadding,
            $this->height - $this->bottomAreaHeight - $this->cardPadding,
            $this->width - $this->cardPadding * 2,
            $this->bottomAreaHeight,
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