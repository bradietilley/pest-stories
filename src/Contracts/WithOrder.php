<?php

namespace BradieTilley\StoryBoard\Contracts;

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
