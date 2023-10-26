<div class="row">
	<?php foreach ($categories as $category):
	$image = get_term_meta( $category->term_id, 'bundles_image', true );
	$bundle_link = get_term_link( $category );
	?>
    <div class="col col-md-3">
    	<a href="<?php echo $bundle_link; ?>">
        	<img class="img-responsive img-thumbnail" src="<?php echo $image; ?>">
        </a>
        <a href="<?php echo $bundle_link; ?>">
        	<h4><?php echo $category->name; ?></h4>
        </a>
    </div>
    <?php endforeach; ?>
</div>