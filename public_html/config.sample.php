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

//
// GENERAL SITE SETTINGS
//

//web team contact
// this email address will be displayed if there is a database error
$config['email_web'] = 'admin@example.com';

//address to send emails from
$config['email_from'] = 'gpg-mailgate-web@example.com';

//this will be used as the subject when a user requests to add a PGP key
$config['email_subject_requestpgp'] = 'Confirm your email address';

//site URL, without trailing slash
$config['site_url'] = 'http://example.com/gpgmw';

//title of the website (displayed on home page)
$config['site_title'] = 'PGP key management';

//language file to use (see language subdirectory)
$config['language'] = 'english';

//whether debug mode should be enabled
$config['debug'] = false;

//
// MAIL SETTINGS
//

//whether to send mail through SMTP (instead of PHP mail function)
$config['mail_smtp'] = false;

//SMTP settings, if mail_smtp is enabled
//this requires Net_SMTP from http://pear.php.net/package/Net_SMTP/ to be installed
$config['mail_smtp_host'] = 'localhost';
$config['mail_smtp_port'] = 25;
$config['mail_smtp_username'] = 'gpgmw';
$config['mail_smtp_password'] = '';

//
// DATABASE SETTINGS
//

//database name (MySQL only); or see include/database.php
$config['db_name'] = 'gpgmw';

//database host
$config['db_host'] = 'localhost';

//database username
$config['db_username'] = 'gpgmw';

//database password
$config['db_password'] = '';

//
// PGP VERIFICATION SETTINGS
//

//whether to enable immediate verification of PGP keys
// keys will always be verified with the email address in our cron job
// but this will enable verification from the web interface before email confirmation
//for this to work, Crypt_GPG from http://pear.php.net/Crypt_GPG must be installed
// (as well as any of its dependencies), and pgpverify_tmpdir must be set
$config['pgpverify_enable'] = false;

//a temporary directory to use for PGP verification, without trailing slash
// gpgmw will create subdirectories from here to use as temporary gpg home directories
// these directories will (should) be deleted immediately after use
$config['pgpverify_tmpdir'] = '/tmp';

//whether to allow blank "keys"
// this is useful to allow users to delete their key from the keystore
// if they no longer want encryption
$config['pgpverify_allowblank'] = true;

//
// LOCK SETTINGS
//

//the time in seconds a user must wait before trying again; otherwise they get locked out (count not increased)
$config['lock_time_initial'] = array('requestpgp' => 10);

//the number of tries a user has (that passes the lock_time_initial test) before being locked by overload (extended duration)
$config['lock_count_overload'] = array('requestpgp' => 3);

//the time that overloads last
$config['lock_time_overload'] = array('requestpgp' => 900);

//time after which locks no longer apply, assuming the lock isn't active
$config['lock_time_reset'] = 300;

//max time to store locks in the database; this way we can clear old locks with one function
$config['lock_time_max'] = 3600;

?>
