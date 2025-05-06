<?php

$found = false;

if(file_exists(__DIR__.'../vendor/autoload.php'))
{
    $found = true;
    include_once(__DIR__.'../vendor/autoload.php');
}

if(file_exists(__DIR__.'../../vendor/autoload.php'))
{
    $found = true;
    include_once(__DIR__.'../../vendor/autoload.php');
    return;
}

if(file_exists(__DIR__.'../../../vendor/autoload.php'))
{
    $found = true;
    include_once(__DIR__.'../../../vendor/autoload.php');
    return;
}

if(file_exists(__DIR__.'../../../../vendor/autoload.php'))
{
    $found = true;
    include_once(__DIR__.'../../../../vendor/autoload.php');
    return;
}

if(!$found)
{
    throw new \Exception('Could not locate composer autoload.php');
}