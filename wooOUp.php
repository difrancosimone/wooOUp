<?php
   /*
   Plugin Name: wooOUp
   Plugin URI: http://#
   Description: Integration between Woocommerce and Edisoftware to get realtime product quantities from Onda - https://www.edisoftware.it - Require a Sql Stored Procedure to work with middleware (ask me for info) using Laravel Apis as middleware backend
   Version: 1.0
   Author: Simone Di Franco
   Author URI:
   License: GPL2
   */

require __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class wooOUp {
    /*
    * Function to init plugin options
    * Server Address and Port of Backend Api
    */
    static function install(){
      $wooOup_options = ['uri' => 'http://127.0.0.1/api/v1/', 'endpoint' => 'product/'];
      add_option('wooOUp_option', $wooOup_options);
    }
    /*
    * Function to unistall plugin options
    */
    static function uninstall(){
      delete_option( 'wooOUp_option' );
    }
    /*
    * Functions to add menu page in Wordpress admin
    * Edit the plugin options
    */
    static function register_wooOUp(){
    	add_menu_page( 'wooOUp', 'wooOUp', 'manage_options', 'wooup', array('wooOUp','wooOUp_menu'));
    }

    static function wooOUp_menu(){
      $options_wooOUp = get_option('wooOUp_option');
      //update options menu ?>
    	<div class="wrap">
    		<h2>Options</h2>
    		<form method="post">
    		<?php settings_fields( 'wooOUp_settings' ); ?>
    		<?php do_settings_sections( 'wooOUp-settings' ); ?>
    		<table class="form-table">
    			<tr valign="top">
    			<th scope="row">Api URI</th>
    			<td><input type="text" name="uri_api" style="width:100%;display:block"; value="<?php echo esc_html($options_wooOUp['uri']); ?>" /></td>
        </tr><tr>
          <th scope="row">Api Endpoint</th>
    			<td><input type="text" name="endpoint_api" style="width:100%;display:block"; value="<?php echo esc_html($options_wooOUp['endpoint']); ?>" /></td>
        </tr>
    		</table>
    		<input type="submit" Value="Save">
    		</form>
    	</div>
    <?php
    	//save worpdress plugin options
  		if ($_POST['uri_api'] && $_POST['endpoint_api']) {
        $options_wooOUp = get_option('wooOUp_option');
        $newoption = ['uri' => $_POST['uri_api'], 'endpoint' => $_POST['endpoint_api']];
        update_option( 'wooOUp_option', $newoption );
      }
    }
    /*
    * Getting quantity from laravel api of product
    */
    private function getApiProductQuantity($prodcod) {
      $options_wooOUp = get_option('wooOUp_option');
      // Create a client with a base URI GET REQEUEST
      //$client = new GuzzleHttp\Client(['base_uri' => 'http://warehouse.leghorngroup.com:5911/api/maggiacHQ.php?codart=']);
      $client = new GuzzleHttp\Client(['base_uri' => esc_html($options_wooOUp['uri'])]);
      $response = $client->get(esc_html($options_wooOUp['endpoint']).$prodcod);
      $body = $response->getBody();
      // Implicitly cast the body to a string and echo it
      $jsonRes = (string) $body;
      $test = json_decode($jsonRes);
      echo "<br>Quantity: ".$test->giac."<br>";
      return $test->giac;
      //return rand(-25, 65);  //enable for test purpose
    }
    /*
    * Function to check availability via Api
    * The goal is show only available product quering directly the Api
    */
    static function wooOUp_product_availability() {
      if (is_product()) {
        global $product, $variationsarray;
        $variationsarray = array();
	      if (is_product() and $product->product_type == 'variable') {
          $handle = new WC_Product_Variable($product);
          $variations1 = $handle->get_children();
          foreach ($variations1 as $value) {
            $single_variation = new WC_Product_Variation($value);
            $tmp = $single_variation->get_variation_attributes();
            $test = array($single_variation->get_sku() => $tmp["attribute_colore"]);
            array_push($variationsarray, $test);
            /*
            * Getting quantities from laravel api and set new stock for product
            */
            $stock_quantity = wooOUp::getApiProductQuantity($single_variation->get_sku()); //call to function test()
            wc_update_product_stock( $single_variation, $stock_quantity );
          }
        } else {
          /*
          * Getting quantities from laravel api and set new stock for product
          */
          $stock_quantity = wooOUp::getApiProductQuantity($product->get_sku()); //call to function test()
          global $product;
          wc_update_product_stock( $product, $stock_quantity );
        }
      }
    }

}
/*
* Setup Hooks
*/
register_activation_hook( __FILE__, array( 'wooOUp', 'install' ));
/*
* Hook to add menù page in admin
*/
add_action( 'admin_menu', array( 'wooOUp','register_wooOUp'));
/*
* Product page function - activate if is on woocommerce product single page
*/
add_action( 'woocommerce_single_product_summary', array( 'wooOUp','wooOUp_product_availability'), 20 );
register_deactivation_hook( __FILE__, array( 'wooOUp', 'uninstall' ) );

?>
