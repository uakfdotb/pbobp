<h1>Cart</h1>

<? foreach($products as $product) { ?>
<h3><?= $product['name'] ?></h3>
<pre><?= $product['description'] ?></pre>
<form method="GET" action="plugin.php">
<input type="hidden" name="plugin" value="cart_republicofchina" />
<input type="hidden" name="view" value="list" />
<input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
<input type="submit" value="Add to cart" />
</form>
<? } ?>
