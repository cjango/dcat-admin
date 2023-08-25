<?php

namespace Dcat\Admin\Grid\Displayers;

use Dcat\Admin\Support\Helper;

class DropdownActions extends Actions
{
    protected string $view = 'admin::grid.dropdown-actions';

    /**
     * @var array
     */
    protected array $default = [];

    public function prepend($action): static
    {
        return $this->append($action);
    }

    /**
     * @param  mixed  $action
     * @return mixed
     */
    protected function prepareAction(&$action)
    {
        parent::prepareAction($action);

        return $action = $this->wrapCustomAction($action);
    }

    /**
     * @param  mixed  $action
     * @return string
     */
    protected function wrapCustomAction($action)
    {
        $action = Helper::render($action);

        if (mb_strpos($action, '</a>') === false) {
            return "<a>$action</a>";
        }

        return $action;
    }

    /**
     * Prepend default `edit` `view` `delete` actions.
     */
    protected function prependDefaultActions()
    {
        foreach ($this->actions as $action => $enable) {
            if (! $enable) {
                continue;
            }

            array_push($this->default, $this->{'render'.ucfirst($action)}());
        }
    }

    /**
     * @param  array  $callbacks
     * @return string
     */
    public function display(array $callbacks = []): string
    {
        $this->resetDefaultActions();

        $this->call($callbacks);

        $this->prependDefaultActions();

        $actions = [
            'default'  => $this->default,
            'custom'   => $this->appends,
            'selector' => ".{$this->grid->getRowName()}-checkbox",
        ];

        return view($this->view, $actions);
    }

    protected function getViewLabel(): string
    {
        return '';
    }

    protected function getEditLabel()
    {
    }

    protected function getQuickEditLabel()
    {
    }

    protected function getDeleteLabel()
    {
    }
}
