<?php

namespace Wsmallnews\Order\Support;

/**
 * 阶段选择器（OrderPipes::scene()->stage('calc') 的链式句柄）
 */
class StageSelector
{
    public function __construct(
        protected string $scene,
        protected string $stage,
    ) {}

    /**
     * 在锚点管道之后插入
     *
     * @param  class-string  $pipe
     */
    public function after(string $key, string $pipe, ?string $as = null): static
    {
        OrderPipes::addOperation($this->scene, $this->stage, 'after', $key, $pipe, $as);

        return $this;
    }

    /**
     * 在锚点管道之前插入
     *
     * @param  class-string  $pipe
     */
    public function before(string $key, string $pipe, ?string $as = null): static
    {
        OrderPipes::addOperation($this->scene, $this->stage, 'before', $key, $pipe, $as);

        return $this;
    }

    /**
     * 替换锚点管道
     *
     * @param  class-string  $pipe
     */
    public function replace(string $key, string $pipe, ?string $as = null): static
    {
        OrderPipes::addOperation($this->scene, $this->stage, 'replace', $key, $pipe, $as);

        return $this;
    }

    /**
     * 移除锚点管道
     */
    public function remove(string $key): static
    {
        OrderPipes::addOperation($this->scene, $this->stage, 'remove', $key, null, null);

        return $this;
    }

    /**
     * 插到清单最前
     *
     * @param  class-string  $pipe
     */
    public function prepend(string $pipe, ?string $as = null): static
    {
        OrderPipes::addOperation($this->scene, $this->stage, 'prepend', '', $pipe, $as);

        return $this;
    }

    /**
     * 追加到清单末尾
     *
     * @param  class-string  $pipe
     */
    public function append(string $pipe, ?string $as = null): static
    {
        OrderPipes::addOperation($this->scene, $this->stage, 'append', '', $pipe, $as);

        return $this;
    }
}
