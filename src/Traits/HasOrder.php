<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasOrder
{
    protected static int $orderCounter = 0;

    /**
     * Get the boot order for this item
     */
    public function getOrder(): int
    {
        return $this->order ??= (++self::$orderCounter);
    }

    /**
     * Set the order to register/boot this item
     * 
     * @return $this 
     */
    public function setOrder(int $order): self
    {
        $this->order = $order;
        self::$orderCounter = max(self::$orderCounter, $order);

        return $this;
    }

    /**
     * Alias of setOrder()
     * 
     * @return $this 
     */
    public function order(int $order): self
    {
        return $this->setOrder($order);
    }
}