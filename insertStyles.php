<?php 
/*
 * Plugin Name: insertStyles
 */
 

function my_plugin_options() {

    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    load_plugin_textdomain('your-unique-name', false, basename( dirname( __FILE__ ) ) . '/languages' );

    $add_db_url = '';
    $a = [];
    
    /**
     * テーマ内のCSSを取得する
     */
    function checkDir($dirNameInput, $dirNameURL) {

        $dirUrl = $dirNameURL."/".$dirNameInput;
        
        if(is_dir($dirUrl)) {

            if($dirNameSub = opendir($dirUrl)) {
                while(($fileSub = readdir($dirNameSub)) !== false) {
                    if ($fileSub != "." && $fileSub != "..") {
                        if($dirNameInput == "css") {
                            echo "<br>";
                            echo "アイウ";
                            echo $dirNameURL.$dirNameInput.'/'.$fileSub;
                            $GLOBALS['a'][] = $dirNameURL.$dirNameInput.'/'.$fileSub;
                            // print_r($arrayCssUrl);
                            echo "<br>";
                            
                        }
                        
                        checkDir($fileSub, $dirUrl);
                        
                    } 
                }
                closedir($dirNameSub);
            }
        } 
        return false;
    }

    // テーマディレクトリまでの絶対パス
    $dirName = get_template_directory()."/";
    // $dirName = '/Applications/MAMP/htdocs/wordpress/wp-content/themes/techis_wordpress/';

    // テーマディレクトリにアクセス
    if ($dir = opendir($dirName)) {
        // テーマディレクトリ直下のディレクトリを全て読み込む
        while (($file = readdir($dir)) !== false) {
            // .と..のハードリンク以外のディレクトリを読み込む
            if ($file != "." && $file != "..") {
                // テーマ直下ディレクトリ内の全CSSを取得する
                checkDir($file, $dirName);
            }
        } 
        // ディレクトリをクローズする
        closedir($dir);
    }


    print_r($GLOBALS['a']); // デバッグ用


    global $wpdb;

    //  以下、データを削除する処理
    // チェックボックスにチェックが入っている時
    if (!empty($_POST['delete-url-checkbox'])) {
        // チェックが入っているチェックボックスのIDを配列で取得する
        $delete_ids = $_POST['delete-url-checkbox'];

        $res = null;

        // データベースからそれぞれのレコードを削除する
        foreach($delete_ids as $delete_id) {
            $res = $wpdb->delete(
                "{$wpdb->prefix}add_style",
                array(
                    'id' => $delete_id,
                ),
                array(
                    '%d'
                )
            );

            if( 1 <= $res ) {
                // 削除に成功
                echo "削除成功";
            } else {
                // 削除に失敗
                echo "削除失敗";
            }
            echo "<br>";
        }
    
    }

    
    // 以下、データを登録する処理
    if(!empty($_POST['add-input'])) {
        $add_db_url = $_POST['add-input'];
        // データベースにデータを登録する
        $res1 = $wpdb->insert(
            "{$wpdb->prefix}add_style",
            array(
                'name' => '',
                'url' => $add_db_url,
            ),
        );

        if( 1 <= $res1 ) {
            // 削除に成功
            echo "登録成功";
        } else {
            // 削除に失敗
            echo "登録失敗";
        }
        echo "<br>";
    }

    // データベースに登録済みのデータを全て取得する
    $style_query = "SELECT * FROM {$wpdb->prefix}add_style";
    $results = $wpdb->get_results( $style_query, OBJECT );

    print_r($results); // デバッグ用

    // フィールドとオプション名の変数
    $hidden_field_name = 'mt_submit_hidden';
    $url_field_name = '';
    $add_text = '';

    // functions.phpのURL
    $path_name = get_template_directory().'/functions.php';

    // functions.phpの内容を取得する
    $functions_text =  file_get_contents($path_name);

    // データベースにデータがあれば追記処理を行う
    if(isset($results[0])) {

        // 一番最初に書き込む場合の処理
        if ( !preg_match("/insertStyle()/", $functions_text, $matches) ) {
        
                // 追記するテキストを用意
                $add_text .= "function insertStyle() {";

                foreach($results as $result) {

                    // すでに登録済みのデータは何もしない
                    if ( preg_match("/$result->name/", $functions_text, $matches) ) {
                        continue;
                    }

                    $add_text .= "
        if(is_front_page() || is_home()) {
            wp_enqueue_style('{$result->name}', '{$result->url}');
        };";
                }

                    $add_text .= "} 
            add_action('wp_enqueue_scripts', 'insertStyle');";
                    echo "出力：".$add_text;

                //FILE_APPENDフラグはファイルの最後に追記することを意味します。
                //LOCK_EXフラグは他の人が同時にファイルへの書き込みをできないようにすることを意味します。
                file_put_contents($path_name, $add_text, FILE_APPEND | LOCK_EX);

        } else {

                // 関数の始まりの文字
                $add_text .= "function insertStyle() {";

                    foreach($results as $result) {
                        // // すでに登録済みのデータには何もしない
                        // if ( preg_match("/$result->name/", $functions_text, $matches) ) {
                        //     continue;
                        //   }
                        // 文字列を作成する  
                        $add_text .= "
                        if(is_front_page() || is_home()) {
                            wp_enqueue_style('{$result->name}', '{$result->url}');
                        };";
                    }

                    // 関数の終わりの文字
                    $add_text .= "} 
                add_action('wp_enqueue_scripts', 'insertStyle');";
                        echo "<br>";
                        echo "<br>";

                        echo $add_text;

                        echo "<br>";
                        echo "<br>";

                    // 書き換え後の文字列を取得 
                    $fix_add_text = preg_replace('/function\sinsertStyle } 
                    add_action(\'wp_enqueue_scripts\', \'insertStyle\')/', $add_text ,$functions_text);
                    // echo "置き換え前の文字列：：".$add_text;
                    // echo "<br>";
                    // echo "置き換え後の文字列：：".$fix_add_text;

                    // 書き換えを実行する
                    file_put_contents($path_name, $fix_add_text);
                    
        
        }
    }   

    // ここで設定編集画面を表示 -----------------------------------------------------------------------

    echo '<div class="wrap">';

    // ヘッダー

    echo "<h2>" . __( 'Menu Test Plugin Settings', 'menu-test' ) . "</h2>";

    // 設定用フォーム
    
    ?>

    <form name="form1" method="post" action="">
        <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

        <?php

        echo "追加するURLを入力してください。";
        echo "<p><input type='text' name='add-input' list='example' class='input-url'></p>";
        ?>
        <input type="hidden" value="" class="input-url-hidden">
        <datalist id='example'>
            <?php 
                foreach($GLOBALS['a'] as $elem) {
                    
                    echo "<option value=".$elem."></option>";
                }
            ?>
        </datalist>
        <?php
            foreach($results as $result) {
               
                $style_name = $result->name;
        ?>
                <p><span><?= $result->ID; ?></span><br><input class="regist-url" type='text' value="<?= $result->url; ?>" name="<?= $style_name; ?>" readonly></p>
                
                <input type='checkbox' name="delete-url-checkbox[]" value='<?= $result->ID; ?>'>削除する
                
        <?php 
    
                echo "<br>";
            } ?>


        <p class="submit">
        <input type="submit" id="submit-btn" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
        </p>

    </form>
</div>
<?php } ?>
<script>
    $('#example').on('change', function () {
        id = $("#example option[value='" + $(this).val() + "']").data('id');
    });
    jQuery('#submit-btn').click(function() {
        $('#stage').val(id);
        $('#search-btn').submit();
    });
</script>
<?php


function my_plugin_menu() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style('insertStyles_style', $plugin_url.'style.css' );
    add_options_page('My Plugin Options', 'insertStyles','manage_options', 'my-unique-identifier', 'my_plugin_options' );
}

add_action('admin_menu', 'my_plugin_menu');

