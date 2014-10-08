<?php


/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_2048($db){

    $sql ="
        SELECT
            `menu_id`
        FROM
            `system_menus`
        WHERE
            `menu_controller` = 'preferences'
            AND
            `menu_action` = 'favorite_lists'
    ";

    $result = $db->query($sql);

    if( $db->errno ) {
        return 'Failed to fetch Favorite Lists Menu menu '.$db->error;
    }


    $favoriteListsMenu = $result->fetch_assoc();

    // Update menu for user location

    $sql = "
        UPDATE
            `system_menus`
        SET
            `menu_inherit` = '" . $favoriteListsMenu['menu_id'] . "',
            `system_admin_only`     = '0'
        WHERE
            `menu_controller` = 'preferences'
            AND
            `menu_action` IN ('common_complaints', 'favorite_diagnoses', 'favorite_test_codes', 'favorite_test_groups', 'favorite_prescriptions');
    ";

    $result = $db->query($sql);

    if( $db->errno ) {
        return 'Failed to update Favorite Lists Menu '.$db->error;
    }

    return true;
}


$errorMessage = db_2048($db);
