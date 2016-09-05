<?php
/*
Plugin Name: WP Firewall
Description: Controle de segurança do WordPress
Version: 1.0
Author: Tableless
Author URI: http://tableless.com.br
*/

if (!class_exists('WP_Firewall')) { //caso a classe já não exista
 class WP_Firewall{ // declara o plugin WP_Firewall
   function WP_Firewall (){ //inicialização da classe: Declara uma ação apara quando tiver uma falha de login
     add_action('init', array($this, 'init'));
     add_action('admin_init', array( $this , 'register_fields' ) );

     add_action('wp_login', array($this, 'log'));
     add_action('wp_login_failed', array($this, 'log_failed'));
   }
 
   public function init( ) {
   	$_LIMIT = get_option( 'firewall_login_limit', 10);
   	$_COUNT = get_transient( 'log_failed_'.$_SERVER['REMOTE_ADDR'] );

   	//faz o bloqueio
   	if($_COUNT >= $_LIMIT):
   		echo "Ops!!! voce excedeu o limite de tentativas :(";
   		exit;
   	endif;
   }

   public function log( ) { //Oba logou!
   	//exclui o transient
   	delete_transient( 'log_failed_'.$_SERVER['REMOTE_ADDR'] );
   }

   public function log_failed( $username ) { //vish. não logou
   	 //recebe o número atual de tentativas do ip
   	 $_COUNT = get_transient( 'log_failed_'.$_SERVER['REMOTE_ADDR'] );

     //Ops.. Login falhou 🙂 o que fazer agora? 	 
   	 set_transient('log_failed_'.$_SERVER['REMOTE_ADDR'], $_COUNT + 1, 12 * HOUR_IN_SECONDS );

     //avisa por e-mail da tentativa de login
     @mail(get_option('admin_email'), 'Login falhou :'.$username, json_encode($_SERVER)); 
   }

	public function register_fields() {
		//registra o campo nas configurações gerais
		register_setting( 'general', 'firewall_login_limit', 'esc_attr' );
		add_settings_field(
			'firewall_login_limit',
			'<label for="extra_blog_desc_id">Limite de tentativas no login</label>',
			array( $this, 'fields_html' ),
			'general'
		);
	} 
	public function fields_html() {
		$value = get_option( 'firewall_login_limit', 10);
		//imprime o campo nas configurações gerais
		echo '<input type="number" id="firewall_login_limit" name="firewall_login_limit" value="' . esc_attr( $value ) . '" />';
	}	  
 }
 
 $WP_Firewall = new WP_Firewall();
}