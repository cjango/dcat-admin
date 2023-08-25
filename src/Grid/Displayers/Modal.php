<?php

namespace Dcat\Admin\Grid\Displayers;

use Closure;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Widgets\Modal as WidgetModal;

class Modal extends AbstractDisplayer
{
    protected string $title;

    protected bool $xl = false;

    protected string $icon = 'fa-clone';

    public function title(string $title): void
    {
        $this->title = $title;
    }

    public function xl(): void
    {
        $this->xl = true;
    }

    public function icon($icon): void
    {
        $this->icon = $icon;
    }

    protected function setUpLazyRenderable(LazyRenderable $renderable)
    {
        return clone $renderable->payload(['key' => $this->getKey()]);
    }

    public function display($callback = null): string
    {
        $title = $this->value ?: $this->trans('title');
        if (func_num_args() == 2) {
            [$title, $callback] = func_get_args();
        }

        $html = $this->value;

        if ($callback instanceof Closure) {
            $callback = $callback->call($this->row, $this);

            if (! $callback instanceof LazyRenderable) {
                $html = Helper::render($callback);

                $callback = null;
            }
        }

        if ($callback && is_string($callback) && is_subclass_of($callback, LazyRenderable::class)) {
            $html = $this->setUpLazyRenderable($callback::make());
        } elseif ($callback && $callback instanceof LazyRenderable) {
            $html = $this->setUpLazyRenderable($callback);
        }

        $title = $this->title ?: $title;

        return WidgetModal::make()
            ->when(true, function ($modal) {
                $this->xl ? $modal->xl() : $modal->lg();
            })
            ->title($title)
            ->body($html)
            ->delay(300)
            ->button($this->renderButton());
    }

    protected function renderButton(): string
    {
        $icon = $this->icon ? "<i class='fa $this->icon'></i>&nbsp;&nbsp;" : '';

        return "<a href='javascript:void(0)'>$icon$this->value</a>";
    }
}
