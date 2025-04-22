<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Inventory</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        #data-container { margin-top: 20px; border: 1px solid #ccc; padding: 15px; min-height: 50px; background-color: #f9f9f9;}
        #error-container { color: red; margin-top: 10px; font-weight: bold; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Available Inventory Items</h1>

    <div id="data-container">
        <?php
        $apiUrl = getenv('BACKEND_API_URL') . "/api/inventory"; // Fetch from ConfigMap - Kubernetes service URL
        $errorMsg = '';
        $data = null;

        // Create a context for error handling and timeout settings
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
        $responseJson = @file_get_contents($apiUrl, false, $context);

        if ($responseJson === false) {
            $error = error_get_last();
            $errorMsg = "Failed to connect to API: " . ($error['message'] ?? 'Unknown error');
        } else {
            // Check HTTP status code
            if (isset($http_response_header[0]) && strpos($http_response_header[0], '200 OK') === false) {
                $errorMsg = "API Error: Received status " . htmlspecialchars($http_response_header[0]);
            } else {
                $data = json_decode($responseJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorMsg = "Failed to decode JSON response: " . json_last_error_msg();
                    $data = null;
                }
            }
        }

        // Display data or error message
        if (!empty($errorMsg)) {
            echo '<div id="error-container">' . htmlspecialchars($errorMsg) . '</div>';
        } elseif ($data !== null && count($data) > 0) {
            echo '<ul>';
            foreach ($data as $item) {
                echo '<li>ID: ' . htmlspecialchars($item['item_id']) . ', Name: ' . htmlspecialchars($item['item_name']) . ', Price: $' . htmlspecialchars($item['price']) . ', Quantity: ' . htmlspecialchars($item['quantity']) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No inventory items found.';
        }
        ?>
    </div>

    <p style="font-size: 0.8em; color: #666; margin-top: 15px;">
        Server Software: <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE']); ?>
    </p>
    <p style="font-size: 0.8em; color: #666; margin-top: 5px;">
        Hostname: <?php echo htmlspecialchars(gethostname()); ?>
    </p>
</body>
</html>
