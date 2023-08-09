<?php

namespace Dcat\Admin\Grid\Displayers;

use Illuminate\Support\Facades\Storage;

class Thumb extends AbstractDisplayer
{
    protected string $baseClass = 'thumb';

    public function display($width = 80, $height = 100): string
    {
        $path = $this->column->getOriginal();

        if (empty($path)) {
            return '';
        }

        if (url()->isValidUrl($path) || mb_strpos($path, 'data:image') === 0) {
            $src = $thumbSrc = $path;
        } else {
            $thumbPath = str_replace('.', '-thumb.', $path);
            $thumbSrc  = Storage::url($thumbPath);
            $src       = Storage::url($path);
        }

        return <<<HTML
<img data-action="preview-img" src="$thumbSrc" data-src="$src" style="max-width:{$width}px;max-height:{$height}px" class="img img-thumbnail">
HTML;
    }
}