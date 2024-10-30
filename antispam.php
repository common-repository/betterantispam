<?php
/*
Plugin Name: BetterAntiSpamBot
Plugin URI: http://wordpress.org/extend/plugins/betterantispam/
Description: Trick the spambots. Real dynamic JavaScript encryption of your email addresses.
Author: noneevr2
Version: 1.0.1
Author URI: http://noneeevr2.com
*/

global $betterantispambot;

class betterantispambot
{ 
	public $betterantispambot_token = "";
	public $betterantispambot_mails = array(); // "key" => "mail_encrypted"
	public $betterantispambot_vars = array(	"token" => "token",
											"addresses" => "addresses",
											"decrypted" => "decrypted",
											"current" => "current",
											"decrypt" => "decrypt",
											"param" => "param0",
											"iterator" => "i",
											"token0" => "token0"
										   );
	public $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	public $lettersplus = "@-_.";
	public $security_level = 3;  // 0 - 3   0: real vars 1: obfuscated vars 2: obfuscated strangechars 3: compressed
	public $obfuscation_length = 7; // +- 2
	public $obfuscate_compatible = 0;
	public $strange_chars_compatible = "IìíîïÌÍÎÏlĨĩĪīĬĭĮİıĳĺļľŀłƖƚſƫǀǁǏǐȈȉȊȋɨɩɫɬɺΙΐΪίιϊϮϯЇІіїӀḬḭḮḯḷḻḽỈỉỊịἰἱἲἳἴἵἶἷὶίῐῑῒΐῖῗῘῙῚΊἸἹἺἻἼἽἾἿҐ
";
	public $strange_chars_incompatible = "ȷȴͰӏᴉᵻᵼᶅᶖỻ";
	public $strange_chars_integers = "0123456789";
	
	function __construct($security_level = 3, $obfuscation_length = 10, $obfuscation_compatible = false){
		$this->security_level = $security_level;
		$this->obfuscation_length = $obfuscation_length;
		$this->obfuscate_compatible = $obfuscation_compatible;
		
		$this->betterantispambot_token = str_shuffle($this->letters.$this->lettersplus);
		
		if ($this->security_level > 0){
			$obfuscation_text = $this->security_level > 1?($this->strange_chars_compatible.($this->strange_chars_compatible?null:$this->strange_chars_incompatible)):($this->letters.$this->strange_chars_integers);
			
			foreach ($this->betterantispambot_vars as &$encoded){
				$len = rand($this->obfuscation_length-2, $this->obfuscation_length + 2);
				while (in_array($_encoded, $this->betterantispambot_vars) || $_encoded == null) 
					$_encoded = ($this->security_level == 1?substr($this->letters, $len, 1):null).(mb_substr($obfuscation_text, rand(0, mb_strlen($obfuscation_text, "UTF-8")-$len-1), $len, "UTF-8"));
				$encoded = $_encoded;
			}
		}
	}
	function mb_str_shuffle($string){
		$len = mb_strlen($string, "UTF-8");
		$sploded = array(); 
		while($len-- > 0) { $sploded[] = mb_substr($string, $len, 1, "UTF-8"); }
		shuffle($sploded);
		return join('', $sploded);
	}
	function js_hexencode($str){ return "\\x" . substr(chunk_split(bin2hex($str),2,"\\x"),0,-2);	}

	
	public function getCode(){
		$addrstr = array();
		foreach($this->betterantispambot_mails as $alias => $mail){
			if ($this->security_level > 0) $addrstr[] = '"'.$this->js_hexencode($alias).'":"'.$this->js_hexencode($mail).'"';
			else $addrstr[] = '"'.$alias.'":"'.$mail.'"';
		}
		$addrstr = implode(",", $addrstr);
		$token_split = array();
		if ($this->security_level > 1){
			$curlen = 0;
			$maxlen = strlen($this->betterantispambot_token);
			$obfuscation_text = $this->security_level > 1?($this->strange_chars_compatible.($this->strange_chars_compatible?null:$this->strange_chars_incompatible)):($this->letters.$this->strange_chars_integers);
			while ($curlen < $maxlen){
				
				$len = rand($this->obfuscation_length-2, $this->obfuscation_length + 2);
				while (in_array($_encoded, $this->betterantispambot_vars) || in_array($_encoded, array_keys($token_split)) || $_encoded==null) {
					$_encoded = ($this->security_level == 1?substr($this->letters, $len, 1):null).(mb_substr($obfuscation_text, rand(0, mb_strlen($obfuscation_text, "UTF-8")-$len-1), $len, "UTF-8"));
				}
				$seglen = rand(10,30);
				$token_split[$_encoded] = $this->js_hexencode(substr($this->betterantispambot_token, $curlen, $seglen));
				$curlen += $seglen;
			}
			$token_concat = implode("+", array_keys($token_split));
		}
		$js_variables_dynamic = array($this->betterantispambot_vars["addresses"].' = {'.$addrstr.'};',
									$this->betterantispambot_vars["decrypted"].'="";');
		foreach ($token_split as $name=>$ts){
			$js_variables_dynamic[] = $name.' = "'.$ts.'";';
		}
		shuffle($js_variables_dynamic);
		$js_variables_dynamic[] = $this->betterantispambot_vars["token"].' = '.
										($this->security_level>1?
											$token_concat:
											'"'.
											($this->security_level>0?
												$this->js_hexencode($this->betterantispambot_token):
												$this->betterantispambot_token
											)
											.'"'
										).';';
		$js_variables_dynamic[] = $this->betterantispambot_vars["current"].'='.$this->betterantispambot_vars["addresses"].'['.$this->betterantispambot_vars["param"].'];';
		$function = '
<script type="text/javascript" language="javascript">
	function '.$this->betterantispambot_vars["decrypt"].'('.$this->betterantispambot_vars["param"].'){
  '.
  implode("\r\n", $js_variables_dynamic)
  .'
  for ('.$this->betterantispambot_vars["iterator"].'=0; '.$this->betterantispambot_vars["iterator"].'<'.$this->betterantispambot_vars["current"].'.length; '.$this->betterantispambot_vars["iterator"].'++) {
    if ('.$this->betterantispambot_vars["token"].'.indexOf('.$this->betterantispambot_vars["current"].'.charAt('.$this->betterantispambot_vars["iterator"].'))==-1) {
     	'.$this->betterantispambot_vars["decrypted"].' += ('.$this->betterantispambot_vars["current"].'.charAt('.$this->betterantispambot_vars["iterator"].'));
    }
    else {     
      '.$this->betterantispambot_vars["decrypted"].' += ('.$this->betterantispambot_vars["token"].'.charAt(('.$this->betterantispambot_vars["token"].'.indexOf('.$this->betterantispambot_vars["current"].'.charAt('.$this->betterantispambot_vars["iterator"].'))-'.$this->betterantispambot_vars["current"].'.length+'.$this->betterantispambot_vars["token"].'.length) % '.$this->betterantispambot_vars["token"].'.length));
    }
  }
document.location.href="mailto:"+'.$this->betterantispambot_vars["decrypted"].';
  }

</script>';
		if ($this->security_level>2) $function = $this->compress($function);
		return $function;
	}
	function compress($buffer) {
        /* remove comments */
        $buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
        /* remove other spaces before/after ) */
        $buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);
        return $buffer;
    }
	function crypto($text){
		$encr = "";

		for($i=0; $i < strlen($text); $i++){
			if (strpos($this->betterantispambot_token, $text[$i]) === false){
				$encr .= $text[$i];
			}else{
				$encr .= $this->betterantispambot_token[(strpos($this->betterantispambot_token, $text[$i])+strlen($text))%strlen($this->betterantispambot_token)];
			}
		}
		return $encr;
	}
	public function setmail($mail, $alias = ""){
		$cmail = $this->crypto($mail);
		if (strlen($alias) == 0 and in_array($cmail, $this->betterantispambot_mails)){
			//no alias specified but mail already registered
			$keys = array_keys($this->betterantispambot_mails, $cmail);
			$alias = $keys[0];
		}elseif (strlen($alias) == 0 or ($this->betterantispambot_mails[$alias] != null and $this->betterantispambot_mails[$alias] != $cmail)){
			//no alias specified or other mail already registered using this alias
			$alias = count($this->betterantispambot_mails);
		}
		$this->betterantispambot_mails[$alias] = $this->crypto($mail);
		return "javascript:".$this->betterantispambot_vars["decrypt"]."('".$alias."');";
	}
}





class wp_framework {
	public function __construct(){
		//Register the global variable
		global $betterantispambot;
		
		//Function to run when the plugin is activated...
		register_activation_hook( __FILE__ , array($this, 'activate'));
		
		//Function to run when the plugin is deactivated...
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		
		//Register the options when initialising the admin area...
		add_action('admin_init', array($this, 'settings_init'));
		
		//Add the menu item to the admin area...
		add_action('admin_menu', array($this, 'menu'));
		
		//Get our options
		$settings = get_option('bspam_settings');
		
		//Create the AntiSpamBot if not already created
		if ($betterantispambot == null) $betterantispambot = new betterantispambot($settings['security_level'], $settings['obfuscation_length'], $settings['obfuscation_compatible']);
	
		//Add the shortcode to WordPress so it can be used on a page...
		add_shortcode('bspam', array($this, 'shortcode'));
		
		// Add the finished JS with the footer
		add_action('wp_footer', array($this, 'printDecryptor'));
		
		// Add the shortcode button to TinyMCE
		add_action('init', array($this, 'add_bspam_button'));
		// Bypass TinyMCE caching
		add_filter( 'tiny_mce_version', array($this, 'my_refresh_mce'));
    }
	
	function activate() {
		
		//If the settings have not been set...
		if (get_option('bspam_settings') == false) {
			
			//Set the default settings...
			$bspam_defaults = array(
				'security_level' => 3,
				'obfuscation_length' => 10,
				'obfuscation_compatible' => true
			);
			
			//Save the defaults...
			update_option('bspam_settings', $bspam_defaults);
			
		}
		
	}
	
	
	//Function to run when the plugin is deactivated...
	function deactivate() {
		
		//Remove all the saved options...
		delete_option('bspam_settings');
		
	}
	
	
	//Function to validate and sanitise the settings...
	function validate($settings) {
		
		//Get the current settings from the database (use them if validation or sanitisation fails)...
		$settings_old = get_option('bspam_settings');
		$error = false;

		
		//Validate and sanitise the Security Level setting...
		if ($settings['security_level'] !== preg_replace("/[^0-9]/", '', $settings['security_level']) || $settings['security_level'] == '' || $settings['security_level'] < 0 || $settings['security_level'] > 3) {
			add_settings_error('bspam_settings', 'bspam_security_level_error', 'Please choose a valid security level.', 'error');
			$settings['security_level'] = $settings_old['security_level'];
			$error = true;
		}
		
		//Validate and sanitise the Obfuscation Length setting...
		if ($settings['obfuscation_length'] !== preg_replace("/[^0-9]/", '', $settings['obfuscation_length']) || $settings['obfuscation_length'] == '' || $settings['obfuscation_length'] < 4 || $settings['obfuscation_length'] > 50) {
			add_settings_error('bspam_settings', 'bspam_obfuscation_length_error', 'Obfuscation Length must be a number between 4 and 50.', 'error');
			$settings['obfuscation_length'] = $settings_old['obfuscation_length'];
			$error = true;
		}
		
		//Validate and sanitise the Obfuscate Compatible setting...
		if ($settings['obfuscation_compatible'] !== '1') {
			$settings['obfuscation_compatible'] = 0;
			$error = true;
		}
		
		if (!$error) add_settings_error('bspam_settings', 'bspam_settings_saved', 'Successfully updated settings', 'updated');
		
		//Return the validated and sanitised fields...
		return $settings;
		
	}
	
	//Function to register all the available options...
	function settings_init() {
		
		//Register the plugin settings with the WordPress Settings API...
		register_setting('bspam_settings', 'bspam_settings', array($this, 'validate'));
		
		//Add the Getting Started section...
		add_settings_section('bspam_section_start', 'Getting Started', array($this, 'section_start_callback'), 'betterantispambot');
		
		//Add the General Settings section...
		add_settings_section('bspam_section_general', 'General Settings', '', 'betterantispambot');
		
		//Add the General Settings fields...
		add_settings_field('security_level', 'Security Level', array($this, 'field_security_level_callback'), 'betterantispambot', 'bspam_section_general');
		add_settings_field('obfuscation_length', 'Variable length', array($this, 'field_obfuscation_length_callback'), 'betterantispambot', 'bspam_section_general');
		add_settings_field('obfuscation_compatible', 'Compatible with legacy JS engines', array($this, 'field_obfuscate_compatible_callback'), 'betterantispambot', 'bspam_section_general');
		
	}
	
	
	//Function to output the Getting Started section...
	function section_start_callback() {
		
		//Print an explanation...
		print '<p>This plugin encrypts your email addresses using dynamic javascript<br />
				Remember: No encryption will ever remain uncrackable</p>';
		print '<p>There are several ways to embed an encrypred email address:</p>';
		print '<ol>';
		print '<li>Posts: Shortcode <code>[bspam mail="your@mail.tld"]Display Text[/bspam]</code></li>';
		print '<li>Posts: Shortcode extension <code>[bspam mail="your@mail.tld" alias="youralias"]Display Text[/bspam]</code></li>';
		print '<li>PHP: <code>global $betterantispambot; echo \'&lt;a href="\'.$betterantispambot->setmail("your@mail.tld").\'&gt;Display Text&lt;/a&gt;\';</code></li>';
		print '<li>PHP: <code>global $betterantispambot; echo \'&lt;a href="\'.$betterantispambot->setmail("your@mail.tld", "youralias").\'&gt;Display Text&lt;/a&gt;\';</code></li>';
		print '</ol>';
		print '<p>All above codes will output a clickable link "Display Text":</p>';
		print '<ol>';
		print '<li><code>&lt;a href="javascript:decrypt(0)"&gt;Display Text&lt;/a&gt;</code></li>';
		print '<li><code>&lt;a href="javascript:decrypt(\'youralias\')"&gt;Display Text&lt;/a&gt;</code></li>';
		print '<li><code>&lt;a href="javascript:decrypt(0)"&gt;Display Text&lt;/a&gt;</code></li>';
		print '<li><code>&lt;a href="javascript:decrypt(\'youralias\')"&gt;Display Text&lt;/a&gt;</code></li>';
		print '</ol>';
		
	}
	
	
	//Function to output the Security Level form field...
	function field_security_level_callback($args) {
		$settings = get_option('bspam_settings');
		print '<input name="bspam_settings[security_level]" type="radio" value="3" ' . checked($settings['security_level'], 3, false) . ' class="small-text" /> Best<br />';
		print '<input name="bspam_settings[security_level]" type="radio" value="2" ' . checked($settings['security_level'], 2, false) . ' class="small-text" /> Good<br />';
		print '<input name="bspam_settings[security_level]" type="radio" value="1" ' . checked($settings['security_level'], 1, false) . ' class="small-text" /> Minimal<br />';
		print '<input name="bspam_settings[security_level]" type="radio" value="0" ' . checked($settings['security_level'], 0, false) . ' class="small-text" /> Realnames (Debug)';
	}
	
	//Function to output the Obfuscation Length form field...
	function field_obfuscation_length_callback($args) {
		$settings = get_option('bspam_settings');
		print '<input name="bspam_settings[obfuscation_length]" type="text" value="' . $settings['obfuscation_length'] . '" class="small-text" /> Minimum: 4 Maximum: 50';
	}
	
	//Function to output the Obfuscate Compatible form field...
	function field_obfuscate_compatible_callback($args) {
		$settings = get_option('bspam_settings');
		print '<input name="bspam_settings[obfuscation_compatible]" type="checkbox" value="1" ' . checked($settings['obfuscation_compatible'], 1, 0) . ' />';
	}
	
	//Function to add a menu item for the settings page...
	function menu() {
		add_options_page('BetterAntiSpamBot Settings', 'BSPAM', 'manage_options', 'bspam_settings', array($this, 'settings'));
		
		if (current_user_can('manage_options')){
			//Display Security Level Warning
			$settings = get_option('bspam_settings');
			
			//Warn if Security Level is set to 0
			if ($settings['security_level'] == 0){
				add_action('admin_notices', array($this, 'security_level_low_notice'));
			}
		}
	}
	
	
	//Function to generate a settings page...
	function settings() {
		
		//Remove any user who should not be able to access this page...
		if (!current_user_can('manage_options')) { wp_die(__('You do not have sufficient permissions to access this page.')); }
		
		//Start generating the settings page...
		print '<div class="wrap">';
		
		//Add an icon and page heading...
		print '<div id="icon-options-general" class="icon32"><br /></div><h2>BetterAntiSpamBot Settings</h2>';
		
		//Output any validation or sanitisation errors...
		//settings_errors();
		
		//Create a form...
		print '<form method="post" action="options.php">';
		
		//Output the Settings API fields...
		settings_fields('bspam_settings');
		
		//Output the sections and fields...
		do_settings_sections('betterantispambot');
		
		//Output the Save Changes button...
		submit_button();
		
		//Close the form and finalise the html...
		print '</form></div>';
		
	}
	
	
	function shortcode($atts, $content = ""){
		global $betterantispambot;
		 $arg = shortcode_atts( array(
			  'mail' => '',
			  'alias' => ''
		 ), $atts );
		if (strlen($content)!==false and strlen($arg['mail'])!==false){
			//$betterantispambot = new betterantispambot();
			return '<a href="'.$betterantispambot->setmail($arg['mail'], $arg['alias']).'">'.$content.'</a>';
		}
	}
	
	function printDecryptor(){
		global $betterantispambot;
		//$betterantispambot = new betterantispambot();
		if (count($betterantispambot->betterantispambot_mails)){
			echo $betterantispambot->getCode();
		}
		
	}
	
	function security_level_low_notice() {
    ?>
    <div class="updated">
        <p><?php _e( 'BSPAM: Use this Security Level for DEBUG ONLY', 'basp' ); ?></p>
    </div>
    <?php
	}
	
	//////////////////////////////////////////////////
	//			TinyMCE Shortcode Button			//
	//////////////////////////////////////////////////
	
	function add_bspam_button(){
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', array($this, 'add_bspam_tinymce_plugin'), 3);
			add_filter('mce_buttons', array($this, 'register_bspam_button'), 3);
		}
	}
	
	function register_bspam_button($buttons) {
	   array_push($buttons, "|", "bspam");
	   return $buttons;
	}
	
	function add_bspam_tinymce_plugin($plugin_array) {
	   $plugin_array['bspam'] = plugin_dir_url(__FILE__).'js/editor_plugin.js';
	   return $plugin_array;
	}
	function my_refresh_mce($ver) {
	  $ver += 3;
	  return $ver;
	}
	

}

new wp_framework();


?>