<?php

    /*
        Scrapes first 12 photos of a spesific users instagram account,
        Then adds them to the database. Keep in mind that any code with
        details
    */

    //returns a big old hunk of JSON from a non-private IG account page.
    function scrape_insta($username) {
    	$insta_source = file_get_contents('http://instagram.com/'.$username);
    	$shards = explode('window._sharedData = ', $insta_source);
    	$insta_json = explode(';</script>', $shards[1]);
    	$insta_array = json_decode($insta_json[0], TRUE);
    	return $insta_array;
    }


    //Establishes database connection
    $dbc = mysqli_connect("localhost", "root", "", "motivateme");
    if(!$dbc){
        echo "Connection could not be established";
        exit;
    }

    //Supply a username, passwords and emails are dynamically created based off username currently, changed to generic values for github Publish
    $username = '';
    $password = md5($username);
    $email    = '@instagram.com';

    //First add user
    $sql = "INSERT INTO `users` (`user_id`, `username`, `hashed_password`, `email`, `profile_image_path`, `date_created`, `date_last_active`)
    VALUES (NULL, '".$username."', '".$password."', '".$email."', NULL, 'CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP')";

    // //Attempts to add user to database
    if(mysqli_query($dbc, $sql)) echo "Username insert success";
    else echo 'username inset failed';

    //Gets the users id from the database
    $idQuery = "SELECT user_id FROM users WHERE username = '$username' ";
    $result  = mysqli_query($dbc, $idQuery);
    $id      = mysqli_fetch_assoc($result);
    echo $id['user_id'];

    //Establishs user account to scrap & scraps it to a json file, saving it as an array
    $results_array = scrape_insta($username);

    for ($i=1; $i < 12; $i++) {
        // $comments = $img['comments'];
        $img = $results_array['entry_data']['ProfilePage'][0]['user']['media']['nodes'][$i];
        $likes = $img['likes']['count'];

        $photoQuery = "INSERT INTO `photos` (`photo_id`, `user_id`, `likes`, `caption`, `image_path`, `date_created`)
                       VALUES (NULL, '".$id['user_id']."', '".$likes."', 'asd', '".$img['display_src']."', CURRENT_TIME())";

       if(mysqli_query($dbc, $photoQuery)) echo "Image insert success";
       else echo 'Image inset failed';

        echo '<img src="'.$img['display_src'].'">';
    }

    //Prints out full json of scraped content
    // echo '<pre>';
    // print_r ($comments);
    // echo '</pre>';

?>
