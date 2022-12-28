<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasOrder
{
    protected static int $orderCounter = 0;

    /**
     * Get the next order number
     */
    protected static function getNextOrder(): int
    {
        return ++self::$orderCounter;
    }

    /**
     * Get the boot order for this item
     */
    public function getOrder(): int
    {
        return $this->order ??= self::getNextOrder();
    }

    /**
     * Set the order to register/boot this item
     *
     * @return $this
     */
    public function setOrder(?int $order): self
    {
        $order ??= self::getNextOrder();

        $this->order = $order;
        self::$orderCounter = max(self::$orderCounter, $order);

        return $this;
    }

    /**
     * Alias of setOrder()
     *
     * @return $this
     */
    public function order(?int $order): self
    {
        return $this->setOrder($order);
    }
}
