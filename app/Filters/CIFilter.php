<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;

class CIFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null){
        if( $arguments[0] == 'guest'){
            if(CIAuth::check()){
                return redirect()->route('home');
            }
        }

        if( $arguments[0] == 'auth'){
            if(!CIAuth::check()){
                return redirect()->route('login.form')->with('fail','You must be logged in first!');
            }
        }

        if (in_array('admin', $arguments) && (!CIAuth::check() || CIAuth::user()->role !== 'admin')) {
            return redirect()->route('login.form')->with('fail', 'You must be an admin to access this page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null){}
}