<?php

require('../includes/classes/layout_tree.php');


class osC_LayoutTree_Admin extends osC_LayoutTree
{
    var $show_category_product_count = true;

    function osC_LayoutTree_Admin()
    {
        global $osC_Database;

        $this->data = array();

        $Qcategories = $osC_Database->query("select c.customers_id as categories_id,c.customers_status as categories_status, 0 as parent_id,' ' as categories_image,'customer' as content_type, c.customers_surname as categories_name,'' as categories_url,'' as categories_page_title,'' as categories_meta_keywords,'' as categories_meta_description from :table_customers c order by c.customers_surname");
        $Qcategories->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcategories->execute();

        while ($Qcategories->next()) {
            $this->data[$Qcategories->valueInt('parent_id')][$Qcategories->valueInt('categories_id')] = array('name' => $Qcategories->value('categories_name'), 'image' => $Qcategories->value('categories_image'), 'count' => 0);
        }

        $Qcategories->freeResult();

        $Qcategories = $osC_Database->query('select c.categories_id, c.parent_id, c.categories_image, cd.categories_name from :table_layout c, :table_categories_description cd where c.categories_id = cd.categories_id order by c.parent_id, c.sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->execute();

        while ($Qcategories->next()) {
            $this->data[$Qcategories->valueInt('parent_id')][$Qcategories->valueInt('categories_id')] = array('name' => $Qcategories->value('categories_name'), 'image' => $Qcategories->value('categories_image'), 'count' => 0);
        }

        $Qcategories->freeResult();

        if ($this->show_category_product_count === true) {
            $this->calculateCategoryProductCount();
        }
    }

    function calculateCategoryProductCount()
    {
        global $osC_Database;

        $totals = array();

        $Qtotals = $osC_Database->query('select p2c.categories_id, count(*) as total from :table_products p, :table_products_to_categories p2c where p2c.products_id = p.products_id group by p2c.categories_id');
        $Qtotals->bindTable(':table_products', TABLE_PRODUCTS);
        $Qtotals->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qtotals->execute();

        while ($Qtotals->next()) {
            $totals[$Qtotals->valueInt('categories_id')] = $Qtotals->valueInt('total');
        }

        $Qtotals->freeResult();

        foreach ($this->data as $parent => $categories) {
            foreach ($categories as $id => $info) {
                if (isset($totals[$id]) && ($totals[$id] > 0)) {
                    $this->data[$parent][$id]['count'] = $totals[$id];

                    $parent_category = $parent;
                    while ($parent_category != $this->root_category_id) {
                        foreach ($this->data as $parent_parent => $parent_categories) {
                            foreach ($parent_categories as $parent_category_id => $parent_category_info) {
                                if ($parent_category_id == $parent_category) {
                                    $this->data[$parent_parent][$parent_category_id]['count'] += $this->data[$parent][$id]['count'];

                                    $parent_category = $parent_parent;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        unset($totals);
    }

    function getPath($category_id, $level = 0, $separator = ' ')
    {
        $path = '';

        foreach ($this->data as $parent => $categories) {
            foreach ($categories as $id => $info) {
                if ($id == $category_id) {
                    if ($level < 1) {
                        $path = $info['name'];
                    } else {
                        $path = $info['name'] . $separator . $path;
                    }

                    if ($parent != $this->root_category_id) {
                        $path = $this->getPath($parent, $level + 1, $separator) . $path;
                    }
                }
            }
        }

        return $path;
    }
}

?>
