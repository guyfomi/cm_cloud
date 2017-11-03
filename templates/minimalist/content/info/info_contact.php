<?php
/*
  $Id: info_contact.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

 $departments =array();
 
 $Qlisting = toC_Departments::getListing();
 while($Qlisting->next()) {
   $departments[] = array('id' => $Qlisting->value('departments_email_address'),
                         'text' => $Qlisting->value('departments_title'));
   
   $departments_description[$Qlisting->value('departments_email_address')] = $Qlisting->value('departments_description');
 }
?>

<!--<h1>--><?php //echo $osC_Template->getPageTitle(); ?><!--</h1>-->

<?php
  if ($messageStack->size('contact') > 0) {
    echo $messageStack->output('contact');
  }

  if (isset($_GET['contact']) && ($_GET['contact'] == 'success')) {
?>

<p><?php echo $osC_Language->get('contact_email_sent_successfully'); ?></p>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
</div>

<?php
  } else {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('contact_title'); ?></h6>

  <div class="content">
    <div style="float: right; padding: 0px 0px 10px 20px;">
      <?php echo nl2br(STORE_NAME_ADDRESS); ?>
    </div>

    <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
      <?php echo '<b>Adresse du Magasin</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?>
    </div>

    <p style="margin-top: 0px;"><?php echo $osC_Language->get('contact'); ?></p>

    <div style="clear: both;"></div>
  </div>
</div>

<form name="contact" action="<?php echo osc_href_link(FILENAME_INFO, 'contact=process', 'AUTO', true, false); ?>" method="post">

<div class="moduleBox">
  <h6></h6>
  <div class="content contact">
    <ol>
    <?php if (!empty($departments)){ ?>
      <li><?php echo osc_draw_label($osC_Language->get('contact_departments_title'), 'department_email') . osc_draw_pull_down_menu('department_email', $departments); ?></li><span id="departments_description"></span>
    <?php } ?>
      <li><?php echo osc_draw_label('Votre Nom', 'name', null, true) . osc_draw_input_field('name', $osC_Customer->getName(), 'size="30"'); ?></li>
      <li><?php echo osc_draw_label('No Telephone', 'telephone') . osc_draw_input_field('telephone', '', 'size="30"'); ?></li>
      <li><?php echo osc_draw_label('Email', 'email', null, true) . osc_draw_input_field('email', $osC_Customer->getEmailAddress(), 'size="30"'); ?></li>
      <li><?php echo osc_draw_label('Message', 'enquiry') . osc_draw_textarea_field('enquiry', null, 38, 5); ?></li>

    <?php if( ACTIVATE_CAPTCHA == '1') {?>
      <li><?php echo osc_draw_label('Code Securite', 'concat_code') . osc_draw_input_field('concat_code', '', 'size="30"'); ?> </li>
      <li><img style = "padding-left: 170px;" src="<?php echo osc_href_link(FILENAME_INFO, 'contact=showImage', 'AUTO', true, false); ?>" alt="Captcha" /></li>
    <?php } ?>
    
    </ol>
  </div>
</div>

<?php
  echo osc_draw_hidden_session_id_field();
?>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?>
</div>

</form>
  <?php if (!empty($departments_description)) { ?>
    <script type="text/javascript">
      window.addEvent("domready", function() {
        var description = {};
      <?php
        foreach($departments_description as $key => $description) {
      ?>
      
        description['<?php echo $key; ?>'] = '<?php echo $description; ?>';
      
      <?php } ?>
          
        $('departments_description').set('html', description[$('department_email').get('value')]);
          
        $('department_email').addEvent('change', function() {
          $('departments_description').set('html', description[this.value]);
        });
      });
    
    </script>
<?php
    }
  }
?>