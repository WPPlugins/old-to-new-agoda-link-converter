<?php
global $wpdb;
if(isset($_POST['oscimp_hidden']) && $_POST['oscimp_hidden'] == 'Y' && check_admin_referer( 'update_settings' )) {

    $max_links_per_page = sanitize_text_field($_POST['max_links_per_page']);
    $exclude_post_ids = sanitize_text_field($_POST['exclude_post_ids']);
    $affiliate_id = sanitize_text_field($_POST['affiliate_id']);
    $sql = "UPDATE ".$wpdb->prefix."agoda_settings SET
                max_links_per_page = '".$max_links_per_page."',
                affiliate_id = '".$affiliate_id."',
                exclude_post_ids='".$exclude_post_ids."'
                WHERE setting_id = '1'";
    $wpdb->query($sql);
}

?>

<BR><BR>
<?php

$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_settings  WHERE setting_id = '1'", ARRAY_A);
$links_per_page = ($row['max_links_per_page'] == 0 ? '' : $row['max_links_per_page']);
$affiliate_id = (isset($row['affiliate_id'])  ? $row['affiliate_id']  : '');
$image_path = plugins_url( 'agoda-logo.png', dirname( __FILE__ ).'/images/' );

?>

<img src="<?php echo $image_path ;?>">
<BR><BR>

<?php if(isset($_GET['success'])): ?>
<div class="updated" style="margin-left:0px;">
    Settings changed
</div><BR><BR>
<?php endif;?>

<?php if(!isset($_GET['success'])): ?>
<div class="updated" style="margin-left:0px;">
    Please enter your credentials to convert Agoda links to the new structure
</div><BR><BR>
<?php endif;?>


<?php if( current_user_can('editor') || current_user_can('administrator') ) {  ?>
<form name="agoda_insert" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table width="893" height="167" style="border:1px solid gray;">

        <?php
        wp_nonce_field( 'update_settings' );

        ?>
        <input type="hidden" name="oscimp_hidden" value="Y">

        <tr>
            <td align="left"> <B>Maximum links per page</B></td>
            <td align="left">
                <input type="text" name="max_links_per_page" value="<?php echo $links_per_page;?>"  autocomplete="off" style="width: 400px;">

            </td>
        </tr>


        <tr>
            <td align="left"> <B>Affiliate id</B></td>
            <td align="left">
                <input type="text" name="affiliate_id" value="<?php echo $affiliate_id;?>"  autocomplete="off" style="width: 400px;">

            </td>
        </tr>

        <tr>
            <td align="left"> <B>Exclude Post Ids (comma seperated)</B></td>
            <td align="left">
                <input type="text" name="exclude_post_ids" value="<?php  echo $row['exclude_post_ids'];?>"  autocomplete="off" style="width: 400px;">

            </td>
        </tr>




        <tr>
            <td align="right">


            </td>
            <td align="right">

                <input type="submit" name="submit" value="Save" />
            </td>
        </tr>
    </table>
</form>
<?php } ?>
