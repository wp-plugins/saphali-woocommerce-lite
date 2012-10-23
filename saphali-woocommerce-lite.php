<?php 
/*
Plugin Name: Saphali Woocommerce LITE
Plugin URI: http://saphali.com/saphali-woocommerce-plugin-wordpress
Description: Saphali Woocommerce LITE - это бесплатный вордпресс плагин, который добавляет набор дополнений к интернет-магазину на Woocommerce.
Version: 1.2.1
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
					// Управление новыми полями
					if(is_array($_POST["billing"]["new_fild"])) {
						foreach($_POST["billing"]["new_fild"] as $k_nf => $v_nf) {
							if($k_nf == 'name')
							foreach($v_nf as $v_nf_f)
							$new_fild[] = $v_nf_f;
							 else {
								foreach($v_nf as $k_nf_f => $v_nf_f) {
									if($k_nf == 'class') {
										$v_nf_f = array ( $v_nf_f );
										$addFild["billing"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
									} else $addFild["billing"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
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
									if($k_nf == 'class') {
										$v_nf_f = array ( $v_nf_f );
										$addFild["shipping"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
									} else $addFild["shipping"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
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
									if($k_nf == 'class') {
										$v_nf_f = array ( $v_nf_f );
										$addFild["order"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
									} else $addFild["order"][$new_fild[$k_nf_f]][$k_nf] = $v_nf_f;
								}
							}
						}
						unset($_POST["order"]["new_fild"]);
					}
					//END 
					$filds = $f->checkout_fields;
					foreach($filds["billing"] as $key_post => $value_post) {
						$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post] = $value_post;
							if($_POST["billing"][$key_post]['public'] != 'on') {
								$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]["public"] = false;
								$fild_remove_filter["billing"][] = $key_post;
							} else {$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]["public"] = true;}
							
							$_POST["billing"][$key_post]['required'] = ($_POST["billing"][$key_post]['required'] == 'on') ? true : false ; 
							
							$_POST["billing"][$key_post]['clear'] = $bool_clear = ($_POST["billing"][$key_post]['clear'] == 'on') ? true : false ;
							
						foreach($value_post as $k_post=> $v_post){
							if( $_POST["billing"][$key_post][$k_post] != $v_post && isset($_POST["billing"][$key_post][$k_post]) ) {
								$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post][$k_post] = $_POST["billing"][$key_post][$k_post];
							}
						}
						if( $bool_clear ){
								$filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]['clear'] = $bool_clear;
						} elseif(isset($filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]['clear'])) {
							unset($filds_new["billing"][$_POST["billing"][$key_post]["order"]][$key_post]['clear']);
						}
						unset($_POST["billing"][$key_post]);
					}
					foreach($filds["shipping"] as $key_post => $value_post) {
						$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post] = $value_post;
						
						if($_POST["shipping"][$key_post]['public'] != 'on') {
							$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]["public"] = false;
							$fild_remove_filter["shipping"][] = $key_post;
						} else {$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]["public"] = true;}
						
						$_POST["shipping"][$key_post]['clear'] = $bool_clear = ($_POST["shipping"][$key_post]['clear'] == 'on') ? true : false ;
						
						$_POST["shipping"][$key_post]['required'] = ($_POST["shipping"][$key_post]['required'] == 'on') ? true : false ;
						
						foreach($value_post as $k_post=> $v_post){
							if( $_POST["shipping"][$key_post][$k_post] != $v_post && isset($_POST["shipping"][$key_post][$k_post]) ) {
								$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post][$k_post] = $_POST["shipping"][$key_post][$k_post];
							}
						}
						if( $bool_clear ){
								$filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]['clear'] = $bool_clear;
						} elseif(isset($filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]['clear'])) {
							unset($filds_new["shipping"][$_POST["shipping"][$key_post]["order"]][$key_post]['clear']);
						}
						unset($_POST["shipping"][$key_post]);
					}
					
					foreach($filds["order"] as $key_post => $value_post) {
						$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post] = $value_post;
						if($_POST["order"][$key_post]['public'] != 'on') {
							$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post]["public"] = false;
							$fild_remove_filter["order"][] = $key_post;
						} else {$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post]["public"] = true;}
						

						$_POST["order"][$key_post]['required'] = ($_POST["order"][$key_post]['required'] == 'on') ? true : false ; 
						
						
						foreach($value_post as $k_post=> $v_post){
							if( $_POST["order"][$key_post][$k_post] != $v_post && isset($_POST["order"][$key_post][$k_post]) ) {
								$filds_new["order"][$_POST["order"][$key_post]["order"]][$key_post][$k_post] = $_POST["order"][$key_post][$k_post];
							}
						}
						unset($_POST["order"][$key_post]);
					}
					// Управление публикацией
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
					//END Управление публикацией
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
					
					if(is_array($_POST["billing"]))
					$filds_finish["billing"] = $filds_finish["billing"] +  $_POST["billing"];
					if(is_array($_POST["shipping"]))
					$filds_finish["shipping"] = $filds_finish["shipping"] +  $_POST["shipping"];
					if(is_array($_POST["order"]))
					$filds_finish["order"] = $filds_finish["order"] + $_POST["order"];
					
					if(is_array($addFild["billing"]))
					$filds_finish["billing"] = $filds_finish["billing"] + $addFild["billing"];
					if(is_array($addFild["shipping"]))
					$filds_finish["shipping"] = $filds_finish["shipping"] + $addFild["shipping"]+ $_POST["shipping"];
					if(is_array($addFild["order"]))
					$filds_finish["order"] = $filds_finish["order"] + $addFild["order"] + $_POST["order"];
					
					
					
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
				<th width="56px">Clear<img class="help_tip" data-tip="Указывает на то, что следующее поле за текущим, будет начинаться с новой строки." src="<? bloginfo('wpurl');?>/wp-content/plugins/woocommerce/assets/images/help.png" /> </th>
				<th>Класс поля</th>
				<th  width="65px">Обя&shy;за&shy;те&shy;ль&shy;ное</th>

				<th  width="65px">Опуб&shy;ли&shy;ко&shy;вать</th>
			
				<th>Удалить/Добавить</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th width="56px">Clear<img class="help_tip" data-tip="Указывает на то, что следующее поле за текущим, будет начинаться с новой строки." src="<? bloginfo('wpurl');?>/wp-content/plugins/woocommerce/assets/images/help.png" /> </th>
				<th>Класс поля</th>
				<th  width="65px">Обя&shy;за&shy;те&shy;ль&shy;ное</th>

				<th  width="65px">Опуб&shy;ли&shy;ко&shy;вать</th>
				
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
					<td><input disabled value='<?=$key?>' type="text" name="billing[<?=$key?>][name]" /></td>
					<td><input value='<?=$value['label']?>' type="text" name="billing[<?=$key?>][label]" /></td>
					<td><input value='<?=$value['placeholder']?>' type="text" name="billing[<?=$key?>][placeholder]" /></td>
					<td><input <? if($value['clear']) echo 'checked'?>  class="<?=$value['clear']?>" type="checkbox" name="billing[<?=$key?>][clear]" /></td>
					<td><?  if(is_array($value['class'])) { foreach($value['class'] as $v_class) { ?>
					<input value='<?=$v_class;?>' type="text" name="billing[<?=$key?>][class][]" /> <? } } else { ?>
					<input value='' type="text" name="billing[<?=$key?>][class][]" /> <?
					} ?></td>
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
				<th width="56px">Clear<img class="help_tip" data-tip="Указывает на то, что следующее поле за текущим, будет начинаться с новой строки." src="<? bloginfo('wpurl');?>/wp-content/plugins/woocommerce/assets/images/help.png" /> </th>
				<th>Класс поля</th>
				<th  width="65px">Обя&shy;за&shy;те&shy;ль&shy;ное</th>

				<th  width="65px">Опуб&shy;ли&shy;ко&shy;вать</th>
			
				<th>Удалить/Добавить</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th width="56px">Clear<img class="help_tip" data-tip="Указывает на то, что следующее поле за текущим, будет начинаться с новой строки." src="<? bloginfo('wpurl');?>/wp-content/plugins/woocommerce/assets/images/help.png" /> </th>
				<th>Класс поля</th>
				<th  width="65px">Обя&shy;за&shy;те&shy;ль&shy;ное</th>

				<th  width="65px">Опуб&shy;ли&shy;ко&shy;вать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</tfoot>
		<tbody id="the-list" class="myTable">
			<? $count = 0;
			if(is_array($checkout_fields["shipping"])) $f->checkout_fields["shipping"] = $checkout_fields["shipping"];
			foreach($f->checkout_fields["shipping"] as $key => $value) {	
			if( empty($value['public']) && !is_array($checkout_fields["shipping"]) ) $value['public'] = true;
				?>
				<tr>
					<td><input disabled value=<?=$key?> type="text" name="shipping[<?=$key?>][name]" /></td>
					<td><input value='<?=$value['label']?>' type="text" name="shipping[<?=$key?>][label]" /></td>
					<td><input value='<?=$value['placeholder']?>' type="text" name="shipping[<?=$key?>][placeholder]" /></td>
					<td><input <? if($value['clear']) echo 'checked'?> class="<?=$value['clear']?>" type="checkbox" name="shipping[<?=$key?>][clear]" /></td>
					<td><?  if(is_array($value['class'])) { foreach($value['class'] as $v_class) { ?>
					
					<input value='<?=$v_class;?>' type="text" name="shipping[<?=$key?>][class][]" /> <? } } else { ?>
					<input value='' type="text" name="shipping[<?=$key?>][class][]" /> <?
					} ?></td>
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
				<th>Класс поля</th>
				<th>Тип поля</th>
				<th  width="65px">Опуб&shy;ли&shy;ко&shy;вать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Название</th>
				<th>Заголовок</th>
				<th>Текст в поле</th>
				<th>Класс поля</th>
				<th>Тип поля</th>
				<th  width="65px">Опуб&shy;ли&shy;ко&shy;вать</th>
				
				<th>Удалить/Добавить</th>
			</tr>
		</tfoot>
		<tbody id="the-list" class="myTable">
			<? $count = 0;
			if(is_array($checkout_fields["order"])) $f->checkout_fields["order"] = $checkout_fields["order"];
			foreach($f->checkout_fields["order"] as $key => $value) {	
				if(empty($value['public']) && !is_array($checkout_fields["order"])) $value['public'] = true;
				?>
				<tr>
					<td><input disabled value=<?=$key?> type="text" name="order[<?=$key?>][name]" /></td>
					<td><input value='<?=$value['label']?>' type="text" name="order[<?=$key?>][label]" /></td>
					<td><input value='<?=$value['placeholder']?>' type="text" name="order[<?=$key?>][placeholder]" /></td>
					
					<td><?  if(is_array($value['class'])) { foreach($value['class'] as $v_class) { ?>
					
					<input value='<?=$v_class;?>' type="text" name="order[<?=$key?>][class][]" /> <? } } else { ?>
					<input value='' type="text" name="order[<?=$key?>][class][]" /> <?
					} ?></td>
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
		<style type="text/css">
		#tiptip_content{font-size:11px;color:#fff;padding:4px 8px;background:#a2678c;border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;box-shadow:1px 1px 3px rgba(0,0,0,0.1);-webkit-box-shadow:1px 1px 3px rgba(0,0,0,0.1);-moz-box-shadow:1px 1px 3px rgba(0,0,0,0.1);text-align:center}#tiptip_content code{background:#855c76;padding:1px}#tiptip_arrow,#tiptip_arrow_inner{position:absolute;border-color:transparent;border-style:solid;border-width:6px;height:0;width:0}#tiptip_holder.tip_top #tiptip_arrow_inner{margin-top:-7px;margin-left:-6px;border-top-color:#a2678c}#tiptip_holder.tip_bottom #tiptip_arrow_inner{margin-top:-5px;margin-left:-6px;border-bottom-color:#a2678c}#tiptip_holder.tip_right #tiptip_arrow_inner{margin-top:-6px;margin-left:-5px;border-right-color:#a2678c}#tiptip_holder.tip_left #tiptip_arrow_inner{margin-top:-6px;margin-left:-7px;border-left-color:#a2678c}img.help_tip{vertical-align:middle;margin:0 0 0 3px}#tiptip_holder{display:none;position:absolute;top:0;left:0;z-index:99999}#tiptip_holder.tip_top{padding-bottom:5px}#tiptip_holder.tip_bottom{padding-top:5px}#tiptip_holder.tip_right{padding-left:5px}#tiptip_holder.tip_left{padding-right:5px}#tiptip_content{font-size:11px;color:#fff;padding:4px 8px;background:#a2678c;border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;box-shadow:1px 1px 3px rgba(0,0,0,0.1);-webkit-box-shadow:1px 1px 3px rgba(0,0,0,0.1);-moz-box-shadow:1px 1px 3px rgba(0,0,0,0.1);text-align:center}#tiptip_content code{background:#855c76;padding:1px}#tiptip_arrow,#tiptip_arrow_inner{position:absolute;border-color:transparent;border-style:solid;border-width:6px;height:0;width:0}#tiptip_holder.tip_top #tiptip_arrow_inner{margin-top:-7px;margin-left:-6px;border-top-color:#a2678c}#tiptip_holder.tip_bottom #tiptip_arrow_inner{margin-top:-5px;margin-left:-6px;border-bottom-color:#a2678c}#tiptip_holder.tip_right #tiptip_arrow_inner{margin-top:-6px;margin-left:-5px;border-right-color:#a2678c}#tiptip_holder.tip_left #tiptip_arrow_inner{margin-top:-6px;margin-left:-7px;border-left-color:#a2678c}
		</style>
		<script type="text/javascript">
		(function($){$.fn.tipTip=function(options){var defaults={activation:"hover",keepAlive:false,maxWidth:"200px",edgeOffset:3,defaultPosition:"bottom",delay:400,fadeIn:200,fadeOut:200,attribute:"title",content:false,enter:function(){},exit:function(){}};var opts=$.extend(defaults,options);if($("#tiptip_holder").length<=0){var tiptip_holder=$('<div id="tiptip_holder" style="max-width:'+opts.maxWidth+';"></div>');var tiptip_content=$('<div id="tiptip_content"></div>');var tiptip_arrow=$('<div id="tiptip_arrow"></div>');$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>')))}else{var tiptip_holder=$("#tiptip_holder");var tiptip_content=$("#tiptip_content");var tiptip_arrow=$("#tiptip_arrow")}return this.each(function(){var org_elem=$(this);if(opts.content){var org_title=opts.content}else{var org_title=org_elem.attr(opts.attribute)}if(org_title!=""){if(!opts.content){org_elem.removeAttr(opts.attribute)}var timeout=false;if(opts.activation=="hover"){org_elem.hover(function(){active_tiptip()},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}else if(opts.activation=="focus"){org_elem.focus(function(){active_tiptip()}).blur(function(){deactive_tiptip()})}else if(opts.activation=="click"){org_elem.click(function(){active_tiptip();return false}).hover(function(){},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}function active_tiptip(){opts.enter.call(this);tiptip_content.html(org_title);tiptip_holder.hide().removeAttr("class").css("margin","0");tiptip_arrow.removeAttr("style");var top=parseInt(org_elem.offset()['top']);var left=parseInt(org_elem.offset()['left']);var org_width=parseInt(org_elem.outerWidth());var org_height=parseInt(org_elem.outerHeight());var tip_w=tiptip_holder.outerWidth();var tip_h=tiptip_holder.outerHeight();var w_compare=Math.round((org_width-tip_w)/2);var h_compare=Math.round((org_height-tip_h)/2);var marg_left=Math.round(left+w_compare);var marg_top=Math.round(top+org_height+opts.edgeOffset);var t_class="";var arrow_top="";var arrow_left=Math.round(tip_w-12)/2;if(opts.defaultPosition=="bottom"){t_class="_bottom"}else if(opts.defaultPosition=="top"){t_class="_top"}else if(opts.defaultPosition=="left"){t_class="_left"}else if(opts.defaultPosition=="right"){t_class="_right"}var right_compare=(w_compare+left)<parseInt($(window).scrollLeft());var left_compare=(tip_w+left)>parseInt($(window).width());if((right_compare&&w_compare<0)||(t_class=="_right"&&!left_compare)||(t_class=="_left"&&left<(tip_w+opts.edgeOffset+5))){t_class="_right";arrow_top=Math.round(tip_h-13)/2;arrow_left=-12;marg_left=Math.round(left+org_width+opts.edgeOffset);marg_top=Math.round(top+h_compare)}else if((left_compare&&w_compare<0)||(t_class=="_left"&&!right_compare)){t_class="_left";arrow_top=Math.round(tip_h-13)/2;arrow_left=Math.round(tip_w);marg_left=Math.round(left-(tip_w+opts.edgeOffset+5));marg_top=Math.round(top+h_compare)}var top_compare=(top+org_height+opts.edgeOffset+tip_h+8)>parseInt($(window).height()+$(window).scrollTop());var bottom_compare=((top+org_height)-(opts.edgeOffset+tip_h+8))<0;if(top_compare||(t_class=="_bottom"&&top_compare)||(t_class=="_top"&&!bottom_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_top"}else{t_class=t_class+"_top"}arrow_top=tip_h;marg_top=Math.round(top-(tip_h+5+opts.edgeOffset))}else if(bottom_compare|(t_class=="_top"&&bottom_compare)||(t_class=="_bottom"&&!top_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_bottom"}else{t_class=t_class+"_bottom"}arrow_top=-12;marg_top=Math.round(top+org_height+opts.edgeOffset)}if(t_class=="_right_top"||t_class=="_left_top"){marg_top=marg_top+5}else if(t_class=="_right_bottom"||t_class=="_left_bottom"){marg_top=marg_top-5}if(t_class=="_left_top"||t_class=="_left_bottom"){marg_left=marg_left+5}tiptip_arrow.css({"margin-left":arrow_left+"px","margin-top":arrow_top+"px"});tiptip_holder.css({"margin-left":marg_left+"px","margin-top":marg_top+"px"}).attr("class","tip"+t_class);if(timeout){clearTimeout(timeout)}timeout=setTimeout(function(){tiptip_holder.stop(true,true).fadeIn(opts.fadeIn)},opts.delay)}function deactive_tiptip(){opts.exit.call(this);if(timeout){clearTimeout(timeout)}tiptip_holder.fadeOut(opts.fadeOut)}}})}})(jQuery);
		jQuery(".tips, .help_tip").tipTip({
			'attribute' : 'data-tip',
			'fadeIn' : 50,
			'fadeOut' : 50,
			'delay' : 200
		});
		jQuery('.button#billing').live('click',function() {
			var obj = jQuery(this).parent().parent();
			obj.html('<td><input value="new_fild'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" type="text" name="billing[new_fild][name][]" /></td><td><input value="" type="text" name="billing[new_fild][label][]" /></td><td><input value="" type="text" name="billing[new_fild][placeholder][]" /></td><td><input type="checkbox" name="billing[new_fild][clear][]" /></td><td><input value="" type="text" name="billing[new_fild][class][]" /></td><td><input checked type="checkbox" name="billing[new_fild][required][]" /></td><td><input checked type="checkbox" name="billing[new_fild][public][]" /></td><td><input id="order_count" rel="sort_order" type="hidden" name="billing[new_fild][order][]" value="'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>');
			obj.removeClass('nodrop nodrag');
			obj.after('<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="billing" value="Добавить +"/></td></tr>');
		});
		jQuery('.button#shipping').live('click',function() {
			var obj = jQuery(this).parent().parent();
			obj.html('<td><input value="new_fild'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" type="text" name="shipping[new_fild][name][]" /></td><td><input value="" type="text" name="shipping[new_fild][label][]" /></td><td><input value="" type="text" name="shipping[new_fild][placeholder][]" /></td><td><input type="checkbox" name="shipping[new_fild][clear][]" /></td><td><input value="" type="text" name="shipping[new_fild][class][]" /></td><td><input checked type="checkbox" name="shipping[new_fild][required][]" /></td><td><input checked type="checkbox" name="shipping[new_fild][public][]" /></td><td><input id="order_count" rel="sort_order" type="hidden" name="shipping[new_fild][order][]" value="'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>');
			obj.removeClass('nodrop nodrag');
			obj.after('<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="shipping" value="Добавить +"/></td></tr>');
		});
		jQuery('.button#order').live('click',function() {
			var obj = jQuery(this).parent().parent();
			obj.html('<td><input value="new_fild'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" type="text" name="order[new_fild][name][]" /></td><td><input value="" type="text" name="order[new_fild][label][]" /></td><td><input value="" type="text" name="order[new_fild][placeholder][]" /></td><td><input value="" type="text" name="order[new_fild][class][]" /></td><td><input checked type="text" name="order[new_fild][type][]" /></td><td><input checked type="checkbox" name="order[new_fild][public][]" /></td><td><input id="order_count" rel="sort_order" type="hidden" name="order[new_fild][order][]" value="'+(parseInt(obj.parent().find('tr td input#order_count:last').val(),10)+1)+'" /><input type="button" class="button" id="billing_delete" value="Удалить -"/></td>');
			obj.removeClass('nodrop nodrag');
			obj.after('<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="order" value="Добавить +"/></td></tr>');
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