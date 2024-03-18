<?php
/*
Plugin Name: Student Info
Description: Plugin to manage student information.
Version: 1.0
Author: Your Name
*/

// Creating 'students' table on plugin activation
register_activation_hook(__FILE__, 'student_info_create_table');

function student_info_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(50) NOT NULL,
        last_name varchar(50) NOT NULL,
        class varchar(20) NOT NULL,
        roll int NOT NULL,
        reg_no varchar(50) NOT NULL,
        marks json NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Implementing shortcode for rendering student information and marks form
function student_info_form_shortcode()
{
    global $wpdb;

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student_info'])) {
        // Sanitize input data
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $class = sanitize_text_field($_POST['class']);
        $roll = intval($_POST['roll']);
        $reg_no = sanitize_text_field($_POST['reg_no']);
        $marks = sanitize_text_field($_POST['marks']);

        // Insert data into 'students' table
        $table_name = $wpdb->prefix . 'students';

        // Data to be inserted
        $data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'class' => $class,
            'roll' => $roll,
            'reg_no' => $reg_no,
            'marks' => $marks
        );

        // Data format for prepared statement
        $data_format = array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
            '%d'
        );

        // Insert data using prepared statement
        $result = $wpdb->insert($table_name, $data, $data_format);

        // Redirect to avoid form resubmission on page refresh
        wp_redirect(get_permalink());
        exit;
    }

    ob_start();
?>
    <form id="student-info-form" method="post">
        <label for="first_name"><?php echo esc_html('First Name:'); ?></label>
        <input type="text" name="first_name" id="first_name" required><br>
        <label for="last_name"><?php echo esc_html('Last Name:'); ?></label>
        <input type="text" name="last_name" id="last_name" required><br>
        <label for="class"><?php echo esc_html('Class:'); ?></label>
        <input type="text" name="class" id="class" required><br>
        <label for="roll"><?php echo esc_html('Roll:'); ?></label>
        <input type="number" name="roll" id="roll" required><br>
        <label for="reg_no"><?php echo esc_html('Registration No:'); ?></label>
        <input type="text" name="reg_no" id="reg_no" required><br>
        <label for="marks"><?php echo esc_html('Marks:'); ?></label>
        <input type="text" name="marks" id="marks" required><br>
        <!-- Add fields for marks of each subject here -->
        <input type="submit" name="submit_student_info" value="<?php echo esc_attr('Submit'); ?>">
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('student_info_form', 'student_info_form_shortcode');


// Implementing metadata API
add_action('rest_api_init', 'student_info_metadata_api');

function student_info_metadata_api()
{
    register_rest_route('student-info/v1', '/metadata', array(
        'methods' => 'GET',
        'callback' => 'student_info_get_metadata',
    ));
}

function student_info_get_metadata()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';
    $columns = $wpdb->get_col("DESC $table_name");
    return $columns;
}


// Implementing shortcode for displaying student information with pagination
function student_info_display_shortcode($atts)
{
    ob_start();
?>
    <div id="student-info-table-container">
        <?php echo student_info_display_table($atts); ?>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('student_info_display', 'student_info_display_shortcode');

function student_info_display_table($atts)
{
    global $wpdb;
    $atts = shortcode_atts(array(
        'per_page' => -1,
    ), $atts);

    $table_name = $wpdb->prefix . 'students';

    // Fetch metadata to determine columns
    $metadata_response = wp_remote_get(site_url('/wp-json/student-info/v1/metadata'));

    if (is_wp_error($metadata_response)) {
        return 'Error retrieving metadata.';
    }

    $metadata = json_decode(wp_remote_retrieve_body($metadata_response));


    if (!empty($metadata)) {
        // Construct SELECT query based on metadata
        $select_columns = implode(', ', $metadata);

        // Fetch data using constructed query
        $students = $wpdb->get_results("SELECT $select_columns FROM $table_name");

        $output = '<table id="studentTable" class="display">';
        $output .= '<thead><tr>';
        foreach ($metadata as $column) {
            $output .= '<th>' . $column . '</th>';
        }
        $output .= '</tr></thead>';
        $output .= '<tbody>';
        foreach ($students as $student) {
            $output .= '<tr>';
            foreach ($metadata as $column) {
                $output .= '<td>' . $student->$column . '</td>';
            }
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';

        // Add pagination links

        $output .= '<script>
            jQuery(document).ready(function($) {
                $("#studentTable").DataTable();
            });
            </script>';

        return $output;
    } else {
        return 'Metadata not found.';
    }
}

// Enqueue scripts for AJAX pagination
function student_info_enqueue_scripts()
{
    wp_enqueue_script('student-info-pagination', plugin_dir_url(__FILE__) . 'js/pagination.js', true);
    wp_enqueue_script('dataTables-min', plugin_dir_url(__FILE__) . 'js/dataTables.min.js', '1.0.0', array('jQuery'), true);
    wp_enqueue_style('dataTables-css', plugin_dir_url(__FILE__) . 'css/dataTables.dataTables.css', time());
}
add_action('wp_enqueue_scripts', 'student_info_enqueue_scripts');
