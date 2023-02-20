<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This object can be ordered by auto incrementing
 * order via getNextOrder or by manually specifying
 * an order.
 */
interface WithOrder
{
    /**
     * Get the next order number
     */
    public static function getNextOrder(): int;

    /**
     * Get the boot order for this item
     */
    public function getOrder(): int;

    /**
     * Set the order to register/boot this item
     */
    public function setOrder(?int $order): static;

    /**
     * Alias of setOrder()
     */
    public function order(?int $order): static;
}
