<?php

namespace Dcat\Admin\Grid\Column;

use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;
use Dcat\Admin\Grid\RowAction;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property Grid $grid
 */
trait HasDisplayers
{
    /**
     * Display using display abstract.
     *
     * @param  string  $abstract
     * @param  array  $arguments
     * @return Column
     */
    public function displayUsing($abstract, $arguments = [])
    {
        $grid   = $this->grid;
        $column = $this;

        return $this->display(function ($value) use ($grid, $abstract, $column, $arguments) {
            $displayer = new $abstract($value, $grid, $column, $this);

            return $displayer->display(...$arguments);
        });
    }

    /**
     * Display column using array value map.
     *
     * @param  array  $values
     * @param  null  $default
     * @return $this
     */
    public function using(array $values, $default = null)
    {
        return $this->display(function ($value) use ($values, $default) {
            if (is_null($value)) {
                return $default;
            }

            if (! is_int($value) && ! is_string($value) && method_exists($value, 'tryFrom')) {
                $value = $value->value;
            }

            return Arr::get($values, $value, $default);
        });
    }

    /**
     * @param  string  $color
     * @return $this
     */
    public function bold($color = null)
    {
        $color = $color ?: Admin::color()->dark80();

        return $this->display(function ($value) use ($color) {
            if (! $value) {
                return $value;
            }

            return "<b style='color: $color'>$value</b>";
        });
    }

    /**
     * Display column with "long2ip".
     *
     * @param  null  $default
     * @return $this
     */
    public function long2ip($default = null)
    {
        return $this->display(function ($value) use ($default) {
            if (! $value) {
                return $default;
            }

            return long2ip($value);
        });
    }

    /**
     * Render this column with the given view.
     *
     * @param  string  $view
     * @return $this
     */
    public function view($view)
    {
        $name = $this->name;

        return $this->display(function ($value) use ($view, $name) {
            $model = $this;

            return view($view, compact('model', 'value', 'name'))->render();
        });
    }

    /**
     * @param  \Closure|string  $val
     * @return $this
     */
    public function prepend($val)
    {
        return $this->display(function ($v, $column) use (&$val) {
            if ($val instanceof Closure) {
                $val = $val->call($this, $v, $column->getOriginal(), $column);
            }

            if (is_array($v)) {
                array_unshift($v, $val);

                return $v;
            } elseif ($v instanceof Collection) {
                return $v->prepend($val);
            }

            return $val.$v;
        });
    }

    /**
     * @param  \Closure|string  $val
     * @return $this
     */
    public function append($val)
    {
        return $this->display(function ($v, $column) use (&$val) {
            if ($val instanceof Closure) {
                $val = $val->call($this, $v, $column->getOriginal(), $column);
            }

            if (is_array($v)) {
                $v[] = $val;

                return $v;
            } elseif ($v instanceof Collection) {
                return $v->push($val);
            }

            return $v.$val;
        });
    }

    /**
     * Split a string by string.
     *
     * @param  string  $d
     * @return $this
     */
    public function explode(string $d = ',')
    {
        return $this->display(function ($v) use ($d) {
            if (is_array($v) || $v instanceof Arrayable) {
                return $v;
            }

            return $v ? explode($d, $v) : [];
        });
    }

    /**
     * Display the fields in the email format as gavatar.
     *
     * @param  int  $size
     * @return $this
     */
    public function gravatar($size = 30)
    {
        return $this->display(function ($value) use ($size) {
            $src = sprintf(
                'https://www.gravatar.com/avatar/%s?s=%d',
                md5(strtolower($value)),
                $size
            );

            return "<img src='$src' class='img img-circle' alt=''/>";
        });
    }

    /**
     * Add a `dot` before column text.
     *
     * @param  array  $options
     * @param  string  $default
     * @return $this
     */
    public function dot($options = [], $default = 'default')
    {
        return $this->prepend(function ($_, $original) use ($options, $default) {
            $style = $default;

            if (! is_null($original)) {
                if (! is_int($original) && ! is_string($original) && method_exists($original, 'tryFrom')) {
                    $original = $original->value;
                }

                $style = Arr::get($options, $original, $default);
            }

            $style = $style === 'default' ? 'dark70' : $style;

            $background = Admin::color()->get($style, $style);

            return "<i class='fa fa-circle' style='font-size: 13px;color: $background'></i>&nbsp;&nbsp;";
        });
    }

    /**
     * Show children of current node.
     *
     * @param  bool  $showAll
     * @param  bool  $sortable
     * @param  mixed  $defaultParentId
     * @return $this
     */
    public function tree(bool $showAll = false, bool $sortable = true, $defaultParentId = null)
    {
        $this->grid->model()->enableTree($showAll, $sortable, $defaultParentId);

        $this->grid->listen(Grid\Events\Fetching::class, function () use ($showAll) {
            if ($this->grid->model()->getParentIdFromRequest()) {
                $this->grid->disableFilter();
                $this->grid->disableToolbar();

                if ($showAll) {
                    $this->grid->disablePagination();
                }
            }
        });

        return $this->displayUsing(Grid\Displayers\Tree::class);
    }

    /**
     * Display column using a grid row action.
     *
     * @param  string  $action
     * @return $this
     * @throws \Dcat\Admin\Exception\InvalidArgumentException
     */
    public function action($action)
    {
        if (! is_subclass_of($action, RowAction::class)) {
            throw new InvalidArgumentException("Action class [$action] must be sub-class of [Dcat\Admin\Grid\RowAction]");
        }

        $grid = $this->grid;

        return $this->display(function ($_, $column) use ($action, $grid) {
            /** @var RowAction $action */
            if (! ($action instanceof RowAction)) {
                $action = $action::make();
            }

            return $action
                ->setGrid($grid)
                ->setColumn($column)
                ->setRow($this);
        });
    }

    /**
     * Display column as boolean , `✓` for true, and `✗` for false.
     *
     * @param  array  $map
     * @param  bool  $default
     * @return $this
     */
    public function bool(array $map = [], $default = false)
    {
        return $this->display(function ($value) use ($map, $default) {
            $bool = empty($map) ? $value : Arr::get($map, $value, $default);

            return $bool ? '<i class="feather icon-check font-md-2 font-w-600 text-primary"></i>' : '<i class="feather icon-x font-md-1 font-w-600 text-70"></i>';
        });
    }

    /**
     * Notes   : 文件大小
     *
     * @Date   : 2023/7/5 17:06
     * @Author : <Jason.C>
     * @param  int  $bytes
     * @return string
     */
    public function filesize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2).' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes.' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes.' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    public function diffForHumans($locale = null)
    {
        if ($locale) {
            Carbon::setLocale($locale);
        }

        return $this->display(function ($value) {
            return Carbon::parse($value)->diffForHumans();
        });
    }
}
