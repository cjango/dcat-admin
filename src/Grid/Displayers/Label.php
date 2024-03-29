<?php

namespace Dcat\Admin\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Support\Helper;

class Label extends AbstractDisplayer
{
    protected string $baseClass = 'label';

    public function display($style = 'primary', $max = null): string
    {
        if (! $value = $this->value($max)) {
            return '';
        }

        $original = $this->column->getOriginal();

        if (! is_null($original) &&! is_int($original) && ! is_string($original) && method_exists($original, 'tryFrom')) {
            $original = $original->value;
        }

        $defaultStyle = is_array($style) ? ($style['default'] ?? 'default') : 'default';

        $background = $this->formatStyle(
            is_array($style) ?
                (is_scalar($original) ? ($style[$original] ?? $defaultStyle) : current($style))
                : $style
        );

        return collect($value)->map(function ($name) use ($background) {
            return "<span class='$this->baseClass' $background>$name</span>";
        })->implode(' ');
    }

    protected function formatStyle($style): string
    {
        $background = 'style="background:#d2d6de;color: #555"';

        if ($style !== 'default') {
            $style = Admin::color()->get($style, $style);

            $background = "style='background:$style'";
        }

        return $background;
    }

    protected function value($max): array
    {
        $values = Helper::array($this->value);

        if ($max && count($values) > $max) {
            $values = array_slice($values, 0, $max);
            $values[] = '...';
        }

        return $values;
    }
}
