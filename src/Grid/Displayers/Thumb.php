<?php

namespace Dcat\Admin\Grid\Displayers;

use Dcat\Admin\Admin;
use Illuminate\Support\Facades\Storage;

class Thumb extends AbstractDisplayer
{
    protected $baseClass = 'thumb';

    protected function addScript()
    {
        $script = <<<'JS'
$('.img-thumbnail').on('click', function () {
    var $this = $(this), data = $this.data('src');
    console.log(data)
});
JS;
        Admin::script($script);
    }

    public function display($width = 80, $height = 100)
    {
        $this->addScript();
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
<img src="$thumbSrc" data-src="$src" style="max-width:{$width}px;max-height:{$height}px" class="img img-thumbnail">
HTML;
    }
}