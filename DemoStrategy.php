<?php
$__callback = false;
if (strstr($_SERVER["PHP_SELF"], "DemoStrategy.php") == "DemoStrategy.php") {
	require '../../Opauth.php';
	require '../../OpauthStrategy.php';
	$__callback = true;
}

/**
 * Demo strategy for Opauth
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @link         http://opauth.org
 * @package      Opauth.DemoStrategy
 * @license      MIT License
 */

/**
 * Demo strategy for Opauth
 * 
 * @package			Opauth.Demo
 */
class DemoStrategy extends OpauthStrategy {
	
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('username', 'password', 'email');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}oauth2callback'
	);
	
	/**
	 * Auth request
	 */
	public function request() {
		$file = __FILE__;
		$root = realpath($_SERVER["DOCUMENT_ROOT"]);
		$cmproot = strtolower(str_replace("\\", "/", $root));
		$cmpfile = strtolower(str_replace("\\", "/", $file));
		if (strstr($cmpfile, $cmproot) == $cmpfile) {
			$url = substr($file, strlen($root));
		} else {
			die("Can`t use demo strategy! DemoStrategy.php must be placed under DocumentRoot (" . $cmproot . "<>" . $cmpfile . ")");
		}
		
		$params = array(
			'redirect_uri' => $this->strategy['redirect_uri']
		);

		if (isset($this->optionals))
		foreach ($this->optionals as $key) {
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}
		
		global $_SESSION;
		$_SESSION["conf"] = CONF_FILE;
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback() {
		if (array_key_exists('auth_token', $_GET) && !empty($_GET['auth_token'])) {
			if (!session_id()) session_start();
			if ($_GET["auth_token"] != session_id()) {
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Invalid token received',
					'raw' => $_GET
				);
				$this->errorCallback($error);
				return;
			}

			unset($_SESSION["try"]);
			unset($_SESSION["conf"]);

			$this->auth = array(
				'uid' => $this->strategy["username"],
				'info' => array(),
				'credentials' => array(
					'token' => $_GET['auth_token']
				),
				'raw' => $_GET
			);
			
			$this->mapProfile($this->strategy, 'username', 'info.name');
			$this->mapProfile($this->strategy, 'blog', 'info.urls.blog');
			$this->mapProfile($this->strategy, 'avatar_url', 'info.image');
			$this->mapProfile($this->strategy, 'bio', 'info.description');
			$this->mapProfile($this->strategy, 'username', 'info.nickname');
			$this->mapProfile($this->strategy, 'email', 'info.email');
			$this->mapProfile($this->strategy, 'location', 'info.location');
			$this->mapProfile($this->strategy, 'url', 'info.urls.homepage');
			$this->callback();
		} else {
			$error = array(
				'code' => 'oauth2callback_error',
				'message' => "No auth_token received",
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
}

if ($__callback) {
	if (!session_id()) session_start();
	if (!isset($_SESSION["conf"])) {
		var_dump($_SESSION);
		die("No conf specified in session, session_id = " . session_id() . "!");
	}

	$complete = false;
	$msg = "";
	$url = "?validate=true";
	$authToken = session_id();
	$authStatus = 0;

	if (isset($_REQUEST["validate"]) && $_REQUEST["validate"]) {
		require $_SESSION["conf"];
		if ($_SESSION["try"] >= 3) {
			$complete = true;
			$authStatus = 500;
			$authToken = "";
			$url = $_REQUEST["redirect_uri"];
		} else 
		if (isset($_REQUEST["username"]) && $_REQUEST["username"] == $config["Strategy"]["Demo"]["username"] &&
			isset($_REQUEST["password"]) && $_REQUEST["password"] == $config["Strategy"]["Demo"]["password"]) {
			$complete = true;
			$url = $_REQUEST["redirect_uri"];
		} else {
			$msg = "Invalid username or password!";
			$_SESSION["try"] ++;
		}
	} else {
		$_SESSION["try"] = 1;
	}
	?>
	<?php if (!$complete) { ?>
	<h2>Please login to demo network</h2>
	<b><?php echo $msg ?> (Retry <?php echo $_SESSION["try"] ?> of 3)</b><p>
	<?php } ?>
	<form action="<?php echo $url ?>" method="<?php echo $complete ? "GET" : "POST" ?>" name="authForm">
		<input type="hidden" name="redirect_uri" value="<?php echo $_REQUEST["redirect_uri"] ?>"/>
		<input type="hidden" name="auth_token" value="<?php echo $authToken ?>"/>
		<input type="hidden" name="auth_status" value="<?php echo $authStatus ?>"/>
		<?php if (!$complete) { ?>
		Username: <input name="username" value="<?php echo @$_REQUEST["username"] ?>"/>
		Password: <input name="password" type="password" value="<?php echo @$_REQUEST["password"] ?>"/>
		<input type="submit" value="Continue"/>
		<?php } ?>
	</form>
	<?php if ($complete) { ?>
	<script>
		document.authForm.submit();
	</script>
	<?php } else { ?>
	<script>
		document.authForm.username.focus();
	</script>
	<?php } ?>
	<?php
}