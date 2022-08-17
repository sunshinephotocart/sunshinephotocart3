<form method="post" action="<?php sunshine_url( 'checkout' ); ?>" id="sunshine--checkout" class="sunshine-form">

	<div id="sunshine--checkout--main">
		<div id="sunshine--checkout--steps">
			<?php sunshine_get_template( 'checkout/steps' ); ?>
		</div>
		<div id="sunshine--checkout--summary">
			<?php sunshine_get_template( 'checkout/summary' ); ?>
		</div>
	</div>

</form>
