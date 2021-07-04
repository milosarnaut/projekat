<?php
    require_once '../../vendor/autoload.php';

    use Milantex\DAW\DataBase;

    # Open a connection using the DAW
    $daw = new DataBase('localhost', 'bayou', 'root', '');

    # Write an SQL query (no parameters)
    $query1 = 'SELECT * FROM `post` WHERE `visible` = 1;';
    # Execute the query and retrieve all result set rows
    $visiblePosts = $daw->selectMany($query1);
    # Print out the dump of the result
    echo '<pre>' . print_r($visiblePosts, true) . '</pre>';

    # Output:
    /*
     *  Array
     *  (
     *      [0] => stdClass Object
     *          (
     *              [post_id] => 1
     *              [created_at] => 2015-02-12 20:44:04
     *              [user_id] => 1
     *              [title] => Limitless Bayou begins
     *              [link] => limitsless-bayou-begins
     *              [content] => This is a sample post in a database used by the Limitless Bayou API.
     *              [visible] => 1
     *          )
     *  )
     */

    # Write an SQL query (with one parameter for the username)
    $query2 = 'SELECT * FROM `user` WHERE `username` = :username AND `active` = 1;';
    # Prepare the parameter associative array
    $params2 = [ ':username' => 'milantex' ];
    # Execute the query and retrieve a single result set
    $user = $daw->selectOne($query2, $params2);
    # Print out the dump of the result
    echo '<pre>' . print_r($user, true) . '</pre>';

    # Output:
    /*
     * stdClass Object
     *  (
     *      [user_id] => 1
     *      [created_At] => 2015-02-12 20:41:12
     *      [username] => milantex
     *      [password] => 30B1B23A56D4AAA7BF951355D4F3CCFB157F2CF7B27D673A1B91613C431F28A76D3E621B9F38FD3C7954D619909EF4CBB574E225D6D60EDC6D423E652112633A
     *      [active] => 1
     *  )
     */

    # Write an SQL query (with an unnamed parameter placeholder)
    $query3 = 'DELETE FROM `post` WHERE `post_id` = ?;';
    # Prepare the ordered parameter array
    $params3 = [ 130 ];
    # Execute the query and retrieve a single result set
    $result3 = $daw->execute($query3, $params3);
    # Check the result
    if (!$result3) {
        # Print out the dump of error information if the result is bad
        echo '<pre>' . print_r($daw->getLastExecutionError(), true) . '</pre>';
    } else {
        # Print out how many records were affected
        $affectedRows = $daw->getLastExecutionAffectedRownCount();
        echo 'Deleted record count: ' . $affectedRows . '<br><br>';
    }

    # Output:
    /*
     * Deleted record count: 0
     */

    # Write an SQL query (with unnamed parameter placeholders)
    $query4 = 'INSERT INTO `post` (`user_id`, `title`, `link`, `content`) '.
              'VALUES (:user_id, :title, :link, :content);';
    # Prepare the parameter associative array
    $params4 = [
        ':user_id' => 1,
        ':title'   => 'A test post',
        ':link'    => 'a-test-post',
        ':content' => '<p>This is the content of the new test post.</p>'
    ];
    # Execute the query and retrieve a single result set
    $result4 = $daw->execute($query4, $params4);
    # Check the result
    if (!$result4) {
        # Print out the dump of error information if the result is bad
        echo '<pre>' . print_r($daw->getLastExecutionError(), true) . '</pre>';
    } else {
        # Get the ID of the new post
        $postId = $daw->getLastInsertId();
        echo 'The ID of the new post is: ' . $postId . '<br><br>';
    }

    # Output:
    /*
     * The ID of the new post is: 6
     */
