<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/JWT.php';
require APPPATH . '/libraries/ExpiredException.php';
require APPPATH . '/libraries/BeforeValidException.php';
require APPPATH . '/libraries/SignatureInvalidException.php';
require APPPATH . '/libraries/JWK.php';
require APPPATH . '/libraries/REST_Controller.php';


use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;

class Sekolah extends REST_Controller
{

    function configToken(){
        $cnf['exp'] = 3600; //milisecond
        $cnf['secretkey'] = '2212336221';
        return $cnf;        
    }

    public function getToken_post(){               
        $exp = time() + 3600;
        $token = array(
            "iss" => 'apprestservice',
            "aud" => 'pengguna',
            "iat" => time(),
            "nbf" => time() + 10,
            "exp" => $exp,
            "data" => array(
                "username" => $this->input->post('username'),
                "password" => $this->input->post('password')
            )
        );       
    
        $jwt = JWT::encode($token, $this->configToken()['secretkey'], 'HS256');
        $output = [
            'status' => 200,
            'message' => 'Berhasil login',
            "token" => $jwt,                
            "expireAt" => $token['exp']
        ];      
    $data = array('kode'=>'200', 'pesan'=>'token', 'data'=>array('token'=>$jwt, 'exp'=>$exp));
    $this->response($data, 200 );       
}

public function authtoken(){
    $secret_key = $this->configToken()['secretkey']; 
    $token = null; 
    $authHeader = $this->input->request_headers()['Authorization'];  
    $arr = explode(" ", $authHeader); 
    $token = $arr[1];        
    if ($token){
        try{
            $decoded = JWT::decode($token, $this->configToken()['secretkey'], array('HS256'));          
            if ($decoded){
                return 'benar';
            }
        } catch (ExpiredException $e) {
            $result = array('pesan'=>'Kode Signature Tidak Sesuai');
            return 'salah';
            
        }
    }       
}

    
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->database();
    }

    //Menampilkan data kontak


    public function index_get(){             
        
        if ($this->authtoken() == 'salah'){
            return $this->response(array('kode'=>'401', 'pesan'=>'signature tidak sesuai', 'data'=>[]), '401');
            die();
        }
        $this->db->select('*');        
        $data = array ('data'=>$this->db->get('telepon')->result());        
        $this->response($data, 200 );
    }



    // function index_get()
    // {

    //     if ($this->authtoken() == 'salah'){
    //         return $this->response(array('kode'=>'401', 'pesan'=>'signature tidak sesuai', 'data'=>[]), '401');
    //         die();
    //     }
    //     $id = $this->get('id');
    //     if ($id == '') {
    //         $kontak = $this->db->get('telepon')->result();
    //     } else {
    //         $this->db->where('id', $id);
    //         $kontak = $this->db->get('telepon')->result();
    //     }
    //     $this->response($kontak, 200);
    // }


    //Mengirim atau menambah data kontak baru
    function index_post()
    {
        $data = array(
            'id' => $this->post('id'),
            'nama' => $this->post('nama'),
            'nomor' => $this->post('nomor')
        );
        $insert = $this->db->insert('telepon', $data);
        if ($insert) {
            $this->response($data, 200);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

    ///Memperbarui data kontak yang telah ada
    function index_put()
    {
        $id = $this->put('id');
        $data = array(
            'id' => $this->put('id'),
            'nama' => $this->put('nama'),
            'nomor' => $this->put('nomor')
        );
        $this->db->where('id', $id);
        $update = $this->db->update('telepon', $data);
        if ($update) {
            $this->response($data, 200);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

    //Menghapus salah satu data kontak
    function index_delete()
    {
        $id = $this->delete('id');
        $this->db->where('id', $id);
        $delete = $this->db->delete('telepon');
        if ($delete) {
            $this->response(array('status' => 'success'), 201);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }
}
?>