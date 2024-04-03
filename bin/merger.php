<?php

use Dropp\Models\Dropp_PDF;
use iio\libmergepdf\Merger;

require_once dirname( __DIR__ ) . '/includes/loader.php';
$merger = new Merger;

// Remove script name
$files = explode(' ', $argv[1]);
foreach ($files as $filename) {
	$merger->addFile( $filename );
}
echo $merger->merge();
