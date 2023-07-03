<?php 
/**
 * Administration panel for the Cloudbeds option page.
 */

$data = cloudbeds_option_data(); 
?>

<section class="cloudbeds-admin _container">
    <nav>
        <ul>
            <li><a href="<?= esc_url(admin_url('options-general.php?page=cloudbeds')) ?>">Cloudbeds</a></li>
            <li><a href="<?= esc_url(admin_url('options-general.php?page=cloudbeds-cache')) ?>">Cache</a></li>
            <li><a href="<?= esc_url(admin_url('options-general.php?page=cloudbeds-sync')) ?>">Sync</a></li>
            <li><a href="<?= esc_url(admin_url('options-general.php?page=cloudbeds-settings')) ?>">Settings</a></li>
        </ul>
    </nav>
    <main class="cloudbeds-main">
        <div class="cloudbeds-grid">
            <div class="cloudbeds-form">
                <header class="cloudbeds-header">
                    <h1>Cloudbeds</h1>
                    <p>WordPress integration utilizing the Cloudbeds API.</p>
                </header>
                <form method="post" action="<?= esc_url(rest_url('/cloudbeds/settings')) ?>"> 
                    <?php wp_nonce_field('wp_rest', '_wpnonce', false); ?>
                    <div>
                        <label>Cloudbeds Admin Email:</label>
                        <input type="text" name="cloudbeds_admin_email" value="<?= esc_attr(get_option('cloudbeds_admin_email')); ?>">
                    </div>
                    <input type="submit" value="Save Settings">
                </form>
            </div>
            <div class="cloudbeds-info">
                <h2>Cloudbeds Settings</h2>
                <p>On this panel, you can set your administration email to receive errors when they occur.</p>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <th>Key</th>
                        <th>Value</th>
                    </thead>
                    <tbody>
                        <?php
                            foreach (cloudbeds_option_data() as $key => $value):
                                if ($key !== 'cloudbeds_admin_email') {
                                    continue;
                                }
                        ?>
                        <tr>
                            <td>
                                <?= esc_html($key) ?>
                            </td>
                            <td>
                                <?= esc_html($value) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</section>