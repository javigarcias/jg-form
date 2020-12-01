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
        
    );
      
    register_post_type( 'sugerencias', $args );
    
}
add_action('init', 'create_post_type_suggestions', 0);

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

if (!defined('ABSPATH')) exit;

//Categoria Personalizada
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