<?php
class ControllerModuleOpenPos extends Controller {
	private $error = array();
	
	public function install()
	{
		$this->updateUpc();
	}

	private function updateUpc()
	{
		$sql = "SELECT * FROM  `" . DB_PREFIX . "product` WHERE upc = ''";
		$query = $this->db->query($sql);
		foreach($query->rows as $row)
		{
			$num = sprintf("%011s", $row['product_id']);
			$upc = $this->createUPC($num);
			$sql = "UPDATE  `" . DB_PREFIX . "product` SET upc = '".$upc."' WHERE product_id = '".$row['product_id']."'";
			$this->db->query($sql);
		}
	}
	public function index()
	{
		$this->updateUpc();
		$this->language->load('module/openpos');
		$this->load->model('setting/setting');
		$this->document->setTitle($this->language->get('heading_title'));
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('openposbarcode', $this->request->post);
		
			$this->session->data['success'] = $this->language->get('text_success');
			$this->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('module/openpos', 'token=' . $this->session->data['token'], 'SSL'));
		}
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_default'] = $this->language->get('text_default');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_store'] = $this->language->get('tab_store');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_url'] = $this->language->get('column_url');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->load->model('setting/store');
		$this->data['stores'] = array();
		$action = array();
		
		$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('module/openpos/setting', 'store_id=0&token=' . $this->session->data['token'], 'SSL')
		);
		
		$this->data['stores'][] = array(
				'store_id' => 0,
				'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
				'url'      => HTTP_CATALOG,
				'selected' => isset($this->request->post['selected']) && in_array(0, $this->request->post['selected']),
				'action'   => $action
		);
		$results = $this->model_setting_store->getStores();
		
		foreach ($results as $result) {
			$action = array();
		
			$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('module/openpos/setting', 'store_id='.$result['store_id'].'&token=' . $this->session->data['token'] . '&store_id=' . $result['store_id'], 'SSL')
			);
		
			$this->data['stores'][] = array(
					'store_id' => $result['store_id'],
					'name'     => $result['name'],
					'url'      => $result['url'],
					'selected' => isset($this->request->post['selected']) && in_array($result['store_id'], $this->request->post['selected']),
					'action'   => $action
			);
		}
		
		$this->load->model('catalog/category');
		$this->data['categories'] = $this->model_catalog_category->getCategories(array());
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		$this->data['breadcrumbs'] = array();
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
		);
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_module'),
				'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
		);
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('module/openpos', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
		);
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->data['openpos'] = $this->request->post;
		} elseif ($this->model_setting_setting->getSetting('openposbarcode')) {
			
			$this->data['openpos'] = $this->model_setting_setting->getSetting('openposbarcode');
				
		}
		$this->data['token'] =  $this->session->data['token'];
		$this->data['action'] = $this->url->link('module/openpos', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['barcode'] = $this->url->link('module/openpos/product', 'token=' . $this->session->data['token'], 'SSL');
		
		
		$this->template = 'module/openpos/openpos.tpl';
		$this->children = array(
				'common/header',
				'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}
	
	public function customerautocomplete()
	{
		$json = array();
		
		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
			$this->load->model('sale/customer');
			$data = array(
					'filter_email'             => $filter_email,
			);
			
			$customer_total = $this->model_sale_customer->getTotalCustomers($data);
			
			$results = $this->model_sale_customer->getCustomers($data);
			
			foreach ($results as $result) {
				$json[] = array(
						'customer_id' => $result['customer_id'],
						'email'        => $result['email']
				);
			}
		}
		
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function setting()
	{
		$this->language->load('module/openpos');
		$this->load->model('catalog/category');
		$this->load->model('setting/store');
		$this->load->model('setting/setting');
		$this->load->model('user/user_group');
		$this->load->model('localisation/order_status');
		$this->data['store_id'] = $this->request->get['store_id'];
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('openpos', $this->request->post,$this->request->post['store_id']);
		
			$this->session->data['success'] = $this->language->get('text_success');
			$this->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('module/openpos/setting', 'store_id='.$this->request->post['store_id'].'&token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_default'] = $this->language->get('text_default');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_store'] = $this->language->get('tab_store');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->data['openpos'] = $this->request->post;
		} elseif ($this->model_setting_setting->getSetting('openpos',$this->data['store_id'])) {
				
			$this->data['openpos'] = $this->model_setting_setting->getSetting('openpos',$this->data['store_id']);
		
		}
		
		$this->data['categories'] = $this->model_catalog_category->getCategories(array());
		$this->data['action'] = $this->url->link('module/openpos/setting', 'store_id='.$this->request->get['store_id'].'&token=' . $this->session->data['token'], 'SSL');
		$this->data['user_groups'] = $this->model_user_user_group->getUserGroups();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->load->model('localisation/tax_class');
		
		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		$this->data['token'] =  $this->session->data['token'];
		$this->data['breadcrumbs'] = array();
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
		);
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_module'),
				'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
		);
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('module/openpos', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
		);
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		$this->data['cancel'] = $this->url->link('module/openpos', 'token=' . $this->session->data['token'], 'SSL');
		
		
		$this->template = 'module/openpos/openpos_setting.tpl';
		$this->children = array(
				'common/header',
				'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}
	
	private function validate()
	{
		return true;
	}
	
	public function product()
	{
		$this->language->load('catalog/product');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/product');
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		
		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = null;
		}
		
		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = null;
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['breadcrumbs'] = array();
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
		);
		
		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
		);
		
		$this->data['print'] = $this->url->link('module/openpos/print', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['back'] = $this->url->link('module/openpos', 'token=' . $this->session->data['token'] . $url, 'SSL');
		
		$this->data['products'] = array();
		
		$data = array(
				'filter_name'	  => $filter_name,
				'filter_model'	  => $filter_model,
				'filter_price'	  => $filter_price,
				'filter_quantity' => $filter_quantity,
				'filter_status'   => $filter_status,
				'sort'            => $sort,
				'order'           => $order,
				'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
				'limit'           => $this->config->get('config_admin_limit')
		);
		
		$this->load->model('tool/image');
		
		$product_total = $this->model_catalog_product->getTotalProducts($data);
		
		$results = $this->model_catalog_product->getProducts($data);
		
		foreach ($results as $result) {
			$action = array();
		
			$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('module/openpos/printbarcode', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL')
			);
		
			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
			}
		
			$special = false;
		
			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);
		
			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] < date('Y-m-d')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] > date('Y-m-d'))) {
					$special = $product_special['price'];
		
					break;
				}
			}
		
			$this->data['products'][] = array(
					'product_id' => $result['product_id'],
					'name'       => $result['name'],
					'model'      => $result['model'],
					'price'      => $result['price'],
					'upc'      	 => $result['upc'],
					'special'    => $special,
					'image'      => $image,
					'quantity'   => $result['quantity'],
					'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
					'selected'   => isset($this->request->post['selected']) && in_array($result['product_id'], $this->request->post['selected']),
					'action'     => $action
			);
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		
		$this->data['column_image'] = $this->language->get('column_image');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->data['button_copy'] = $this->language->get('button_copy');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');
		
		$this->data['token'] = $this->session->data['token'];
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_name'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.model' . $url, 'SSL');
		$this->data['sort_price'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, 'SSL');
		$this->data['sort_quantity'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.quantity' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.status' . $url, 'SSL');
		$this->data['sort_order'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();
		$this->data['filter_name'] = $filter_name;
		$this->data['filter_model'] = $filter_model;
		$this->data['filter_price'] = $filter_price;
		$this->data['filter_quantity'] = $filter_quantity;
		$this->data['filter_status'] = $filter_status;
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		
		$this->template = 'module/openpos/openpos_product.tpl';
		$this->children = array(
				'common/header',
				'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}
	
	public function printbarcode()
	{
		require_once(DIR_SYSTEM . 'library/pos/php-barcode.php');
		require_once(DIR_SYSTEM . 'library/pos/fpdf.php');
		$this->load->model('catalog/product');
		$this->load->model('setting/setting');
		$product_id = $this->request->get['product_id'];
		$product = $this->model_catalog_product->getProduct($product_id);
		
		$angle    = 0; 
		$fontSize = 10;
		$marge    = 0;  
		$x        = 40;
		$y        = 38;
		$height   = 10;
		$width    = 0.5; 
		$code     = $product['upc'];
		$type     = 'ean13';
		$black    = '000000';
		$pdf = new eFPDF('P', 'mm',array(80,60));
		$pdf->AddPage();
		$data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);
		$pdf->SetFont('Arial','',$fontSize);
		$pdf->SetTextColor(0, 0, 0);
		$len = $pdf->GetStringWidth(html_entity_decode($product['name']));
		$pdf->TextWithRotation(($x - $len/2) , 20, html_entity_decode($product['name']), 0);
		$pdf->SetFont('Arial','B','15');
		$price = $this->currency->format($product['price']);
		$len = $pdf->GetStringWidth($price);
		$pdf->TextWithRotation(($x - $len/2), 30, $price, 0);
		$pdf->SetFont('Arial','',$fontSize);
		$len = $pdf->GetStringWidth($data['hri']);
		Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + $marge, $angle, $xt, $yt);
		$pdf->TextWithRotation($x + $xt, $y + $height , $data['hri'], $angle);
		$pdf->Output();
	}
	
	function createCheckDigit($code) {
		$evensum = 0;
		$oddsum = 0;
		if ($code) {
			for ($counter=0;$counter<=strlen($code)-1;$counter++) {
				$codearr[]=substr($code,$counter,1);
			}
				
			for ($counter=0;$counter<=count($codearr)-1;$counter++) {
				if ( $counter&1 ) {
					$evensum = $evensum + $codearr[$counter];
				} else {
					$oddsum = $oddsum + $codearr[$counter];
				}
			}
				
			$oddsum = $oddsum *3;
			$oddeven = $oddsum + $evensum;
				
			for ($number=0;$number<=9;$number++) {
				if (($oddeven+$number)%10==0) {
					$checksum = $number;
				}
			}
				
			return $checksum;
		} else {
			return false;
		}
	}
	
	function createUPC($code) {
		if ($code!="") {
			$checkdigit = $this->createCheckDigit($code);
			$upc = $code . $checkdigit;
				
			return $upc;
		} else {
			return false;
		}
	}
	
	function validateUPC($upc) {
		if ($upc!="") {
			$checkdigit = substr($upc, -1);
			$code = substr($upc, 0, -1);
				
			$checksum = $this->createCheckDigit($code);
			if ($checkdigit == $checksum) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}



