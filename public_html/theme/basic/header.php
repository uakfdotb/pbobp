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
?>

<html>
<head>
<title><?= lang('pbobp') ?><? if(isset($title)) { ?> -- <?= $title ?><? } ?></title>
</head>
<body>

<p><?
$navbar_first = true;
foreach($navbar as $navbar_name => $navbar_target) {
	if(is_array($navbar_target)) {
		foreach($navbar_target as $navbar_name => $navbar_target) {
			?>
			<a href="<?= $navbar_target ?>"><?= $navbar_name ?></a>
			<?
		}
	} else {
		?>
		<a href="<?= $navbar_target ?>"><?= $navbar_name ?></a>
		<?
	}

	if($navbar_first) {
		$navbar_first = false;
	}
} ?></p>
