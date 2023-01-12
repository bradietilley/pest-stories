<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasOrder
{
    protected static int $orderCounter = 0;

    /**
     * Get the next order number
     */
    public static function getNextOrder(): int
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
     */
    public function setOrder(?int $order): static
    {
        $order ??= self::getNextOrder();

        $this->order = $order;
        self::$orderCounter = max(self::$orderCounter, $order);

        return $this;
    }

    /**
     * Alias of setOrder()
     */
    public function order(?int $order): static
    {
        return $this->setOrder($order);
    }
}
