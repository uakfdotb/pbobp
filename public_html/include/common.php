<?php
/*

	pbobp
	Copyright [2013] [Favyen Bastani]

	This file is part of the pbobp source code.

	pbobp is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	pbobp source code is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with pbobp source code. If not, see <http://www.gnu.org/licenses/>.

*/

if(!isset($GLOBALS['IN_PBOBP'])) {
	die('Access denied.');
}

function string_begins_with($string, $search)
{
	return (strncmp($string, $search, strlen($search)) == 0);
}

function boolToString($bool) {
	return $bool ? 'true' : 'false';
}

//returns an absolute path to the include directory, with trailing slash
function includePath() {
	$self = __FILE__;
	$lastSlash = strrpos($self, "/");
	return substr($self, 0, $lastSlash + 1);
}

//returns a relative path to the web root directory, without trailing slash
function basePath() {
	$commonPath = __FILE__;
	$requestPath = $_SERVER['SCRIPT_FILENAME'];

	//count the number of slashes
	// number of .. needed for include level is numslashes(request) - numslashes(common)
	// then add one more to get to base
	$commonSlashes = substr_count($commonPath, '/');
	$requestSlashes = substr_count($requestPath, '/');
	$numParent = $requestSlashes - $commonSlashes + 1;

	$basePath = ".";
	for($i = 0; $i < $numParent; $i++) {
		$basePath .= "/..";
	}

	return $basePath;
}

//returns a relative path to the given subdirectory, with trailing slash
function contextPath($context) {
	if($context == "main") {
		$basePath = basePath();

		if($basePath == '.') {
			return '';
		} else {
			return '/';
		}
	}

	if(basename(dirname($_SERVER['SCRIPT_FILENAME'])) == $context) {
		return "";
	} else {
		return basePath() . '/' . $context . '/';
	}
}

//returns a URL to the web root directory, without trailing slash
function webPath() {
	//duplicate code with basePath to get the number of directories we have to go up
	$commonPath = __FILE__;
	$requestPath = $_SERVER['SCRIPT_FILENAME'];
	$commonSlashes = substr_count($commonPath, '/');
	$requestSlashes = substr_count($requestPath, '/');
	$numParent = $requestSlashes - $commonSlashes + 1;

	$webPath = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$webPath .= $_SERVER['SERVER_NAME'];
	$webPath .= $_SERVER['REQUEST_URI'];
	$webPath = dirname($webPath);

	for($i = 0; $i < $numParent; $i++) {
		$webPath = dirname($webPath);
	}

	return $webPath;
}

//creates a link or form target based on current URL
//strips certain GET variables that are specified
//returns array(link_string, input for form string, unsanitized_link_string)
// the first two return values are sanitized
function pbobp_create_form_target($ignore_get = array()) {
	$form_string = "";
	$link_string = "?";
	foreach($_GET as $key => $val) {
		if(!in_array($key, $ignore_get)) {
			$form_string .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '" />';
			$link_string .= urlencode($key) . '=' . urlencode($val) . '&';
		}
	}

	$link_string = pbobp_page_requested() . $link_string;
	return array('link_string' => htmlspecialchars($link_string), 'form_string' => $form_string, 'unsanitized_link_string' => $link_string);
}

function uid($length) {
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "";

	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[secure_random() % strlen($characters)];
	}

	return $string;
}

//recursive htmlspecialchars
//this will NOT sanitize a special key in the root array, 'unsanitized_data'
function pbobp_html_sanitize($x, $root = true) {
	if(!is_array($x)) {
		return htmlspecialchars($x, ENT_QUOTES);
	}

	$new_array = array();

	foreach($x as $k => $v) {
		//check whether we should skip this key
		if($k === 'unsanitized_data' && $root === true) {
			$new_array[$k] = $v;
		} else {
			//argument keys ought to be safe but sanitize just in case
			$new_array[htmlspecialchars($k, ENT_QUOTES)] = pbobp_html_sanitize($v, false);
		}
	}

	return $new_array;
}

//gets a template and outputs it
// page: the name of the page to output
// context: main, panel, admin, etc.
// args: arguments to pass to the template
//  special args['unsanitized_data'] won't be sanitized for HTML output; use with extreme caution
// override_path: override the directory that the page is in
// noheader: don't display the theme header/footer
// return_data: buffer and return the output data instead of outputting
function get_page($page, $context, $args = array(), $override_path = false, $noheader = false, $return_data = false) {
	//let pages use some variables
	$config = $GLOBALS['config'];

	//figure out what tabs to display in navbar
	if($context == "main" || $context == "panel") {
		if(!isset($_SESSION['user_id']) && $context == "main") {
			$navbar = array("Home" => "index.php", "Login" => "login.php", "Register" => "register.php");
		} else {
			$url_pre = "";
			if($context == "main") {
				//have to adjust URL's
				$url_pre = "panel/";
			}

			$navbar = array("Home" => "{$url_pre}index.php", "Account" => "{$url_pre}account.php", "Services" => "{$url_pre}services.php", "Billing" => array("Invoices" => "{$url_pre}invoices.php"), "Support" => array("Tickets" => "{$url_pre}tickets.php"), "Logout" => "{$url_pre}index.php?action=logout");
		}
	} else if($context == "admin") {
		$navbar = array("Home" => "index.php", "Users" => "users.php", "Billing" => array("Invoices" => "invoices.php", "Transactions" => "transactions.php"), "Services" => "services.php", "Support" => array("Tickets" => "tickets.php"), "Setup" => array("Plugins" => "plugins.php", "Products" => "products.php", "User fields" => "userfields.php", "Currencies" => "currency.php", "Support" => "setup_support.php"), "Configuration" => "config.php", "Extra" => array(), "Logout" => "index.php?action=logout");
	} else if($context == "none") {
		//this is a special context denoting a non-page-type page
	} else {
		//oops, context should be one of the above
		return;
	}

	//extend navbar with plugins
	$plugin_args = array($context, &$navbar);
	plugin_call_reference("pbobp_navbar", $plugin_args);

	//sanitize arguments, and put in local variable space
	extract(pbobp_html_sanitize($args));

	$basePath = basePath();
	$contextPath = contextPath($context);
	$theme_name = config_get('theme_name');
	$themePath = $basePath . "/theme/$theme_name";

	if($override_path !== false) {
		$themePageInclude = basePath() . "$override_path/$page.php";
	} else {
		$themePageInclude = "$themePath/$context/$page.php";
	}

	//enable output buffering if desired
	if($return_data) {
		ob_start(); //this will create a new buffer for us even if someone else is using ob_start already
	}

	if(!$noheader && file_exists("$themePath/header.php")) {
		include("$themePath/header.php");
	}

	if(file_exists($themePageInclude)) {
		include($themePageInclude);
	}

	if(!$noheader && file_exists("$themePath/footer.php")) {
		include("$themePath/footer.php");
	}

	//return the data if desired
	if($return_data) {
		//this will return the current buffer created above and return it, adding fields for CSRF protection
		//note that when we launch we also make a call to create a buffer
		// however, the above call to ob_start creates a new buffer stacked on top
		// then, the below call will ONLY close the stacked buffer
		//this means that we can csrfguard a return_data and also csrfguard outputted contents later!
		return csrfguard_inject_helper();
	}
}

function isAscii($str) {
    return 0 == preg_match('/[^\x00-\x7F]/', $str);
}

function pbobp_currency_round($x) {
	return number_format((double)$x, 2, '.', '');
}

function array_splice_assoc(&$input, $offset, $length, $replacement) {
	$replacement = (array) $replacement;
	$key_indices = array_flip(array_keys($input));
	if (isset($input[$offset]) && is_string($offset)) {
		$offset = $key_indices[$offset];
	}
	if (isset($input[$length]) && is_string($length)) {
		$length = $key_indices[$length] - $offset;
	}

	$input = array_slice($input, 0, $offset, TRUE)
		+ $replacement
		+ array_slice($input, $offset + $length, NULL, TRUE);
}

//returns random number from 0 to 2^24
function secure_random() {
	return hexdec(bin2hex(secure_random_bytes(3)));
}

function recursiveDelete($dirPath) {
	foreach(
		new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$dirPath, FilesystemIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST
		)
		as $path) {
		$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
	}

	rmdir($dirPath);
}

function pbobp_redirect($url, $get = array(), $statusCode = 303) {
	global $config;
	$get_string = '';

	foreach($get as $k => $v) {
		if(!empty($get_string) || strpos($url, '?') !== false) {
			$get_string .= '&';
		} else {
			$get_string .= '?';
		}

		$get_string .= urlencode($k);
		$get_string .= '=';
		$get_string .= urlencode($v);
	}

	$manual_redirect = isset($config['manual_redirects']) && $config['manual_redirects'];

	if($manual_redirect) {
		echo "<a href=\"$url$get_string\">Click here to continue.</a>";
	} else {
		header('Location: ' . $url . $get_string, true, $statusCode);
	}

	exit;
}

function pbobp_page_requested() {
	$this_page = basename($_SERVER['REQUEST_URI']);
	if (strpos($this_page, "?") !== false) $this_page = explode("?", $this_page)[0];
	return $this_page;
}

function pbobp_get_backtrace() {
	$array = debug_backtrace();
	$str = "";
	$counter = 0;

	foreach($array as $el) {
		$str .= "#$counter\t" . $el['function'] . '(';

		$first = true;
		foreach($el['args'] as $arg) {
			if($first) {
				$first = false;
			} else {
				$str .= ',';
			}

			$str .= print_r($arg, true);
		}

		$str .= ") called at {$el['file']}:{$el['line']}\n";
		$counter++;
	}

	return htmlspecialchars($str);
}

//returns true on success or false on failure
function pbobp_mail($subject, $body, $to) {
	$config = $GLOBALS['config'];
	$from = filter_var(config_get('mail_from'), FILTER_SANITIZE_EMAIL);

	if(isset($config['redirect_email']) && $config['redirect_email'] !== false) {
		$body = "This is a redirected email: original to $to\n\n" . $body;
		$to = $config['redirect_email'];
	}

	$to = filter_var($to, FILTER_SANITIZE_EMAIL);

	if($to === false || $from === false) {
		return false;
	}

	if(config_get('mail_smtp')) {
		require_once "Mail.php";

		$host = config_get('mail_smtp_host');
		$port = config_get('mail_smtp_port');
		$username = config_get('mail_smtp_username');
		$password = config_get('mail_smtp_password');
		$headers = array ('From' => $from,
						  'To' => $to,
						  'Subject' => $subject,
						  'Content-Type' => 'text/plain');
		$smtp = Mail::factory('smtp',
							  array ('host' => $host,
									 'port' => $port,
									 'auth' => true,
									 'username' => $username,
									 'password' => $password));

		$mail = $smtp->send($to, $headers, $body);

		if (PEAR::isError($mail)) {
			return false;
		} else {
			return true;
		}
	} else {
		$headers = "From: $from\r\n";
		$headers .= "Content-type: text/plain\r\n";
		return mail($to, $subject, $body, $headers);
	}
}

//secure_random_bytes from https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP
/*
* The function is providing, at least at the systems tested :),
* $len bytes of entropy under any PHP installation or operating system.
* The execution time should be at most 10-20 ms in any system.
*/
function secure_random_bytes($len = 10) {

   /*
* Our primary choice for a cryptographic strong randomness function is
* openssl_random_pseudo_bytes.
*/
   $SSLstr = '4'; // http://xkcd.com/221/
   if (function_exists('openssl_random_pseudo_bytes') &&
       (version_compare(PHP_VERSION, '5.3.4') >= 0 ||
substr(PHP_OS, 0, 3) !== 'WIN'))
   {
      $SSLstr = openssl_random_pseudo_bytes($len, $strong);
      if ($strong)
         return $SSLstr;
   }

   /*
* If mcrypt extension is available then we use it to gather entropy from
* the operating system's PRNG. This is better than reading /dev/urandom
* directly since it avoids reading larger blocks of data than needed.
* Older versions of mcrypt_create_iv may be broken or take too much time
* to finish so we only use this function with PHP 5.3 and above.
*/
   if (function_exists('mcrypt_create_iv') &&
      (version_compare(PHP_VERSION, '5.3.0') >= 0 ||
       substr(PHP_OS, 0, 3) !== 'WIN'))
   {
      $str = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);
      if ($str !== false)
         return $str;
   }


   /*
* No build-in crypto randomness function found. We collect any entropy
* available in the PHP core PRNGs along with some filesystem info and memory
* stats. To make this data cryptographically strong we add data either from
* /dev/urandom or if its unavailable, we gather entropy by measuring the
* time needed to compute a number of SHA-1 hashes.
*/
   $str = '';
   $bits_per_round = 2; // bits of entropy collected in each clock drift round
   $msec_per_round = 400; // expected running time of each round in microseconds
   $hash_len = 20; // SHA-1 Hash length
   $total = $len; // total bytes of entropy to collect

   $handle = @fopen('/dev/urandom', 'rb');
   if ($handle && function_exists('stream_set_read_buffer'))
      @stream_set_read_buffer($handle, 0);

   do
   {
      $bytes = ($total > $hash_len)? $hash_len : $total;
      $total -= $bytes;

      //collect any entropy available from the PHP system and filesystem
      $entropy = rand() . uniqid(mt_rand(), true) . $SSLstr;
      $entropy .= implode('', @fstat(@fopen( __FILE__, 'r')));
      $entropy .= memory_get_usage();
      if ($handle)
      {
         $entropy .= @fread($handle, $bytes);
      }
      else
      {
         // Measure the time that the operations will take on average
         for ($i = 0; $i < 3; $i ++)
         {
            $c1 = microtime(true);
            $var = sha1(mt_rand());
            for ($j = 0; $j < 50; $j++)
            {
               $var = sha1($var);
            }
            $c2 = microtime(true);
     $entropy .= $c1 . $c2;
         }

         // Based on the above measurement determine the total rounds
         // in order to bound the total running time.
         $rounds = (int)($msec_per_round*50 / (int)(($c2-$c1)*1000000));

         // Take the additional measurements. On average we can expect
         // at least $bits_per_round bits of entropy from each measurement.
         $iter = $bytes*(int)(ceil(8 / $bits_per_round));
         for ($i = 0; $i < $iter; $i ++)
         {
            $c1 = microtime();
            $var = sha1(mt_rand());
            for ($j = 0; $j < $rounds; $j++)
            {
               $var = sha1($var);
            }
            $c2 = microtime();
            $entropy .= $c1 . $c2;
         }

      }
      // We assume sha1 is a deterministic extractor for the $entropy variable.
      $str .= sha1($entropy, true);
   } while ($len > strlen($str));

   if ($handle)
      @fclose($handle);

   return substr($str, 0, $len);
}

?>
