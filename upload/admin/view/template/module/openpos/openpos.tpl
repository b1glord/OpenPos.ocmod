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
      <div class="buttons"><a href="<?php echo $barcode; ?>" class="button">Barkod Yazdır</a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
	     
	      <a href="#tab-store"><?php echo $tab_store; ?></a>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        
        <div id="tab-store">
          <table class="list">
	          <thead>
	            <tr>
	              
	              <td class="left"><?php echo $column_name; ?></a></td>
	              <td class="left"><?php echo $column_url; ?></td>
	              <td class="right"><?php echo $column_action; ?></td>
	            </tr>
	          </thead>
	          <tbody>
	            <?php if ($stores) { ?>
	            <?php foreach ($stores as $store) { ?>
	            <tr>
	              
	              <td class="left"><?php echo $store['name']; ?></td>
	              <td class="left"><?php echo $store['url']; ?></td>
	              <td class="right"><?php foreach ($store['action'] as $action) { ?>
	                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
	                <?php } ?></td>
	            </tr>
	            <?php } ?>
	            <?php } else { ?>
	            <tr>
	              <td class="center" colspan="4"><?php echo $text_no_results; ?></td>
	            </tr>
	            <?php } ?>
	          </tbody>
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
function image_upload(field, thumb) {
	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: '<?php echo $text_image_manager; ?>',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).val()),
					dataType: 'text',
					success: function(data) {
						$('#' + thumb).replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
					}
				});
			}
		},	
		bgiframe: false,
		width: 800,
		height: 400,
		resizable: false,
		modal: false
	});
};
//--></script> 
<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script> 
<?php echo $footer; ?>