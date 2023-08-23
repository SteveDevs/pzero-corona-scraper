<?php
require 'simple_html_dom.php'; // Include the Simple HTML DOM Parser

// URL of the webpage to scrape
$url = "https://www.worldometers.info/coronavirus/";

// Load the webpage content
$html = file_get_html($url);

if ($html) {
    // Calculate hash of the table's HTML content
    $table = $html->find('#main_table_countries_today', 0);
    $tableHash = md5($table->outertext);

    // Generate a cache file name based on the URL
    $cacheFile = md5($url) . '.json';

    // Check if cached data is available
    if (file_exists($cacheFile)) {
        // Read the cached table hash from the cache file
        $cachedTableHash = json_decode(file_get_contents($cacheFile . '.hash'), true);
        // Use cached JSON data if the table hash matches
        if ($cachedTableHash && $cachedTableHash === $tableHash) {
            $jsonData = file_get_contents($cacheFile);
            echo $jsonData;
            exit;
        }
    }

    $data = [];

    if ($table) {
        foreach ($table->find('tr') as $rowIndex => $row) {
            if ($rowIndex === 0) {
                continue; // Skip header row
            }

            $rowData = [
                'Country' => trim($row->find('td', 1)->plaintext),
                'TotalCases' => trim($row->find('td', 2)->plaintext),
                'NewCases' => trim($row->find('td', 3)->plaintext),
                'TotalDeaths' => trim($row->find('td', 4)->plaintext),
                'NewDeaths' => trim($row->find('td', 5)->plaintext),
                'TotalRecovered' => trim($row->find('td', 6)->plaintext),
            ];

            $data[] = $rowData;
        }

        // Convert data array to JSON
        $jsonString = json_encode($data, JSON_PRETTY_PRINT);

        // Save the data to a cache file
        file_put_contents($cacheFile, $jsonString);

        // Save the table hash to a cache file
        file_put_contents($cacheFile . '.hash', json_encode($tableHash));

        // Output the JSON string
        echo $jsonString;
    } else {
        echo "Table not found.";
    }
} else {
    echo "Failed to load webpage.";
}
?>
