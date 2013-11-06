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
<!DOCTYPE HTML>
<html>
<head>
	<title><?= lang('pbobp') ?><? if(isset($title)) { ?> -- <?= $title ?><? } ?></title>
	<link href="<?= $themePath ?>/assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= $themePath ?>/assets/css/pbobp.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
</head>
<body>

<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand" href="./">pbobp</a>
			<div class="nav">
				<ul class="nav">
					<? foreach($navbar as $navbar_name => $navbar_target) { ?>
						<? if(is_array($navbar_target)) { ?>
							<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= $navbar_name ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
							<? foreach($navbar_target as $navbar_name => $navbar_target) { ?>
								<li><a href="<?= $contextPath . $navbar_target ?>"><?= $navbar_name ?></a></li>
							<? } ?>
							</ul>
							</li>
						<? } else { ?>
							<li><a href="<?= $contextPath . $navbar_target ?>"><?= $navbar_name ?></a></li>
						<? } ?>
					<? } ?>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="container">
