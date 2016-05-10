# Magento: Google Dynamic Remarketing Tag

Google remarketing can help you reach people who have previously visited your website as they visit other sites on the Google Display Network or search on Google. Using remarketing, you can show these customers messages tailored to them based on which sections of your site they visited.

With Anaraky GDRT you can simply and easy integrate the Google Dynamic Remarketing Tag in your store's pages.

More info at http://www.magentocommerce.com/magento-connect/anaraky-gdrt-google-dynamic-remarketing-tag-for-magento.html

## Features:
- Very easy to set up.
- Safe (it does not change any core files or structure of database in the Magento).
- Multi-stores support.
- Possibility to choose what to use for product id (SKU or ID).
- Possibility to choose the calculation of total value ('ecomm_totalvalue') with or without taxes.
- Possibility to change page types.

## Compatible with:
- 1.5
- 1.6
- 1.6.1
- 1.6.2.0
- 1.7
- 1.8
- 1.8.1
- 1.9

## Installation:

### Via modman
```
modman clone https://github.com/sreichel/magento-Anaraky-GDRT.git
```
### Via composer:
```
{
    "require": {
        "anaraky/gdrt": "*",
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/sreichel/magento-Anaraky-GDRT.git"
        }
    ]
}