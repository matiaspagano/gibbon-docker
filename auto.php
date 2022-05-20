<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\View\Page;
use Gibbon\Data\Validator;
use Gibbon\Install\Config;
use Gibbon\Install\Context;
use Gibbon\Install\Http\Exception\RecoverableException;
use Gibbon\Install\Http\InstallController;
use Gibbon\Install\Installer;
use Gibbon\Http\Url;

include '../version.php';

// For offline installer
$_SERVER['PHP_SELF'] = 'installer/install.php';
$_SERVER['HTTP_HOST'] = 'localhost';

include '../gibbon.php';

//Module includes
require_once '../modules/System Admin/moduleFunctions.php';

// Sanitize the whole $_POST array
$validator = $container->get(Validator::class);
$_POST = $validator->sanitize($_POST);

// Fix missing locale causing failed page load
if (empty($gibbon->locale->getLocale())) {
    $gibbon->locale->setLocale('es_MX');
}

$guid = Installer::randomGuid();
$session->setGuid($guid);
$session->set('guid', $guid);
$session->set('absolutePath', realpath('../'));
$session->set('stringReplacement', []); // Deal with non-existent stringReplacement session


// Page object for rendering
$page = new Page($container, [
    'title'   => __('Gibbon Installer'),
    'address' => '/installer/install.php',
]);

// Create a controller instance.
$controller = InstallController::create(
    $container,
    $session,
    $page
);

// Generate installer object.
$installer = new Installer($container->get('twig'));


// Generate installation context from the environment.
$context = (Context::fromEnvironment())
    ->setInstallPath(dirname(__DIR__));

ob_start();

// Attempt to download & install the required language files
$locale_code = 'es_MX';

//Set language pre-install
if (function_exists('gettext')) {
    $gibbon->locale->setLocale($locale_code);
    bindtextdomain('gibbon', '../i18n');
    textdomain('gibbon');
}

// Prevent memory or time limit issues.
ini_set('memory_limit', '5120M');
set_time_limit(0);

$data['databaseServer'] = getenv('DB_HOST');
$data['databaseName'] = getenv('DB_NAME');
$data['databaseUsername'] = getenv('DB_USER');
$data['databasePassword'] = getenv('DB_PASSWORD');
$data['demoData'] = getenv('DEMO_DATA');

// randomGuid
$guid = Installer::randomGuid();

// Check for the presence of a config file (if it hasn't been created yet)
$context->validateConfigPath();

// Get and set database variables (not set until step 1)
$config = InstallController::parseConfigSubmission($guid, $data);


// Check if demo data should be installed.
$shouldInstallDemoData = InstallController::parseDemoDataInstallFlag($data);

// Initialize database for the installer with the config data.
echo "Initialize database for the installer with the config data.";
$installer->useConfigConnection($config);

// Create and check existance of the config file.
echo "Create and check existance of the config file.";
$installer->createConfigFile($context, $config);

// Run database installation of the config
echo "Run database installation of the config";
$installer->install($context, $locale_code, $shouldInstallDemoData);

// User Register
echo "User Creation";

$user_data['title'] = "Mr.";
$user_data['surname'] = "ADMIN";
$user_data['firstName'] = "ADMIN";
$user_data['username'] = getenv('GIBBON_USERNAME');
$user_data['passwordNew'] = getenv('GIBBON_PASSWORD');
$user_data['passwordConfirm'] = getenv('GIBBON_PASSWORD');
$user_data['email'] = getenv('GIBBON_EMAIL');
$user_data['absoluteURL'] = getenv('GIBBON_URL');
$user_data['absolutePath'] = "/var/www/html";
$user_data['systemName'] = getenv('GIBBON_SYSTEM_NAME') || "Gibbon";
$user_data['installType'] = getenv('GIBBON_INSTALL_TYPE') || "Production";
$user_data['timezone'] = getenv('GIBBON_TIMEZONE') || "America/Argentina/Buenos_Aires";
$user_data['country'] = getenv('GIBBON_COUNTRY') || "Argentina";
$user_data['currency'] = getenv('GIBBON_CURRENCY') || "ARS $";
$user_data['statsCollection'] = "N";
$user_data['cuttingEdgeCode'] = "No";
$user_data['support'] = "";
$user_data['organisationName'] = getenv('GIBBON_ORGANISATION_NAME') || "Gibbon";
$user_data['organisationNameShort'] = getenv('GIBBON_ORGANISATION_INITIALS') || "Gibbon";
$user_data['gibboneduComOrganisationName'] = "";
$user_data['gibboneduComOrganisationKey'] = "";
$user_data['cuttingEdgeCodeHidden'] = "";

// Connect database according to config file information.
echo "Connect database according to config file information.";

$config = Config::fromFile($context->getConfigPath());
$installer->useConfigConnection($config);
$absoluteURL = InstallController::guessAbsoluteUrl();

InstallController::validateUserSubmission($user_data);
InstallController::validatePostInstallSettingsSubmission($user_data);

// Write the submitted user to database.
echo "Write the submitted user to database.";
$user = InstallController::parseUserSubmission($user_data);
$installer->createUser($user);

// Set the new user as teaching staff.
echo "Set the new user as teaching staff.";
$installer->setPersonAsStaff(1, 'Teaching');

// Parse all submitted settings and store to Gibbon database.
echo "Parse all submitted settings and store to Gibbon database.";
$settingsFail = false;
$settings = InstallController::parsePostInstallSettings($user_data);
foreach ($settings as $scope => $scopeSettings) {
    foreach ($scopeSettings as $key => $value) {
        $settingsFail = !$installer->setSetting($key, $value, $scope) || $settingsFail;
    }
}

if ($settingsFail) {
    echo('Installer: settings failed. Will trigger RecoverableException.');
} else {
    echo('Installer Finished!!!');
}