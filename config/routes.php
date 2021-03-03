<?php

/*
 *  POSHUK electron-optical complex
 *
 *  @author       Alex Grey
 *  @copyright    Copyright © 2019 Alex Grey (alex@grey.kiev.ua)
 *  @license      https://opensource.org/licenses/GPL-3.0
 *  @since        Version 1.0
 *
 */


//$app->get('/', 'IndexController:index')->add($userMiddleware);
$app->get('/', 'IndexController:index');
$app->post('/client/works', 'IndexController:clientWorks')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/client/alarms', 'IndexController:clientAlarms')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/client/getalarms', 'IndexController:currentAlarms')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/client/getmodes', 'IndexController:getmodes')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/client/getgps', 'IndexController:getGPS')->add($userMiddleware)->add($checkToken)->add($checkAjax);
//$app->post('/client/setcomplex', 'IndexController:selectComplex')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/client/setcomplex', 'IndexController:selectComplex')->add($checkToken)->add($checkAjax);

$app->get('/login', 'AuthController:login')->add($anonMiddleware);
$app->post('/login', 'AuthController:doLogin')->add($anonMiddleware)->add($checkToken)->add($checkAjax);
//$app->get('/setlang/{lang}', 'AuthController:setLang')->add($anonMiddleware);
$app->get('/setlang/{lang}', 'AuthController:setLang');
$app->get('/restore', 'AuthController:restoreAccess')->add($anonMiddleware);
$app->post('/restore', 'AuthController:doRestoreAccess')->add($anonMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/register', 'AuthController:doRegisterUser')->add($anonMiddleware)->add($checkToken)->add($checkAjax);
$app->get('/logout', 'AuthController:doLogout')->add($userMiddleware);

$app->get('/profile', 'ProfileController:index')->add($userMiddleware);
$app->post('/profile/save', 'ProfileController:save')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/profile/lang', 'ProfileController:lang')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/profile/password', 'ProfileController:password')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->get('/profile/synchronize', 'ProfileController:synchronize')->add($userMiddleware);

$app->get('/map', 'MapController:index')->add($userMiddleware);
$app->post('/map/works', 'MapController:mapworks')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->get('/map/viewdetect/{detect:[0-9]+}', 'MapController:viewDetect')->add($userMiddleware);
$app->post('/map/getimage', 'MapController:getImage')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/map/refresh', 'MapController:refresh')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/map/detects', 'MapController:detects')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/map/notifyoff', 'MapController:notifyOff')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/map/departed', 'MapController:departed')->add($userMiddleware)->add($checkToken)->add($checkAjax);
//$app->post('/map/clear', 'MapController:clear')->add($userMiddleware)->add($checkToken)->add($checkAjax);

$app->get('/stream', 'VideoController:index')->add($userMiddleware);

$app->get('/sessions', 'StorageController:index')->add($userMiddleware);
$app->post('/sessions/setstorage', 'StorageController:setStorageType')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/sessions/load', 'StorageController:loadSessions')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/sessions/delete', 'StorageController:deleteSession')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->get('/sessions/view/{stype}/{sid}', 'StorageController:viewSession')->add($userMiddleware);
$app->post('/sessions/gettrack', 'StorageController:getTrack')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/sessions/gettracks', 'StorageController:getComplexSession')->add($userMiddleware)->add($checkToken)->add($checkAjax);
//$app->post('/sessions/download', 'StorageController:download')->add($userMiddleware)->add($checkToken)->add($checkAjax);
//$app->post('/sessions/getfullimg', 'StorageController:getTrackImg')->add($userMiddleware)->add($checkToken)->add($checkAjax);
//$app->post('/sessions/transfer', 'StorageController:transfer')->add($userMiddleware)->add($checkToken)->add($checkAjax);

$app->get('/settings', 'SettingsController:settings')->add($userMiddleware);
$app->get('/settings/modes', 'SettingsController:modes')->add($userMiddleware);
$app->post('/settings/modes/sync', 'SettingsController:syncModes')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/addcomplex', 'SettingsController:addComplex')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/editcomplex', 'SettingsController:editComplex')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/delcomplex', 'SettingsController:deleteComplex')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->get('/settings/mailing', 'SettingsController:mailingList')->add($userMiddleware);
$app->post('/settings/mailing/add', 'SettingsController:addContact')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/mailing/del', 'SettingsController:deleteContact')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->get('/settings/users', 'SettingsController:users')->add($userMiddleware);
$app->post('/settings/users/setrole', 'SettingsController:setUserRole')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/users/delete', 'SettingsController:deleteUser')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/setlogger', 'SettingsController:setLogger')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/settings/testing', 'SettingsController:testConnect')->add($userMiddleware)->add($checkToken)->add($checkAjax);

$app->post('/system/info', 'SystemController:info')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/system/start', 'SystemController:start')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/system/control', 'SystemController:control')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/system/telemetry', 'SystemController:telemetry')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/system/streamcheck', 'SystemController:streamcheck')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/system/servstart', 'SystemController:startServices')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/system/servstop', 'SystemController:stopServices')->add($userMiddleware)->add($checkToken)->add($checkAjax);

$app->get('/diagnostic', 'DiagController:index')->add($userMiddleware);
$app->post('/diagnostic/step1', 'DiagController:step1')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/diagnostic/step2', 'DiagController:step2')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/diagnostic/step3', 'DiagController:step3')->add($userMiddleware)->add($checkToken)->add($checkAjax);
$app->post('/diagnostic/step4', 'DiagController:step4')->add($userMiddleware)->add($checkToken)->add($checkAjax);

$app->post('/main/works', 'MapController:mapworks')->add($checkToken)->add($checkAjax);
$app->post('/main/refresh', 'MapController:refresh')->add($checkToken)->add($checkAjax);
$app->post('/main/detects', 'MapController:detects')->add($checkToken)->add($checkAjax);
$app->post('/main/streamcheck', 'SystemController:streamcheck')->add($checkToken)->add($checkAjax);
$app->get('/main/detect/{detect:[0-9]+}', 'IndexController:viewDetect');
$app->post('/main/getimage', 'MapController:getImage')->add($checkToken)->add($checkAjax);
$app->post('/main/departed', 'MapController:departed')->add($checkToken)->add($checkAjax);
