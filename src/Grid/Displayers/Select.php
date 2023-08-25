<?php

namespace Dcat\Admin\Grid\Displayers;

use Closure;
use Dcat\Admin\Admin;

class Select extends AbstractDisplayer
{
    public function display($options = [], $refresh = false): string
    {
        if ($options instanceof Closure) {
            $options = $options->call($this, $this->row);
        }

        return Admin::view('admin::grid.displayer.select', [
            'column'  => $this->column->getName(),
            'value'   => $this->value,
            'url'     => $this->url(),
            'options' => $options,
            'refresh' => $refresh,
        ]);
    }

    protected function url(): string
    {
        return $this->resource().'/'.$this->getKey();
    }
}
