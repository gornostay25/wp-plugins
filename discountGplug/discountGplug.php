<?php
/**
 * Plugin Name: Discount Form
 * Description: Discount Form get data plugin
 * Author: gornostay25
 * Version: 1.0
 */



function discountGplug_render_settings_page() {
    echo "<h2>".get_admin_page_title()."</h2>";
    add_option("discountGplug_btoken","");
    add_option("discountGplug_bcid","");
    //add_option("discountGplug_push","");
    echo "<hr><h3>Настройка телеграм уведомлений</h3>";
    discountGplug_render_settings_telegram();

};

function discountGplug_render_settings_telegram() {
    if (isset($_POST["discountGplug_telegram_setup_btn"])){
        if (
            function_exists('current_user_can') && 
            !current_user_can("manage_options")
            ){
            die("Hacker go out");
        };

        if (function_exists("check_admin_referer")){
            check_admin_referer("discountGplug_telegram_setup_form");
        }

        update_option("discountGplug_btoken",$_POST['discountGplug_botToken']);
        update_option("discountGplug_bcid",$_POST['discountGplug_botcid']);

    }

    echo "<form name='discountGplug_telegram_setup' method='post' action='".$_SERVER["PHP_SELF"]."?page=discountGplug-options&saved=true'>";
    if (function_exists('wp_nonce_field')) {
        wp_nonce_field('discountGplug_telegram_setup_form');
    }
    echo "
    <table>
        <tr>
            <td style='text-align: right;font-weight: bold;text-transform: uppercase;'>Bot token</td>
            <td><input type='text' name='discountGplug_botToken' value='".get_option("discountGplug_btoken")."'></td>
            <td style='color:#666'>Используйте <a href='https://t.me/botfather'>BotFather</a>, чтобы узнать его</td>
        </tr>
        <br>
        <tr>
            <td style='text-align: right;font-weight: bold;text-transform: uppercase;'>User client id</td>
            <td><input type='text' name='discountGplug_botcid' value='".get_option("discountGplug_bcid")."'></td>
            <td style='color:#666'>Узнать ваш идентификатор у <a href='https://api.telegram.org/bot".get_option("discountGplug_btoken")."/getUpdates'>бота</a></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type='submit' name='discountGplug_telegram_setup_btn' value='Сохранить'></td>
            <td>&nbsp;</td>
        </tr>
    </table>
    ";
    echo "</form>";
}



function discountGplug_widget_render(){
    $data = discountGplug_getFromdb();
    
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" >
    ';
    echo '<form action="">
    
    <table class="table table-hover">
    <thead>
      <tr>
        <th scope="col">Имя</th>
        <th scope="col">Дата Заказа</th>
        <th scope="col">Способ связи</th>
      </tr>
    </thead>
    <tbody>';
    foreach ($data as $value) {
        echo '<tr onclick="location.href=">
        <th scope="row">'.$value->uname.'</th>
        <td>'.$value->order_time.'</td>
        <td>'.discountGplug_arrayshort2String($value->social).'</td>
      </tr>
     ';
    }
     
    echo '</tbody>
  </table>

  </form>
    ';
};

function discountGplug_render_ulist(){
    if (isset($_POST["discountGplug_userlist_btn"])){
        if (
            function_exists('current_user_can') && 
            !current_user_can("manage_options")
            ){
            die("Hacker go out");
        };

        if (function_exists("check_admin_referer")){
            check_admin_referer("discountGplug_userlist_form");
        }

        //delete operation
        discountGplug_removeFromdb($_POST["discountGplug_userlist_btn"]);


    }


    echo "<h2>User list</h2><hr>";
    $data = discountGplug_getFromdb();

    echo '<div class="table-responsive">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" >
    <form method="post" action="'.$_SERVER["PHP_SELF"].'?page=discountGplug&saved=true">';
    if (function_exists('wp_nonce_field')) {
        wp_nonce_field('discountGplug_userlist_form');
    };
    echo '
    <table class="table">
  <thead>
    <tr>
      <th scope="col">Имя</th>
      <th scope="col">Язык</th>
      <th scope="col">Дата заказа</th>
      <th scope="col">Phone</th>
      <th scope="col">Email</th>
      <th scope="col">Способ связи</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    ';

    foreach ($data as $value) {
        echo '
        <tr>
      <th scope="row">'.$value->uname.'</th>
      <td>'.$value->ulanguage.'</td>
      <td>'.$value->order_time.'</td>
      <td><a href="tel:'.$value->phone.'">'.$value->phone.'</a></td>
      <td><a href="mailto:'.$value->email.'">'.$value->email.'</a></td>
      <td>'.discountGplug_arrayshort2String($value->social).'</td>
      <td><button class="btn btn-danger" type="submit" name="discountGplug_userlist_btn" value="'.$value->rand_id.'">Удалить</button></td>
    </tr>
        ';
    }
    
    echo '
  </tbody>
</table></form></div>
    ';
    
}

function discountGplug_add_admin_page() {
    add_menu_page(
        'Discount form',
          'Discount form User list',
          'manage_options',
          'discountGplug',
          'discountGplug_render_ulist',
          "",
          20
    );
    add_submenu_page( 
        'discountGplug', 
        'Discount form',
         'Discount form options', 
         'manage_options', 
         'discountGplug-options',
          'discountGplug_render_settings_page');
};
function discountGplug_add_widget(){
    wp_add_dashboard_widget( 
        'discountGplug_widget', 
        'Заявки', 
        'discountGplug_widget_render'
     );

};

function discountGplug_activation(){
    global $wpdb;
    //create db teble
/*
rand_id(int) | order_time | ulanguage(text) | uname(text) | phone(text) | email(text) | social(json)
*/
if(!get_option('discountGplug_tables_created', false)) {

    $ptbd_table_name = $wpdb->prefix.'discountGplug_udata';

    if ($wpdb->get_var("SHOW TABLES LIKE '". $ptbd_table_name ."'"  ) != $ptbd_table_name ) {

        $sql  = 'CREATE TABLE `'.$ptbd_table_name.'` (
            `rand_id` INT NOT NULL AUTO_INCREMENT,
            `uname` VARCHAR(255),
            `ulanguage` CHAR(5),
            `phone` VARCHAR(255),
            `email` VARCHAR(255),
            `order_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `social` VARCHAR(255),
            PRIMARY KEY (`rand_id`)
        );';

        $wpdb->query($sql);
        update_option('discountGplug_tables_created', true);
    }
}



};
function discountGplug_uninstall(){
    global $wpdb;
    delete_option("discountGplug_btoken");
    delete_option("discountGplug_bcid");
    $ptbd_table_name = $wpdb->prefix.'discountGplug_udata';

    if ($wpdb->get_var("SHOW TABLES LIKE '". $ptbd_table_name ."'"  ) == $ptbd_table_name ) {

        $sql  = 'DROP TABLE `'.$ptbd_table_name.'`;';

        $wpdb->query($sql);
        update_option('discountGplug_tables_created', false);
    }
};



function discountGplug_write2db($fdata){
    global $wpdb;
    $ptbd_table_name = $wpdb->prefix.'discountGplug_udata';
    $r = $wpdb->insert(
        $ptbd_table_name,
        array
        (
            "uname"=>$fdata["name"],
            "ulanguage"=>$fdata["language"],
            "phone"=>$fdata["phone"],
            "email"=>$fdata["email"],
            "social"=>$fdata["social"],
        ),
        array(
            "%s",
            "%s",
            "%s",
            "%s",
            "%s"
        )
    );
    
    discountGplug_Send2TG($fdata);
    return '{"ok":"ok"}';
}

function discountGplug_Send2TG($data){
    $MDtemp = "**Новый заказ**(".$data["language"].")\n".$data["name"]."\nТелефон: `".$data["phone"]."`\nEmail: `".$data["email"]."`\n".discountGplug_arrayshort2String($data["social"]);
    if (get_option("discountGplug_btoken")){
        $Targs = array("body"=>array(
            "chat_id"=>"".get_option("discountGplug_bcid"),
            "text"=>$MDtemp,
            "parse_mode"=>"Markdown"
        ));
        return wp_remote_retrieve_body(wp_remote_get("https://api.telegram.org/bot".get_option("discountGplug_btoken")."/sendMessage",$Targs));
    }
    
}

function discountGplug_arrayshort2String($arofsh){
    
    if (!is_array($arofsh)){
        $arofsh = json_decode($arofsh);
    }
    
    $final = "";
    foreach ($arofsh as $value) {
        switch ($value) {
            case 'wa':
                $final .= " WhatsApp ";
                break;
            case 'tg':
                $final .= " Telegram ";
                break;
            case 'vb':
                $final .= " Viber ";
                break;
            case 'ml':
                $final .= " Email ";
                break;  
        }
    
    }
    return $final;
}


function discountGplug_removeFromdb($id){
    global $wpdb;
    $ptbd_table_name = $wpdb->prefix.'discountGplug_udata';
    $wpdb->delete( $ptbd_table_name, array("rand_id"=>$id));

}
function discountGplug_getFromdb(){
    global $wpdb;
    $ptbd_table_name = $wpdb->prefix.'discountGplug_udata';
    $reslt = $wpdb->get_results( "SELECT * FROM `".$ptbd_table_name."` ORDER BY `rand_id` DESC" );
    return $reslt;

}

register_activation_hook(__FILE__,"discountGplug_activation");
register_uninstall_hook( __FILE__,"discountGplug_uninstall");

add_filter( 'discountGplug_api', 'discountGplug_api_hook',8);
function discountGplug_api_hook($fdata) {
  return discountGplug_write2db($fdata);
}

add_action( 'wp_dashboard_setup', 'discountGplug_add_widget' );
add_action( 'admin_menu', 'discountGplug_add_admin_page' );