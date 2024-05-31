<?php 
/**
 * Administration panel for the Cloudbeds option page.
 */

$data = cloudbeds_option_data(); 
?>

<section class="cloudbeds-admin _container">
    <main class="cloudbeds-main">
        <?php include CLOUDBEDS_PLUGIN_PATH . 'src/templates/template-part/navigation.php'; ?>
        <div class="cloudbeds-grid">
            <div class="cloudbeds-form">
                <header class="cloudbeds-header">
                    <h1>Cloudbeds</h1>
                    <p>WordPress integration utilizing the Cloudbeds API.</p>
                </header>
                <form method="post" action="<?= esc_url(rest_url('/cloudbeds/connect')) ?>"> 
                    <?php wp_nonce_field('wp_rest', '_wpnonce', false); ?>
                    <?php if ($data['cloudbeds_status'] == "The plugin must be reconnected to the Cloudbeds API."): ?>
                        <input type="hidden" name="cloudbeds_client_id" value="<?= esc_attr(get_option('cloudbeds_client_id')); ?>">
                        <input type="hidden" name="cloudbeds_client_secret" value="<?= esc_attr(get_option('cloudbeds_client_secret')); ?>">
                    <?php else: ?>
                    <div>
                        <label>Client ID:</label>
                        <input type="text" name="cloudbeds_client_id" value="<?= esc_attr(get_option('cloudbeds_client_id')); ?>">
                    </div>
                    <div>
                        <label>Client Secret:</label>
                        <input type="text" name="cloudbeds_client_secret" value="">
                    </div>
                    <?php endif; ?>
                    <input type="submit" value="Connect to Cloudbeds">
                </form>
            </div>
            <div class="cloudbeds-info">
                <?php switch ($data['cloudbeds_status']) {
                    case 'Syncing to Production':
                    case 'Connected':
                        $time_left = ceil(((intval($data['cloudbeds_access_token_timestamp']) + 1800) - time()) / 60);
                        ?>
                        <h2>Status: ✅ <?= esc_html($data['cloudbeds_status']) ?></h2>
                        <p>Your website is connected to Cloudbeds and is receiving information from the server. You can now use the functions <code>cloudbeds_api_get</code> and <code>cloudbeds_api_post</code> to interact with the <a href="https://hotels.cloudbeds.com/api/docs/">Cloudbeds API</a>.</p>
                        <p>The next refresh will happen in <?= esc_html($time_left) ?> minutes.</p>
                        <table class="widefat fixed" cellspacing="0">
                            <thead>
                                <th>Key</th>
                                <th>Value</th>
                            </thead>
                            <tbody>
                                <?php
                                    foreach (cloudbeds_option_data() as $key => $value):
                                        if ($key == 'cloudbeds_client_secret' || $key == 'cloudbeds_admin_email') {
                                            continue;
                                        }

                                        if ($data['cloudbeds_status'] == 'Syncing to Production' && $key == 'cloudbeds_data_key') {
                                            continue;
                                        }
                                ?>
                                <tr>
                                    <td>
                                        <?php echo esc_html($key) ?>
                                    </td>
                                    <td>
                                        <?php echo esc_html($value) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php
                        break;
                    default:
                        ?>
                        <h2>Status: ❌ Not Connected<?php echo ($data['cloudbeds_status']) ? esc_html(" - {$data['cloudbeds_status']}") : '' ?></h2>
                        <p>To connect to Cloudbeds, you will need to create an API key. You can do this by <a href="https://hotels.cloudbeds.com/auth/login" target="_blank">logging into your Cloudbeds account</a>. Once logged in, click the "gear" icon located in the top right. Navigate to "API Credentials" on the left hand sidebar.</p>
                        <p>You are ready to create a new API key with the following settings:</p>
                        <ul>
                            <li><code>Name: WordPress Plugin</code></li>
                            <li><code>Integration Type: App Integration</code></li>
                            <li><code>Redirect URI: <?= esc_url(rest_url('/cloudbeds/auth')) ?></code></li>
                        </ul>
                        <p>Once complete, enter in the <code>Client ID</code> and <code>Client Secret</code> values into the form on this page and submit to connect your website to Cloudbeds.</p>
                        <p>If you're working in a local or staging environment, you can <a href="<?= esc_url(CLOUDBEDS_ADMIN_SYNC_URL) ?>">sync your data</a> to the production environment using a data key.</p>
                        <?php
                        break;
                } ?>
            </div>
        </div>
    </main>
</section>