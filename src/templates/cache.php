<?php 
/**
 * Administration panel for the Cloudbeds cache page.
 */

$table = cloudbeds_cache_retrieve_table();
?>

<section class="cloudbeds-admin _container">
    <main class="cloudbeds-main">
        <?php include CLOUDBEDS_PLUGIN_PATH . 'src/templates/template-part/navigation.php'; ?>
        <header class="cloudbeds-header">
            <h1>Cloudbeds</h1>
            <p>WordPress integration utilizing the Cloudbeds API.</p>
        </header>
        <div class="cloudbeds-info">
            <table class="widefat" cellspacing="0">
                <thead>
                    <?php if (empty($table)): ?>
                        <tr>
                            <th>No data available</th>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_keys(get_object_vars($table[0])) as $heading): 
                            if ($heading == 'id' || $heading == 'response') {
                                continue;
                            }    
                        ?>
                            <th><?= esc_html($heading) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </thead>
                </thead>
                <tbody>
                    <?php foreach ($table as $row): ?>
                        <tr>
                            <?php foreach ($row as $key => $value):
                                if ($key == 'id' || $key == 'response') {
                                    continue;
                                }    

                                // Calculate the timestamp in a human readable format.
                                if ($key == 'timestamp' && $value != 0) {
                                    $readable_time = wp_date('F j, Y g:ia', $value);
                                    $seconds_left = 86400 - (time() - $value);

                                    if ($seconds_left < 0) {
                                        $seconds_left = 0;
                                    }

                                    $value = $readable_time . " ($seconds_left seconds left)";
                                }
                            ?>
                                <td>
                                    <?= esc_html($value) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</section>