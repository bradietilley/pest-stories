<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasOrder
{
    protected static int $max = 0;

    /**
     * Get the boot order for this item
     */
    public function getOrder(): int
    {
        return $this->order ??= (++self::$max);
    }

    /**
     * Set the order to register/boot this item
     * 
     * @return $this 
     */
    public function order(int $order): self
    {
        $this->order = $order;
        self::$max = max(self::$max, $order);

        return $this;
    }
}