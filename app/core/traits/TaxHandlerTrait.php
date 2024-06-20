<?php

namespace boctulus\SW\core\traits;

/*
    // Usage example
    
    try {
        // Set 21% tax for product with ID 123
        TaxHandler::setTax(123, 21.0);
        echo "Tax for product 123 set to 21%.\n";

        // Set 10.5% tax for product with ID 456
        TaxHandler::setTax(456, 10.5);
        echo "Tax for product 456 set to 10.5%.\n";
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }    
*/
trait TaxHandlerTrait
{
    /**
     * Set tax class for a given product based on percentage.
     * 
     * @param int $pid Product ID
     * @param float $percentage_tax Tax percentage
     * @throws \InvalidArgumentException If tax percentage is not supported
     */
    public static function setTax(int $pid, float $percentage_tax) {
        // Get all tax classes
        $tax_classes = self::getAllTaxClasses();
        
        // Find the matching tax class based on percentage
        $tax_class = self::findTaxClassByPercentage($tax_classes, $percentage_tax);
        
        if (!$tax_class) {
            throw new \InvalidArgumentException("Tax no admitido.");
        }

        // Update product tax meta data
        update_post_meta($pid, '_tax_status', $tax_class['status']);
        update_post_meta($pid, '_tax_class', $tax_class['class']);
    }

    /**
     * Get all tax classes and their respective percentages.
     * 
     * @return array Array of tax classes with their percentages
     */
    private static function getAllTaxClasses(): array {
        // Get the tax classes from WooCommerce
        $tax_classes = \WC_Tax::get_tax_classes();
        
        // Add the default tax class
        $tax_classes = array_merge([''], $tax_classes);

        $tax_rates = [];

        foreach ($tax_classes as $tax_class) {
            $class_name = $tax_class ? $tax_class : 'standard';
            $rates = \WC_Tax::get_rates($tax_class);

            foreach ($rates as $rate) {
                $tax_rates[] = [
                    'class' => $tax_class,
                    'percentage' => $rate->tax_rate,
                    'status' => $rate->tax_rate > 0 ? 'taxable' : 'none',
                ];
            }
        }

        return $tax_rates;
    }

    /**
     * Find the tax class that matches the given percentage.
     * 
     * @param array $tax_classes Array of tax classes
     * @param float $percentage_tax Tax percentage
     * @return array|null Matching tax class or null if not found
     */
    private static function findTaxClassByPercentage(array $tax_classes, float $percentage_tax): ?array {
        foreach ($tax_classes as $tax_class) {
            if ($tax_class['percentage'] == $percentage_tax) {
                return $tax_class;
            }
        }
        return null;
    }
}
