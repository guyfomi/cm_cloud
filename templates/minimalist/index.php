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
    <link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/style.css"/>
    <?php
    if ($osC_Template->hasPageTags()) {
    echo $osC_Template->getPageTags();
}

    if ($osC_Template->hasStyleSheet()) {
        $osC_Template->getStyleSheet();
    }

    if ($osC_Template->hasJavascript()) {
        $osC_Template->getJavascript();
    }
    ?>
    <script type="text/javascript">
    </script>
</head>
<body>
<div class="main">
    <div class="main_resize">
        <div class="main_left">
            <div class="logo"><?php
                          echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_image($osC_Template->getLogo(), STORE_NAME), 'id="siteLogo"');
                ?>
            </div>
            <div class="clr"></div>
            <br>
    <?php
        $content_left = $osC_Template->getBoxGroup('left');
            echo $content_left;
            ?>
        </div>

        <div class="main_right">
            <div class="search">
                <form id="form1" name="form1" method="post" action="#">
                    <label><span>
            <input name="q" type="text" class="keywords" id="textfield" maxlength="50" value="Recherche..."/>
            </span>
                        <input name="b" type="image" src="images/search.gif" class="button"/>
                    </label>

                    <div class="clr"></div>
                </form>
            </div>
            <div class="clr"></div>
            <br>
    <?php
                              if ($osC_Services->isStarted('breadcrumb')) {
            ?>
            <div style="width: 100%;">
                <div id="breadcrumbPath"">
                    <?php
                                                                                    echo $breadcrumb->trail(' &raquo; ');
                    ?>
                </div>
                <div id='contact_link'><?php
                                                                echo osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), 'Contactez nous');
                    ?></div>
            </div>
            <div class="clr"></div>
                    <?php

        }
            ?>
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
        <div class="clr"></div>
        <div class="footer">
            <div class="footer_resize">
                <p>
    <?php
//                echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')) . '&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('box_information_contact')) . '&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook&new'), 'Vos critiques et suggestions&nbsp;&nbsp;');
        echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_INFO, 'sitemap'), $osC_Language->get('box_information_sitemap')) . '&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'rss'), osc_image(DIR_WS_IMAGES . 'rss16x16.png') . '<span>RSS</span>') . '&nbsp;&nbsp;';
        ?>
                </p>

                <div class="clr"></div>
                <p>
    <?php
                                            echo sprintf($osC_Language->get('footer'), date('Y'), STORE_NAME);
        ?>
                </p>

            </div>
        </div>
    </div>
    <?php

    if ($osC_Services->isStarted('banner') && $osC_Banner->exists('468x60')) {
        echo '<p align="center">' . $osC_Banner->display() . '</p>';
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
