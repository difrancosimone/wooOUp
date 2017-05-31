<?php
   /*
   Plugin Name: Nonsoloanima Remove User
   Plugin URI: http://nonsoloanima.tv
   Description: Plugin per la rimozione utenti dal portale
   Version: 1.0
   Author: Simone
   Author URI: 
   License: GPL2
   */
   
//aggiungo la pagina nell'admin  
add_action( 'admin_menu', 'register_nsa_del_usr_page' );

function register_nsa_del_usr_page(){
	//add_menu_page( 'Nonsoloanima Rimozione Utenti', 'Nonsoloanima Rimozione Utenti', 'manage_options', 'custompage', 'nsa_del_usr_menu_page', plugins_url( 'myplugin/images/icon.png' ), 6 ); 
	
	add_menu_page( 'Nonsoloanima Rimozione Utenti', 'Nonsoloanima Rimozione Utenti', 'manage_options', 'nsadelusr', 'nsa_del_usr_menu_page'); 
}

function nsa_del_usr_menu_page(){
//creo la struttura della pagina opzioni ?>
	<div class="wrap">
		<h2>Nonsoloanima Rimozione Utenti</h2>
		<form method="post">
		<?php settings_fields( 'nsa_del_usr_settings' ); ?>
		<?php do_settings_sections( 'nsa-settings' ); ?>
		<table class="form-table">
			<tr valign="top">
			<th scope="row">Id Utente Da Rimuovere</th>
			<td><input type="text" name="id_usr" /></td>
			</tr>
		</table>
		<input type="submit" Value="Rimuovi">
		</form>
	</div>
	

<?php
	//recupero l'id ed eseguo le query di rimozione
	if ($_POST['id_usr']) {
		if (is_int($_POST['id_usr'])) {
			global $wpdb;
			$prepared1 = $wpdb->prepare( "DELETE FROM $wpdb->users WHERE `ID` = %d", $_POST['id_usr'] );
			echo $prepared1."<br>";
			$prepared2 = $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE `user_id` = %d", $_POST['id_usr'] );
			echo $prepared2."<br>";
			$result1 = $wpdb->query($prepared1);
			$result2 = $wpdb->query($prepared2);
			// controllo lo stato delle query
			if ($result1 > 0 AND $result2 > 0) {
				echo '<h3 align="center">Utente Rimosso<h3>';
			} else {
				echo "C'é stato un errore!";
			}
		} else { echo "<h3>Errore! - Il valore inserito non é un numero</h3>";}
	}

}
?>
