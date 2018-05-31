<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

interface DeliveryInformationManagementInterface
{
    /**
     * @param int $cartId
     * @param string $date
     * @param int    $time
     *
     * @return bool
     */
    public function update($cartId, $date, $time);
}
