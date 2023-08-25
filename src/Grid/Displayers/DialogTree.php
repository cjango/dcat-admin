<?php

namespace Dcat\Admin\Grid\Displayers;

use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Support\Helper;
use Illuminate\Contracts\Support\Arrayable;

class DialogTree extends AbstractDisplayer
{
    protected string $url;

    protected string $title;

    protected array $area = ['580px', '600px'];

    protected array $options = [
        'plugins'  => ['checkbox', 'types'],
        'core'     => [
            'check_callback' => true,

            'themes' => [
                'name'       => 'proton',
                'responsive' => true,
            ],
        ],
        'checkbox' => [
            'keep_selected_style' => false,
        ],
        'types'    => [
            'default' => [
                'icon' => false,
            ],
        ],
    ];

    protected array $columnNames = [
        'id'     => 'id',
        'text'   => 'name',
        'parent' => 'parent_id',
    ];

    protected array $nodes = [];

    protected $checkAll;

    protected int $rootParentId = 0;

    /**
     * @param  array  $data
     * @return $this
     */
    public function nodes($data): static
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->nodes = &$data;

        return $this;
    }

    public function rootParentId($id): static
    {
        $this->rootParentId = $id;

        return $this;
    }

    public function url(string $source): static
    {
        $this->url = admin_url($source);

        return $this;
    }

    public function checkAll(): static
    {
        $this->checkAll = true;

        return $this;
    }

    /**
     * @param  array  $options
     * @return $this
     */
    public function options($options = []): static
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function title($title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param  string  $width
     * @param  string  $height
     * @return $this
     */
    public function area(string $width, string $height): static
    {
        $this->area = [$width, $height];

        return $this;
    }

    public function setIdColumn(string $name): static
    {
        $this->columnNames['id'] = $name;

        return $this;
    }

    public function setTitleColumn(string $name): static
    {
        $this->columnNames['text'] = $name;

        return $this;
    }

    public function setParentColumn(string $name): static
    {
        $this->columnNames['parent'] = $name;

        return $this;
    }

    public function display($callbackOrNodes = null): string
    {
        if (is_array($callbackOrNodes) || $callbackOrNodes instanceof Arrayable) {
            $this->nodes($callbackOrNodes);
        } elseif ($callbackOrNodes instanceof Closure) {
            $callbackOrNodes->call($this->row, $this);
        }

        return Admin::view('admin::grid.displayer.dialogtree', [
            'value'        => $this->format($this->value),
            'nodes'        => $this->nodes,
            'title'        => $this->title ?: $this->column->getLabel(),
            'options'      => $this->options,
            'area'         => $this->area,
            'columnNames'  => $this->columnNames,
            'url'          => $this->url,
            'checkAll'     => $this->checkAll,
            'rootParentId' => $this->rootParentId,
        ]);
    }

    protected function format($val): string
    {
        return implode(',', Helper::array($val, true));
    }
}
