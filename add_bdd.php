<?php

/**
 * Récup en bdd les users, avec les new ids.
 * 
 * Créer un fichier new_users.csv avec les utilisateurs de la bdd.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function get_users($bdd, $dossier){
    if (!file_exists("/var/lib/mysql-files/$dossier/new_users.csv")) {
        $reqGetUsers = "SELECT id, user_email 
            FROM `hr8qI_users`
            INTO OUTFILE '/var/lib/mysql-files/$dossier/new_users.csv'
            FIELDS TERMINATED BY ','
            LINES TERMINATED BY '\\n'";
            //suppression WHERE id > 13
        
        $bdd->exec($reqGetUsers);
    }
}

/**
 * Récup en bdd les posts, avec les new ids.
 * 
 * Crée un fichier new_posts.csv avec les posts de la bdd.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function get_posts($bdd, $dossier, $post_type=''){
    if (file_exists("/var/lib/mysql-files/$dossier/new_posts.csv")) {
        unlink("/var/lib/mysql-files/$dossier/new_posts.csv");
    }

    $reqGetPosts = "SELECT *
        FROM `hr8qI_posts`
        WHERE post_type LIKE '%$post_type%'
        INTO OUTFILE '/var/lib/mysql-files/$dossier/new_posts.csv'
        FIELDS TERMINATED BY ','
        LINES TERMINATED BY '\\n'";
    
    $bdd->exec($reqGetPosts);
}

/**
 * Récup en bdd les postmeta.
 * 
 * Crée un fichier new_postmeta.csv avec les postmeta de la bdd.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function get_postsMeta($bdd, $dossier) {
    if (file_exists("/var/lib/mysql-files/$dossier/new_postmeta.csv")) {
        unlink("/var/lib/mysql-files/$dossier/new_postmeta.csv");
    }

    $reqGetPostMeta = "SELECT *
        FROM `hr8qI_postmeta`
        INTO OUTFILE '/var/lib/mysql-files/$dossier/new_postmeta.csv'
        FIELDS TERMINATED BY ','
        LINES TERMINATED BY '\\n'";
    $bdd->exec($reqGetPostMeta);
}

/**
 * Récup en bdd les contests, avec les new ids.
 * 
 * Crée un fichier contest/new_post.csv avec les posts de la bdd.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function get_contest($bdd, $dossier) {
    $reqGetContest = "SELECT *
        FROM `hr8qI_posts`
        WHERE `post_type` = 'contest'
        INTO OUTFILE '/var/lib/mysql-files/$dossier/contest/new_post.csv'
        FIELDS TERMINATED BY ','
        LINES TERMINATED BY '\\n'";
    $bdd->exec($reqGetContest);
}

/**
 * Change les IDs dans le fichier usermeta.
 * 
 * Crée un fichier meta_user_updated.csv avec les usermeta de la bdd.
 *
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function change_idUserMeta($dossier){
    // Change ids in usermeta
    $old = fopen("/var/lib/mysql-files/$dossier/old_users.csv", 'r');
    $meta = fopen("./$dossier/usermeta.csv", 'r');

    $replace_id = [];

    fgetcsv($old, null, ';');
    fgetcsv($meta, null, ';');

    while ($ligne_old = fgetcsv($old, null, ';')) {
        $old_data = explode(',', $ligne_old[0]);
        
        $new = fopen("/var/lib/mysql-files/$dossier/new_users.csv", 'r');

        while ($ligne_new = fgetcsv($new, null, ';')) {
            $data = explode(',', $ligne_new[0]);

            if (!strcmp(trim($data[1], '"'),trim($old_data[4], '"'))) {
                $replace_id[$old_data[0]] = trim($data[0], '"');
                break;
            }
        }
        fclose($new);
    }
    // var_dump($replace_id);

    // Rewrite usermeta with new IDs
    echo "Réécriture du fichier meta avec les nouveaux IDs\n";
    $output = fopen("/var/lib/mysql-files/$dossier/meta_user_updated.csv", 'w');
    fputcsv($output, ["id","user_id","meta_key","meta_value"], ',');

    while ($ligne_meta = fgetcsv($meta, null, ',')) {
        if (isset($replace_id[$ligne_meta[1]])) {
            $ligne_meta[1] = $replace_id[$ligne_meta[1]];
        }
        
        // Replace Kg0768AR0
        $ligne_meta[2] = str_replace("Kg0768AR0", "hr8qI", $ligne_meta[2]);

        fputcsv($output, $ligne_meta, ',');
    }

    fclose($meta);
    fclose($output);
}

/**
 * Change les IDs des auteurs dans le fichier posts.
 * 
 * Crée un fichier old_posts.csv avec les posts de la bdd.
 *
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function change_authorId($dossier){
    $post = fopen("./$dossier/old_posts.csv", 'r');

    $author_id = [];

    fgetcsv($post, null, ';');

    while ($ligne_post = fgetcsv($post, null, ';')) {
        $post_data = explode(',', $ligne_post[0]);
        if (!array_key_exists($post_data[1], $author_id)) {
            
            $old = fopen("/var/lib/mysql-files/$dossier/old_users.csv", 'r');
            fgetcsv($old, null, ';');
            
            while ($ligne_old = fgetcsv($old, null, ';')) {
                $old_data = explode(',', $ligne_old[0]);
                
                if (strcmp($old_data[0], $post_data[1]) == 0) {
                    $new = fopen("/var/lib/mysql-files/$dossier/new_users.csv", 'r');

                    while ($ligne_new = fgetcsv($new, null, ';')) {
                        $data = explode(',', $ligne_new[0]);
                
                        if (!strcmp($data[1],$old_data[4])) {
                            $author_id[$post_data[1]] = $data[0];
                            break;
                        }
                    }
                    fclose($new);
                }
                
            }
            fclose($old);
        }
    }
    fclose($post);

    $post = fopen("./$dossier/old_posts.csv", 'r');
    fgetcsv($post, null, ';');
    $output = fopen("/var/lib/mysql-files/$dossier/old_posts.csv", 'w');
    fputcsv($output, ["post_author","post_date","post_date_gmt","post_content","post_title","post_excerpt","post_status","comment_status","ping_status","post_password","post_name","to_ping","pinged","post_modified","post_modified_gmt","post_content_filtered","post_parent","guid","menu_order","post_type","post_mime_type","comment_count"], ',');

    while ($ligne_meta = fgetcsv($post, null, ',')) {
        // Remplacer ID si trouvé
        if (isset($author_id[$ligne_meta[1]])) {
            $ligne_meta[1] = $author_id[$ligne_meta[1]];
        }

        // Écriture ligne par ligne avec virgule comme séparateur
        fputcsv($output, $ligne_meta, ',');
    }

    fclose($post);
    fclose($output);
}

/**
 * Change les IDs dans le fichier postmeta.
 * 
 * Crée un fichier postmeta.csv avec les postmeta de la bdd.
 *
 * @param string $dossier Nom du dossier ou se trouve.
 * @param string $categorie Categorie du postmeta (contest ou autre).
 *
 * @return void
 */
function change_idPostMeta($dossier, $categorie){
    if ($categorie == 'contest') {
        $old = fopen("./$dossier/contest/old_posts.csv", 'r');
        $new = fopen("/var/lib/mysql-files/$dossier/contest/new_post.csv", 'r');

        // Output file for updated contest data
        $output = fopen("/var/lib/mysql-files/$dossier/contest/contest_data.csv", 'w');
        $meta = fopen("./$dossier/contest/old_fca_cc_activity_tbl.csv" , 'r');
    } else {
        $old = fopen("./$dossier/old_posts.csv", 'r');
        $new = fopen("/var/lib/mysql-files/$dossier/new_posts.csv", 'r');

        if ($categorie == 'postmeta') {
            $output = fopen("/var/lib/mysql-files/$dossier/postmeta.csv", 'w');
            $meta = fopen("./$dossier/postmeta.csv", 'r');
        } else {
            $output = fopen("/var/lib/mysql-files/$dossier/slide/new_slider.csv", 'w');
            $meta = fopen("./$dossier/slide/wgl_slides.csv", 'r');
        }
    }
    $replace_id = [];

    $first_meta_line = fgetcsv($meta, null, ';');

    fgetcsv($old, null, ';');

    while ($ligne_old = fgetcsv($old, null, ';')) {
        $old_data = explode(',', $ligne_old[0]);

        while ($ligne_new = fgetcsv($new, null, "\n")) {
            $data = explode(',', $ligne_new[0]);

            if ( strcasecmp(trim($data[2], '"'),trim($old_data[2], '"'))==0 && strcasecmp(trim($data[5], '"'),trim($old_data[5], '"'))==0 && strcasecmp(trim($data[11], '"'),trim($old_data[11], '"'))==0 ) {
                $replace_id[$old_data[0]] = $data[0];
                break;
            }
        }
    }
    fclose($new);

    // Réécriture du fichier meta avec les nouveaux IDs
    fputcsv($output, $first_meta_line, ',');

    while ($ligne_meta = fgetcsv($meta, null, ',')) {
        // Remplacer ID si trouvé
        if (isset($replace_id[$ligne_meta[1]])) {
            $ligne_meta[1] = $replace_id[$ligne_meta[1]];
        }
        if ( $categorie == 'slide' && isset($replace_id[$ligne_meta[2]]) ) {
            $ligne_meta[2] = $replace_id[$ligne_meta[2]];
        }

        // Écriture ligne par ligne avec virgule comme séparateur
        fputcsv($output, $ligne_meta, ',');
    }
    fclose($meta);
    fclose($output);
}

/**
 * Ajoute en bdd les users.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function add_users($bdd, $dossier) {
    $reqPushUser = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/old_users.csv'
        INTO TABLE `hr8qI_users`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (@ID, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`)
        SET user_login = user_login,
            user_pass = user_pass,
            user_nicename = user_nicename,
            user_email = user_email,
            user_url = user_url,
            user_registered = user_registered,
            user_activation_key = user_activation_key,
            user_status = user_status,
            display_name = display_name";

    $bdd->exec($reqPushUser);
}

/**
 * Ajoute en bdd les usermeta.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function add_usermeta($bdd, $dossier){
    change_idUserMeta($dossier);

    // Add usermeta to database
    $reqPushUserMeta = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/meta_user_updated.csv'
        INTO TABLE `hr8qI_usermeta`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (@id, `user_id`, `meta_key`, `meta_value`)
        SET user_id = user_id,
            meta_key = meta_key,
            meta_value = meta_value";
    $bdd->exec($reqPushUserMeta);
}

/**
 * Ajoute en bdd les posts.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function add_posts($bdd, $dossier){
    change_authorId($dossier);

    $reqPushPost = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/old_posts.csv'
        INTO TABLE `hr8qI_posts`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (@ID, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`)
        SET post_author = post_author,
            post_date = post_date,
            post_date_gmt = post_date_gmt,
            post_content = post_content,
            post_title = post_title,
            post_excerpt = post_excerpt,
            post_status = post_status,
            comment_status = comment_status,
            ping_status = ping_status,
            post_password = post_password,
            post_name = post_name,
            to_ping = to_ping,
            pinged = pinged,
            post_modified = post_modified,
            post_modified_gmt = post_modified_gmt,
            post_content_filtered = post_content_filtered,
            post_parent = post_parent,
            guid = guid,
            menu_order = menu_order,
            post_type = post_type,
            post_mime_type = post_mime_type,
            comment_count = comment_count";
    
    $bdd->exec($reqPushPost);
}

/**
 * Met à jour les données des posts/postmeta si la date de modification est plus récente.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function updatePostData($bdd, $dossier) {
    $lastPostsFile = fopen("/var/lib/mysql-files/$dossier/last_posts.csv", "r");
    $newPostsFile = fopen("/var/lib/mysql-files/$dossier/new_posts.csv", "r");

    $updatedPostsFile = fopen("/var/lib/mysql-files/$dossier/updated_posts.csv", "w");
    $addPostFile = fopen("/var/lib/mysql-files/$dossier/add_posts.csv", "w");

    $postsToUpdate = [];
    $postsToAdd = [];

    fgetcsv($lastPostsFile, 0, ',');

    while ($oldPost = fgetcsv($lastPostsFile, 0, ',')) {
        $lastPostData = explode(',', $oldPost[0]);
        $postsToAdd[] = $lastPostData[0];

        while ($newPost = fgetcsv($newPostsFile, 0, ',')) {
            $newPostData = explode(',', $newPost[0]);

            if (strcasecmp(trim($newPostData[2], '"'), trim($lastPostData[2], '"')) == 0 && strcasecmp(trim($newPostData[5], '"'), trim($lastPostData[5], '"')) == 0 && strcasecmp(trim($newPostData[11], '"'), trim($lastPostData[11], '"')) == 0) {
                $postsToUpdate[$newPostData[0]] = $lastPostData[0];
            }
        }
    }

}

/**
 * Ajoute en bdd les postmeta.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function add_postmeta($bdd, $dossier){
    change_idPostMeta($dossier, 'postmeta');

    $reqPushPostMeta = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/postmeta.csv'
        INTO TABLE `hr8qI_postmeta`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (@id, `post_id`, `meta_key`, `meta_value`)
        SET post_id = post_id,
            meta_key = meta_key,
            meta_value = meta_value";
    
    $bdd->exec($reqPushPostMeta);
}

/**
 * Remplace les dates (0000-00-00 00:00:00) dans les fichiers forminator.
 * 
 * Créer un fichier old_views.csv et old_entry_meta.csv avec les dates corrigées.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function add_date_forminator($dossier) {
    $tab_fichier = [
        "old_views.csv" => [
            'bonne_date' => 5,
            'up_date' => 6,
        ],
        "old_entry_meta.csv" => [
            'bonne_date' => 4,
            'up_date' => 5,
        ],
    ];

    foreach ($tab_fichier as $fichier => $cases) {
        $filename = "./$dossier/forminator/".$fichier;
        $filetmp = "/var/lib/mysql-files/$dossier/".$fichier;

        if (($handle = fopen($filename, 'r')) !== false && ($tempHandle = fopen($filetmp, 'w')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',', '"')) !== false) {
                if ($data[$cases['up_date']] == '0000-00-00 00:00:00') {
                    $data[$cases['up_date']] = $data[$cases['bonne_date']];
                }
                fputcsv($tempHandle, $data, ',', '"');
            }
            fclose($tempHandle);
            fclose($handle);
        } else {
            echo "Impossible d'ouvrir le fichier.";
        }
    }
}

/**
 * Ajoute les données forminator en bdd.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve.
 *
 * @return void
 */
function add_data_forminator($bdd, $dossier) {
    // delete old views
    $reqSuppViews = "DELETE FROM `hr8qI_frmt_form_views`";
    $bdd->exec($reqSuppViews);

    // add new views
    $reqAddNewViews = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/old_views.csv'
        INTO TABLE `hr8qI_frmt_form_views`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (`view_id`, `form_id`, `page_id`, `ip`, `count`, `date_created`, `date_updated`)
        SET view_id = view_id,
            form_id = form_id,
            page_id = page_id,
            ip = ip,
            count = count,
            date_created = date_created,
            date_updated = date_updated";
    $bdd->exec($reqAddNewViews);

    // delete old reports
    $reqSuppReports = "DELETE FROM `hr8qI_frmt_form_reports`";
    $bdd->exec($reqSuppReports);

    // add new reports
    $reqAddReports = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/old_reports.csv'
        INTO TABLE `hr8qI_frmt_form_reports`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (`report_id`, `report_value`, `status`, `date_created`, `date_updated`)
        SET report_id = report_id,
            report_value = report_value,
            status = status,
            date_created = date_created,
            date_updated = date_updated";
    $bdd->exec($reqAddReports);

    // delete old entry
    $reqSuppEntry = "DELETE FROM `hr8qI_frmt_form_entry`";
    $bdd->exec($reqSuppEntry);

    // add new entry
    $reqAddEntry = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/old_entry.csv'
        INTO TABLE `hr8qI_frmt_form_entry`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (`entry_id`, `entry_type`, `draft_id`, `form_id`, `is_spam`, `date_created`)
        SET entry_id = entry_id,
            entry_type = entry_type,
            draft_id = draft_id,
            form_id = form_id,
            is_spam = is_spam,
            date_created = date_created";
    $bdd->exec($reqAddEntry);

    // delete old entry meta
    $reqSuppEntryMeta = "DELETE FROM `hr8qI_frmt_form_entry_meta`";
    $bdd->exec($reqSuppEntryMeta);

    // add new entry meta
    $reqAddEntryMeta = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/old_entry_meta.csv'
        INTO TABLE `hr8qI_frmt_form_entry_meta`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (`meta_id`, `entry_id`, `meta_key`, `meta_value`, `date_created`, `date_updated`)
        SET meta_id = meta_id,
            entry_id = entry_id,
            meta_key = meta_key,
            meta_value = meta_value,
            date_created = date_created,
            date_updated = date_updated";
    $bdd->exec($reqAddEntryMeta);
}

/**
 * Ajoute les données du concours en bdd.
 *
 * @param string $bdd Nom de la bdd.
 * @param string $dossier Nom du dossier ou se trouve les fichiers.
 *
 * @return void
 */
function add_data_contest($bdd, $dossier) {
    // delete old contest
    $reqSuppContest = "DELETE FROM `hr8qI_fca_cc_activity_tbl`";
    $bdd->exec($reqSuppContest);

    // add new contest
    $reqAddContest = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/contest/contest_data.csv'
        INTO TABLE `hr8qI_fca_cc_activity_tbl`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (`id`, `contest`, `name`, `email`, `time`, `ip`, `status`)
        SET id = id,
            contest = contest,
            name = name,
            email = email,
            time = time,
            ip = ip,
            status = status";
    $bdd->exec($reqAddContest);
}

function add_slide($bdd, $dossier) {
    // delete old slide
    $reqSuppSlide = "DELETE FROM `hr8qI_wgl_slides`";
    $bdd->exec($reqSuppSlide);

    // add new slide
    $reqAddSlide = "LOAD DATA INFILE '/var/lib/mysql-files/$dossier/slide/new_slider.csv'
        INTO TABLE `hr8qI_wgl_slides`
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"'
        LINES TERMINATED BY '\\n'
        IGNORE 1 ROWS
        (`id`, `desktop_image_id`, `mobile_image_id`, `link`, `end_date`)
        SET id = id,
            desktop_image_id = desktop_image_id,
            mobile_image_id = mobile_image_id,
            link = link,
            end_date = end_date";
    $bdd->exec($reqAddSlide);
}



function addHeaders($fileHeaders, $filename, $bdd, $dossier) {
    if ($filename == 'posts') {
        if (file_exists("./$dossier/maj/maj_posts.csv")) {
            unlink("./$dossier/maj/maj_posts.csv");
        }

        // get_posts($bdd, $dossier);
        if (file_exists("/var/lib/mysql-files/$dossier/maj_posts.csv")) {
            unlink("/var/lib/mysql-files/$dossier/maj_posts.csv");
        }
        $reqGetPosts = "SELECT ID, post_date, post_title, post_type
            FROM `hr8qI_posts`
            INTO OUTFILE '/var/lib/mysql-files/$dossier/maj_posts.csv'
            FIELDS TERMINATED BY ';'
            ENCLOSED BY '\"'
            ESCAPED BY '\"'
            LINES TERMINATED BY '\\n'";
        
        $bdd->exec($reqGetPosts);

        $file = "./$dossier/maj/new_posts.csv";
        $contenu = file_get_contents("/var/lib/mysql-files/$dossier/maj_posts.csv");
    } else {
        if (file_exists("./$dossier/maj/new_postmeta.csv")) {
            unlink("./$dossier/maj/new_postmeta.csv");
        }

        get_postsMeta($bdd, $dossier);
        $file = "./$dossier/maj/new_postmeta.csv";
        $contenu = file_get_contents("/var/lib/mysql-files/$dossier/new_postmeta.csv");
    }
    
    file_put_contents($file, $fileHeaders. "\n" .$contenu);
}

/**
 * Transforme les données CSV dans un tableau associatif
 * 
 * @param string $filename Chemin vers le fichier CSV
 * 
 * @return array Tableau des données
 */
function csvToTab($filename) {
    echo $filename;
    $data = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        $headers = fgetcsv($handle, null, ";");
        while (($row = fgetcsv($handle, null, ";"))) {
            $data[] = array_combine($headers, $row);
        }
        fclose($handle);
    }
    return $data;
}

/**
 * Transforme les tableau en fichier csv
 * 
 * @param array $tab Tableau à transformer
 * @param string $name Nom de fichier csv à créer
 * 
 * @return void
*/
function tabToCsv($tab, $name) {
    $file = fopen($name, 'w');
    $cpt = 0;

    foreach ($tab as $ligne) {
        if ($cpt == 0) {
            fputcsv($file, array_keys($ligne));
            $cpt++;
        }
        fputcsv($file, $ligne);
    }
    fclose($file);
}

/**
 * Trouve un post dans V2 qui correspond au post V1
 * @param array $postV1 Post de la version 1
 * @param array $postsV2 Tous les posts de la version 2
 * @return array|null Post trouvé ou null
 */
function trouverPostCorrespondant($postV1, $postsV2) {
    foreach ($postsV2 as $postV2) {
        if ($postV1['post_date'] === $postV2['post_date'] && 
            $postV1['post_title'] === $postV2['post_title'] &&
            $postV1['post_type'] === $postV2['post_type']) {
            return $postV2;
        }
    }
    return null;
}

/**
 * Parcours les posts et regarde s'il y a des posts à modifier ou ajouter.
 * 
 * @param string $dossier Nom du dossier ou se trouve les fichiers.
 * @return array Résultat de l'analyse.
 */
function checkAction($posts1, $posts2, $date) {
    $mapId = [];
    $postsModif = [];
    $postsAdd = [];

    foreach ($posts1 as $p1) {
        $egaux = trouverPostCorrespondant($p1, $posts2);

        if ($egaux) {
            
            if ($p1['post_modified'] > $date) {
                $mapId[$p1['ID']] = $egaux['ID'];

                $modif = $p1;
                $modif['ID'] = $egaux['ID'];
                $postsModif[] = $modif;
            }
        } else {
            $add = $p1;
            unset($add['ID']);
            $postsAdd[$p1['ID']] = $add;
            $mapId[$p1['ID']] = "nouveau";
        }
    }

    return [
        'add'   => $postsAdd,
        'modif' => $postsModif,
        'map'   => $mapId
    ];
}


function maj($bdd, $dossier, $date) {
    $postsV1 = csvToTab("./test/hr8qI_posts.csv");
    echo "\n\nPassage au 2\n";

    $headers = "ID;post_date;post_title;post_type";
    addHeaders($headers, 'posts', $bdd, $dossier);

    $postsV2 = csvToTab("./$dossier/maj/new_posts.csv");

    echo "\n\n";
    // $actions = checkAction($postsV1, $postsV2, $date);
    // var_dump($actions);

    // tabToCsv($actions['add'], "./$dossier/maj/postsToAdd.csv");
    // tabToCsv($actions['modif'], "./$dossier/maj/postsToUpdate.csv");

    // try {
    //     add_posts()
    // } catch (\Throwable $th) {
    //     //throw $th;
    // }
}


/**
 * Vérifie si la chaîne d'options est valide.
 *
 * @param string $str La chaîne d'options à valider.
 *
 * @return true|die()
 */
function is_valid_option_string($str) {
    if (empty($str)) {
        die(" Erreur: l'option ne doit pas être vide.\n");
    }

    // Only allow letters p, u, f, c, s, n each at most once
    if (!preg_match('/^[pufcsnm]{1,6}$/', $str)) {
        if ($str !== 'a' && $str !== 'h') {
            die("Erreur: l'option doit contenir uniquement les lettres p, u, f, c, s, n, mchacune au plus une fois. Ou alors seulement a ou h.\n");
        }
    }
    // Check for duplicates
    $letters = str_split($str);
    if (count($letters) !== count(array_unique($letters))) {
        die("Erreur: l'option doit contenir uniquement les lettres p, u, f, c, s, n, m chacune au plus une fois.\n");
    }
    return true;
    // die("L'option est valide.\n");
}


// Check if the script is run with the correct number of arguments
if (!($argc = 6 || $argc = 7 && $argv[1] != " " && $argv[2] != " " && $argv[3] != " " && $argv[4] != " " && $argv[5] != " ")) {
    echo "Usage: php add_bdd.php <option> <nom_site> <database_name> <username> <password> <date (only if option n)>\n";
    die();
}

// Validate the option string
if (substr($argv[1], 0, 1) !== '-') {
    die("Erreur: l'option doit commencer par un '-'.\n");
}
is_valid_option_string(substr($argv[1], 1));


// Connect to the database
try {
    $database = $argv[3];
    $username = $argv[4];
    $password = $argv[5];

    // Create connection
    $conn = new PDO("mysql:host=localhost:3306;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

echo "Connection to database $database successful.\n";

if (strpos($argv[1], 'p') !== false || $argv[1] == "-a") {
    echo "Adding posts...\n";
    add_posts($conn, $argv[2]);

    echo "Getting posts...\n";
    get_posts($conn, $argv[2], "attachment");

    echo "Adding postmeta...\n";
    add_postmeta($conn, $argv[2]);
}
if (strpos($argv[1], 'u') !== false || $argv[1] == "-a" ) {
    echo "Adding users...\n";
    add_users($conn, $argv[2]);

    echo "Getting users...\n";
    get_users($conn, $argv[2]);

    echo "Adding usermeta...\n";
    add_usermeta($conn, $argv[2]);
}
if (strpos($argv[1], 'f') !== false || $argv[1] == "-a") {
    echo "Change dates forminator...\n";
    add_date_forminator($argv[2]);

    echo "Adding data forminator...\n";
    add_data_forminator($conn, $argv[2]);
}
if (strpos($argv[1], 'c') !== false || $argv[1] == "-a") {
    echo "Getting contest...\n";
    get_contest($conn, $argv[2]);

    echo "Changing ID postmeta...\n";
    change_idPostMeta($argv[2], 'contest');

    echo "Adding contest...\n";
    add_data_contest($conn, $argv[2]);
}
if (strpos($argv[1], 's') !== false || $argv[1] == "-a") {
    get_posts($conn, $argv[2], "attachment");
    echo "Changing ID postmeta for slide...\n";
    change_idPostMeta($argv[2], 'slide');

    echo "Adding slide...\n";
    add_slide($conn, $argv[2]);
}
if (strpos($argv[1], 'm') !== false) {
    maj($conn, $argv[2], $argv[6]);
    // var_dump($argv);
}
