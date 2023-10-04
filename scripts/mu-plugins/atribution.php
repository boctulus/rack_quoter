<?php
/*
Plugin Name: Authorship attribution
*/

if (! defined('WP_PLUGIN_DIR')) {
	return; // can't be accessed directly as it needs to be triggering with the WordPress environment
}

if (isset($_GET['credits'])){
    add_action('wp_footer', function(){ 
    	$app_name = 'Plugin development';

    	if (function_exists('config')){
    		$app_name = config()['app_name'] ?? $app_name;
    	} 
		
        ?>
            <div id="dev-credits" style="
            height: 60px;
            text-align: center; margin: auto;
            width: 100%;
            padding-top: 20px;
            padding-bottom: 20px;
            background-color: #f0ad4e; ">
            <strong><?= $app_name ?></strong> by <b>Pablo Bozzolo</b> < boctulus@gmail.com >
            </div>

			<script>
				document.addEventListener('DOMContentLoaded', function() {
					if (window.location.href.includes('credits')) {
						// Fuera elemento molesto
						document.querySelector('a.messenger').style.display = 'none'; 

						var creditsElement = document.getElementById('dev-credits');
						if (creditsElement) {
							creditsElement.scrollIntoView({ behavior: 'smooth' });
						}
					}
				});
			</script>
        <?php    
    }); 
}