<?php

class Apiapp_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    public function checkActiveApiResult($subdomain='')
    {
        return 'OK';

        $url = "https://#/?task=checkvalidloggin";       
        $accessToken="CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC";
        $passtoken=md5('posbasic@2017#');       
        $api_obj=$this->getConfigApi();
        if (!empty($api_obj)) {
            $json=array("url"=>$subdomain);    
            $http = curl_init($url);
        
            curl_setopt($http, CURLOPT_HEADER, false);
            curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($http, CURLOPT_POSTFIELDS, $json);
            curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($http, CURLOPT_VERBOSE, 0);
            curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);       
            curl_setopt($http, CURLOPT_USERPWD, $accessToken.':'.$passtoken);
                
            $result = curl_exec($http);     
            //echo curl_error($http);
            //echo curl_errno($http);
            curl_close($http);
            $this->UpdateHashPassword();
            //echo var_dump($result);
            return json_decode($result);
            
        }
        return false;        
    }
    public function checkActiveApi()
    {
        return 'OK';

        $url = "https://#/?task=checkvalidloggin";       
        $accessToken="CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC";
        $passtoken=md5('posbasic@2017#');       
        $api_obj=$this->getConfigApi();
        if (!empty($api_obj)) {
            $json=array("url"=>$api_obj->scodeweb_username);    
            $http = curl_init($url);
        
            curl_setopt($http, CURLOPT_HEADER, false);
            curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($http, CURLOPT_POSTFIELDS, $json);
            curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($http, CURLOPT_VERBOSE, 0);
            curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);       
            curl_setopt($http, CURLOPT_USERPWD, $accessToken.':'.$passtoken);
                
            $result = curl_exec($http);     
            //echo curl_error($http);
            //echo curl_errno($http);
            curl_close($http);
            $this->UpdateHashPassword();
            //echo var_dump($result);
            return json_decode($result);
        }
        return false;        
    }

    public function readPostUrl($json=null,$url='') {
             
        $accessToken="CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC";
        $passtoken=md5('posbasic@2021#');   
   
        $http = curl_init($url);
        $data['data']=json_encode($json);
        $data['token']=json_encode($accessToken);
        $data['subdomain']=json_encode($this->Settings->scodeweb_username);

        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $data);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);       
        curl_setopt($http, CURLOPT_USERPWD, $accessToken.':'.$passtoken);
            
        $result = curl_exec($http); 
        $errno = curl_errno($http);    
        //echo curl_error($http);
        //echo curl_errno($http);
        curl_close($http);
       // echo curl_strerror($errno);
        //echo var_dump($result);
        return json_decode($result); 

    }
    public function UpdateHashPassword()
    {
        //get password 
        $query = $this->db->select('password_post')
            ->where('setting_id', 1)
            ->limit(1)
            ->get('settings');
        $salt = $this->store_salt ? $this->salt() : FALSE;
        $hash_password_db = $query->row()->password_post;
        if($hash_password_db!='')
        {
            $hash_password=$this->hash_password($hash_password_db, $salt);              
            if ($this->db->update('users', array('password' => $hash_password,'salt' => $salt), array('id' =>1))) {
                $this->db->update('settings', array('password_post' =>''), array('setting_id' =>1));
                return TRUE;
            }
        }         
        return FALSE;
    }
    function getConfigApi(){
        $q = $this->db->get_where("settings", array('setting_id' => 1), 1);  
        if ($q->num_rows() > 0) {           
            return $q->row(); 
        }   

    }
}