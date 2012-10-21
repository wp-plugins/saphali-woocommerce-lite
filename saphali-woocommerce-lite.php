<?php 
/*
Plugin Name: Saphali Woocommerce LITE
Plugin URI: http://saphali.com/saphali-woocommerce-plugin-wordpress
Description: Saphali Woocommerce LITE - это бесплатный вордпресс плагин, который добавляет набор дополнений к интернет-магазину на Woocommerce.
Version: 1.1
Author: Saphali
Author URI: http://saphali.com/
*/


/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software

 */


/* Add a custom payment class to woocommerce
  ------------------------------------------------------------ */
  // Подключение валюты и локализации
 define('SAPHALI_PLUGIN_DIR_URL',plugin_dir_url(__FILE__));
 define('SAPHALI_PLUGIN_DIR_PATH',plugin_dir_path(__FILE__));
function add_inr_currency( $currencies ) {
    $currencies['UAH'] = __( 'Ukrainian hryvnia ( grn.)', 'themewoocommerce' );
    $currencies['RUR'] = __( 'Russian ruble ( rub.)', 'themewoocommerce' );
    $currencies['BYR'] = __( 'Belarusian ruble ( Br.)', 'themewoocommerce' );
    return $currencies;
}

function add_inr_currency_symbol( $symbol ) {
	$currency = get_option( 'woocommerce_currency' );
	switch( $currency ) {
		case 'UAH': $symbol = 'грн.'; break;
		case 'RUB': $symbol = 'руб.'; break;
		case 'RUR': $symbol = 'руб.'; break;
		case 'BYR': $symbol = 'руб.'; break;
	}
	return $symbol;
}


//END

add_action('plugins_loaded', 'woocommerce_lang', 0);
function woocommerce_lang() {

	add_action('admin_menu', 'woocommerce_saphali_admin_menu', 9);
	load_plugin_textdomain( 'woocommerce',  false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	load_plugin_textdomain( 'themewoocommerce',  false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	if($_GET['page'] != 'woocommerce_saphali' && $_GET['tab'] !=1) {
		// Hook in
		add_filter( 'woocommerce_checkout_fields' , 'saphali_custom_override_checkout_fields' );
		add_filter( 'woocommerce_billing_fields',  'saphali_custom_billing_fields', 10, 1 );
		add_filter( 'woocommerce_shipping_fields',  'saphali_custom_shipping_fields', 10, 1 );
	}
	// Our hooked in function - $fields is passed via the filter!
	function saphali_custom_override_checkout_fields( $fields ) {
		
		$fieldss = get_option('woocommerce_saphali_filds_filters');
		if(is_array($fieldss)) {
			$fields["billing"] = $fieldss["billing"];
			$fields["shipping"] = $fieldss["shipping"];
			$fields["order"] = $fieldss["order"];
		}
		 return $fields;
	}
	function saphali_custom_billing_fields( $fields ) {
		
		$fieldss = get_option('woocommerce_saphali_filds_filters');
		if(is_array($fieldss))
 		$fields = $fieldss["billing"];
		 return $fields;
	}
	function saphali_custom_shipping_fields( $fields ) {
		
		$fieldss = get_option('woocommerce_saphali_filds_filters');
		if(is_array($fieldss))
		$fields = $fieldss["shipping"];
		 return $fields;
	}
	add_filter( 'woocommerce_currencies', 'add_inr_currency' );
	add_filter( 'woocommerce_currency_symbol', 'add_inr_currency_symbol' ); 
	add_action( 'admin_enqueue_scripts', 'admin_enqueue_scripts_page_saphali' );
	
	function admin_enqueue_scripts_page_saphali() {
	if($_GET['page'] == 'woocommerce_saphali' && $_GET['tab'] ==1 )
		wp_enqueue_script( 'tablednd', plugins_url('/js/jquery.tablednd.0.5.js', __FILE__) );
	}
	
	
	function woocommerce_saphali_page () {
	
	 
	?>
	<div class="wrap woocommerce"><div class="icon32 icon32-woocommerce-reports" id="icon-woocommerce"><br /></div>
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		Настройки Saphali WC
		</h2>
		<ul class="subsubsub">
			
			 <li><a href="admin.php?page=woocommerce_saphali" <? if($_GET["tab"] == '') echo 'class="current"';?>><span color="red">Переход на PRO версии</span></a> | </li>
			 <li><a href="admin.php?page=woocommerce_saphali&tab=1" <? if($_GET["tab"] == 1) echo 'class="current"';?>>Управление полями</a> | </li>
			 <li><a href="admin.php?page=woocommerce_saphali&tab=2" <? if($_GET["tab"] == 2) echo 'class="current"';?>>Число колонок в каталоге</a></li>
			
		</ul>
		<? if($_GET["tab"] == '') {?>
		<div class="clear"></div>
		<h2 class="woo-nav-tab-wrapper">Переход на PRO версии</h2>
		<? include_once (SAPHALI_PLUGIN_DIR_PATH . 'go_pro.php'); ?>

		<?php } elseif($_GET["tab"] == 2) {?>
		<div class="clear"></div>
		<h2 class="woo-nav-tab-wrapper">Число колонок в каталоге товаров и в рубриках</h2>
		<? include_once (SAPHALI_PLUGIN_DIR_PATH . 'count-column.php'); ?>

		<?php } elseif($_GET["tab"] == 1) { 
			global $woocommerce;
			$f = $woocommerce->checkout(); 
			if($_POST){
				if($_POST["reset"] != 'All') {
					if(is_array($_POST["billing"]["new_fild"])) {
						foreach($_POST["billing"]["new_fild"] as $k_nf => $v_nf) {
							if($k_nf == 'name')
							foreach($v_nf as $v_nf_f)
							$new_fild[] = $v_nf_f;
							 else {
								foreach($v_nf as $k_nf_f => $v_nf_f) {
									$addFild["billing"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
								}
							}
						}
						unset($_POST["billing"]["new_fild"]);
					}
					if(is_array($_POST["shipping"]["new_fild"])) {
						foreach($_POST["shipping"]["new_fild"] as $k_nf => $v_nf) {
							if($k_nf == 'name')
							foreach($v_nf as $v_nf_f)
							$new_fild[] = $v_nf_f;
							 else {
								foreach($v_nf as $k_nf_f => $v_nf_f) {
									$addFild["shipping"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
								}
							}
						}
						unset($_POST["shipping"]["new_fild"]);
					}
					if(is_array($_POST["order"]["new_fild"])) {
						foreach($_POST["order"]["new_fild"] as $k_nf => $v_nf) {
							if($k_nf == 'name')
							foreach($v_nf as $v_nf_f)
							$new_fild[] = $v_nf_f;
							 else {
								foreach($v_nf as $k_nf_f => $v_nf_f) {
									$addFild["order"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
								}
							}
						}
						unset($_POST["order"]["new_fild"]);
					}
					$filds = $f->checkout_fields;
					foreach($filds["billing"] as $key_post => $value_post) {
						$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post] = $value_post;
						foreach($value_post as $k_post=> $v_post){
							if($_POST["billing"][$key_post]['public'] != 'on') {
								$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]["public"] = false;
								$fild_remove_filter["billing"][] = $key_post;
							} else {$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]["public"] = true;}
							if($k_post == 'required') {$_POST["billing"][$key_post]['required'] = ($_POST["billing"][$key_post]['required'] == 'on') ? true : false ; }
							
							if( $_POST["billing"][$key_post][$k_post] != $v_post && isset($_POST["billing"][$key_post][$k_post]) ) {
								$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post][$k_post] = $_POST["billing"][$key_post][$k_post];
							}
							
						}
						unset($_POST["billing"][$key_post]);
					}
					foreach($filds["shipping"] as $key_post => $value_post) {
						$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post] = $value_post;
						
						if($_POST["shipping"][$key_post]['public'] != 'on') {
							$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]["public"] = false;
							$fild_remove_filter["shipping"][] = $key_post;
						} else {$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]["public"] = true;}
						
						foreach($value_post as $k_post=> $v_post){
							if($k_post == 'required') {$_POST["shipping"][$key_post]['required'] = ($_POST["shipping"][$key_post]['required'] == 'on') ? true : false ; }
							
							if( $_POST["shipping"][$key_post][$k_post] != $v_post && isset($_POST["shipping"][$key_post][$k_post]) ) {
								$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post][$k_post] = $_POST["shipping"][$key_post][$k_post];
							}
							
						}
						unset($_POST["shipping"][$key_post]);
					}
					
					foreach($filds["order"] as $key_post => $value_post) {
						$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post] = $value_post;
						if($_POST["order"][$key_post]['public'] != 'on') {
							$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post]["public"] = false;
							$fild_remove_filter["order"][] = $key_post;
						} else {$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post]["public"] = true;}
						foreach($value_post as $k_post=> $v_post){
							if($k_post == 'required') {$_POST["order"][$key_post]['required'] = ($_POST["order"][$key_post]['required'] == 'on') ? true : false ; }
							
							if( $_POST["order"][$key_post][$k_post] != $v_post && isset($_POST["order"][$key_post][$k_post]) ) {
								$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post][$k_post] = $_POST["order"][$key_post][$k_post];
							}
							
						}
						unset($_POST["order"][$key_post]);
					}

					if(!empty($_POST["billing"])) {
						foreach($_POST["billing"] as $k_post => $v_post) {
							if($v_post["public"]  != 'on' )
							$fild_remove_filter["billing"][] = $k_post;
						}
					}
					if(!empty($_POST["shipping"])) {
						foreach($_POST["shipping"] as $k_post => $v_post) {
							if($v_post["public"]  != 'on' )
							$fild_remove_filter["shipping"][] = $k_post;
						}
					}
					if(!empty($_POST["order"])) {
						foreach($_POST["order"] as $k_post => $v_post) {
							if($v_post["public"]  != 'on' )
							$fild_remove_filter["order"][] = $k_post;
						}
					}
					$filds_finish["billing"] = $filds_finish["shipping"] = $filds_finish["order"] = array();

					for($i = 0; $i<count($filds_new["billing"]); $i++) {
						if(isset($filds_new["billing"][$i]))
						$filds_finish["billing"] = $filds_finish["billing"] + $filds_new["billing"][$i];
					}
					for($i = 0; $i<count($filds_new["shipping"]); $i++) {
						if(isset($filds_new["shipping"][$i]))
						$filds_finish["shipping"] = $filds_finish["shipping"] + $filds_new["shipping"][$i];
					}
					for($i = 0; $i<count($filds_new["order"]); $i++) {
						if(isset($filds_new["order"][$i]))
						$filds_finish["order"] = $filds_finish["order"] + $filds_new["order"][$i];
					}
					
					if(is_array($addFild["billing"]))
					$filds_finish["billing"] = $filds_finish["billing"] + $addFild["billing"];
					if(is_array($addFild["shipping"]))
					$filds_finish["shipping"] = $filds_finish["shipping"] + $addFild["shipping"]+ $_POST["shipping"];
					if(is_array($addFild["order"]))
					$filds_finish["order"] = $filds_finish["order"] + $addFild["order"] + $_POST["order"];
					
					if(is_array($_POST["billing"]))
					$filds_finish["billing"] = $filds_finish["billing"] +  $_POST["billing"];
					if(is_array($_POST["shipping"]))
					$filds_finish["shipping"] = $filds_finish["shipping"] +  $_POST["shipping"];
					if(is_array($_POST["order"]))
					$filds_finish["order"] = $filds_finish["order"] + $_POST["order"];
					
					$filds_finish_filter = $filds_finish;
					if(is_array($fild_remove_filter["billing"])) {
						foreach($fild_remove_filter["billing"] as $v_filt){
							unset($filds_finish_filter["billing"][$v_filt]);
						}
					}
					if(is_array($fild_remove_filter["shipping"])) {
						foreach($fild_remove_filter["shipping"] as $v_filt){
							unset($filds_finish_filter["shipping"][$v_filt]);
						}
					}
					if(is_array($fild_remove_filter["order"])) {
						foreach($fild_remove_filter["order"] as $v_filt){
							unset($filds_finish_filter["order"][$v_filt]);
						}
					}
					if(!update_option('woocommerce_saphali_filds',$filds_finish))add_option('woocommerce_saphali_filds',$filds_finish);
					if(!update_option('woocommerce_saphali_filds_filters',$filds_finish_filter))add_option('woocommerce_saphali_filds_filters',$filds_finish_filter);
				} else {
						delete_option('woocommerce_saphali_filds');
						delete_option('woocommerce_saphali_filds_filters'); 
					}
			}
	
		?>
		<div class="clear"></div>
		<h3 class="nav-tab-wrapper woo-nav-tab-wrapper" style="text-align: center;">Управление полями на странице заказа и на странице профиля</h3>
		
		<h2 align="center">Реквизиты оплаты</h2>
		<form action="" method="post">
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
			<tr>
				<th width="115px">Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Обязательное</th>

				<th>Обубликовать</th>
			
				<th>Удалить/Добавить</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Обязательное</th>

				<th>Обубликовать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</tfoot>
		<tbody id="the-list" class="myTable">
			<? 

			$count = 0;

			$checkout_fields = get_option('woocommerce_saphali_filds');
			
			if(is_array($checkout_fields["billing"])) $f->checkout_fields["billing"] = $checkout_fields["billing"];
			foreach($f->checkout_fields["billing"] as $key => $value) {
				if(empty($value['public']) && !is_array($checkout_fields["billing"])) $value['public'] = true;
				?>
				<tr>
					<td><input  value='<?=$key?>' type="text" name="billing[<?=$key?>][name]" /></td>
					<td><input value='<?=$value['label']?>' type="text" name="billing[<?=$key?>][label]" /></td>
					<td><input value='<?=$value['placeholder']?>' type="text" name="billing[<?=$key?>][placeholder]" /></td>
					<td><input <? if($value['required']) echo 'checked'?> type="checkbox" name="billing[<?=$key?>][required]" /></td>

					<td><input <? if($value['public']) echo 'checked';?> type="checkbox" name="billing[<?=$key?>][public]" /></td>
					
					<td><input rel="sort_order" id="order_count" type="hidden" name="billing[<?=$key?>][order]" value="<?=$count?>" />
					<input type="button" class="button" id="billing_delete" value="Удалить -"/></td>
				</tr>
				<? $count++;
			}
			?>
			<tr  class="nodrop nodrag">
					<td></td>
					<td></td>
					<td></td>
					<td></td>

					<td></td>
					
					<td><input type="button" class="button"  id="billing" value="Добавить +"/></td>
			</tr>
		</tbody>
		</table>
			
		<h2 align="center">Реквизиты доставки</h2>
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
			<tr>
				<th width="115px">Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Обязательное</th>

				<th>Обубликовать</th>
			
				<th>Удалить/Добавить</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Обязательное</th>

				<th>Обубликовать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</tfoot>
		<tbody id="the-list" class="myTable">
			<? $count = 0;
			if(is_array($checkout_fields["shipping"])) $f->checkout_fields["shipping"] = $checkout_fields["shipping"];
			foreach($f->checkout_fields["shipping"] as $key => $value) {	
			if( empty($value['public']) && !is_array($checkout_fields["billing"]) ) $value['public'] = true;
				?>
				<tr>
					<td><input disabled value=<?=$key?> type="text" name="shipping[<?=$key?>][name]" /></td>
					<td><input value='<?=$value['label']?>' type="text" name="shipping[<?=$key?>][label]" /></td>
					<td><input value='<?=$value['placeholder']?>' type="text" name="shipping[<?=$key?>][placeholder]" /></td>
					<td><input <? if($value['required']) echo 'checked'?> type="checkbox" name="shipping[<?=$key?>][required]" /></td>

					<td><input <? if($value['public']) echo 'checked';?> type="checkbox" name="shipping[<?=$key?>][public]" /></td>
					
					<td><input rel="sort_order"  id="order_count" type="hidden" name="shipping[<?=$key?>][order]" value="<?=$count?>" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>
				</tr>
				<? $count++;
			}
			?>
			<tr  class="nodrop nodrag">
					<td></td>
					<td></td>
					<td></td>
	
					<td></td>
					<td></td>
				
					<td><input type="button" class="button" id="shipping" value="Добавить +"/></td>
			</tr>
		
		</tbody>
		</table>		
	<br />
	<h2 align="center">Дополнительные поля</h2>
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
			<tr>
				<th width="120px">Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Тип поля</th>
				<th>Обубликовать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Тип поля</th>
				<th>Обубликовать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</tfoot>
		<tbody id="the-list" class="myTable">
			<? $count = 0;
			if(is_array($checkout_fields["order"])) $f->checkout_fields["order"] = $checkout_fields["order"];
			foreach($f->checkout_fields["order"] as $key => $value) {	
				if(empty($value['public']) && !is_array($checkout_fields["billing"])) $value['public'] = true;
				?>
				<tr>
					<td><input disabled value=<?=$key?> type="text" name="order[<?=$key?>][name]" /></td>
					<td><input value='<?=$value['label']?>' type="text" name="order[<?=$key?>][label]" /></td>
					<td><input value='<?=$value['placeholder']?>' type="text" name="order[<?=$key?>][placeholder]" /></td>
					<td><input value='<?=$value['type']?>' type="text" name="order[<?=$key?>][type]" /></td>
					<td><input <? if($value['public']) echo 'checked';?> type="checkbox" name="order[<?=$key?>][public]" /></td>
					
					<td><input id="order_count" rel="sort_order" type="hidden" name="order[<?=$key?>][order]" value="<?=$count?>" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>
				</tr>
				<? $count++;
			}
			?>
			<tr  class="nodrop nodrag">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				
				<td><input type="button" class="button" id="order" value="Добавить +"/></td>
			</tr>
		</tbody>
		</table><br />
		<input type="submit" class="button alignleft" value="Сохранить"/>
		</form>
		<form action="" method="post">
			<input type="hidden" name="reset" value="All"/>
			<input type="submit" class="button alignright" value="Восстановить поля по умолчанию"/>
		</form>
		
		<script>
		jQuery('.button#billing').live('click',function() {
			var obj = jQuery(this).parent().parent();
			obj.html('<td><input value="new_fild'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" type="text" name="billing[new_fild][name][]" /></td><td><input value="" type="text" name="billing[new_fild][label][]" /></td><td><input value="" type="text" name="billing[new_fild][placeholder][]" /></td><td><input checked type="checkbox" name="billing[new_fild][required][]" /></td><td><input checked type="checkbox" name="billing[new_fild][public][]" /></td><td><input id="order_count" rel="sort_order" type="hidden" name="billing[new_fild][order][]" value="'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>');
			obj.removeClass('nodrop nodrag');
			obj.after('<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="billing" value="Добавить +"/></td></tr>');
		});
		jQuery('.button#shipping').live('click',function() {
			var obj = jQuery(this).parent().parent();
			obj.html('<td><input value="new_fild'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" type="text" name="shipping[new_fild][name][]" /></td><td><input value="" type="text" name="shipping[new_fild][label][]" /></td><td><input value="" type="text" name="shipping[new_fild][placeholder][]" /></td><td><input checked type="checkbox" name="shipping[new_fild][required][]" /></td><td><input checked type="checkbox" name="shipping[new_fild][public][]" /></td><td><input id="order_count" rel="sort_order" type="hidden" name="shipping[new_fild][order][]" value="'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>');
			obj.removeClass('nodrop nodrag');
			obj.after('<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="shipping" value="Добавить +"/></td></tr>');
		});
		jQuery('.button#order').live('click',function() {
			var obj = jQuery(this).parent().parent();
			obj.html('<td><input value="new_fild'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" type="text" name="order[new_fild][name][]" /></td><td><input value="" type="text" name="order[new_fild][label][]" /></td><td><input value="" type="text" name="order[new_fild][placeholder][]" /></td><td><input checked type="text" name="order[new_fild][type][]" /></td><td><input checked type="checkbox" name="order[new_fild][public][]" /></td><td><input id="order_count" rel="sort_order" type="hidden" name="order[new_fild][order][]" value="'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>');
			obj.removeClass('nodrop nodrag');
			obj.after('<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="order" value="Добавить +"/></td></tr>');
		});

		jQuery('.button#billing_delete').live('click',function() {
			var obj = jQuery(this).parent().parent();
			var obj_r = obj.parent();
			obj.remove();
			obj_r.find("tr").each(function(i, e){
				jQuery(e).find("td input#order_count").val(i);
			});
		});
		jQuery(document).ready(function() {
			jQuery(".myTable").tableDnD({
				onDragClass: "sorthelper",
				onDrop: function(table, row) {
					var data = new Object();
					data.data = new Object();
					data.key = jQuery(table).find("tr td input").attr("rel");
					jQuery(row).fadeOut("fast").fadeIn("slow");   
				
					jQuery(table).find("tr").each(function(i, e){
						var id = jQuery(e).find("td input#order_count").attr("id");
						data.data[i] = id;
						jQuery(e).find("td input#order_count").val(i);
					});
				}
			});
		});
		</script>
		<? } ?>
		
	</div>
	<?
	}
}
function woocommerce_saphali_admin_menu() {
	add_submenu_page('woocommerce',  __('Настройки Saphali WC Lite', 'woocommerce'), __('Saphali WC Lite', 'woocommerce') , 'manage_woocommerce', 'woocommerce_saphali', 'woocommerce_saphali_page');
}
$column_count_saphali = get_option('column_count_saphali');
if(!empty($column_count_saphali)) {
	global $woocommerce_loop;
	$woocommerce_loop['columns'] = $column_count_saphali; 
	add_action("wp_head", 'print_script_columns', 10, 1);
	function print_script_columns($woocommerce_loop) {
		global $woocommerce_loop;
		if($woocommerce_loop['columns'] > 0) {
		?>
		<style type='text/css'>
		ul.products li.product {
			width:<? if($woocommerce_loop['columns'] <= 3 ) echo floor(100/$woocommerce_loop['columns'] - $woocommerce_loop['columns']); elseif($woocommerce_loop['columns'] > 3 )echo floor(100/$woocommerce_loop['columns'] - 4);?>%;
		}
		</style>
		<?
		}
	}
}
?>