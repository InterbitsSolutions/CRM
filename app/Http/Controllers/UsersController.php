<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRoles;
use App\Models\UserActionlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;


class UsersController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /* For Add Users */
    public function addUser() {
        $roles = UserRoles::get();
        if (!empty($_POST)) {
            $checkUserExist = User::where('email', "=", $_POST['email'])->first();
            try {
                if (empty($checkUserExist)) {
                    $addUSer = new User();
                    $addUSer->name = $_POST['name'];
                    $addUSer->email = $_POST['email'];
                    $addUSer->password = Hash::make($_POST['password']);
                    $addUSer->role = $_POST['role'];
                    $addUSer->is_admin = 1;
                    $addUSer->save();
                    $message = " User Added successfully!";
                    flash($message);
                    return Redirect::to("list-users");
                } else {
                    $message = "User with this email: " . $_POST['email'] . " already exist in system, please create with another name!";
                    flash($message);
                    return Redirect::to("list-users");
                }
            } catch (\Aws\S3\Exception\S3Exception $e) {
                $message = $e->getMessage();
                $errorMessage = 'There is some error while adding Users. Please try again later!';
                flash($errorMessage, "danger");
                return Redirect::to('list-users');
            }
        }
        return view('Users.addUser', compact('roles'));
    }

    /* For List Users */
    public function listUser() {
        $users = User::get();
        $roles = UserRoles::all()->pluck('role_name', 'id')->all();
        $login_user_id = Auth::user()->id;
        return view('Users.viewUsers', compact('users', 'roles', 'login_user_id'));
    }
    /* For Delete User */
    public function deleteUser($id) {
        if (!empty($id)) {
            if ($id == 1) {
                flash('This Super Admin cannot be deleted!');
            } else {
                $whereArray = array('id' => $id);
                User::where($whereArray)->delete();
                flash('User deleted successfully!');
            }
            return Redirect::to("list-users");
        }
    }
    /* For Edit User */
    public function editUser($id) {

        $roles = UserRoles::get();
        if (!empty($_POST)) {
            try {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $hidden_email = $_POST['hidden_email'];
                if ($id == 1) {
                    flash('This Super Admin cannot be updated!');
                    return Redirect::to('list-users');
                } else {

                    if ($_POST['email'] != $_POST['hidden_email']) {
                        $checkUserExist = User::where('email', "=", $_POST['email'])->first();

                        if (empty($checkUserExist)) {
                            $adduser = User::find($id);
                            $adduser->name = $name;
                            $adduser->email = $email;
                            $adduser->role = $role;
                            $adduser->save();
                            $message = " User Updated successfully!";
                            flash($message);
                            return Redirect::to("list-users");
                        } else {
                            $message = "User with this email: " . $_POST['email'] . " already exist in system, please create with another name!";
                            flash($message);
                            return Redirect::to("list-users");
                        }
                    } else {
                        $adduser = User::find($id);
                        $adduser->name = $name;
                        $adduser->email = $email;
                        $adduser->role = $role;
                        $adduser->save();
                        $message = " User Updated successfully!";
                        flash($message);
                        return Redirect::to("list-users");
                    }
                }
            } catch (\Aws\S3\Exception\S3Exception $e) {
                $message = $e->getMessage();
                $errorMessage = 'There is some error while Updating User. Please try again later!';
                flash($errorMessage, "danger");
                return Redirect::to('list-users');
            }
        }
        $user = User::findOrFail($id);
        return view('Users.editUsers', compact('user', 'roles'));
    }

    public function changePassword() {
        if (!empty($_POST)) {
            $userid = $_POST['userid'];
            $password = $_POST['password'];

            if ($userid == 1) {

                $message = "Success ";
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                flash('The password of this Super Admin cannot be updated!');
                return json_encode($return);
            } else {
                $adduser = User::find($userid);
                $adduser->password = Hash::make($password);
                $adduser->save();

                $message = "Success ";
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                flash("Password updated successfully!");
                return json_encode($return);
            }
        } else {
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }
    /*User Log Section Starts*/
    public function userLog() {
        $userDetails = DB::select("Select users.name as name,config_auth.aws_name as aws_name,user_action_log.id as id,user_action_log.bucket_name as bucket_name,user_action_log.action_performed as action_performed,user_action_log.created_at as created from user_action_log,config_auth,users 
WHERE user_action_log.user_id = users.id AND user_action_log.aws_id = config_auth.id order by user_action_log.created_at DESC");
        return view('Users.userLog', compact('userDetails'));
    }

    public function deletelog($id) {   
        if (!empty($id)) {
            $whereArray = array('id' => $id);
            UserActionlog::where($whereArray)->delete();
            flash('Log deleted successfully!');
            return Redirect::to("user-log");
        }
    }

    public function deleteMultipleLog() {
        if (!empty($_POST)) {
            $logs = $_POST['logs'];
            foreach ($logs as $log) {
                $whereArray = array('id' => $log);
                UserActionlog::where($whereArray)->delete();
            }
            $message = "Success ";
            $return = array(
                'type' => 'success',
                'message' => $message,
            );
            flash("Log Deleted successfully!");
            return json_encode($return);
        }
    }
    /*User Log Ends*/

}
