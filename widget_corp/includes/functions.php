<?php

// ------------------ //
// ----- MISC ------- //
// ------------------ //

// REDIRECT TO SPECIFIED PAGE
function redirect_to($new_location) {
    header ("Location: " . $new_location );
    exit;
}

// ERROR CHECK QUERY
function confirm_query($result_set) {
    If (!$result_set) {
        die ("Query failed");
    }
}



// ------------------ //
// ---- SUBJECTS ---- //
// ------------------ //

// SQL QUERY TO GET ALL SUBJECTS
function get_all_subjects($access) {
    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;

    if ($access == "admin") {
        // GET ALL SUBJECTS ORDERED BY POSITION
        $subjectsquery = "SELECT * FROM subjects ORDER BY position ASC";
    } elseif ($access == "public" )  {
        // GET ALL (VISIBLE) SUBJECTS ORDERED BY POSITION
        $subjectsquery = "SELECT * FROM subjects WHERE visible = 1 ORDER BY position ASC";

    }




    // STORE RESULT
    $subjectsresult = mysqli_query($connection, $subjectsquery);
    // CHECK FOR ERRORS
    confirm_query($subjectsresult);
    // RETURN DATA FROM FUNCTION
    return $subjectsresult;
}

// SQL QUERY TO GET PAGES RELATED TO SUBJECT
function get_related_pages_for_subject ($subject_id, $access) {
    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;

    if ( $access == "admin") {
        // GET ALL RELATED PAGES FOR EACH SUBJECT
        $pagesquery = "SELECT id, menu_name FROM pages WHERE  subject_id = {$subject_id} ORDER BY position ASC";

    } elseif ( $access == "public") {
        // GET ALL (VISIBLE) RELATED PAGES FOR EACH SUBJECT
        $pagesquery = "SELECT id, menu_name FROM pages WHERE visible = 1 AND subject_id = {$subject_id} ORDER BY position ASC";

    }





    $pagesresult = mysqli_query($connection, $pagesquery);
    // CHECK FOR ERRORS
    confirm_query($pagesquery);
    // RETURN DATA FROM FUNCTION
    return $pagesresult;
}

// SQL QUERY TO GET SUBJECT BY ID
function get_subject_by_id($subject_id) {
    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;
    // ESCAPE CHARACTERS TO AVOID SQL INJECTION (HACKING)
    $safe_subject_id = mysqli_real_escape_string($connection, $subject_id);
    // GET SUBJECTS BY ID
    $subjectquery = "SELECT * FROM subjects WHERE id = {$safe_subject_id} LIMIT 1";
    // STORE RESULT
    $subjectresult = mysqli_query($connection, $subjectquery);
    // CHECK FOR ERRORS
    confirm_query($subjectresult);
    // GET THE RETURNED SUBJECT RECORD AS AN ASSOCIATED ARRAY
    if ($subject = mysqli_fetch_assoc($subjectresult)) {
        // RETURN DATA FROM FUNCTION
        return $subject;
    } else {
        // RETURN NULL FROM FUNCTION
        return null;
    }
}



// ------------------ //
// ----- PAGES ------ //
// ------------------ //

// SQL QUERY TO GET A PAGE BY ID
function get_page_by_id ($page_id) {
    // SQL QUERY TO GET PAGE BY ID

    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;
    // ESCAPE CHARACTERS TO AVOID SQL INJECTION (HACKING)
    $safe_page_id = mysqli_real_escape_string($connection, $page_id);
    // GET PAGE BY ID
    $pagequery = "SELECT * FROM pages WHERE id = {$safe_page_id} LIMIT 1";
    // STORE RESULT
    $pageresult = mysqli_query($connection, $pagequery);
    // CHECK FOR ERRORS
    confirm_query($pagequery);
    // GET THE RETURNED PAGE RECORD AS AN ASSOCIATED ARRAY
    if ($page = mysqli_fetch_assoc($pageresult)) {
        // RETURN DATA FROM FUNCTION
        return $page;
    } else {
        // RETURN NULL FROM FUNCTION
        return null;
    }

}

// SQL QUERY TO GET ALL RELATED PAGES BY SUBJECT ID
function get_all_pages_by_subject_id($subject_id) {
    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;
    // GET ALL RELATED PAGES ORDERED BY POSITION
    $pagesquery = "SELECT * FROM pages WHERE subject_id = {$subject_id} ORDER BY position ASC";
    // STORE RESULT
    $pagesresult = mysqli_query($connection, $pagesquery);
    // CHECK FOR ERRORS
    confirm_query($pagesresult);
    // RETURN DATA FROM FUNCTION
    return $pagesresult;
}

// SQL QUERY TO FIND DEFAULT PAGE FOR SUBJECT
function get_default_page_for_subject ( $subject_id ) {
    $page_set = get_related_pages_for_subject( $subject_id, "public" );
    if ( $page = mysqli_fetch_assoc( $page_set ) ) {
        // THERE WAS A SET OF RELATED PAGES RETURNED FOR THE SPECIFIED SUBJECT
        return $page;
    } else {
        return null;
    }
}



// ------------------ //
// ----- ADMINS ----- //
// ------------------ //

// SQL QUERY TO GET ALL ADMINS
function get_all_admins() {

    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;
    // GET ALL SUBJECTS ORDERED BY POSITION
    $adminsquery = "SELECT * FROM admins ORDER BY username ASC";
    // STORE RESULT
    $adminsresult = mysqli_query($connection, $adminsquery);
    // CHECK FOR ERRORS
    confirm_query($adminsresult);
    // RETURN DATA FROM FUNCTION
    return $adminsresult;

}

// SQL QUERY TO GET A ADMIN BY ID
function get_admin_by_id ($admin_id) {
    // SQL QUERY TO GET PAGE BY ID

    // GET THE CONNECTION VARIABLE AND MAKE IT GLOBAL
    global $connection;
    // ESCAPE CHARACTERS TO AVOID SQL INJECTION (HACKING)
    $safe_admin_id = mysqli_real_escape_string($connection, $admin_id);
    // GET ADMIN BY ID
    $adminquery = "SELECT * FROM admins WHERE id = {$safe_admin_id} LIMIT 1";
    // STORE RESULT
    $adminresult = mysqli_query($connection, $adminquery);
    // CHECK FOR ERRORS
    confirm_query($adminquery);
    // GET THE RETURNED ADMIN RECORD AS AN ASSOCIATED ARRAY
    if ($admin = mysqli_fetch_assoc($adminresult)) {
        // RETURN DATA FROM FUNCTION
        return $admin;
    } else {
        // RETURN NULL FROM FUNCTION
        return null;
    }

}



// ------------------ //
// --- ENCRYPTION --- //
// ------------------ //

// ADMIN PASSWORD ENCRYPTION
function password_encrypt($password) {

    $hash_format = "$2y$10$";                   // Tells PHP to use Blowfish Encryption with a "cost" of 10
    $salt_length = 22;                          // Blowfish salts should be 22 characters or more
    $salt = generate_salt ( $salt_length );
    $format_and_salt = $hash_format . $salt;
    $hash = crypt($password,$format_and_salt);
    return $hash;

}

// ADMIN PASSWORD SALT
function generate_salt($length) {
    // Not 100% Unique but is 100% random
    // MD5 returns 32 characters
    $unique_rantom_string = md5(uniqid(mt_rand(), true));

    // Valid characters for a salt are [a-aA-Z0-9./]
    $base64_string = base64_encode($unique_rantom_string);

    // But not '+' which is valid in base64 encoding
    $modified_base64_string = str_replace('+', '.', $base64_string);

    // Truncate string to the correct length
    $salt = substr($modified_base64_string, 0, $length);

    return $salt;
}