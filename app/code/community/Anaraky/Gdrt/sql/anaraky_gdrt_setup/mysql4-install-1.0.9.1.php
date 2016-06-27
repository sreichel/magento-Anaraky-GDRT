<?php

$installer = $this;
$installer->startSetup();

# change config values
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/gdrt_enable'
    WHERE path = 'gdrt/general/gdrt_enable';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/mail_template_ebay'
    WHERE path = 'gdrt/general/gdrt_product_id';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/mail_template_ebay'
    WHERE path = 'gdrt/general/gdrt_product_id_prefix';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/mail_template_ebay'
    WHERE path = 'gdrt/general/gdrt_product_id_prefix_ofcp';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/mail_template_ebay'
    WHERE path = 'gdrt/general/gdrt_product_id_ending';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/mail_template_ebay'
    WHERE path = 'gdrt/general/gdrt_product_id_ending_ofcp';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/mail_template_shop'
    WHERE path = 'gdrt/general/gdrt_tax';
    ");

$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/gc_id'
    WHERE path = 'gdrt/general/gc_id';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_general/gc_label'
    WHERE path = 'gdrt/general/gc_label';
    ");

    ###

$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_pages/home'
    WHERE path = 'gdrt/pages/home';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_pages/searchresults'
    WHERE path = 'gdrt/pages/searchresults';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_pages/category'
    WHERE path = 'gdrt/pages/category';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_pages/product'
    WHERE path = 'gdrt/pages/product';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_pages/cart'
    WHERE path = 'gdrt/pages/cart';
    ");
$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_pages/purchase'
    WHERE path = 'gdrt/pages/purchase';
    ");

    ###

$installer->run("
    UPDATE {$this->getTable('core_config_data')}
    SET path = 'google/gdrt_debug/show_info'
    WHERE path = 'gdrt/debug/show_info';
    ");

$installer->endSetup();
