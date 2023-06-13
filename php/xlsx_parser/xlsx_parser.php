<?php

require_once('SimpleXLSX.php');
use Shuchkin\SimpleXLSX;

error_reporting(E_ALL ^ E_DEPRECATED);

$in_dir = './in/';
$out_dir = './out/';
$out_extension = '.csv';
$separator = ',';

$remove_first_row = false;

echo 'XLSX PARSER - Rov 06/2023' . PHP_EOL;

if (is_dir($in_dir) === false)
{
    echo "No 'in' directory found. Please create one and put the .XLSX files in it. Exiting.";
    exit();
}

$files = glob($in_dir. "*.xlsx");
if ($files === false)
{
    echo 'An error occurred while ~globbing files. Exiting.';
    exit();
}
if (empty($files))
{
    echo 'No .xlsx files found. Exiting.';
    exit();
}

echo 'Found ' . count($files) . ' .XLSX file(s).' . PHP_EOL;

if (is_dir($out_dir) === false)
{
    echo "Creating 'out' directory... ";
    if (mkdir($out_dir) === false)
    {
        echo "Failed! Exiting.";
        exit();
    }
    echo 'done!' . PHP_EOL;
}
else
    echo "Directory 'out' already exists and will be used." . PHP_EOL;

$success_count = 0;
$error_count = 0;
$skip_count = 0;
foreach ($files as $file)
{
    $filename = pathinfo($file, PATHINFO_FILENAME);

    echo "Parsing file '" . $file . "'... ";

    if ($xlsx = SimpleXLSX::parse($file))
    {
        $output_filename = $filename . $out_extension;

        if (file_exists($out_dir . $output_filename) === true)
        {
            echo 'file already exists. Skipping it.' . PHP_EOL;
            $skip_count++;
            continue;
        }

        $file_handle = fopen($out_dir . $output_filename, 'wb');
        $rows = $xlsx->readRows();
        $header = true;

        foreach ($rows as $row)
        {
            if ($remove_first_row)
            {
                if ($header)
                {
                    $header = false;
                    continue;
                }
            }

            fwrite($file_handle, implode($separator, $row) . PHP_EOL);
        }

        fclose($file_handle);

        echo 'done! Output filename: ' . $output_filename . PHP_EOL;
    }
    else
    {
        echo 'error! ' . SimpleXLSX::parseError();
        $error_count++;
        continue;
    }

    $success_count++;
}

echo 'All done! '
    . $success_count . ' file(s) parsed successfully, '
    . $error_count . ' file(s) parsed with errors and '
    . $skip_count . ' file(s) skipped.';
