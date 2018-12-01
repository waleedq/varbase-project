<?php

function get_file($url, $local_path, $newfilename)
{
    $err_msg = '';
    echo "Downloading $url";
    echo "\n";
    $out = fopen($local_path.$newfilename,"wrxb");
    if ($out == FALSE){
      print "File not opened<br>";
      exit;
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_FILE, $out);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_exec($ch);

    curl_close($ch);
    //fclose($handle);

}//end function

echo "Varbase-project updater started!\n";

$path = getcwd()."/composer.json";
if(!file_exists($path)){
  echo "\n";
  echo "Please run this command from your varbase-project root directory";
  exit;
}
$string = file_get_contents(getcwd()."/composer.json");
$json=json_decode($string,true);

if(isset($json["name"]) && $json["name"] != "vardot/varbase-project"){
  echo "\n";
  echo "Please run this command from your varbase-project root directory";
  exit;
}

if(!isset($json["name"])){
  echo "\n";
  echo "Please run this command from your varbase-project root directory";
  exit;
}

if(!isset($json["autoload"])){
  $json["autoload"] = [
    "psr-4" => [
      "Varbase\\composer\\" => "scripts/composer"
    ]
  ];
}else if(isset($json["autoload"]["psr-4"])){
  $json["autoload"]["psr-4"]["Varbase\\composer\\"] = "scripts/composer";
}else{
  $json["autoload"]["psr-4"] = [
    "Varbase\\composer\\" => "scripts/composer"
  ];
}

if(!isset($json["scripts"])){
  $json["scripts"] = [
    "varbase-composer-generate" => [
      "Varbase\\composer\\VarbaseUpdate::generate"
    ]
  ];
}else if(isset($json["scripts"])){
  $json["scripts"]["varbase-composer-generate"]= [
    "Varbase\\composer\\VarbaseUpdate::generate"
  ];
}
$drupalPath = "docroot";
if (file_exists(getcwd().'/web')) {
  $drupalPath = "web";
}

echo "Drupal root set to " . $drupalPath . " if your drupal root is differnet than this, please change install-path inside composer.json under extra section.\n";

if(!isset($json["extra"])){
  $json["extra"] = [
    "install-path" => $drupalPath
  ];
}else{
  $json["extra"]["install-path"] = $drupalPath;
}

$jsondata = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);


if (!file_exists(getcwd().'/scripts/composer')) {
    mkdir(getcwd().'/scripts/composer', 0777, true);
}

if (!file_exists(getcwd().'/drush')) {
    mkdir(getcwd().'/drush', 0777, true);
}

if (!file_exists(getcwd().'/bin')) {
    mkdir(getcwd().'/bin', 0777, true);
}

get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/scripts/composer/VarbaseUpdate.php", getcwd().'/scripts/composer/', 'VarbaseUpdate.php');
get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/scripts/composer/update-varbase.sh", getcwd().'/scripts/composer/', 'update-varbase.sh');

//only download them if they don't exist
if (!file_exists(getcwd().'/tags.json')) {
    get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/tags.json", '', 'tags.json');
}
if (!file_exists(getcwd().'/drush/policy.drush.inc')) {
    get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/drush/policy.drush.inc", getcwd().'/drush/', 'policy.drush.inc');
}
if (!file_exists(getcwd().'/drush/README.md')) {
    get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/drush/README.md", getcwd().'/drush/', 'README.md');
}
if (!file_exists(getcwd().'/bin/drush8')) {
    get_file("https://github.com/drush-ops/drush/releases/download/8.1.18/drush.phar", getcwd().'/bin/', 'drush8');
}
if (!file_exists(getcwd().'/.download-before-update')) {
  get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/.download-before-update", getcwd().'/', '.download-before-update');
}
if (!file_exists(getcwd().'/.enable-after-update')) {
  get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/.enable-after-update", getcwd().'/', '.enable-after-update');
}
if (!file_exists(getcwd().'/.skip-update')) {
  get_file("https://raw.githubusercontent.com/Vardot/varbase-project/8.6.x-update/.skip-update", getcwd().'/', '.skip-update');
}

chmod(getcwd().'/bin/drush8', 0755);
chmod(getcwd().'/scripts/composer/update-varbase.sh', 0755);
chmod(getcwd().'/scripts/composer/VarbaseUpdate.php', 0755);

if(file_put_contents($path, $jsondata)) {
  echo "varbase-project successfully updated.\n";
  echo "Now you can run ./scripts/composer/update-varbase.sh to update varbase to latest version.\n";
  echo "Thank you.\n";
}
