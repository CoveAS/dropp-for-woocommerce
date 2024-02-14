<?php

$packages = [
	'iio/libmergepdf' => 'hanneskod/libmergepdf',
	'setasign/fpdi' => 'setasign/fpdi',
	'tecnickcom/tcpdf' => 'tecnickcom/tcpdf',
];

chdir(__DIR__ . '/includes');

class Package
{
	public function __construct(
		public string $name,
		public string $slug,
		public string $zip,
		public string $dir,
		public string $short,
		public string $url,
	)
	{
	}
}

$colors = (object)[
	'black' => "\033[0;30m",
	'dark_gray' => "\033[1;30m",
	'blue' => "\033[0;34m",
	'light_blue' => "\033[1;34m",
	'green' => "\033[0;32m",
	'light_green' => "\033[1;32m",
	'cyan' => "\033[0;36m",
	'light_cyan' => "\033[1;36m",
	'red' => "\033[0;31m",
	'light_red' => "\033[1;31m",
	'purple' => "\033[0;35m",
	'light_purple' => "\033[1;35m",
	'brown' => "\033[0;33m",
	'yellow' => "\033[1;33m",
	'light_gray' => "\033[0;37m",
	'white' => "\033[1;37m",
	'reset' => "\033[0m",
];
$packages = array_map(
	function ($path, $name) {
		$url = sprintf(
			'https://github.com/%s/archive/refs/heads/master.zip',
			$path
		);
		$parts = explode('/', $name);
		$slug = str_replace('/', '_', $name);
		return new Package(
			name: $name,
			slug: $slug,
			zip: "./$slug.zip",
			dir: $parts[0],
			short: $parts[1],
			url: $url
		);
	},
	$packages,
	array_keys($packages)
);
// Download new stuff
echo "Downloading new packages\n";
foreach ($packages as $package) {
	if (file_exists($package->zip)) {
		echo "{$colors->green}✔{$colors->reset} $package->zip\n";
		continue;
	}
	$content = file_get_contents($package->url);
	echo "{$colors->blue}⇩{$colors->reset} $package->zip\n";
	file_put_contents($package->zip, $content);
}

// Delete old stuff
echo "\nDeleting old packages\n";
foreach ($packages as $package) {
	`rm -rf $package->dir`;
	echo "{$colors->red}⨉{$colors->reset} $package->dir\n";
}

echo "\nUnzipping new packages\n";
foreach ($packages as $package) {
	`unzip $package->zip -d $package->dir`;
	echo "{$colors->green}⇧{$colors->reset} $package->zip → $package->dir\n";

	`rm $package->zip`;
	echo "{$colors->red}⨉{$colors->reset} $package->zip\n";
}

echo "\nMoving unzipped directories\n";
foreach ($packages as $package) {
	$files = scandir($package->dir);
	if (count($files) > 3) {
		echo "{$colors->red}ERROR: Too many files!{$colors->reset}\n";
		var_dump($files);
		die(1);
	}
	foreach ($files as $file) {
		if ($file === '.' || $file === '..') {
			continue;
		}
		$filename = "$package->dir/$file";
		if (!is_dir($filename)) {
			echo "{$colors->red}ERROR: NON-directory file discovered! $filename{$colors->reset}\n";
			die(1);
		}
		echo "$filename → $package->name\n";
		`mv $filename $package->name`;
	}
}


echo "\nCleaning up fonts in tcpdf\n";
`rm tecnickcom/tcpdf/fonts/*.z`;
echo "{$colors->red}⨉{$colors->reset} tecnickcom/tcpdf/fonts/*.z\n";
`rm -r tecnickcom/tcpdf/examples/`;
echo "{$colors->red}⨉{$colors->reset} tecnickcom/tcpdf/examples\n";

$dir = 'tecnickcom/tcpdf/fonts';
$files = scandir($dir);
foreach ($files as $file) {
	if ($file === '.' || $file === '..') {
		continue;
	}
	$filename = "$dir/$file";
	if (!is_dir($filename)) {
		continue;
	}
	`rm -r $filename`;
	echo "{$colors->red}⨉{$colors->reset} $filename\n";
}
