<?php

$agent = get_browser(null, true);

// Output the result
echo '<pre>';
echo "Result:\n\n";
print_r ($agent);
echo '</pre>';

?>