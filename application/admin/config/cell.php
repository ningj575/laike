<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    'system_menu_index' => [
            ['field' => 'id', 'title' => 'ID', 'fixed' => 'left', 'width' => 60],
            ['title' => 'permission_name', 'templet' => '#title',],
            ['field' => 'css', 'title' => 'style', 'templet' => '#styleCss'],
            ['field' => 'name', 'title' => 'node', 'width' => 280],
            ['field' => 'param', 'title' => 'url_parameter'],
            ['field' => 'status', 'title' => 'menu_status', 'templet' => '#buttonTpl'],
            ['field' => 'c_time', 'title' => 'add_time'],
            ['field' => 'sort', 'title' => 'sort', 'edit' => 'text', 'width' => 80],
            ['title' => 'action', 'toolbar' => '#table-useradmin-admin', 'align' => 'center'],
    ],
    'system_role_index' => [
            ['field' => 'id', 'title' => 'ID', 'fixed' => 'left', 'sort' => true],
            ['title' => 'role_name', 'field' => 'title',],
            ['field' => 'status', 'title' => 'status', 'templet' => '#buttonTpl'],
            ['field' => 'c_time', 'title' => 'add_time'],
            ['field' => 'u_time', 'title' => 'update_time'],
            ['title' => 'action', 'toolbar' => '#table-useradmin-admin', 'align' => 'center', 'minWidth' => 250],
    ],
    'system_admin_index' => [
            ['type' => 'checkbox'],
            ['field' => 'id', 'title' => 'ID', 'sort' => true, 'width' => 80],
            ['field' => 'admin_name', 'title' => 'admin_name', 'width' => 200],
            ['field' => 'title', 'title' => 'admin_role', 'width' => 200],
            ['field' => 'loginnum', 'title' => 'loginnum', 'width' => 150],
            ['field' => 'last_login_ip', 'title' => 'last_login_ip', 'width' => 200],
            ['field' => 'last_login_time', 'title' => 'last_login_time', 'width' => 200],
            ['field' => 'real_name', 'title' => 'real_name', 'width' => 200],
            ['field' => 'status', 'title' => 'status', 'templet' => '#buttonTpl', 'width' => 120],
//            ['field' => 'uid_key', 'title' => '用户标识','width' => 220],
            ['title' => 'action', 'toolbar' => '#table-useradmin-admin'],
    ],
    
];
