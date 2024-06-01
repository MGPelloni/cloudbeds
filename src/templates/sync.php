<?php 
/**
 * Administration panel for the Cloudbeds sync functionality.
 */

$data = cloudbeds_option_data(); 
$sync_res = cloudbeds_sync_connect();
$sync_website = get_option('cloudbeds_sync_website');
$sync_key = get_option('cloudbeds_sync_key');
$message_class = "-error";

if ($sync_res == "Sync successful!") {
    $message_class = "-success";
}

?>

<section class="cloudbeds-admin cloudbeds-sync _container">
    <main class="cloudbeds-main">
        <?php include CLOUDBEDS_PLUGIN_PATH . 'src/templates/template-part/navigation.php'; ?>
        <div class="cloudbeds-grid">
            <div class="cloudbeds-form">
                <header class="cloudbeds-header">
                    <h1>Sync</h1>
                    <p>Use the form to sync your non-production website to a production site.</p>
                </header>
                <?php if ($sync_res): ?>
                    <h4 class="<?php echo esc_attr($message_class) ?>"><?php echo esc_html($sync_res) ?></h4>
                <?php endif; ?>
                <form method="post" action=""> 
                    <?php wp_nonce_field('cloudbeds_sync', '_wpnonce', false); ?>
                    <div>
                        <label>Website URL:</label>
                        <input type="text" name="target_website" value="<?php echo esc_attr($sync_website) ?>">
                    </div>
                    <div>
                        <label>Key:</label>
                        <input type="text" name="data_key" value="<?php echo esc_attr($sync_key) ?>">
                    </div>
                    <input type="submit" value="Connect to Website">
                </form>
            </div>
            <div class="cloudbeds-info">
                <p>This form is to be utilized only when a production site has been successfully set up with the Cloudbeds plugin and is connected to Cloudbeds.</p> 
                <p>On your production site, find the <code>Cloudbeds Data Key</code> and add it to the form. Set the website URL in the form to the target <code>Site Address (URL)</code> found in WordPress general settings.</p>
            </div>
        </div>
    </main>
</section>