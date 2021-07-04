[![Build Status](https://travis-ci.org/Milantex/milantex-daw.svg?branch=master)](https://travis-ci.org/Milantex/milantex-daw)
[![codecov](https://codecov.io/gh/Milantex/milantex-daw/branch/master/graph/badge.svg)](https://codecov.io/gh/Milantex/milantex-daw)
[![Code Climate](https://codeclimate.com/github/Milantex/milantex-daw/badges/gpa.svg)](https://codeclimate.com/github/Milantex/milantex-daw)
[![Latest Stable Version](https://poser.pugx.org/milantex/daw/v/stable)](https://packagist.org/packages/milantex/daw)
[![Total Downloads](https://poser.pugx.org/milantex/daw/downloads)](https://packagist.org/packages/milantex/daw)
[![License](https://poser.pugx.org/milantex/daw/license)](https://packagist.org/packages/milantex/daw)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/39907987-6b7f-42ee-9ea9-aebb60ecc7e6/mini.png)](https://insight.sensiolabs.com/projects/39907987-6b7f-42ee-9ea9-aebb60ecc7e6)

# What is Milantex-DAW?
This package provides a mechanism to easily connect and use a MySQL/MariaDB database. Check the text below for information about how to install it and to see examples of how to use it.

## Installation
#### Using composer in the command line
You can use composer to install the package using the following command from within your project's source directory:

`composer require milantex/daw`

Make sure to update your autoloader if needed:

`composer dump-autoload -o`

#### Requiring the package as a dependency in composer.json
Add the following code to your composer.json. Here is an example of a composer.json file with the milantex/daw package required:

```javascript
{
    "name": "your-name/your-project",
    "description": "Your project's description",
    "type": "project",
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "your@mail.tld"
        }
    ],
    "require": {
        "milantex/daw": "*"
    }
}
```

Make sure to run the composer command to install dependencies:

`composer install`

## Using it in your project

To start using the DAW, first require the composer's autoload script:
```php
require_once 'vendor/autoload.php';
```
After that, you can create an instance of Milantex\DAW\DataBase:
```php
use Milantex\DAW\DataBase;

# Open a connection using the DAW
$daw = new DataBase('localhost', 'bayou', 'root', '');
```
Here is an example showing how you can select multiple records from the database and print it out:
```php
# Write an SQL query (no parameters)
$query1 = 'SELECT * FROM `post` WHERE `visible` = 1;';

# Execute the query and retrieve all result set rows
$visiblePosts = $daw->selectMany($query1);

# Print out the dump of the result
echo '<pre>' . print_r($visiblePosts, true) . '</pre>';
```

**Output:**
```html
<pre>
  Array
  (
      [0] => stdClass Object
          (
              [post_id] => 1
              [created_at] => 2015-02-12 20:44:04
              [user_id] => 1
              [title] => Limitless Bayou begins
              [link] => limitsless-bayou-begins
              [content] => This is a sample post in a database.
              [visible] => 1
          )
  )
</pre>
```
Here is an example showing how you can select a single record from the database with an SQL query and named parameter placeholders:
```php
# Write an SQL query (with one parameter for the username)
$query2 = 'SELECT * FROM `user` WHERE `username` = :username AND `active` = 1;';

# Prepare the parameter associative array
$params2 = [ ':username' => 'milantex' ];

# Execute the query and retrieve a single result set
$user = $daw->selectOne($query2, $params2);

# Print out the dump of the result
echo '<pre>' . print_r($user, true) . '</pre>';
```
**Output:**
```html
<pre>
  stdClass Object
  (
      [user_id] => 1
      [created_At] => 2015-02-12 20:41:12
      [username] => milantex
      [password] => SOME_HASH_VALUE
      [active] => 1
  )
</pre>
```
Here is an example showing how you can delete records from the database using unnamed parameter placeholders (ordered):
```php
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
```
**Output:**
```html
Deleted record count: 0
```
Here is an example showing how you can add a record to a table, again using named parameter placeholders:
```php
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
```
**Output:**
```html
The ID of the new post is: 6
```
