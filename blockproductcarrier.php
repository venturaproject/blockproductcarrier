<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class BlockProductCarrier extends Module
{
	protected $_html = '';
	protected $_postErrors = array();

	protected static $cache_specials;

	public function __construct()
	{
		$this->name = 'blockproductcarrier';
		$this->tab = 'front_office_features';
		$this->version = '1.3.0';
		$this->author = 'ventura';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Block product carrier');
		$this->description = $this->l('Adds a block displaying products carrier');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
	
	
	    $category_config = '';
		$categories = Category::getSimpleCategories((int)Configuration::get('PS_LANG_DEFAULT'));
		foreach ($categories as $category)
		$category_config .= (int)$category['id_category'].',';
		$category_config = rtrim($category_config, ',');
		Configuration::updateValue('PS_CATEGORIES_PROD_CARRIER', $category_config);

		if (!Configuration::get('PRODUCT_CARRIER_IMG_WIDTH'))
		Configuration::updateValue('PRODUCT_CARRIER_IMG_WIDTH', 40);
		if (!Configuration::get('PRODUCT_CARRIER_IMG_HEIGHT'))
		Configuration::updateValue('PRODUCT_CARRIER_IMG_HEIGHT', 40);
		Configuration::updateValue('PRODUCT_CARRIER_DELAY', 1);
		Configuration::updateValue('ACTIVE_IN_PRODUCTS_LIST', 0);
		Configuration::updateValue('PRODUCT_CARRIER_IS_FREE', 0);
		Configuration::updateValue('PRODUCT_CARRIER_IMG', 1);
		Configuration::updateValue('PRODUCT_CARRIER_NAME', 1);
		Configuration::updateValue('PS_PRODUCTS_PROD_CARRIER', '');

		$this->_clearCache('*');

		$success = parent::install()
			&& $this->registerHook('header')
			&& $this->registerHook('displayProductButtons')
		    && $this->registerHook('displayProductCarrier');

		return $success;
	}

	public function uninstall()
	{
		Configuration::deleteByName('PS_CATEGORIES_PROD_CARRIER');
		Configuration::deleteByName('PS_PRODUCTS_PROD_CARRIER');
		Configuration::deleteByName('PRODUCT_CARRIER_NAME');
		Configuration::deleteByName('PRODUCT_CARRIER_DELAY');
		Configuration::deleteByName('ACTIVE_IN_PRODUCTS_LIST');
		Configuration::deleteByName('PRODUCT_CARRIER_IS_FREE');
		Configuration::deleteByName('PRODUCT_CARRIER_IMG_WIDTH');
		Configuration::deleteByName('PRODUCT_CARRIER_IMG_HEIGHT');
		Configuration::deleteByName('PRODUCT_CARRIER_IMG');
		$this->_clearCache('*');
		return parent::uninstall();
	}

	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit($this->name))
		{
		    Configuration::updateValue('PS_PRODUCTS_PROD_CARRIER', implode(',', Tools::getValue('PS_PRODUCTS_PROD_CARRIER')));
            Configuration::updateValue('PS_CATEGORIES_PROD_CARRIER', $this->productCarrierCategories(Tools::getValue('categoryBox')));
			Configuration::updateValue('PRODUCT_CARRIER_NAME', (int)Tools::getValue('PRODUCT_CARRIER_NAME'));
			Configuration::updateValue('ACTIVE_IN_PRODUCTS_LIST', (int)Tools::getValue('ACTIVE_IN_PRODUCTS_LIST'));
			Configuration::updateValue('PRODUCT_CARRIER_DELAY', (int)Tools::getValue('PRODUCT_CARRIER_DELAY'));
			Configuration::updateValue('PRODUCT_CARRIER_IS_FREE', (int)Tools::getValue('PRODUCT_CARRIER_IS_FREE'));
			Configuration::updateValue('PRODUCT_CARRIER_IMG', (int)Tools::getValue('PRODUCT_CARRIER_IMG'));
			Configuration::updateValue('PRODUCT_CARRIER_IMG_WIDTH', (int)Tools::getValue('PRODUCT_CARRIER_IMG_WIDTH'));
			Configuration::updateValue('PRODUCT_CARRIER_IMG_HEIGHT', (int)Tools::getValue('PRODUCT_CARRIER_IMG_HEIGHT'));
			$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->renderForm();
	}

	private function productCarrierCategories($categories)
	{
		$cat = '';
		if ($categories && is_array($categories))
			foreach ($categories as $category)
				$cat .= $category.',';
		return rtrim($cat, ',');
	}
	
	public function isProductSelected()
	
	{
		
	    $products_selected = explode(',', Configuration::get('PS_PRODUCTS_PROD_CARRIER'));
		
		foreach (Product::getProducts() as $product){
		   
         $result = (in_array($product['id_product'],$products_selected)) ? true: false;
      
       }
        return  $result;
	}

  public function hookDisplayProductCarrier($params)
    {
     	
		
		
	
		if (!$this->isCached('productcarriers-list.tpl', $this->getCacheId()))
		{
		if (Configuration::get('PS_CATALOG_MODE') || Configuration::get('ACTIVE_IN_PRODUCTS_LIST')==0)
		return;
		$product = new Product($params['product']['id_product']);
		
		
		$default_category= $product->getDefaultCategory();
	    $category_name = new Category ($product->getDefaultCategory(),(int)$this->context->language->id);
		
		$product_carriers= $product->getCarriers();
        $data_carrier = array();
	
        foreach ($product_carriers as $row) {
        $carrier_obj = new Carrier((int)$row['id_carrier'], $this->context->language->id);
	    $product_manufacturer= new Manufacturer($params['product']['id_manufacturer'], $this->context->language->id);
	
	  
	  
        $data_carrier[] = array(
		'id_carrier' => $carrier_obj->id_reference,
		'id_carrier_reference' => $row['id_carrier'],
		'manufacturer_name' => $product_manufacturer->name,
		'manufacturer_id' => $product_manufacturer->id,
		'name' => $carrier_obj->name,
        'delay' => $carrier_obj->delay,
		'img' => (file_exists(_PS_SHIP_IMG_DIR_.$row['id_carrier'].'.jpg')) ? true : false,
		'img_manu' => (file_exists(_PS_MANU_IMG_DIR_.$product_manufacturer->id.'.jpg')) ? true : false,
		'is_free' => $carrier_obj->is_free,
        );
		
		foreach($data_carrier as $key=>$value)
        {
         if(!$value['is_free'] && Configuration::get('PRODUCT_CARRIER_IS_FREE') == 1)
            unset($data_carrier[$key]);
        }
        }
		
		if (Configuration::get('PS_CATALOG_MODE') || Configuration::get('ACTIVE_IN_PRODUCTS_LIST')==0)
		return;
		
        $this->context->smarty->assign('category_name',$category_name->name);
        $this->smarty->assign(array(
			
				'data_carriers' => $data_carrier,
			    'default_category' => $default_category,
			    'terms' => Configuration::get('PRODUCTLIST_CONTENT', (int)$this->context->language->id),
				'products' => $params['product'],
				'manufacturer_name' => $product_manufacturer->name,
		'manufacturer_id' => $product_manufacturer->id,
		'img_manu' => (file_exists(_PS_MANU_IMG_DIR_.$product_manufacturer->id.'.jpg')) ? true : false,
				'CARRIER_IMG_WIDTH' => Configuration::get('PRODUCT_CARRIER_IMG_WIDTH'),
				'CARRIER_IMG_HEIGHT' => Configuration::get('PRODUCT_CARRIER_IMG_HEIGHT'),
				'CARRIER_IMG' => Configuration::get('PRODUCT_CARRIER_IMG'),
				'CARRIER_NAME' => Configuration::get('PRODUCT_CARRIER_NAME'),
				'CARRIER_DELAY' => Configuration::get('PRODUCT_CARRIER_DELAY'),
				
			));
			 }
       return $this->display(__FILE__, 'productcarriers-list.tpl', $this->getCacheId());
	   
    }
	
		public function hookDisplayProductButtons($params)
	{
		
		
	    if (!$this->isCached('productcarriers.tpl', $this->getCacheId()))
		{
		
		$product = new Product((int)Tools::getValue('id_product'), false, $this->context->language->id); 
		$products_selected = explode(',', Configuration::get('PS_PRODUCTS_PROD_CARRIER')); 
		
	    $isProducSelected = (in_array($product->id,$products_selected)) ? true: false;
       
     
		
		$product_carriers= $product->getCarriers();
		$data_carrier = array();
		
        foreach ($product_carriers as $row) {
        $carrier_obj = new Carrier((int)$row['id_carrier'], $this->context->language->id);
	  
        $data_carrier[] = array(
	
		'id_carrier' => $carrier_obj->id_reference,
		'id_carrier_reference' => $row[ 'id_carrier'],
		'name' => $carrier_obj->name,
        'delay' => $carrier_obj->delay,
		'img' => (file_exists(_PS_SHIP_IMG_DIR_.$row['id_carrier'].'.jpg')) ? true : false,
		'is_free' => $carrier_obj->is_free,
        );
	

		
        }
        $this->smarty->assign(array(
				'isProducSelected' =>$isProducSelected,
                'data_carriers' =>$data_carrier,
				'data_carriers_nb' => count($data_carrier),
			    'CARRIER_IMG_WIDTH' => Configuration::get('PRODUCT_CARRIER_IMG_WIDTH'),
				'CARRIER_IMG_HEIGHT' => Configuration::get('PRODUCT_CARRIER_IMG_HEIGHT'),
				'CARRIER_IMG' => Configuration::get('PRODUCT_CARRIER_IMG'),
				'CARRIER_NAME' => Configuration::get('PRODUCT_CARRIER_NAME'),
				'CARRIER_DELAY' => Configuration::get('PRODUCT_CARRIER_DELAY'),
				
			));
	 }
		return $this->display(__FILE__, 'productcarriers.tpl', $this->getCacheId());
	}
		public function hookHeader($params)
	{
	 if (isset($this->context->controller->php_self) && $this->context->controller->php_self === 'index' ||
                                                        $this->context->controller->php_self === 'category' ||
														$this->context->controller->php_self === 'product')
														
	$this->context->controller->addCSS($this->_path.'views/css/productcarriers.css');
	}


	
	public function renderForm()
	{
		$root_category = Category::getRootCategory();
		$root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);

		if (Tools::getValue('categoryBox'))
			$selected_categories = Tools::getValue('categoryBox');
		else
			$selected_categories = explode(',', Configuration::get('PS_CATEGORIES_PROD_CARRIER'));
		
		
		
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				
				       
					   
					'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Carrier name'),
						'name' => 'PRODUCT_CARRIER_NAME',
						'desc' => $this->l('Show the carrier name'),
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
					),
						array(
						'type' => 'switch',
						'label' => $this->l('Carrier delay'),
						'name' => 'PRODUCT_CARRIER_DELAY',
						'desc' => $this->l('Show the carrier delay'),
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
					),
						array(
						'type' => 'switch',
						'label' => $this->l('Carrier logo'),
						'name' => 'PRODUCT_CARRIER_IMG',
						'desc' => $this->l('Show the carrier logo.'),
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
					),
						array(
						'type' => 'text',
						'label' => $this->l('Logo height'),
						'name' => 'PRODUCT_CARRIER_IMG_HEIGHT',
						'suffix' => $this->l('px'),
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Define the carrier logo height')
					),
					
					    array(
						'type' => 'text',
						'label' => $this->l('Logo width'),
						'name' => 'PRODUCT_CARRIER_IMG_WIDTH',
						'suffix' => $this->l('px'),
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Define the carrier logo width')
					),
						array(
					'type' => 'switch',
					'label' => $this->l('Active'),
					'name' => 'ACTIVE_IN_PRODUCTS_LIST',
					'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
					'class' => 't',
					'is_bool' => true,
					'desc' => sprintf($this->l('Please, before activate this feature. Add this code in the product-list.tpl of your theme in use, set before ').'<br/><b>'.$this->l('%s '),'&lt;p class="product-desc" itemprop="description"&gt;').' </b><pre style="display: inline-block;">{hook h=\'displayProductCarrier\' product=$product}</pre>'

				),
					
								array(
						'type' => 'switch',
						'label' => $this->l('Carrier free only'),
						'name' => 'PRODUCT_CARRIER_IS_FREE',
						'desc' => $this->l('Show only the free carrier'),
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
					),
				array(
						'type' => 'swap',
						'label' => $this->l('Destination page for the block\'s link'),
						'desc' => $this->l('Show only in this products'),
						'name' => 'PS_PRODUCTS_PROD_CARRIER[]',
						'required' => false,
						'multiple' => true,
						'default_value' => $this->l('Show only in this products'),
						'options' => array(
							'query' => Product::getProducts((int)Configuration::get('PS_LANG_DEFAULT'), 0, 0, 'id_product', 'asc', false, true, $this->context),
							'id' => 'id_product',
							'name' => 'name'
						)
					),
						array(
						'type' => 'categories',
						'label' => $this->l('Vouchers created by the loyalty system can be used in the following categories:'),
						'name' => 'categoryBox',
						'desc' => $this->l('Mark the boxes of categories in which loyalty vouchers can be used.'),
						'tree' => array(
							'use_search' => true,
							'id' => 'categoryBox',
							'use_checkbox' => true,
							'selected_categories' => $selected_categories,
						),
						//retro compat 1.5 for category tree
						'values' => array(
							'trads' => array(
								'Root' => $root_category,
								'selected' => $this->l('Selected'),
								'Collapse All' => $this->l('Collapse All'),
								'Expand All' => $this->l('Expand All'),
								'Check All' => $this->l('Check All'),
								'Uncheck All' => $this->l('Uncheck All')
							),
							'selected_cat' => $selected_categories,
							'input_name' => 'categoryBox[]',
							'use_radio' => false,
							'use_search' => false,
							'disabled_categories' => array(),
							'top_category' => Category::getTopCategory(),
							'use_context' => true,
						)
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = $this->name;
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
		    'PS_PRODUCTS_PROD_CARRIER[]' => explode(',', Configuration::get('PS_PRODUCTS_PROD_CARRIER')),
		    'PRODUCT_CARRIER_IS_FREE' => Tools::getValue('PRODUCT_CARRIER_IS_FREE', Configuration::get('PRODUCT_CARRIER_IS_FREE')),
			'PRODUCT_CARRIER_NAME' => Tools::getValue('PRODUCT_CARRIER_NAME', Configuration::get('PRODUCT_CARRIER_NAME')),
			'ACTIVE_IN_PRODUCTS_LIST' => Tools::getValue('ACTIVE_IN_PRODUCTS_LIST', Configuration::get('ACTIVE_IN_PRODUCTS_LIST')),
			'PRODUCT_CARRIER_DELAY' => Tools::getValue('PRODUCT_CARRIER_DELAY', Configuration::get('PRODUCT_CARRIER_DELAY')),
			'PRODUCT_CARRIER_IMG' => Tools::getValue('PRODUCT_CARRIER_IMG', Configuration::get('PRODUCT_CARRIER_IMG')),
			'PRODUCT_CARRIER_IMG_WIDTH' => Tools::getValue('PRODUCT_CARRIER_IMG_WIDTH', Configuration::get('PRODUCT_CARRIER_IMG_WIDTH')),
			'PRODUCT_CARRIER_IMG_HEIGHT' => Tools::getValue('PRODUCT_CARRIER_IMG_HEIGHT', Configuration::get('PRODUCT_CARRIER_IMG_HEIGHT'))
		);
	}


}
