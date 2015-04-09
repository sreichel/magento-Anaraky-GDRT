<?php

class Anaraky_Gdrt_Model_Adminhtml_System_Config_Source_Useasid
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> 'Product SKU'),
            array('value' => 0, 'label'=> 'Product ID')
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => 'Product ID',
            1 => 'Product SKU',
        );
    }

}