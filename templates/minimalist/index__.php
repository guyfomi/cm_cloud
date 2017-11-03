<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $osC_Language->getTextDirection(); ?>"
      xml:lang="<?php echo $osC_Language->getCode(); ?>" lang="<?php echo $osC_Language->getCode(); ?>">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $osC_Language->getCharacterSet(); ?>"/>
    <meta http-equiv="x-ua-compatible" content="ie=7"/>

    <title><?php echo ($osC_Template->hasMetaPageTitle() ? $osC_Template->getMetaPageTitle() . ' - '
            : '') . STORE_NAME; ?></title>
    <base href="<?php echo osc_href_link(null, null, 'AUTO', false); ?>"/>

    <link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/stylesheet.css"/>
    <link rel="stylesheet" type="text/css" href="ext/autocompleter/Autocompleter.css"/>

    <!--[if IE]>
    <style type="text/css">
        #productInfoTab a {
            top: 11px;
        }

        #productInfoTab a.unselect {
            top: 13px;
        }
    </style>
    <![endif]-->
    <?php
    if ($osC_Template->hasPageTags()) {
        echo $osC_Template->getPageTags();
    }

    if ($osC_Template->hasStyleSheet()) {
        $osC_Template->getStyleSheet();
    }
    ?>

    <meta name="Generator" content="TomatoCart"/>
</head>
<body>
<?php
  if ($osC_Template->hasPageHeader()) {
    ?>

<div id="pageHeader">
    <div id="headerBar" style="height: 120px;">
        <?php
                          echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_image($osC_Template->getLogo(), STORE_NAME), 'id="siteLogo"');
        ?>
        <div id="navigation" style="margin-top: 80px;">
            <div id="navigationInner">
        <?php

            global $cPath, $osC_CategoryTree, $osC_Template, $osC_Database, $osC_Language;

            echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'index'), $osC_Language->get('home')) . '&nbsp;&nbsp;';

            $Qcategories = $osC_Database->query('select c.categories_id, c.parent_id, c.categories_image, cd.categories_name, cd.categories_url, cd.categories_page_title, cd.categories_meta_keywords, cd.categories_meta_description from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id and c.categories_status = 1 and c.parent_id = 0 order by c.parent_id, c.sort_order, cd.categories_name');
            $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
            $Qcategories->bindInt(':language_id', $osC_Language->getID());
            $Qcategories->execute();

            while ($Qcategories->next()) {
                echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $Qcategories->value('categories_id')), $Qcategories->value('categories_name')) . '&nbsp;&nbsp;';
            }

            ?>

            <div style="height: 30px; width: 203px; margin-bottom: 0pt; padding-bottom: 0px; float: right;">
                <form name="search" action="<?php echo osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false);?>"
                      method="get">
                    <p class="keywords"
                       style="margin-top: -7px; width: 200px;"><?php echo osc_draw_input_field('keywords', null, 'maxlength="20"') ?></p>

                    <p><input type="image"
                              src="<?php echo 'templates/' . $osC_Template->getCode() . '/images/button_quick_find.png'; ?>"
                              alt="<?php echo $osC_Language->get('box_search_heading'); ?>"
                              title="<?php echo $osC_Language->get('box_search_heading'); ?>"
                              id="quickSearch"
                              style="margin-bottom: 0px; padding-top: 0px; padding-bottom: 0px; margin-top: 46px; margin-left: 0pt;"/><?php echo osc_draw_hidden_session_id_field(); ?>
                    </p>
                </form>
            </div>
            </div>
        </div>
    </div>

</div>

        <?php

}
?>
<?php
      if ($osC_Services->isStarted('breadcrumb')) {
    ?>
<div id="breadcrumbPath"
     style="padding-top: 5px; background: none repeat scroll 0% 0% transparent; height: 20px; margin-top: -1px; width: 955px;">
    <?php
            echo $breadcrumb->trail(' &raquo; ');
    ?>
</div>
    <?php

}
?>
<div id="pageWrapper">
    <div id="pageBlockLeft">
<?php
    $content_left = $osC_Template->getBoxGroup('left');

    if (!empty($content_left)) {
        ?>

        <div id="pageColumnLeft">
            <div class="boxGroup">
                <?php
                          echo $content_left;
                ?>
            </div>
        </div>

                <?php

    } else {
        ?>
        <style type="text/css"><!--
        #pageContent {
            width: 745px;
        }

        /
        /
        --></style>
        <?php

    }
    ?>

    <div id="pageContent">

<?php
        if ($messageStack->size('header') > 0) {
    echo $messageStack->output('header');
}

    if ($osC_Template->hasPageContentModules()) {
        foreach ($osC_Services->getCallBeforePageContent() as $service) {
            $$service[0]->$service[1]();
        }

        $content_before = $osC_Template->getContentGroup('before');
        if (!empty($content_before)) {
            echo $content_before;
        }
    }

    if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
        include('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
    } else {
        if (file_exists('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename())) {
            include('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
        } else {
            include('templates/' . DEFAULT_TEMPLATE . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
        }
    }
    ?>

    <div style="clear: both;"></div>

<?php
        if ($osC_Template->hasPageContentModules()) {
    foreach ($osC_Services->getCallAfterPageContent() as $service) {
        $$service[0]->$service[1]();
    }

    $content_after = $osC_Template->getContentGroup('after');
    if (!empty($content_after)) {
        echo $content_after;
    }
}
    ?>

    </div>
    </div>

<?php
    $content_right = $osC_Template->getBoxGroup('right');
    ?>
<?php
    if (!empty($content_right)) {
    ?>
    <div id="pageColumnRight">
        <div class="boxGroup">
            <?php
                      echo $content_right;
            ?>
        </div>
    </div>

            <?php

} elseif (empty($content_right) && empty($content_left)) {
    ?>
    <style type="text/css"><!--
    #pageContent, #pageBlockLeft {
        width: 960px;
    }

    --></style>
    <?php

} elseif (empty($content_right)) {
    ?>
    <style type="text/css"><!--
    #pageContent {
        width: 745px;
    }

    #pageBlockLeft {
        width: 960px;
    }

    /
    /
    --></style>
    <?php

}

    unset($content_left);
    unset($content_right);
    ?>
</div>

<?php
    if ($osC_Template->hasPageFooter()) {
    ?>

<div id="pageFooter">
    <?php
            echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')) . '&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('box_information_contact')) . '&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook&new'), 'Deposer une citation&nbsp;&nbsp;');
    echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_INFO, 'sitemap'), $osC_Language->get('box_information_sitemap')) . '&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'rss'), osc_image(DIR_WS_IMAGES . 'rss16x16.png') . '<span>RSS</span>') . '&nbsp;&nbsp;';
    ?>
    <div style="clear:both"></div>
    <p style="margin: 3px;">
    <?php
                    echo sprintf($osC_Language->get('footer'), date('Y'), STORE_NAME);
        ?>
    </p>
</div>

    <?php
    if ($osC_Template->hasJavascript()) {
        $osC_Template->getJavascript();
    }
    
            if ($osC_Services->isStarted('banner') && $osC_Banner->exists('468x60')) {
        echo '<p align="center">' . $osC_Banner->display() . '</p>';
    }
}
?>


<script type="text/javascript">
    window.addEvent('domready', function() {
    });
</script>


<?php
    if ($osC_Services->isStarted('piwik')) {
        echo $toC_Piwik->renderJs();
    }
?>
</body>
</html>