<?php

namespace Dcat\Admin\Models;

use Closure;
use Dcat\Admin\Admin;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

trait MenuCache
{
    protected string $cacheKey = 'dcat-admin-menus-%d-%s';

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param  \Closure  $builder
     * @return mixed
     */
    protected function remember(Closure $builder): mixed
    {
        if (! $this->enableCache()) {
            return $builder();
        }

        return $this->getStore()->remember($this->getCacheKey(), null, $builder);
    }

    /**
     * @return bool|void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function flushCache()
    {
        if (! $this->enableCache()) {
            return;
        }

        return $this->getStore()->delete($this->getCacheKey());
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return sprintf($this->cacheKey, (int) static::withPermission(), Admin::app()->getName());
    }

    /**
     * @return bool
     */
    public function enableCache(): bool
    {
        return config('admin.menu.cache.enable');
    }

    /**
     * Get cache store.
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function getStore(): Repository
    {
        return Cache::store(config('admin.menu.cache.store', 'file'));
    }
}
