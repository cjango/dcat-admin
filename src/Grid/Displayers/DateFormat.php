<?php

namespace Dcat\Admin\Grid\Displayers;

use Carbon\Carbon;

class DateFormat extends AbstractDisplayer
{
    public function display($format = 'Y-m-d H:i:s'): string
    {
        if (empty($this->value)) {
            return '';
        }
        return Carbon::createFromDate($this->value)->format($format);
    }
}
