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
      $wooOup_options = ['address' => '127.0.0.1', 'port' => '80'];
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
      //creo la struttura della pagina opzioni ?>
    	<div class="wrap">
    		<h2>Options</h2>
    		<form method="post">
    		<?php settings_fields( 'wooOUp_settings' ); ?>
    		<?php do_settings_sections( 'wooOUp-settings' ); ?>
    		<table class="form-table">
    			<tr valign="top">
    			<th scope="row">Api Server Address</th>
    			<td><input type="text" name="address_api" value="<?php echo esc_html($options_wooOUp['address']); ?>" /></td>
          <th scope="row">Api Server Port</th>
    			<td><input type="text" name="port_api" value="<?php echo esc_html($options_wooOUp['port']); ?>" /></td>
    			</tr>
    		</table>
    		<input type="submit" Value="Save">
    		</form>
    	</div>
    <?php
    	//save worpdress plugin options
  		if ($_POST['address_api'] && $_POST['port_api']) {
        $options_wooOUp = get_option('wooOUp_option');
        $newoption = ['address' => $_POST['address_api'], 'port' => $_POST['port_api']];
        update_option( 'wooOUp_option', $options_wooOUp, $newoption );
      }
    }
    /*
    * Function that alter the dropdown vaiation menù by product availability
    */
    static function wooOUp_variation_option( $term ) {
        global $wpdb, $product, $variationsarray;
        var_dump($variationsarray); //test
        //var_dump($term);
        $result = $wpdb->get_col( "SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$term'" );
        $term_slug = ( !empty( $result ) ) ? $result[0] : $term;
        //build query to get vars
        $query = "SELECT postmeta.post_id AS product_id
                    FROM {$wpdb->prefix}postmeta AS postmeta
                        LEFT JOIN {$wpdb->prefix}posts AS products ON ( products.ID = postmeta.post_id )
                    WHERE postmeta.meta_key LIKE 'attribute_%'
                        AND postmeta.meta_value = '$term_slug'
                        AND products.post_parent = $product->id";
        $variation_id = $wpdb->get_col( $query );
        $parent = wp_get_post_parent_id( $variation_id[0] );
        if ( $parent > 0 ) {
    	    $_product = new WC_Product_Variation( $variation_id[0] );
      		if ($term) {
      			// calculating itemized price
      			$totsomma = $_product->get_price();
      			return $term . ' --- ' .$totsomma;
      		}
        }
        return $term;
    }
    /*
    * Getting quantity from laravel api of product
    */
    private function getApiProductQuantity($sku) {
      /*global $variationsarray;
      foreach ($variationsarray as $keym => $valuem) {
        foreach ($valuem as $key => $value) {
          echo $key." - ".$value."<br>";
          // qui per ogni key che é la sku devo controllare via api e sostituire con la quantità
          $finalqt = array($value => "100");
          array_push($variationquantityes, $finalqt);
        }
      }*/
      return "25";
      // Create a client with a base URI GET REQEUEST
      /*$client = new GuzzleHttp\Client(['base_uri' => 'http://samples.openweathermap.org/data/2.5/']);
      $response = $client->get('weather?q=London,uk&appid=b1b15e88fa797225412429c1c50c122a1');
      //$response = $client->request('GET', 'weather?q=London,uk&appid=b1b15e88fa797225412429c1c50c122a1');
      //echo "ResponseCode: ".$response->getStatusCode()."<br>";
      $body = $response->getBody();
      // Implicitly cast the body to a string and echo it
      $jsonRes = (string) $body;
      $test = json_decode($jsonRes);
      echo "<br>Longitude: ".$test->coord->lon."<br>";*/
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
            //echo $single_variation->get_sku();
            echo $single_variation->get_id();
            $tmp = $single_variation->get_variation_attributes();
            $test = array($single_variation->get_sku() => $tmp["attribute_colore"]);
            array_push($variationsarray, $test);
            /*
            * Getting quantities from laravel api and set new stock for product
            */
            $stock_quantity = wooOUp::getApiProductQuantity($single_variation->get_sku()); //call to function test()
            wc_update_product_stock( $single_variation, $stock_quantity );
            ?>
              <script>
                console.log("PLUGIN OK SKU-> <?php echo $single_variation->get_sku()." - ".implode(" / ", $single_variation->get_variation_attributes()); ?>");
              </script>
            <?php
          }
          /*
          * Product page function - hook the dropdown variations menu to show only avalable products
          */
          //add_filter( 'woocommerce_variation_option_name', array( 'wooOUp','wooOUp_variation_option') );
        } else {
          ?>
            <script>
              console.log("PLUGIN OK SKU-> <?php echo $product->get_sku(); ?>");
              //console.log("PLUGIN OK SKU-> <?php echo $variationsarray[1]; ?>");
            </script>
          <?php
          /*
          * Getting quantities from laravel api and set new stock for product
          */
          $stock_quantity = wooOUp::getApiProductQuantity($sku); //call to function test()
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
