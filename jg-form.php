<?php
/*
    Plugin Name: Formulario Sugerencias Gutenberg
    Plugin URI:
    Descriprion: Agrega un formulario con bloque de Gutenberg nativo
    Version: 1.0
    Author: Javier García Sanchez
    Author URI: https://www.linkedin.com/in/javigarciasanchez/
    License: GPL2
    License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


//Libreria CMB2 para Custom Fields

if ( file_exists (dirname( __FILE__ ) . '/CMB2/init.php')) {
    require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

add_action('cmb2_admin_init', 'forms_imputs');

function forms_imputs() {
    $prefix = 'jg_forms_inputs_';
    
    $metabox_sugerencias = new_cmb2_box(array(
        'id'            => $prefix . 'metabox',
        'title'         => __('Custom fields', 'cmb2'),
        'object_types'  => array('sugerencias')
    ));
    
    $metabox_sugerencias->add_field(array(
        'name'      => __('Nombre', 'cmb2'),
        'desc'      => __('Escribe tu nombre', 'cmb2'),
        'id'        => $prefix . 'nombre',
        'type'      => 'text',
    ));
    $metabox_sugerencias->add_field(array(
        'name'      => __('Apellidos', 'cmb2'),
        'desc'      => __('Escribe tus apellidos', 'cmb2'),
        'id'        => $prefix . 'apellidos',
        'type'      => 'text',
    ));
    $metabox_sugerencias->add_field(array(
        'name'      => __('Email', 'cmb2'),
        'desc'      => __('Escribe tu email', 'cmb2'),
        'id'        => $prefix . 'email',
        'type'      => 'text_email',
    ));
    $metabox_sugerencias->add_field(array(
        'name'      => __('Sugerencia', 'cmb2'),
        'desc'      => __('Indica tu sugerencia', 'cmb2'),
        'id'        => $prefix . 'sugerencia',
        'type'      => 'textarea',
    ));
    
}

//Crear Sugerencia desde Front con Shortcode

function jg_create_suggestion(){

    $cmb = new_cmb2_box(array(
        'id'           => 'send_suggestion',
        'object_types' => array('page'),
        'hookup'       => false,
        'save_fields'  => false,
    ));
    $cmb->add_field( array(
        'name'  => 'Nombre',
        'id'    => 'nombre_id',
        'type'  => 'text'
    ));
    $cmb->add_field( array(
        'name'  => 'Apellidos',
        'id'    => 'apellidos_id',
        'type'  => 'text'
    ));
    $cmb->add_field( array(
        'name'  => 'Email',
        'id'    => 'email_id',
        'type'  => 'text_email'
    ));
    $cmb->add_field( array(
        'name'  => 'Sugerencia',
        'id'    => 'sugerencia_id',
        'type'  => 'textarea'
    ));
}
add_action('cmb2_init', 'jg_create_suggestion');

//Obtiene los valores del formulario
function jg_suggestion_fields() {
    //ID del CMB2 box
    $metabox_id = 'send_suggestion';

    $object_id = 'fake-object-id';

    return cmb2_get_metabox($metabox_id, $object_id);
}

//Shortcode crear sugerencia > [jg_create_suggestion_shortcode]

function jg_create_form_suggestion() {
    echo '<h2 class="tex-center">Enviar Sugerencia</h2>';
    //Obtiene el ID del formulario
    $cmb = jg_suggestion_fields();

    $output = '';

    //Tratamiento de errores
    if( ( $error = $cmb->prop('submission_error')) && is_wp_error($error)) {
        $output .= '<h3' . sprintf( __('Hubo un error: %s ', 'jg_form'), '<strong>' . $error->get_error_message() . '</strong>' ) . '</h3>';
    }

    //Notificación usuario si todo es correcto
    if( isset($_GET['post_submited']) && ($post = get_post( absint($_GET['post_submited'])))){
        //Obtener nombre de usuario
        $nombre = get_post_meta($post->ID, 'nombre_id', 1);
        $nombre = $nombre ? " " . $nombre : '';
        $output .= '<h3>' . sprintf( __('Gracias %s por tu sugerencia ', 'jg_form'), esc_html($nombre) ) . '</h3>';

    }

    $output .= cmb2_get_metabox_form($cmb, 'fake-object-id', array('save_button' => 'Enviar Sugerencia'));

    return $output;
}
add_shortcode('jg_create_suggestion_shortcode', 'jg_create_form_suggestion');

function jg_insert_suggestion() {
    //En caso de ue no se envie un formulario, no ejecuta
    if(empty($_POST) || !isset( $_POST['submit-cmb'], $_POST['ogject_id'])) {
        return false;
    }

    //Obtiene los valores del formulario
    $cmb = jg_suggestion_fields();

    $post_data = array();

    //Revisar nonce de seguridad
    if( !isset($_POST[ $cmb->nonce()]) || !wp_verify_nonce($_POST[ $cmb->nonce()], $cmb->nonce() )) {
        return $cmb->prop('submission_error', new WP_Error('security_fail', 'Fallo de seguridad.'));
    }

    if(empty($_POST[nombre_id])){
        return $cmb->prop('submission_error', new WP_Error('post_data_missing', 'Falta tu nombre'));
    }

    //Sanitizar datos
    $valores_sanitizados = $cmb->get_sanitized_values($_POST);
    
    $post_data['meta_input'] = array(
        'jg_forms_inputs_nombre' => $valores_sanitizados[nombre_id],
        'jg_forms_inputs_apellidos' => $valores_sanitizados[apellidos_id],
        'jg_forms_inputs_email' => $valores_sanitizados[email_id],
        'jg_forms_inputs_sugerencia' => $valores_sanitizados[sugerencia_id],

    );

    $post_data['post_type'] = 'sugerencias';

    $nuevo_post = wp_insert_post($post_data, true);
    
    $cmb->save_fields($nuevo_post, 'post', $valores_sanitizados);

}
add_action('cmb2_after_init', 'jg_insert_suggestion');

//Imprimir Sugerencias

function jg_suggestion($texto) {
    
    $args =array(
        'post_type'     => 'sugerencias',
        'order'         => 'DESC',
        'post_per_page' => -1,
        
    );
    echo '<h2 class="tex-center">Sugerencias</h2>';

    echo '<ul id="sugerencias">'; 
    $sugerencias = new WP_Query($args); while($sugerencias->have_posts()): $sugerencias->the_post();
    echo '<li>';
    echo '<p><b>Nombre: </b>' . get_post_meta(get_the_ID(),'jg_forms_inputs_nombre', true);
    echo '<p><b>Apellidos: </b>' . get_post_meta(get_the_ID(),'jg_forms_inputs_apellidos', true);
    echo '<p><b>Email: </b>' . get_post_meta(get_the_ID(),'jg_forms_inputs_email', true);
    echo '<p><b>Sugerencia: </b>' . get_post_meta(get_the_ID(),'jg_forms_inputs_sugerencia', true);
    endwhile; wp_reset_postdata();
    echo '</li>';
}

//Crear shortcode para imprimir sugerencias> [jg_send_suggestion_shortcode]

add_shortcode('jg_send_suggestion_shortcode', 'jg_suggestion');


//Custom Post Type

function create_post_type_suggestions() {
    //Etiquetas para el Post Type
    $labels = array(
        'name'                  => _x( 'Sugerencias', 'Post type general name', 'jg-form' ),
        'singular_name'         => _x( 'Sugerencia', 'Post type singular name', 'jg-form' ),
        'menu_name'             => _x( 'Sugerencias', 'Admin Menu text', 'jg-form'),
        'parent_item_colon'     => __( 'Sugerencia Padre', 'jg-form' ),
        'all_items'             => __( 'Todas las Sugerencias', 'jg-form' ),
        'view_item'             => __( 'Ver Sugerencia', 'jg-form' ),
        'add_new_item'          => __( 'Agregar Nueva Sugerencia', 'jg-form' ),
        'add_new'               => __( 'Agregar Nueva Sugerencia', 'jg-form' ),
        'edit_item'             => __( 'Editar Sugerencia', 'jg-form' ),
        'search_items'          => __( 'Buscar Sugerencia', 'jg-form' ),
        'not_found'             => __( 'No encontrado', 'jg-form' ),
        'not_found_in_trash'    => __( 'No encontrado en la papelera', 'jg-form' ),
    );   
    
    //Otras opciones para el Post Type
    $args = array(
        'label'              => __('sugerencias', 'jg-form'),
        'description'        => __('Sugerencias', 'jg-form'),
        'labels'             => $labels,
        'supports'           => array( 'title', 'editor' ),
        'hierarchical'       => false, 
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_admin_bar'  => true,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-format-chat',
        'can_export'         => true,
        'has_archive'        => true,
        'exclude_from_search'=> false,
        'capability_type'    => 'page',
        'show_in_rest'       => true,
        'rest_base'          => 'sugerencias'
        
    );
      
    register_post_type( 'sugerencias', $args );
    
}
add_action('init', 'create_post_type_suggestions', 0);

//Custom Fields - Metaboxes
/*
function jg_add_metaboxes(){
    add_meta_box('metaboxes', 'Custom fields', 'metaboxes_design', 'sugerencias', 'normal', 'high', null);
}

add_action( 'add_meta_boxes', 'jg_add_metaboxes');

function save_metaboxes($post_id, $post, $update) {
    if(!isset($_POST['meta-box-nonce']) || !wp_verify_nonce( $_POST['meta-box-nonce'], basename(__FILE__)))
    return $post_id;

    if(!current_user_can('edit_post', $post_id))
    return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
    return $post_id;

    $name_metabox ="";
    $last_name_metabox ="";
    $mail_metabox ="";
    $suggest_metabox ="";

    if(isset($_POST['name-metabox'])) {
        $name_metabox = $_POST['name-metabox'];
    }
    update_post_meta($post_id, 'name-metabox', $name_metabox);

    if(isset($_POST['last-name-metabox'])) {
        $last_name_metabox = $_POST['last-name-metabox'];
    }
    update_post_meta($post_id, 'last-name-metabox', $last_name_metabox);

    if(isset($_POST['mail-metabox'])) {
        $mail_metabox = $_POST['mail-metabox'];
    }
    update_post_meta($post_id, 'mail-metabox', $mail_metabox);

    if(isset($_POST['suggest-metabox'])) {
        $suggest_metabox = $_POST['suggest-metabox'];
    }
    update_post_meta($post_id, 'suggest-metabox', $suggest_metabox);
}
add_action('save_post', 'save_metaboxes', 10, 3);

function metaboxes_design($post) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    ?>
    <div>
        <label>Nombre</label>
        <input name="name-metabox" type="text" value="<?php echo get_post_meta($post->ID,'name-metabox', true ) ?>">
        <br/>
    </div>
    <div>
        <label>Apellidos</label>
        <input name="last-name-metabox" type="text" value="<?php echo get_post_meta($post->ID,'last-name-metabox', true ) ?>">
        <br/>
    </div>
    <div>
        <label>Email:</label>
        <input name="mail-metabox" type="text" value="<?php echo get_post_meta($post->ID,'mail-metabox', true ) ?>">
        <br/>
    </div>
    <div>
        <label>Sugerencia</label>
        <textarea name="suggest-metabox"> 
        <?php echo get_post_meta($post->ID,'suggest-metabox', true ) ?>
        </textarea>
        <br/>
    </div>
    <?php
}
*/
if (!defined('ABSPATH')) exit;

//Categoria Bloques Personalizada

function custom_category($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'formularios',
                'title'=> 'Formularios',
                'icon'=> 'heart'
            )
        )
    );
}
add_filter('block_categories', 'custom_category', 10, 2);

// Registro de bloques, scripts y CSS

function register_blocks(){

    //Si gutenberg no existe, salir
    if(!function_exists('register_block_type')){
        return;
    }

    //Registrar los bloques
    wp_register_script(
        'jg-editor-script',
        plugins_url('build/index.js', __FILE__ ),
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
        filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js')
    );

    //Estilos editor
    wp_register_style(
        'jg-editor-styles',
        plugins_url('build/editor.css', __FILE__ ),
        array('wp-edit-blocks'),
        //filemtime( plugin_dir_path( __FILE__ ) . 'build/editor.css')
    );
    //Estilos bloques
    wp_register_style(
        'jg-front-styles',
        plugins_url('build/styles.css', __FILE__ ),
        array(),
        //filemtime( plugin_dir_path( __FILE__ ) . 'build/styles.css')
    );

    //Arreglo bloques
    $blocks = [
        'jg/formularios'
    ];

    //Recorrer bloques y añadimos scripts y styles
    foreach($blocks as $block) {
        register_block_type($block, array(
            'editor_script' => 'jg-editor-script', 
            'editor_style' => 'jg-editor-styles',
            'style' => 'jg-front-styles'
        ));
    }

}
add_action('init', 'register_blocks');