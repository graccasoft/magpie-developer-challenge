<?php

namespace App;

class Product implements \JsonSerializable
{
    private string $title;
    private float $price;
    private string $imageUrl;
    private int $capacityMB;
    private string $colour;
    private string $availabilityText;
    private bool $isAvailable;
    private string $shippingText;
    private string $shippingDate;

    /**
     * @return string The title of product
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return float The price of product
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string The product image url
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return int The product capacity in MB
     */
    public function getCapacityMB(): int
    {
        return $this->capacityMB;
    }

    /**
     * @param int $capacityMB
     */
    public function setCapacityMB(int $capacityMB): void
    {
        $this->capacityMB = $capacityMB;
    }

    /**
     * @return string The product colour
     */
    public function getColour(): string
    {
        return $this->colour;
    }

    /**
     * @param string $colour
     */
    public function setColour(string $colour): void
    {
        $this->colour = $colour;
    }

    /**
     * @return string The product availability text
     */
    public function getAvailabilityText(): string
    {
        return $this->availabilityText;
    }

    /**
     * @param string $availabilityText
     */
    public function setAvailabilityText(string $availabilityText): void
    {
        $this->availabilityText = $availabilityText;
    }

    /**
     * @return bool If product available or not
     */
    public function getIsAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @return string The product shipping text
     */
    public function getShippingText(): string
    {
        return $this->shippingText;
    }

    /**
     * @param string $shippingText
     */
    public function setShippingText(string $shippingText): void
    {
        $this->shippingText = $shippingText;
    }

    /**
     * @return string The product shipping date
     */
    public function getShippingDate(): string
    {
        return $this->shippingDate;
    }

    /**
     * @param string $shippingDate
     */
    public function setShippingDate(string $shippingDate): void
    {
        $this->shippingDate = $shippingDate;
    }


    /**
     * So that the product properties can be added to json
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * This is required to compare if 2 different products are the same
     * @return string
     */
    public function  __toString(): string
    {
        return $this->title." ".$this->colour." ".$this->capacityMB;
    }
}
