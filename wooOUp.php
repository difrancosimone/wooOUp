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

//add settings page in wordpress admin menù
add_action( 'admin_menu', 'register_wooOUp' );

function register_wooOUp(){
	add_menu_page( 'wooOUp', 'wooOUp', 'manage_options', 'wooOUp', 'wooOUp_page');
}

function wooOUp_page(){
//creo la struttura della pagina opzioni ?>
	<div class="wrap">
		<h2>Options</h2>
		<form method="post">
		<?php settings_fields( 'wooOUp_settings' ); ?>
		<?php do_settings_sections( 'wooOUp-settings' ); ?>
		<table class="form-table">
			<tr valign="top">
			<th scope="row">Api Server Address</th>
			<td><input type="text" name="ip_api" /></td>
			</tr>
		</table>
		<input type="submit" Value="Salva">
		</form>
	</div>


<?php
	//recupero l'id ed eseguo le query di rimozione
	if ($_POST['ip_api']) {
		if (is_int($_POST['ip_api'])) {
			global $wpdb;
			$prepared1 = $wpdb->prepare( "DELETE FROM $wpdb->users WHERE `ID` = %d", $_POST['ip_api'] );
			echo $prepared1."<br>";
			$prepared2 = $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE `user_id` = %d", $_POST['ip_api'] );
			echo $prepared2."<br>";
			$result1 = $wpdb->query($prepared1);
			$result2 = $wpdb->query($prepared2);
			// controllo lo stato delle query
			if ($result1 > 0 AND $result2 > 0) {
				echo '<h3 align="center">Impostazioni Salvate<h3>';
			} else {
				echo "C'é stato un errore!";
			}
		} else { echo "<h3>Errore! - Il server Api non risponde</h3>";}
	}

}
?>
