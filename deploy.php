<?php

use Dropp\Deploy\Git;
use Dropp\Deploy\Output;

$url = 'https://plugins.svn.wordpress.org/dropp-for-woocommerce';
$cwd = getcwd();
$dir = __DIR__;
if (!file_exists("$dir/svn_dir")) {
	echo "Warning: no svn dir specified. Using /tmp/dropp-svn\n";
	$svnDir = '/tmp/dropp-svn';
} else {
	assert(is_readable("$dir/svn_dir"));
	$svnDir = trim(file_get_contents("$dir/svn_dir"));
}

if (!is_dir($svnDir)) {
	mkdir($svnDir);
}

require_once __DIR__ . '/deploy/load.php';

function handleException(Throwable $exception): void
{
	$output = new Output();
	$output->fatal($exception->getMessage());
}
set_exception_handler('handleException');

$output = new Output();

$username = $argv[1] ?? '';
if (!$username) {
	$output->fatal("Usage: php deploy.php \$username");
}

if (strpos($cwd, $dir)) {
	$output->fatal("Don't run this command inside the dropp repo please");
}

// Check git status and get the version number from the current tag
$git = new Git($dir);
$status = $git->status();
$version = $git->getTag();
if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
	$output->fatal("Invalid version number in git tag, \"$version\". Should be \"#.#.#\".");
}


// Just make sure we are in the current working directory to run CLI commands
assert(getcwd() === $cwd);

// Check that the SVN tag is available
$svn = new \Dropp\Deploy\Svn($url, $cwd);
$tags = $svn->list();

if (in_array($version, $tags, true)) {
	$output->fatal("Version, $version, already exists in SVN");
}

foreach ($tags as $tag) {
	if (empty($tag)) {
		$output->fatal("Encountered empty tag: [$tag]");
	}
}

$esc_version = str_replace('.', '\.', $version);
$output->info("Checking readme.txt version number");
$content = `head -n 20 $dir/readme.txt`;
if (!preg_match('/Stable tag:\s+' . $esc_version . '/', $content, $matches)) {
	$output->fatal("Stable tag doesn't match $version in readme.txt");
}

// Extract WooCommerce version
if (!preg_match('/WC tested up to:\s+([\d\.]+)/', $content, $wc_matches)) {
	$output->fatal("Could not find WooCommerce version in readme.txt");
}
$wc_version = $wc_matches[1];

// Extract WordPress version
if (!preg_match('/Tested up to:\s+([\d\.]+)/', $content, $wp_matches)) {
	$output->fatal("Could not find WordPress version in readme.txt");
}
$wp_version = $wp_matches[1];

$output->info("WooCommerce version: $wc_version");
$output->info("WordPress version: $wp_version");

// Build frontend assets from source before copying to SVN.
$output->info("Building assets (npm ci && npm run production)");
$buildCmd = sprintf('cd %s && npm ci && npm run production 2>&1', escapeshellarg($dir));
exec($buildCmd, $buildOut, $buildResult);
if ($buildResult !== 0) {
	echo implode("\n", $buildOut), "\n";
	$output->fatal("Asset build failed");
}

$expectedAssets = [
	'assets/js/dropp.js',
	'assets/js/dropp-admin.js',
	'assets/js/dropp-location-button.js',
	'assets/css/dropp.css',
	'assets/css/dropp-admin.css',
];
foreach ($expectedAssets as $relAsset) {
	if (!file_exists("$dir/$relAsset")) {
		$output->fatal("Expected built asset missing after build: $relAsset");
	}
}

// Create a new dir
if (!is_dir($svnDir)) {
	if (!mkdir($svnDir)) {
		$output->fatal("Could not make the directory");
	}
}
chdir($svnDir);

if (is_dir('.svn')) {
	// Update an existing SVN
	$output->info("Updating SVN");
	$svn->update();
} else {
	$output->info("Checking out from SVN");
	// Checkout a new shallow copy
	$svn->checkout();
	$svn->update('--set-depth infinity trunk');
}

// Remove existing trunk
$output->info("Copying from git repo");
exec('rm -rf trunk', $out, $result);
if ($result) {
	var_dump($out, $result);
	$output->fatal("Could not remove old trunk");
}
exec('cp -r "' . $dir . '/" trunk/', $out, $result);
if ($result) {
	$output->fatal("Copying git repo failed");
}
if (!file_exists('trunk/deploy.php')) {
	$output->fatal("deploy.php script (this file) was not copied");
}

// Go into the trunk
chdir('trunk');
$content = file_get_contents('dropp-for-woocommerce.php');

// Replace version number placeholder
$output->info("Replacing dropp-for-woocommerce.php version number");
$content = str_replace('###DROPP_VERSION###', $version, $content);

$output->info("Replacing dropp-for-woocommerce.php WordPress tested to version number");
$content = str_replace('###WP_VERSION###', $wp_version, $content);

$output->info("Replacing dropp-for-woocommerce.php WooCommerce tested to version number");
$content = str_replace('###WC_VERSION###', $wc_version, $content);
file_put_contents('dropp-for-woocommerce.php', $content);

$output->info("Replacing classes/class-dropp.php version number");
$content = file_get_contents('classes/class-dropp.php');
$content = str_replace('###DROPP_VERSION###', $version, $content);
file_put_contents('classes/class-dropp.php', $content);

$output->info("Checking dropp-for-woocommerce.php version number");
$content = `head -n 20 dropp-for-woocommerce.php`;
if (!preg_match('/\* Version:\s+' . $esc_version . '/', $content, $matches)) {
	$output->fatal("Version doesn't match $version in dropp-for-woocommerce.php");
}

$output->info("Checking classes/class-dropp.php version number");
$content = `head -n 100 classes/class-dropp.php`;
if (!preg_match('/\sVERSION\s+=\s+\'' . $esc_version . '\';/', $content, $matches)) {
	$output->fatal("Version doesn't match $version in classes/class-dropp.php\n\n" . $content);
}

// Cleanup
$cleaner = new \Dropp\Deploy\Cleaner();
$cleaner->removeDesktopServicesStore();

$cleaner->removeFiles([
	'tags',
	'svn_dir',
	'update-pdf-packages.php',
	'svn-publisher.php',
	'.gitignore',
	'composer.json',
	'composer.lock',
	'deploy.php',
	'dev-warnings.php',
	'README.md',
	'CONTRIBUTING.md',
	'package.json',
	'package.lock',
	'webpack.mix.js',
]);

$cleaner->removeDirs([
	'.idea',
	'.git',
	'deploy',
	'node_modules',
]);

if (file_exists('.gitignore')) {
	$output->fatal("Cleanup failed");
}

// Add and commit changes
$svn->add('.');
if ($result) {
	$output->fatal("Could not stage svn changes");
}

$files = $svn->status();
foreach ($files as $file) {
	if ($file->modifier == '!') {
		$file->remove();
	}
	if ($file->modifier == '~') {
		$output->fatal("SVN has a problem with one of the files, \"$file->path\"");
	}
}

$files = $svn->status();
foreach ($files as $file) {
	$output->info($file->getColorLabel() . $file->path);
}

$answer = trim(readline('Does this look ok? [y/N]'));
if ($answer !== 'y') {
	$output->fatal("Exiting without committing");
}

$output->info("Committing to SVN");

$svn->commit($username, "Synchronized trunk with master branch from Github");

chdir('..');
$svn->cp('trunk', "tags/$version");
$svn->commit($username, "Updated the version number to $version");

$output->info("Done 🎉");

