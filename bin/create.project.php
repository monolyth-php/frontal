#!/usr/bin/php5
<?php

/**
 * A quick and dirty script to setup a new project.
 */
function input()
{
    $handle = fopen("php://stdin", "r");
    return trim(fgets($handle));
}

function addFile($name, $content)
{
    if (file_exists($name)) {
        // Don't overwrite existing files; they may already contain overrides,
        // and/or this may be an incremental run of this script.
        return;
    }
    $fp = fopen($name, 'w');
    if (!$fp) {
        die("Error: file $name not writeable! Aborting.\n");
    }
    fwrite($fp, $content);
    fclose($fp);
}

function addDir($name)
{
    if (file_exists($name) && is_dir($name)) {
        return;
    }
    if (!mkdir($name, 0755)) {
        die("Error: directory $name could not be created! Aborting.\n");
    }
}

// Query for the project name:
echo "Base URL for this project (e.g., example.com): ";
$url = input();

// Query for $site:
echo "Value of \$site (leave empty to default to URL without dots, e.g. examplecom): ";
$site = input();
if ($site == '') {
    $site = str_replace('.', '', $url);
}

// Database settings:
echo "Database engine (m[ysql] or p[ostgresql]): ";
while (true) {
    $engine = input();
    if ($engine == 'm' || $engine == 'p') {
        $engineName = $engine == 'm' ? 'MySQL' : 'PgSQL';
        break;
    }
    echo "Please enter m[ysql] or p[ostgresql]: ";
}
echo "Database host (leave empty for localhost): ";
$host = input();
if (!strlen($host)) {
    $host = 'localhost';
}
echo "Database name (leave empty to default to \$site): ";
$name = input();
if (!strlen($name)) {
    $name = $site;
}
echo "Database user (leave empty to default to database name): ";
$user = input();
if (!strlen($user)) {
    $user = $name;
}
echo "Database password: ";
$pass = input();

// Gathered all the required information.
// Present this to the user and ask for confirmation:
echo <<<EOT

Your project will now be created with the following settings:
URL: $url
\$site: $site

Database:
Engine: $engineName
Host: $host
Name: $name
User: $user
Password: $pass

Continue? [y/n] 
EOT;
$continue = strtolower(input());
if ($continue != 'y') {
    die("Cancelled!\n");
}

// Okay... let's go! First, we need to determine what our base-path is.
// Normally, this is the path "above" the location of the monolyth-folder,
// i.e. two levels up.
$basepath = realpath(dirname(__FILE__).'/../../');
echo <<<EOT
Project will now be created. Your basepath was determined to be $basepath.
You may override this now, or just hit enter to confirm it:

EOT;
$overrideBasepath = input();
if (strlen($overrideBasepath)) {
    $basepath = $overrideBasepath;
}

addFile("$basepath/init.php", <<<EOT
<?php

namespace project;
// setup the BASE_PATH
define("BASE_PATH", realpath(dirname(__FILE__)));
const BASE_PATH = \\BASE_PATH;
define("SITETEST", false);
const SITETEST = \\SITETEST;

// Include monolyth:
require_once 'monolyth/core/Monolyth.php';

// Include or define your project routes here:

EOT
);
addDir("$basepath/$url");
addDir("$basepath/$url/www");
addFile("$basepath/$url/www/index.php", <<<EOT
<?php

namespace project;
use monolyth\\core;

const SECURE = false;
\$site = '$site';
define('WWW_PATH', realpath(dirname(__FILE__)));
const WWW_PATH = \\WWW_PATH;
require_once "init.php";
core\MonoLyth::run();

EOT
);
addDir("$basepath/$url/project");
addDir("$basepath/$url/project/core");
addDir("$basepath/$url/project/core/Config");
addFile("$basepath/$url/project/core/Config/DB.php", <<<EOT
<?php

namespace project\\core;

class DB_Config
{
    public function __construct()
    {  
        \$this->$site = array(
            'engine' => 'sql\\$engineName',
            'dsn' => 'mysql://$user:$pass@$host/$name',
        );
    }
}

EOT
);
addFile("$basepath/$url/project/core/Config/Language.php", <<<EOT
<?php

namespace project\\core;
use monolyth\\core;

class Language_Config extends core\Language_Config
{
    public function __construct()
    {  
        parent::__construct();
        \$this->status['default'] = 'en';
        \$this->status['current'] = 'en';
        \$this->available = array(\$this->en);
    }
}

EOT
);
addFile("$basepath/$url/project/core/Config/Sitedata.php", <<<EOT
<?php

namespace project\\core;

class Sitedata_Config
{
    public static \$alternatives = array(
        'http://$url' => 'http://www.$url',
    );

    public \$$site = array(
        'url' => '$url',
        'urlimg' => '$url',
        'name' => '$url',
        'protocol' => 'http',
        'protocols' => 'https',
        'httplogin' => 'monolyth\\account\\Login',
    );

    public function __construct()
    {
        \$this->{$site}['pathimg'] = \project\WWW_PATH;
        foreach (\$this as &\$data) {
            if (\\project\\SITETEST) {
                \$data['protocols'] = 'http';
                \$data['url'] = \$_SERVER['SERVER_NAME'];
                \$data['urlimg'] = '';
            }
            \$data['http']    = "{\$data['protocol']}://www.{\$data['url']}";
            \$data['https']   = "{\$data['protocols']}://secure.{\$data['url']}";
            \$data['httpimg'] = "http://www.{\$data['urlimg']}";
            if (\project\SITETEST) {
                \$data['http'] = "http://{\$_SERVER['SERVER_NAME']}";
                \$data['https'] = \$data['http'];
                \$data['httpimg'] = '';
            }
        }
        \$this->monad =& \$this->$site;
    }
}

EOT
);
addFile("$basepath/$url/project/core/Project.php", <<<EOT
<?php

namespace project\\core;

abstract class Project extends \monolyth\Project
{
}

EOT
);
addDir("$basepath/$url/project/core/output");
addDir("$basepath/$url/project/core/output/html");
addDir("$basepath/$url/project/core/output/html/template");
addFile("$basepath/$url/project/core/output/html/template/page.php", <<<EOT
<html lang="en" class="no-js" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8"/>
    <!--[if IE]><meta http-equiv="X-UA-Compatible"
        content="IE=edge,chrome=1"/><![endif]-->
    <title><?php echo implode(' - ', \$title) ?></title>
    <meta name="description" content=""/>
    <meta name="author" content="Me"/>
    <meta name="generator" content="monolyth"/>
    <meta name="viewport" content="width=device-width;
        initial-scale=1.0; maximum-scale=1.0;"/>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="apple-touch-icon" href="/apple-touch-icon.png"/>
</head>
<body>
    <header><h1>Default project template</h1></header>
    <section>
        <?php echo \$content ?>
    </section>
    <footer>generated by monolyth's create.project script</footer>
</body>
</html>

EOT
);
// Finally, generate basic database structure. If it's MySQL, we'll need root
// access to define triggers and stuff.
while (true) {
    echo "Please supply the root password of the MySQL database: ";
    $pw = input();
    if ($conn = mysql_connect($host, 'root', $pw)) {
        break;
    }
    echo "Password incorrect; please try again.\n";
}
mysql_query("DROP DATABASE IF EXISTS $name");
mysql_query("CREATE DATABASE $name");
mysql_query("GRANT ALL ON $name.* TO $user@$host IDENTIFIED BY '$pass'");
mysql_query("FLUSH PRIVILEGES");

`mysql -u root -p$pw $name < ../info/sql/mysql/prepare.sql`;
`mysql -u root -p$pw $name < ../info/sql/mysql/schema.sql`;
`mysql -u root -p$pw $name < ../info/sql/mysql/functions.sql`;
`mysql -u root -p$pw $name < ../info/sql/mysql/triggers.sql`;
`mysql -u root -p$pw $name < ../info/sql/mysql/defaults.sql`;
`mysql -u root -p$pw $name < ../info/sql/mysql/foreignkeys.sql`;

echo "All done! Happy coding...\n\n";

