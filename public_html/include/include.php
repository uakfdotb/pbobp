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

$GLOBALS['IN_PBOBP'] = true;

//require PHP >= 5.4
// if we didn't have this check, user would see syntax errors instead :)
if(version_compare(phpversion(), '5.4') < 0) {
	die('pbobp requires PHP >= 5.4 -- you are running ' . phpversion() . '!');
}

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/language.php");
require_once(dirname(__FILE__) . "/common.php");
require_once(dirname(__FILE__) . "/database.php");
require_once(dirname(__FILE__) . "/session.php");
require_once(dirname(__FILE__) . "/plugin.php");
require_once(dirname(__FILE__) . "/sanitize.php");

?>
