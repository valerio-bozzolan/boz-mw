<?php
# Leaflet Wikipedians map
# Copyright (C) 2017 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

class Header {
	static $args;

	static function spawn($menu_uid, $args = [] ) {
		$menu = get_menu_entry($menu_uid);

		$args = merge_args_defaults($args, [
			'complete-title' => true,
			'title' => $menu->name,
			'url'   => $menu->url,
			'og'    => [],
			'container' => true,
			'cors' => false
		] );

		$args['og'] = merge_args_defaults($args['og'], [
			'title' => $args['title'],
			'type'  => 'website',
			'url'   => $args['url']
		] );

		self::$args = $args;
		$args = & self::$args;

		header("Content-Type: text/html; charset=" . CHARSET);

		$args['cors'] and header("Access-Control-Allow-Origin: *");
		?>
<!DOCTYPE html>
<html>
<head>
	<title><?php $args['complete-title'] and printf("%s &mdash; %s", $args['title'], SITE_NAME ) or printf(SITE_NAME) ?></title>

	<?php foreach($args['og'] as $id => $value): ?>
	<meta property="og:<?php echo $id ?>" content="<?php _esc_attr( $value ) ?>" />
	<?php endforeach ?>

	<?php load_module('header') ?>

</head>
<body>

	<div class="container">
		<h1><a href="<?php echo $args['url'] ?>"><?php echo $args['title'] ?></a></h1>
	</div>

	<?php if( $args['container'] ): ?>
	<div class="container">
	<?php endif ?>

<?php SPAM::spawn() ?>

<?php } }
