<?php
/**
 * @author luchaos
 */

namespace Completionist\Vanity\Card\Layout;

use Imagine\Gd\Image;
use Imagine\Image\Point;

class AStatsRetro extends Layout
{
    protected $id = 'astats-retro';
    protected $width = 300;
    protected $height = 80;
    protected $backgroundColor = '000000';
    protected $textColor = 'C8C8C8';
    protected $textColorHighlight = 'FFFFFF';
    protected $fontName = 'Helvetica Bold.ttf';
    protected $fontSize = 14;
    protected $schemeColor = '000000';

    private $avatarSize = 32;

    /**
     * @return Image
     */
    public function draw()
    {
        $this->addImage($this->image, $this->data('steam.avatar'), 260, 5, $this->avatarSize);

        $this->addText($this->image, 'AStats.nl', 4, 4, 14, '8C8CFF');

        $this->addText($this->image, $this->data('steam.name'), 4, 24, 14, 'FFFF00');

        $this->image->draw()->polygon(
            [new Point(10, 44), new Point(290, 44), new Point(290, 45), new Point(10, 45)], $this->color('646464'), true
        );

        $totalAchievements = $this->data('steam.totalAchievements');
        $this->addText($this->image, $totalAchievements.' Achievements', 4, 49, 12);

        $totalGamesPerfect = $this->data('steam.totalGamesPerfect');
        $this->addText($this->image, $totalGamesPerfect.' Games 100%', 4, 64, 12);

        $rank = $this->data('astats.rank');
        $label = ($rank > 999 ? 'Rank: ' : 'Worldwide rank: ').$rank;
        $posX = $rank > 99 ? $rank > 999 ? 130 : 97 : 105;
        $this->addText($this->image, $label, $posX, 6, 12, '42FFFF');

        $this->addAchievements();

        return $this->image;
    }

    private function addAchievements()
    {
        $achievements = $this->data('achievements');
        if (isset($achievements['best'])) {
            $achievements = $achievements['best'];
        }
        if (count($achievements)) {
            $items = 5;
            for ($i = 0; $i <= $items; $i++) {
                $achievement = isset($achievements[$i]) ? (object)$achievements[$i] : null;
                if ($achievement) {
                    $size = 22;
                    $borderSize = 1;
                    $margin = 1;
                    $x = 300 - (($items - $i) * ($size + $borderSize * 2 + $margin)) - 2;
                    $y = 80 - ($size + 2) - 4;
                    $this->addImage($this->image, $achievement->image, $x, $y, $size);
                }
            }
        }
    }
}