<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Academic Free License version 3.0
 *
 * This source file is subject to the Academic Free License (AFL 3.0) that is
 * bundled with this package in the files license_afl.txt / license_afl.rst.
 * It is also available through the world wide web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "welcome";
$route['404_override'] = '';

$route['v1/user/(:any)/ftpinfo'] = 'pctool/ftpinfo/$1';
$route['v1/user/(:any)/(followers|followees)'] ='person/$2/$1';

$route['v1/user/(:num)/applyAuthor'] = 'person/apply_author/$1';
$route['v1/user/(:num|me)'] = 'person/user/$1';

$route['v1/users'] = 'person/users';
$route['v1/(magazine|element|author)/(:num)/(like|cancelLike)'] = 'person/like/$1/$2/$3';

$route['v1/pctool/(.*)'] = 'pctool/$1';
$route['v1/auth/(.*)'] = 'auth/$1';

$route['v1/comment/(.*)'] = 'comment/$1';
$route['v1/(magazine)/(:num)/comments'] = 'comment/comments/$1/$2';

$route['v1/(magazine|element)/(:num)/(like|cancelLike)'] = 'person/like/$1/$2/$3';
$route['v1/user/(:num)/follow'] = 'person/follow/$1/1';
$route['v1/user/(:num)/unfollow'] = 'person/follow/$1/0';

$route['v1/user/(:num)/magazines/(like|published|unpublished)'] = 'mag/user_magazines/$1/$2';
$route['v1/mag/(.*)'] = 'mag/$1';
$route['v1/user/(:any)/tags/own'] = 'mag/user_tags/$1/$2';
$route['v1/magazine/(:num)'] = 'mag/magazine/$1';
$route['v1/magazine/(:num)/(pub)'] = 'mag/magazine/$1/$2';
$route['v1/magazines'] = 'mag/magazines';
$route['v1/element/(:num)'] = 'mag/element/$1';
$route['v1/elements'] = 'mag/elements';
$route['v1/user/(:num)/elements/like'] = 'mag/user_liked_elements/$1';
$route['v1/cates'] = 'mag/cates';
$route['v1/tags'] = 'mag/tags';

$route['v1/recommendation/magazines/cate/(:num)'] = 'recommendation/by_category/$1';
$route['v1/recommendation/magazines/maylike'] = 'recommendation/maylike';
$route['v1/recommendation/(.*)'] = 'recommendation/$1';

$route['v1/ltapp/ads/(image|text)/(indexmaga|indexelement)']='ltapp/ads/$1/$2';

$route['v1/sns/oauthzieurl'] = 'sns/oauthzieurl';
$route['v1/sns/callback'] = 'sns/callback';
$route['v1/sns/unbind'] = 'sns/unbind';
$route['v1/sns/bind'] = 'sns/bind';
$route['v1/sns/bindinfo'] = 'sns/bindinfo';
$route['v1/sns/share'] = 'sns/share';

$route['v1/user/(:num)/activities'] = 'msg/msg_list/$1';
$route['v1/activity/(:num)'] = 'msg/msg_delput/$1';
$route['v1/activity/add'] = 'msg/msg_add';

$route['v1/ltapp/ads/(image|text|magazine|element)/(.*)']='ad/ad_list/$1/$2';

$route['v1/user/changepwd'] = 'person/change_password';

$route['v1/stat/magread/(:any)'] = 'stats/magread/$1';

$route['v1/magclient/verinfo/(android|ios|pctool)'] = 'magclient/get_ver_info/$1';

$route['v1/magdl/(:num)/dist/(.*)'] = 'magdl/dist/$1/$2';
$route['v1/magdl/(:num)/resources'] = 'magdl/resources/$1/$2';

$route['v1/search/suggest'] = 'search/suggestion';
$route['v1/search/hotquery'] = 'search/hotquery';

$route['v1/user/checkexists'] = 'person/checkexists';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
