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

//database name (MySQL only); or see include/database.php
$config['db_name'] = 'pbobp';

//database host
$config['db_host'] = 'localhost';

//database username
$config['db_username'] = 'pbobp';

//database password
$config['db_password'] = '';

//language we're using
$config['language'] = 'english';

//email address for users to contact in case of error
$config['email_web'] = 'support@example.com';

//whether to enable debug mode
$config['debug'] = true;

//whether to use manual redirects (display link)
// this could be useful for debugging
$config['manual_redirects'] = false;

//lock settings
//these determine settings that prevent brute force attacks by a single IP address
// the first three options are per-action and can be customized for each action
// if an action isn't specified, the default times are used
// see include/lock.php
$config['lock_time_initial'] = array();
$config['lock_count_overload'] = array();
$config['lock_time_overload'] = array();
$config['lock_time_reset'] = 300;
$config['lock_time_max'] = 3600;

date_default_timezone_set('UTC');

?>
