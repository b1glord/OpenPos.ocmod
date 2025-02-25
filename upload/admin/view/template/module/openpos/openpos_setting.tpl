<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/setting.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
	      <a href="#tab-general"><?php echo $tab_general; ?></a>
	    
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
       	<input type="hidden" name="store_id" value="<?php echo $store_id; ?>">
        <div id="tab-general">
          <table class="form">
            
            <tr>
              <td><span class="required">*</span> Kategori Gösterimi İçin Seçim</td>
              <td>
              	<div id="product-filter" class="scrollbox">
              		
              		<?php 
              			
              			$class = 'even'; 
              			if(!isset($openpos['openpos_categories']))
              			{
              				$openpos['openpos_categories'] = array();
              			}
              		?>
	                 
	                  <?php foreach ($categories as $category) { ?>
	                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
	                  <div class="<?php echo $class; ?>">
	                  	
	                    <?php if ( in_array($category['category_id'],$openpos['openpos_categories'])) { ?>
	                    <input type="checkbox" name="openpos_categories[]" value="<?php echo $category['category_id']; ?>" checked="checked" />
	                    <?php echo $category['name']; ?>
	                    <?php } else { ?>
	                    <input type="checkbox" name="openpos_categories[]" value="<?php echo $category['category_id']; ?>" />
	                    <?php echo $category['name']; ?>
	                    <?php } ?>
	                  </div>
	                 <?php } ?>
                    
                </div>
                <?php if (isset($error_category) and $error_category) { ?>
                <span class="error"><?php echo $error_category; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td>
              	<span class="required">*</span> Varsayılan Müşteri E-Posta Adresi
              	<br/>
              	<span class="help">(Otomatik)</span>
              </td>
              <td>
              	<div>
              		<?php
              			if(!isset($openpos['openpos_default_customer']['email']))
              			{
              				$openpos['openpos_default_customer']['email'] = '';
              			}
              			
              			if(!isset($openpos['openpos_default_customer']['id']))
              			{
              				$openpos['openpos_default_customer']['id'] = '';
              			}
              		?>
              		<input type="text" id="default_customer_email" value="<?php echo $openpos['openpos_default_customer']['email'];?>"  name="openpos_default_customer[email]" size="40">              
                    <input type="hidden" id="default_customer_id" value="<?php echo $openpos['openpos_default_customer']['id'];?>" name="openpos_default_customer[id]" size="40">              
                    
                </div>
                <?php if (isset($error_store) and $error_store) { ?>
                <span class="error"><?php echo $error_store; ?></span>
                <?php } ?></td>
            </tr>
            
             <tr>
              <td><span class="required">*</span> Varsayılan Sipariş Durumu
              <br/>
              	<span class="help">(Varsayılan Sipariş Ödeme Durumu)</span>
              </td>
              
              <td>
              	<select name="config_pos_complete_status_id">
                  	<?php foreach($order_statuses as $s): ?>
              		  <?php if($s['order_status_id'] == $openpos['config_pos_complete_status_id']):?>
              		  <option value="<?php echo $s['order_status_id']; ?>" selected="selected"><?php echo $s['name']; ?></option>
              		  <?php else: ?>
              		  <option value="<?php echo $s['order_status_id']; ?>"><?php echo $s['name']; ?></option>
              		  <?php endif; ?>
                      
                      <?php endforeach; ?>
                </select>
                </td>
            </tr>
            <tr>
              <td><span class="required">*</span>Kullanıcı Grubu
               <br/>
              	<span class="help">(Kullanıcı grubu izin POS giriş)</span>
              </td>
              <td>
              		<select name="config_pos_user_group_id">
              		  <?php foreach($user_groups as $g): ?>
              		  <?php if($g['user_group_id'] == $openpos['config_pos_user_group_id']):?>
              		  <option value="<?php echo $g['user_group_id']; ?>" selected="selected"><?php echo $g['name']; ?></option>
              		  <?php else: ?>
              		  <option value="<?php echo $g['user_group_id']; ?>"><?php echo $g['name']; ?></option>
              		  <?php endif; ?>
                      
                      <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
              <td><span class="required">*</span>POS Ödeme Metodu
              <br/>
              	<span class="help">(POS ödeme Nakit kabul)</span>
              </td>
              </td>
              <td>
              	<input type="text" id="default_payment" value="<?php echo isset($openpos['default_payment'])?$openpos['default_payment']:'';?>"  name="default_payment">
              	<br/>
              	<span class="help">Örnek: Kredi Kartı Tek Çekim | Kredi Kartı Taksitli | Payu Express Taksitli | Banka Havalesi EFT / BTM </span>
              </td>
            </tr>
            <tr>
              <td><span class="required">*</span>Vergi</td>
              <td>
              	<select name="config_pos_tax">
                      <option value="0" <?php if(isset($openpos['config_pos_tax']) and $openpos['config_pos_tax'] == 0):?>selected="selected"<?php endif;?> >Vergi Yok</option>
                      <option value="-1"<?php if(isset($openpos['config_pos_tax']) and $openpos['config_pos_tax'] == -1):?>selected="selected"<?php endif;?> >Ürün Vergisi Kullan</option>
                      <?php foreach($tax_classes as $s): ?>
              		  <?php if($s['tax_class_id'] == $openpos['config_pos_tax']):?>
              		  <option value="<?php echo $s['tax_class_id']; ?>" selected="selected"><?php echo $s['title']; ?></option>
              		  <?php else: ?>
              		  <option value="<?php echo $s['tax_class_id']; ?>"><?php echo $s['title']; ?></option>
              		  <?php endif; ?>
                      
                      <?php endforeach; ?>
                </select>
              </td>
            </tr>
            
          </table>
        </div>
        
        
        
  </div>
</div>

<script type="text/javascript"><!--

// Category
$('#default_customer_email').autocomplete({
	delay: 500,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=module/openpos/customerautocomplete&token=<?php echo $token; ?>&filter_email=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.email,
						value: item.customer_id
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		
		$('#default_customer_email').val(ui.item.label);
		$('#default_customer_id').val(ui.item.value);
				
		return false;
	},
	focus: function(event, ui) {
      return false;
   }
});
//--></script> 

<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script> 
<?php echo $footer; ?>