<?php
   /*
   Plugin Name: wooOUp
   Plugin URI: http://#
   Description: Integration between woocommerce and Edisoftware OndaUp software using Laravel Apis to get realtime product quantities
   Version: 1.0
   Author: Simone Di Franco
   Author URI:
   License: GPL2
   */


class wooOUp {
    static function install(){
      // array of options
      $wooOup_options = ['ip' => '127.0.0.1', 'port' => '80'];
      // add a new option
      add_option('wooOUp_option', $wooOup_options);
    }

    static function uninstall(){
      delete_option( 'wooOUp_option' );
    }

    static function register_wooOUp(){
    	add_menu_page( 'wooOUp', 'wooOUp', 'manage_options', 'wooup', array('wooOUp','wooOUp_menu'));
    }

    static function wooOUp_menu(){
      $options_wooOUp = get_option('wooOUp_option');
      //creo la struttura della pagina opzioni ?>
    	<div class="wrap">
    		<h2>Options</h2>
    		<form method="post">
    		<?php settings_fields( 'wooOUp_settings' ); ?>
    		<?php do_settings_sections( 'wooOUp-settings' ); ?>
    		<table class="form-table">
    			<tr valign="top">
    			<th scope="row">Api Server Address</th>
    			<td><input type="text" name="ip_api" value="<?php echo esc_html($options_wooOUp['ip']); ?>" /></td>
          <th scope="row">Api Server Port</th>
    			<td><input type="text" name="port_api" value="<?php echo esc_html($options_wooOUp['port']); ?>" /></td>
    			</tr>
    		</table>
    		<input type="submit" Value="Save">
    		</form>
    	</div>

    <?php
    	//save worpdress plugin options
  		if ($_POST['ip_api'] && $_POST['port_api']) {
        $options_wooOUp = get_option('wooOUp_option');
        $newoption = ['ip' => $_POST['ip_api'], 'port' => $_POST['port_api']];
        update_option( 'wooOUp_option', $options_wooOUp, $newoption );
      }
    }

    static function wooOUp_product_availability() {
      if (is_product()) {
        global $product;
        $variationsarray = array();
	      if (is_product() and $product->product_type == 'variable') {
          $handle=new WC_Product_Variable($product);
          $variations1=$handle->get_children();
          foreach ($variations1 as $value) {
            $single_variation=new WC_Product_Variation($value);
            echo $single_variation->get_sku();
            array_push($variationsarray, $single_variation->get_sku());
            print_r($variationsarray);
            echo '<option  value="'.$value.'">'.implode(" / ", $single_variation->get_variation_attributes()).'-'.get_woocommerce_currency_symbol().$single_variation->price.'</option>';
          }
        }
        ?>
          <script>
            console.log("PLUGIN OK SKU-> <?php echo $product->get_sku(); ?>");
            console.log("PLUGIN OK SKU-> <?php echo $variationsarray[1]; ?>");
          </script>
        <?php
      }
    }

}

//actiovation function
register_activation_hook( __FILE__, array( 'wooOUp', 'install' ));
//add settings page in wordpress admin menù
add_action( 'admin_menu', array( 'wooOUp','register_wooOUp'));
// product page function - activate if is on woocommerce product single page
add_action( 'woocommerce_before_single_product_summary', array( 'wooOUp','wooOUp_product_availability'), 20 );
register_deactivation_hook( __FILE__, array( 'wooOUp', 'uninstall' ) );


?>
