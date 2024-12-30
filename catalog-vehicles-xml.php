<?php
/*
    Plugin Name: Catalog Vehicles
    Plugin URI: https://github.com/kendoace/
    GitHub Plugin URI: kendoace/vin-vehicle-import/
    Description: A simple plugin to export 'Buy Here Pay Here' custom post type as XML.
    Version: 1.0
    Author: Aleksandar Petreski
    Author URI: https://www.linkedin.com/in/aleksandar-petreski/
*/

// Hook to add admin menu
function export_bhph_cpt_menu() {
    add_menu_page(
        'Catalog Vehicles',   // Page title
        'Catalog Vehicles',   // Menu title
        'manage_options',             // Capability
        'export_bhph_cpt',            // Menu slug
        'export_bhph_cpt_page',       // Callback function to display the page
        'dashicons-download'          // Icon
    );
}
add_action('admin_menu', 'export_bhph_cpt_menu');

// Callback function to display the admin page
function export_bhph_cpt_page() {
    ?>
    <div class="wrap">
        <h1>Export Vehicles</h1>
        <p>Click the button below to export all published "Vehicles" as an XML file for FB catalog.</p>
        <form method="post" action="">
            <input type="submit" name="export_bhph_cpt_fb_xml" class="button button-primary" value="FB Template">
        </form>
        <p>Click the button below to export all published "Vehicles" as an XML file for Google Merchant.</p>
        <form method="post" action="">
            <input type="submit" name="export_bhph_cpt_google_xml" class="button button-primary" value="Google Template">
        </form>
    </div>
    <?php
    if (isset($_POST['export_bhph_cpt_fb_xml'])) {
        export_fb_xml();
    }
    if (isset($_POST['export_bhph_cpt_google_xml'])) {
        export_google_xml();
    }
}

// Function to escape problematic characters
function escape_special_characters($string) {
    // Replace problematic characters with their XML-safe equivalents
    $string = str_replace('&lsaquo;', '<', $string); // Replace &lsaquo; with <
    $string = str_replace('&rsaquo;', '>', $string); // Replace &rsaquo; with >
    $string = str_replace('&#8212;', '-', $string); // Replace &#8212; with em dash
    
    // Use htmlspecialchars to ensure all other special characters are properly encoded
    return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
}

// Function to export Buy Here Pay Here CPT to FB XML
function export_fb_xml() {
    // Set the appropriate headers for XML file download
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment; filename="fb_catalog_export.xml"');

    // Set the PHP environment to suppress any HTML output
    ob_clean(); // Clear any output buffers
    flush(); // Flush the output buffer to avoid any previous content

    // Initialize XMLWriter
    $xml = new XMLWriter();
    $xml->openURI('php://output');
    $xml->startDocument('1.0', 'UTF-8');
    $xml->setIndent(4);
    $xml->startElement('data');

    // Set up query arguments for the custom post type
    $args = array(
        'post_type' => 'buy-here-pay-here', // Your custom post type slug
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    // Create a new WP_Query instance
    $query = new WP_Query($args);

    // Get posts from the query
    $posts = $query->posts;

    if (empty($posts)) {
        echo '<p>No posts found to export.</p>';
        return;
    }

    // Loop through each post
    foreach ($posts as $post) {
        // Get ACF fields for the post
        $acf_fields = get_fields($post->ID);

        // Start the <post> element
        $xml->startElement('post');
        
        $xml->writeElement('vehicle_id', $post->ID);

        $xml->writeElement('title', escape_special_characters($post->post_title));

        $xml->writeElement('description', escape_special_characters($post->post_content));

        $xml->writeElement('price', escape_special_characters($acf_fields['price'] . 'USD' ?? ''));

        $xml->writeElement('url', esc_url(get_permalink($post->ID)));

        $xml->writeElement('body_style', escape_special_characters($acf_fields['style'] ?? ''));
        
        $xml->writeElement('mileage_unit', escape_special_characters('MI'));
        
        $xml->writeElement('mileage_value', escape_special_characters($acf_fields['mileage'] ?? ''));
        
        $xml->writeElement('state_of_vehicle', escape_special_characters('USED'));
        
        $xml->writeElement('make', escape_special_characters($acf_fields['make'] ?? ''));
        
        $xml->writeElement('model', escape_special_characters($acf_fields['model'] ?? ''));
        
        $xml->writeElement('year', escape_special_characters($acf_fields['year'] ?? ''));

        $image = get_the_post_thumbnail_url($post->ID, 'full');
        $xml->writeElement('image_url', $image ? esc_url($image) : '');
        
        $xml->writeElement('vin', escape_special_characters($acf_fields['vin'] ?? ''));
        
        $xml->writeElement('transmission', escape_special_characters($acf_fields['transmission'] ?? ''));
        
        $xml->writeElement('fuel_type', escape_special_characters($acf_fields['fuel_type'] ?? ''));
        
        $xml->writeElement('drivetrain', escape_special_characters($acf_fields['drivetrain'] ?? ''));
        
        $xml->writeElement('exterior_color', escape_special_characters($acf_fields['color'] ?? ''));

        // End the <post> element
        $xml->endElement();
    }

    // End the root <data> element
    $xml->endElement();

    // End the document
    $xml->endDocument();
    
    // Flush the XML to the browser
    $xml->flush();
    exit;
}


// Function to export Buy Here Pay Here CPT to FB XML
function export_google_xml() {
    // Set the appropriate headers for XML file download
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment; filename="google_merchant_export.xml"');

    // Set the PHP environment to suppress any HTML output
    ob_clean(); // Clear any output buffers
    flush(); // Flush the output buffer to avoid any previous content

    // Initialize XMLWriter
    $xml = new XMLWriter();
    $xml->openURI('php://output');
    $xml->startDocument('1.0', 'UTF-8');
    $xml->setIndent(4);
    $xml->startElement('data');

    // Set up query arguments for the custom post type
    $args = array(
        'post_type' => 'buy-here-pay-here', // Your custom post type slug
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    // Create a new WP_Query instance
    $query = new WP_Query($args);

    // Get posts from the query
    $posts = $query->posts;

    if (empty($posts)) {
        echo '<p>No posts found to export.</p>';
        return;
    }

    // Loop through each post
    foreach ($posts as $post) {
        // Get ACF fields for the post
        $acf_fields = get_fields($post->ID);

        // Start the <post> element
        $xml->startElement('post');
        
        $xml->writeElement('id', $post->ID);

        $xml->writeElement('title', escape_special_characters($post->post_title));

        $xml->writeElement('description', escape_special_characters($post->post_content));

        $xml->writeElement('link', esc_url(get_permalink($post->ID)));

        $image = get_the_post_thumbnail_url($post->ID, 'full');
        $xml->writeElement('image_link', $image ? esc_url($image) : '');

        $xml->writeElement('price', escape_special_characters($acf_fields['price'] . 'USD' ?? ''));
        
        $xml->writeElement('brand', escape_special_characters($acf_fields['make'] ?? ''));
        
        $xml->writeElement('color', escape_special_characters($acf_fields['color'] ?? ''));
        
        $xml->writeElement('year', escape_special_characters($acf_fields['year'] ?? ''));

        $xml->writeElement('body_style', escape_special_characters($acf_fields['style'] ?? ''));
        
        $xml->writeElement('mileage_unit', escape_special_characters('MI'));
        
        $xml->writeElement('mileage_value', escape_special_characters($acf_fields['mileage'] ?? ''));
        
        $xml->writeElement('state_of_vehicle', escape_special_characters('USED'));
        
        $xml->writeElement('make', escape_special_characters($acf_fields['make'] ?? ''));
        
        $xml->writeElement('model', escape_special_characters($acf_fields['model'] ?? ''));
        
        $xml->writeElement('vin', escape_special_characters($acf_fields['vin'] ?? ''));
        
        $xml->writeElement('transmission', escape_special_characters($acf_fields['transmission'] ?? ''));
        
        $xml->writeElement('fuel_type', escape_special_characters($acf_fields['fuel_type'] ?? ''));
        
        $xml->writeElement('drivetrain', escape_special_characters($acf_fields['drivetrain'] ?? ''));

        // End the <post> element
        $xml->endElement();
    }

    // End the root <data> element
    $xml->endElement();

    // End the document
    $xml->endDocument();
    
    // Flush the XML to the browser
    $xml->flush();
    exit;
}
