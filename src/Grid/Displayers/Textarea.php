<?php

namespace Dcat\Admin\Grid\Displayers;

class Textarea extends Editable
{
    protected $type = 'textarea';

    protected $view = 'admin::grid.displayer.editinline.textarea';

    public function defaultOptions(): array
    {
        return [
            'rows' => 5,
        ];
    }
}
