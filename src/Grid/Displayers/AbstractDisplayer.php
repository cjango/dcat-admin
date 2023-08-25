<?php

namespace Dcat\Admin\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use stdClass;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractDisplayer
{
    /**
     * @var array
     */
    protected static array $css = [];

    /**
     * @var array
     */
    protected static array $js = [];

    /**
     * @var Grid
     */
    protected Grid $grid;

    /**
     * @var Column
     */
    protected Column $column;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public Model $row;

    /**
     * @var mixed
     */
    protected mixed $value;

    /**
     * Create a new displayer instance.
     *
     * @param  mixed  $value
     * @param  Grid  $grid
     * @param  Column  $column
     * @param  \stdClass  $row
     */
    public function __construct(mixed $value, Grid $grid, Column $column, stdClass $row)
    {
        $this->value  = $value;
        $this->grid   = $grid;
        $this->column = $column;

        $this->setRow($row);
        $this->requireAssets();
    }

    protected function requireAssets(): void
    {
        if (static::$js) {
            Admin::js(static::$js);
        }

        if (static::$css) {
            Admin::css(static::$css);
        }
    }

    protected function setRow($row): void
    {
        if (is_array($row)) {
            $row = new Fluent($row);
        }

        $this->row = $row;
    }

    /**
     * @return string
     */
    public function getElementName(): string
    {
        $name = explode('.', $this->column->getName());

        if (count($name) == 1) {
            return $name[0];
        }

        $html = array_shift($name);
        foreach ($name as $piece) {
            $html .= "[$piece]";
        }

        return $html;
    }

    /**
     * Get key of current row.
     *
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->row->{$this->grid->getKeyName()};
    }

    /**
     * Get url path of current resource.
     *
     * @return string
     */
    public function resource(): string
    {
        return $this->grid->resource();
    }

    /**
     * Get translation.
     *
     * @param  string  $text
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function trans(string $text): TranslatorInterface|string
    {
        return trans("admin.$text");
    }

    /**
     * Display method.
     *
     * @return mixed
     */
    abstract public function display(): string;
}
