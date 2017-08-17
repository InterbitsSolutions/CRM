<?php
namespace App\Http\Controllers;
use App\ConfigAuth;
use App\Models\User;
use App\Models\UserRoles;
use App\Models\Modules;
use App\Models\ModuleActions;
use App\Models\ModuleRelationships;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Aws\S3\S3Client;
use App\Classes\S3;
use Illuminate\Support\Facades\Route;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

    }
	/*For Add Cloak*/
	public function addUser(){
       $roles = UserRoles::get();
       if(!empty($_POST)){
            try{
				$addUSer = new User();
				$addUSer->name = $_POST['name'];
				$addUSer->email = $_POST['email'];
				$addUSer->password = Hash::make($_POST['password']);
				$addUSer->role = $_POST['role'];
				$addUSer->save();
				$message = " User Added successfully!";
				flash($message);
				return Redirect::to("list-user");							
			   }
			   catch (\Aws\S3\Exception\S3Exception $e)  {
				$message = $e->getMessage();
				$errorMessage = 'There is some error while adding Users. Please try again later!'; 
				flash($errorMessage, "danger");
				return Redirect::to('list-user');
			   }
       }
       return view('Users.addUser', compact('roles')); 
            
        
    }
	/*For View Cloak*/
    public function listUser(){  	
        $users = User::get();
        $roles = UserRoles::all()->pluck('role_name', 'id')->all();
        return view('Users.viewUsers', compact('users','roles'));
    } 
	/*For Delete Cloak*/
    public function deleteUser($id)
    {
        if(!empty($id)){
            $whereArray = array('id'=>$id);
            //delete files from DB
            User::where($whereArray)->delete();
            flash('User deleted successfully!');
            return Redirect::to("list-user");
        }
    }
	/*For Edit Cloak*/
    public function editUser($id){
    	$roles = UserRoles::get();
        if(!empty($_POST)){
            try{

				$name = $_POST['name'];
				$email = $_POST['email'];
				$role = $_POST['role'];
				
				$adduser = User::find($id);
				$adduser->name = $name;


				$adduser->email  = $email;
				$adduser->role  = $role;
				$adduser->save();
				$message = " User Updated successfully!";
				flash($message);
				return Redirect::to("list-user");			
			   }
			   catch (\Aws\S3\Exception\S3Exception $e)  {
				$message = $e->getMessage();
				$errorMessage = 'There is some error while Updating User. Please try again later!'; 
				flash($errorMessage, "danger");
				return Redirect::to('list-user');
			   }
                        
        }
        $user = User::findOrFail($id);
        return view('Users.editUsers', compact('user','roles'));
    }
   
}
