<?php
/**
 * @author luchaos
 */

namespace Completionist\Vanity\Card;

use Completionist\Vanity\Card\Layout\AStatsRetro;
use Completionist\Vanity\Card\Layout\Classic;
use Completionist\Vanity\Card\Layout\Layout;
use Exception;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;

class Card
{

    /** @var int $cacheLifetime */
    protected $cacheLifetime;

    /** @var String $outputDirectory */
    protected $outputDirectory;

    /** @var int $filename */
    protected $filename;

    /** @var String $format */
    protected $format = 'png';

    /** @var bool $addSuffix */
    protected $addSuffix;

    /** @var String $fontPath */
    protected $fontPath;

    /** @var Layout $layout */
    protected $layout;

    /** @var string $layoutType */
    protected $layoutType = 'classic';

    /** @var array $layoutOptions */
    protected $layoutOptions;

    /** @var array $data */
    protected $data;

    /** @var Image $image */
    protected $image;

    /** @var Imagine $imagine */
    protected $imagine;


    /**
     * Card constructor.
     *
     * @param string|null $outputDirectory
     * @param string|null $filename
     * @param string|null $format
     * @param bool|null   $addSuffix
     * @param string|null $fontPath
     */
    public function __construct($outputDirectory = null, $filename = null, $format = null, $addSuffix = null,
        $fontPath = null
    ) {
        if (!is_null($outputDirectory)) {
            $this->outputDirectory($outputDirectory);
        }
        if (!is_null($filename)) {
            $this->filename($filename, $addSuffix);
        }
        if (!is_null($format)) {
            $this->format($format);
        }
        if (!is_null($fontPath)) {
            $this->fonts($fontPath);
        }
        return $this;
    }

    /**
     * @param int $cacheLifetime
     * @return $this
     */
    public function cacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = $cacheLifetime;
        return $this;
    }

    /**
     * @param String $outputDirectory
     * @return $this
     */
    public function outputDirectory($outputDirectory)
    {
        $this->outputDirectory = rtrim($outputDirectory, '/');
        return $this;
    }

    /**
     * @param int  $filename
     * @param bool $addSuffix
     * @return $this
     */
    public function filename($filename, $addSuffix = false)
    {
        $this->filename = $filename;
        $this->addSuffix = $addSuffix;
        return $this;
    }

    /**
     * @param String $format
     * @return $this
     */
    public function format($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param String|Layout $layout
     * @param null|array    $layoutOptions
     * @param null|array    $data
     * @return $this
     */
    public function layout($layout, $layoutOptions = null, $data = null)
    {
        if(is_a($layout, 'Completionist\Vanity\Card\Layout\Layout')) {
            $this->layout = $layout;
            $this->layoutType = $layout->id();
        } else {
            $this->layoutType = $layout;
        }
        if ($layoutOptions) {
            $this->layoutOptions = $layoutOptions;
        }
        if (is_null($this->layout)) {
            switch ($this->layoutType) {
                case 'classic':
                default:
                    $this->layout = new Classic;
                    break;
            }
        }
        if ($data) {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * @param String $fontPath
     * @return $this
     */
    public function fonts($fontPath)
    {
        $this->fontPath = rtrim($fontPath, '/');
        return $this;
    }

    /**
     * @param array $data
     * @return array|string|null
     */
    public function data($data)
    {
        if (!is_null($this->data) && is_string($data)) {
            if(is_array($this->data)) {
                return $this->resolveDotNotation($this->data, $data);
            }
        } else {
            if(!empty($data)) {
                $this->data = $data;
                return $this->data;
            }
        }
    }

    /**
     * @param string $option
     * @param null   $default
     * @return array|null|string
     */
    public function option($option, $default = null)
    {
        if (!empty($this->layoutOptions) && is_string($option)) {
            $option = $this->resolveDotNotation($this->layoutOptions, $option, $default);
            if($option === 'false'){
                return false;
            }
            if($option === 'true'){
                return true;
            }
            return $option;
        }
    }

    /**
     * @param null|int $cacheLifetime
     * @throws Exception
     */
    public function show($cacheLifetime = null)
    {
        if (!is_null($cacheLifetime)) {
            $this->cacheLifetime = $cacheLifetime;
        }
        $this->validate();
        $this->card()->show($this->format);
    }

    public function base64()
    {
        return base64_encode($this->card()->get($this->format));
    }

    /**
     * @throws Exception
     */
    private function validate()
    {
        if (empty($this->filename)) {
            throw new Exception('Invalid Steam ID: "'.$this->filename.'".');
        }

        if (is_null($this->format) || !in_array($this->format, ['png', 'png'])) {
            throw new Exception('Invalid format "'.$this->format.'". Use "png" or "jpg".');
        }

        // TODO: validate file exists
        // TODO: validate directory is writable
        // TODO: validate given data is sufficient
    }

    /**
     * @return Image|\Imagine\Image\ImageInterface
     */
    private function card()
    {
        $this->imagine = new Imagine();

        $imagePath = $this->imagePath($this->addSuffix ? $this->layout->id() : null);

        if (!empty($this->cacheLifetime)
            && file_exists($imagePath)
            && (time() - filemtime($imagePath) <= $this->cacheLifetime)
        ) {
            // cached card image
            return $this->imagine->open($imagePath);
        } else {
            // fresh card image
            return $this->drawLayout()->save($imagePath);
        }
    }

    /**
     * @return Image
     */
    private function drawLayout()
    {
        $this->layout->card($this);
        $this->layout->fonts($this->fontPath);
        return $this->layout->draw();
    }

    // == helpers

    /**
     * Image path helper
     *
     * @param String|null $suffix
     * @return string
     */
    private function imagePath($suffix = null)
    {
        return $this->outputDirectory.'/'.$this->filename.($suffix ? '-'.$suffix : '').'.'.$this->format;
    }

    /**
     * Array dot notation accessor helper
     *
     * @param array $a
     * @param       $path
     * @param null  $default
     * @return array|null
     */
    protected function resolveDotNotation(array $a, $path, $default = null)
    {
        $current = $a;
        $p = strtok($path, '.');
        while ($p !== false) {
            if (!isset($current[$p])) {
                return $default;
            }
            $current = $current[$p];
            $p = strtok('.');
        }
        return $current;
    }
}