<?php
namespace model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Index
 * @author zh 2681674909@qq.com 2015-4-14 
 */
class IndexModel extends Model {
   
    public function index(){
        echo time();
        return 'hello<Br/>';
    }
}
